<?php

namespace QIT_CLI\Commands;

use QIT_CLI\App;
use QIT_CLI\Commands\CustomTests\RunE2ECommand;
use QIT_CLI\PreCommand\Interfaces\LocalTestCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\is_windows;

class RunActivationTestCommand extends QITCommand implements LocalTestCommand {
	protected static $defaultName = 'run:activation'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/**
	 * LocalTestCommand interface implementation.
	 */
	public function get_environment_name(): string {
		return $this->input->getOption( 'environment' ) ?? 'default';
	}

	public function should_prepare_environment(): bool {
		return true;
	}

	public function get_test_type(): string {
		return 'activation';
	}

	public function get_test_profile(): string {
		return $this->input->getOption( 'profile' ) ?? 'default';
	}

	protected function configure(): void {
		parent::configure();

		$this->setDescription( 'Run activation tests' )
			->addArgument( 'woo_extension', InputArgument::REQUIRED, 'Extension slug or ID' )
			->addOption( 'source', null, InputOption::VALUE_OPTIONAL, 'Local source path' )
			->addOption( 'zip', null, InputOption::VALUE_OPTIONAL, 'Local ZIP file (deprecated, use --source)' )
			->addOption( 'wp', null, InputOption::VALUE_OPTIONAL, 'WordPress version' )
			->addOption( 'woo', null, InputOption::VALUE_OPTIONAL, 'WooCommerce version' )
			->addOption( 'php_version', null, InputOption::VALUE_OPTIONAL, 'PHP version' )
			->addOption( 'plugin', 'p', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Additional plugins', [] )
			->addOption( 'theme', 't', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Additional themes', [] )
			->addOption( 'php_extension', 'x', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'PHP extensions', [] )
			->addOption( 'object_cache', 'o', InputOption::VALUE_NONE, 'Enable Object Cache' )
			->addOption( 'dependencies_mode', null, InputOption::VALUE_OPTIONAL, 'Dependencies mode', 'activate' )
			->addOption( 'json', 'j', InputOption::VALUE_NEGATABLE, 'JSON output', false )
			->addOption( 'wait', 'w', InputOption::VALUE_NEGATABLE, '(Deprecated)', false )
			->addOption( 'ignore-fail', 'i', InputOption::VALUE_NEGATABLE, '(Deprecated)', false );
	}

	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		if ( is_windows() ) {
			$output->writeln( '<comment>To run Activation Tests on Windows, please use WSL.</comment>' );

			return Command::FAILURE;
		}

		// Get the E2E command
		$run_e2e_command = App::make( RunE2ECommand::class );
		$run_e2e_command->setApplication( $this->getApplication() );

		// Build options for E2E command
		$run_e2e_options = [
			'command'                   => 'run:e2e',
			'woo_extension'             => $input->getArgument( 'woo_extension' ),
			// The activation test is a test package from WooCommerce
			'test'                      => 'woocommerce/activation:stable', // Or could be determined by woo version
			// Pass through options
			'--profile'                 => $this->get_test_profile(),
			'--environment'             => $this->get_environment_name(),
			// Skip automatic activation for activation tests
			'--skip_activating_plugins' => true,
			'--skip_activating_themes'  => true,
			// No retries for activation tests
			'--pw_options'              => '--retries=0',
		];

		// Handle deprecated --zip option
		$zip = $input->getOption( 'zip' );
		if ( $zip ) {
			if ( $input->getOption( 'source' ) ) {
				$output->writeln( '<error>Cannot use both --zip and --source options. Use only --source.</error>' );

				return Command::FAILURE;
			}
			$run_e2e_options['--source'] = $zip;
		} else {
			$source = $input->getOption( 'source' );
			if ( $source ) {
				$run_e2e_options['--source'] = $source;
			}
		}

		// Pass through other options
		foreach ( [ 'wp', 'woo', 'php_version', 'object_cache', 'dependencies_mode' ] as $option ) {
			$value = $input->getOption( $option );
			if ( $value ) {
				$run_e2e_options[ "--{$option}" ] = $value;
			}
		}

		// Pass through array options
		foreach ( [ 'plugin', 'theme', 'php_extension' ] as $option ) {
			$values = $input->getOption( $option );
			if ( $values ) {
				$run_e2e_options[ "--{$option}" ] = $values;
			}
		}

		// Set verbosity
		if ( $output->isVerbose() ) {
			$run_e2e_options['--verbose'] = true;
		} elseif ( $output->isVeryVerbose() ) {
			$run_e2e_options['--very-verbose'] = true;
		}

		// JSON output
		if ( $input->getOption( 'json' ) ) {
			$run_e2e_options['--json'] = true;
		}

		// Mark that we're running an activation test
		App::setVar( 'QIT_ACTIVATION_TEST', 'yes' );

		// Run the E2E command
		$exit_code = $run_e2e_command->run(
			new ArrayInput( $run_e2e_options ),
			$output
		);

		// Handle deprecated --ignore-fail option
		if ( $input->getOption( 'ignore-fail' ) ) {
			return Command::SUCCESS;
		}

		return $exit_code;
	}
}
