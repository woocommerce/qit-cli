<?php

namespace QIT_CLI\Commands;

use QIT_CLI\Commands\CustomTests\RunE2ECommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

class RunActivationTestCommand extends Command {
	protected static $defaultName = 'run:activation'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure() {
		$this
			->setDescription( 'Run Activation tests.' )
			->setHelp( 'Run the Woo activation test against a given extension.' )
			->addArgument( 'woo_extension', InputArgument::REQUIRED, 'A WooCommerce Extension Slug or Marketplace ID.' )
			->addOption( 'source', null, InputOption::VALUE_OPTIONAL, 'The source of the main extension under test. Accepts a slug, a file, a URL. If not provided, the source will be the slug.' )
			->addOption( 'wp', null, InputOption::VALUE_OPTIONAL, 'The WordPress version. Accepts "stable", "nightly", or a version number.', 'stable' )
			->addOption( 'woo', null, InputOption::VALUE_OPTIONAL, 'The WooCommerce Version. Accepts "stable", "nightly", or a GitHub Tag (eg: 8.6.1).', 'stable' )
			->addOption( 'plugin', 'p', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Plugin to activate in the environment. Accepts paths, Woo.com slugs/product IDs, WordPress.org slugs or GitHub URLs.', [] );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$run_e2e_command = $this->getApplication()->find( RunE2ECommand::getDefaultName() );

		$resource_stream = fopen( 'php://temp', 'w+' );

		$run_e2e_options = [];

		// Sut.
		$run_e2e_options['woo_extension'] = $input->getArgument( 'woo_extension' );

		$run_e2e_options['--sut_action']              = 'activate';
		$run_e2e_options['--skip_activating_plugins'] = true;

		if ( ! empty( $input->getOption( 'source' ) ) ) {
			$run_e2e_options['--source'] = $input->getOption( 'source' );
		}
		$run_e2e_options['--wp']  = $input->getOption( 'wp' );
		$run_e2e_options['--woo'] = $input->getOption( 'woo' );

		foreach ( $input->getOption( 'plugin' ) as $plugin ) {
			$run_e2e_options['--plugin'][] = $plugin;
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

		$run_e2e_output = stream_get_contents( $resource_stream, - 1, 0 );

		$output->writeln( $run_e2e_output );

		return $run_e2e_exit_code;
	}
}
