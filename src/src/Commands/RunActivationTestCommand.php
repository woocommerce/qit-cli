<?php

namespace QIT_CLI\Commands;

use QIT_CLI\App;
use QIT_CLI\Commands\CustomTests\RunE2ECommand;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\is_windows;

/**
 * Runs the official activation test‑package against the current SUT.
 *
 * Behavioural differences compared with run:e2e:
 *   • The test‑package is *always* `woocommerce/activation:stable`
 *     (cannot be overridden – the CLI flag is injected programmatically).
 *   • Plugins / themes are not activated inside the container.
 *   • Playwright retries are disabled.
 *   • Deprecated options --wait / --ignore-fail still exist for BC.
 */
class RunActivationTestCommand extends RunE2ECommand {

	protected static $defaultName = 'run:activation'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase
	protected string $test_type   = 'activation';

	/******************************************************************
	 * CLI definition
	 *****************************************************************/
	protected function configure(): void {
		parent::configure();
		$this->setDescription( 'Run activation tests' )
			// Deprecated flags kept for backwards‑compat only
			->addOption( 'wait', 'w', InputOption::VALUE_NEGATABLE, '(Deprecated – ignored)', false )
			->addOption( 'ignore-fail', 'i', InputOption::VALUE_NEGATABLE, '(Deprecated)', false );
	}

	/******************************************************************
	 * Execution
	 *****************************************************************/
	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		/** @var \QIT_CLI\QITInput $input */

		/* ─ special path for unit‑tests that only inspect config parsing ─ */
		if ( getenv( 'QIT_SELF_TEST' ) === 'remote_test' ) {
			$profile_cfg = $this->get_current_test_profile( $this->test_type, $this->get_test_profile() );
			$output->write( json_encode( $profile_cfg, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
			return self::SUCCESS;
		}

		/* ─ platform guard ─ */
		if ( is_windows() ) {
			$output->writeln( '<comment>To run Activation Tests on Windows, please use WSL.</comment>' );
			return self::FAILURE;
		}

		/****************************************************************
		 * Inject activation‑specific defaults BEFORE delegating to parent
		 */
		// Determine WooCommerce version to match test package version
		$woo_version = 'stable';
		if ( $input->hasOption( 'woo' ) ) {
			$woo_version = $input->getOption( 'woo' );
		}

		// Set test package - use local path for development
		$local_test_package = '/home/lucas/automattic/qit/qit-manager/ci/tests/activation/test-package';
		if ( is_dir( $local_test_package ) ) {
			// Use local test package for development
			$input->setOption( 'test-package', [ $local_test_package ] );
		} else {
			// Fall back to remote package
			$input->setOption( 'test-package', [ "woocommerce/activation:$woo_version" ] );
		}
		$input->setOption( 'skip_activating_plugins', true );
		$input->setOption( 'skip_activating_themes', true );
		$input->setOption( 'pw_options', '--retries=0' );

		// Flag for anything downstream that needs to know we are in activation mode
		App::setVar( 'QIT_ACTIVATION_TEST', 'yes' );

		/****************************************************************
		 * Delegate to parent implementation (now config‑aware)
		 */
		$exit_code = parent::doExecute( $input, $output );

		/****************************************************************
		 * Handle deprecated --ignore-fail flag (only if user typed it)
		 */
		if ( $input->hasOption( 'ignore-fail' ) && $input->getOption( 'ignore-fail' ) ) {
			return self::SUCCESS;            // Treat any result as success
		}

		return $exit_code;
	}
}
