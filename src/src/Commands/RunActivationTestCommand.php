<?php

namespace QIT_CLI\Commands;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\OptionReuseTrait;
use QIT_CLI\Commands\CustomTests\RunE2ECommand;
use QIT_CLI\RequestBuilder;
use QIT_CLI\Tunnel\TunnelRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use function QIT_CLI\get_manager_url;

class RunActivationTestCommand extends Command {
	use OptionReuseTrait;

	protected static $defaultName = 'run:activation'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure() {
		$this
			->setDescription( 'Run Activation tests.' )
			->setHelp( 'Run the Woo activation test against a given extension.' )
			->addArgument( 'woo_extension', InputArgument::REQUIRED, 'A WooCommerce Extension Slug or Marketplace ID.' )
			->reuseOption( RunE2ECommand::getDefaultName(), 'source' )
			->reuseOption( RunE2ECommand::getDefaultName(), 'wp' )
			->reuseOption( RunE2ECommand::getDefaultName(), 'woo' )
			->reuseOption( RunE2ECommand::getDefaultName(), 'php_version' )
			->reuseOption( RunE2ECommand::getDefaultName(), 'object_cache' )
			->reuseOption( RunE2ECommand::getDefaultName(), 'plugin' )
			->reuseOption( RunE2ECommand::getDefaultName(), 'ui' )
			->reuseOption( RunE2ECommand::getDefaultName(), 'no_upload_report' )
			->reuseOption( RunE2ECommand::getDefaultName(), 'notify' )
			->reuseOption( RunE2ECommand::getDefaultName(), 'php_extension' )
			->reuseOption( RunE2ECommand::getDefaultName(), 'tunnel' );

		$this->addOption(
			'json',
			'j',
			InputOption::VALUE_NEGATABLE,
			'(Deprecated) Whether to return the JSON object of the test that was created.',
			false
		);

		$this->addOption(
			'wait',
			'w',
			InputOption::VALUE_NEGATABLE,
			'(Deprecated)',
			false
		);

		$this->addOption(
			'ignore-fail',
			'i',
			InputOption::VALUE_NEGATABLE,
			'(Deprecated)',
			false
		);

		$this->addOption(
			'zip',
			null,
			InputOption::VALUE_OPTIONAL,
			'Deprecated. Use --source instead.'
		);
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$run_e2e_command = $this->getApplication()->find( RunE2ECommand::getDefaultName() );

		$resource_stream = fopen( 'php://temp', 'w+' );

		$run_e2e_options = [];

		// Sut.
		$run_e2e_options['woo_extension'] = $input->getArgument( 'woo_extension' );

		$run_e2e_options['--sut_action']              = 'activate';
		$run_e2e_options['--pw_options']              = '--retries=0';
		$run_e2e_options['--skip_activating_plugins'] = true;

		foreach ( $this->reused_options as $reused_option ) {
			if ( $reused_option === 'tunnel' ) {
				$run_e2e_options["--tunnel"] = TunnelRunner::get_tunnel_value( $input );
			} else {
				$run_e2e_options["--$reused_option"] = $input->getOption( $reused_option );
			}
		}

		// --zip deprecated option.
		if ( ! empty( $input->getOption( 'zip' ) ) ) {
			if ( ! empty( $input->getOption( 'source' ) ) ) {
				$output->writeln( '<error>Cannot use both --zip and --source options. Use only --source.</error>' );

				return Command::FAILURE;
			}

			$run_e2e_options['--source'] = $input->getOption( 'zip' );
		}

		// Set the test.
		$run_e2e_options['--plugin'][] = 'woocommerce:test:activation';

		if ( $output->isVerbose() ) {
			$run_e2e_options['--verbose'] = true;
		} elseif ( $output->isVeryVerbose() ) {
			$run_e2e_options['--very-verbose'] = true;
		}

		$run_e2e_exit_code = $run_e2e_command->run(
			new ArrayInput( $run_e2e_options ),
			new StreamOutput( $resource_stream )
		);

		if ( ! $input->getOption( 'json' ) ) {
			$run_e2e_output = stream_get_contents( $resource_stream, - 1, 0 );
			$output->writeln( $run_e2e_output );
		} else {
			$test_run_id = App::make( Cache::class )->get( 'QIT_LAST_LOCAL_TEST_FINISHED' );

			if ( empty( $test_run_id ) ) {
				$output->writeln( json_encode( [ 'error' => 'No test run ID found.' ] ) );

				return Command::FAILURE;
			}

			$json = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/get-single' ) )
				->with_method( 'POST' )
				->with_post_body( [
					'test_run_id' => $test_run_id,
				] )
				->with_retry( 3 )
				->request();

			$output->writeln( $json );
		}

		// Backwards compatibility with old "activation" test. To be used in scripting.
		if ( $input->getOption( 'ignore-fail' ) ) {
			return Command::SUCCESS;
		}

		return $run_e2e_exit_code;
	}
}
