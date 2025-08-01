<?php

namespace QIT_CLI\Commands;

use QIT_CLI\App;
use QIT_CLI\Commands\CustomTests\RunE2ECommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\is_windows;

class RunActivationTestCommand extends RunE2ECommand {

	protected static $defaultName = 'run:activation'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase
	protected string $test_type = 'activation';

	protected function configure(): void {
		parent::configure();

		// Override command-specific details
		$this->setDescription( 'Run activation tests' );

		// Add activation-specific deprecated options
		$this->addOption( 'wait', 'w', InputOption::VALUE_NEGATABLE, '(Deprecated)', false )
			->addOption( 'ignore-fail', 'i', InputOption::VALUE_NEGATABLE, '(Deprecated)', false );
	}

	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		// Handle QIT_SELF_TEST for remote test configuration testing
		if ( getenv( 'QIT_SELF_TEST' ) === 'remote_test' ) {
			// Get resolved test configuration
			$test_config = $this->get_current_test_profile( $this->test_type, $this->get_test_profile() );
			$output->write( json_encode( $test_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
			return self::SUCCESS;
		}

		if ( is_windows() ) {
			$output->writeln( '<comment>To run Activation Tests on Windows, please use WSL.</comment>' );
			return self::FAILURE;
		}

		// Force activation test package - users cannot override this
		$input->setOption( 'test-package', [ 'woocommerce/activation:stable' ] );

		// Set activation-specific options
		$input->setOption( 'skip_activating_plugins', true );
		$input->setOption( 'skip_activating_themes', true );
		$input->setOption( 'pw_options', '--retries=0' );

		// Mark that we're running an activation test
		App::setVar( 'QIT_ACTIVATION_TEST', 'yes' );

		// Call parent E2E execution
		$exit_code = parent::doExecute( $input, $output );

		// Handle deprecated --ignore-fail option
		if ( $input->getOption( 'ignore-fail' ) ) {
			return self::SUCCESS;
		}

		return $exit_code;
	}
}
