<?php

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\Environment\EnvInfo;
use QIT_CLI\Environment\EnvironmentMonitor;
use QIT_CLI\Environment\Environments\E2EEnvironment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use function QIT_CLI\format_elapsed_time;

class DownEnvironmentCommand extends Command {
	/** @var E2EEnvironment */
	protected $e2e_environment;

	/** @var EnvironmentMonitor */
	protected $environment_monitor;

	protected static $defaultName = 'env:down'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	public function __construct( E2EEnvironment $e2e_environment, EnvironmentMonitor $environment_monitor ) {
		$this->e2e_environment     = $e2e_environment;
		$this->environment_monitor = $environment_monitor;
		parent::__construct( static::$defaultName );
	}

	protected function configure() {
		$this->setDescription( 'Stops a local test environment.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$running_environments = $this->environment_monitor->get();

		if ( empty( $running_environments ) ) {
			$output->writeln( "<info>No environments running.</info>" );

			return Command::SUCCESS;
		}

		if ( count( $running_environments ) === 1 ) {
			// Stop the single running environment
			$this->stopEnvironment( array_key_first( $running_environments ), $output );

			return Command::SUCCESS;
		}

		$running_environments = array_map( function ( EnvInfo $environment ) {
			return sprintf( '%s (started %s ago)', $environment->temporary_env, format_elapsed_time( time() - $environment->started ) );
		}, $running_environments );

		// More than one environment running, let user choose which one to stop
		$helper   = new QuestionHelper();
		$question = new ChoiceQuestion(
			'Please select the environment to stop (defaults to the first):',
			array_keys( $running_environments ),
			0
		);
		$question->setErrorMessage( 'Environment %s is invalid.' );

		$selectedEnvironment = $helper->ask( $input, $output, $question );
		$this->stopEnvironment( $selectedEnvironment, $output );

		return Command::SUCCESS;
	}

	private function stopEnvironment( string $temporary_environment, OutputInterface $output ) {
		// Implement the logic to stop the environment
		$this->e2e_environment->down( $this->environment_monitor->get_env_info_by_id( $temporary_environment ) );
		$output->writeln( "<info>Environment '$temporary_environment' stopped.</info>" );
	}
}
