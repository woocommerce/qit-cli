<?php

namespace QIT_CLI\Commands;

use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\LocalTests\ConfigurationProcessor;
use QIT_CLI\LocalTests\EnvironmentRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractRunTestCommand extends Command {

	/** @var ConfigurationProcessor */
	protected $configuration_processor;

	/** @var EnvironmentRunner */
	protected $environment_runner;

	public function __construct(
		ConfigurationProcessor $configuration_processor,
		EnvironmentRunner $environment_runner
	) {
		$this->configuration_processor = $configuration_processor;
		$this->environment_runner      = $environment_runner;
		parent::__construct();
	}

	/**
	 * The main entry point that sets up the environment and then runs the tests.
	 *
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 *
	 * @return int
	 */
	protected function execute( InputInterface $input, OutputInterface $output ): int {
		try {
			// Start with an empty array for env_up_options.
			$env_up_options = [];

			// Hardcoded for now, when adding another test type this needs to be further abstracted from RunE2ECommand.
			$sut_type = 'plugin';

			// Merge configuration from qit.yml and CLI into final env_up_options.
			$env_up_options = $this->configuration_processor->process_configuration( $input, $env_up_options, $sut_type );

			// Start the environment and retrieve EnvInfo.
			$env_info = $this->environment_runner->run_environment( $env_up_options );

			// Run the specific tests implemented by the concrete command.
			$exit_code = $this->run_tests( $env_info );

			return $exit_code;
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

			return Command::FAILURE;
		}
	}

	/**
	 * Each concrete command implementing tests (E2E, Security, API, etc.)
	 * must provide their own run_tests method.
	 *
	 * @param EnvInfo $env_info The information about the environment.
	 *
	 * @return int Exit code, Command::SUCCESS or other relevant code.
	 */
	abstract protected function run_tests( EnvInfo $env_info ): int;
}
