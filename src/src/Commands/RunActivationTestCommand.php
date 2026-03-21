<?php

namespace QIT_CLI\Commands;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Commands\RunE2ECommand;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Runs the official activation test‑package against the current SUT.
 *
 * Behavioural differences compared with run:e2e:
 *   • The test‑package is *always* `woocommerce/activation:stable`
 *     (cannot be overridden – the CLI flag is injected programmatically).
 *   • Plugins / themes are not activated inside the container.
 *   • Playwright retries are disabled.
 */
class RunActivationTestCommand extends RunE2ECommand {

	protected static $defaultName = 'run:activation'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase
	protected string $test_type   = 'activation';

	/******************************************************************
	 * CLI definition
	 *****************************************************************/
	protected function configure(): void {
		parent::configure();
		$this->setDescription( 'Run activation tests' );

		$cache           = App::make( Cache::class );
		$extension_sets  = $cache->get_manager_sync_data( 'extension_sets' );
		$available_sets  = is_array( $extension_sets ) ? implode( ', ', array_keys( $extension_sets ) ) : '';
		$set_description = '(Optional) The predefined set of extensions to include in the test.';
		if ( ! empty( $available_sets ) ) {
			$set_description .= sprintf( ' <comment>[possible values: %s]</comment>', $available_sets );
		}

		$this->addOption(
			'extension_set',
			null,
			InputOption::VALUE_OPTIONAL,
			$set_description
		);
	}

	/******************************************************************
	 * Execution
	 *****************************************************************/
	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		/** @var \QIT_CLI\QITInput $input */

		/* ─ special path for unit‑tests that only inspect config parsing ─ */
		if ( getenv( 'QIT_SELF_TEST' ) === 'remote_test' ) {
			$profile_cfg = $this->get_current_test_profile( $this->test_type, $this->get_test_profile() );

			// Merge CLI overrides into profile config (same as CreateRunCommands step 2)
			$cli_overrides = [
				'wordpress_version',
				'woocommerce_version',
				'php_version',
			];
			foreach ( $cli_overrides as $opt_name ) {
				if ( $input->hasOption( $opt_name ) && $input->getOption( $opt_name ) !== null ) {
					$profile_cfg[ $opt_name ] = $input->getOption( $opt_name );
				}
			}

			$output->write( json_encode( $profile_cfg, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
			return self::SUCCESS;
		}

		// Validate profile exists if explicitly provided
		$profile_name = $input->get_profile_name();
		// This will throw an exception if the profile doesn't exist
		$this->get_current_test_profile( $this->test_type, $profile_name );

		/****************************************************************
		 * Resolve --extension_set into --plugin options
		 */
		$extension_set_name = $input->getOption( 'extension_set' );
		if ( ! empty( $extension_set_name ) ) {
			$cache          = App::make( Cache::class );
			$extension_sets = $cache->get_manager_sync_data( 'extension_sets' );

			if ( ! is_array( $extension_sets ) || ! isset( $extension_sets[ $extension_set_name ] ) ) {
				$available = is_array( $extension_sets ) ? implode( ', ', array_keys( $extension_sets ) ) : 'none';
				$output->writeln( sprintf( '<error>Unknown extension set "%s". Available sets: %s</error>', $extension_set_name, $available ) );
				return self::INVALID;
			}

			/** @var array<string> $set_plugins */
			$set_plugins     = $extension_sets[ $extension_set_name ];
			$current_plugins = $input->getOption( 'plugin' ) ?: [];
			$current_plugins = is_array( $current_plugins ) ? $current_plugins : [];
			$merged_plugins  = array_unique( array_merge( $current_plugins, $set_plugins ) );
			$input->setOption( 'plugin', array_values( $merged_plugins ) );
		}

		/****************************************************************
		 * Inject activation‑specific defaults BEFORE delegating to parent
		 */
		// Always use 'latest' version for activation test package
		$input->setOption( 'test-package', [ 'woocommerce/activation:latest' ] );
		$input->setOption( 'skip_activating_plugins', true );
		$input->setOption( 'skip_activating_themes', true );

		// Flag for anything downstream that needs to know we are in activation mode
		App::setVar( 'QIT_ACTIVATION_TEST', 'yes' );

		/****************************************************************
		 * Delegate to parent implementation (now config‑aware)
		 */
		$exit_code = parent::doExecute( $input, $output );

		return $exit_code;
	}
}
