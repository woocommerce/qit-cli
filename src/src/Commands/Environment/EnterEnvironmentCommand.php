<?php

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\EnvInfo;
use QIT_CLI\Environment\EnvironmentMonitor;
use QIT_CLI\Environment\Environments\E2EEnvironment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use function QIT_CLI\format_elapsed_time;

class EnterEnvironmentCommand extends Command {
	/** @var E2EEnvironment */
	protected $e2e_environment;

	/** @var EnvironmentMonitor */
	protected $environment_monitor;

	/** @var Docker */
	protected $docker;

	protected static $defaultName = 'env:enter'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	public function __construct(
		E2EEnvironment $e2e_environment,
		EnvironmentMonitor $environment_monitor,
		Docker $docker
	) {
		$this->e2e_environment     = $e2e_environment;
		$this->environment_monitor = $environment_monitor;
		$this->docker              = $docker;
		parent::__construct( static::$defaultName ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	protected function configure() {
		$this->setDescription( 'Enter the PHP container of a running test environment.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$running_environments = $this->environment_monitor->get();

		if ( empty( $running_environments ) ) {
			$output->writeln( '<info>No environments running.</info>' );

			return Command::SUCCESS;
		}

		if ( count( $running_environments ) === 1 ) {
			$this->docker->enter_environment( array_shift( $running_environments ) );

			return Command::SUCCESS;
		}

		$environment_choices = array_map( function ( EnvInfo $environment ) {
			return sprintf( 'ID: %s, Created: %s, Status: %s',
				$environment->env_id,
				format_elapsed_time( $environment->created_at ),
			$environment->status );
		}, $running_environments );

		// Let user choose which environment to enter.
		$helper   = new QuestionHelper();
		$question = new ChoiceQuestion(
			'Please select the environment to enter:',
			$environment_choices
		);
		$question->setErrorMessage( 'Environment %s is invalid.' );

		$selected_environment = $helper->ask( $input, $output, $question );

		try {
			$environment = $this->environment_monitor->get_env_info_by_id( $selected_environment );
		} catch ( \Exception $e ) {
			$output->writeln( '<error>Selected environment not found.</error>' );

			return Command::FAILURE;
		}

		$this->docker->enter_environment( $environment );

		return Command::SUCCESS;
	}
}
