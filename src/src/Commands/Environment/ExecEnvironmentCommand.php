<?php

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\EnvInfo;
use QIT_CLI\Environment\EnvironmentMonitor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use function QIT_CLI\format_elapsed_time;

class ExecEnvironmentCommand extends Command {
	/** @var EnvironmentMonitor */
	protected $environment_monitor;

	/** @var Docker */
	protected $docker;

	protected static $defaultName = 'env:exec'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	public function __construct(
		EnvironmentMonitor $environment_monitor,
		Docker $docker
	) {
		$this->environment_monitor = $environment_monitor;
		$this->docker              = $docker;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setDescription( 'Execute a command inside the PHP container of a running test environment.' )
			->addArgument( 'command_to_run', InputArgument::REQUIRED, 'The command to execute in the environment' )
			->addOption(
				'env_var',
				null,
				InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
				'Environment variable in key=value format'
			)
			->addOption( 'user', null, InputOption::VALUE_OPTIONAL, 'The user to run the command as' )
			->addOption( 'timeout', null, InputOption::VALUE_OPTIONAL, 'Timeout for the command', 300 )
			->addOption( 'image', null, InputOption::VALUE_OPTIONAL, 'The Docker image to use', 'php' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$running_environments = $this->environment_monitor->get();

		if ( empty( $running_environments ) ) {
			$output->writeln( '<info>No environments running.</info>' );

			return Command::SUCCESS;
		}

		if ( count( $running_environments ) === 1 ) {
			$environment = array_shift( $running_environments );
		} else {
			$environment_choices = array_map( function ( EnvInfo $environment ) {
				return sprintf( 'ID: %s, Created: %s, Status: %s',
					$environment->env_id,
					format_elapsed_time( time() - $environment->created_at ),
					$environment->status
				);
			}, $running_environments );

			$helper   = new QuestionHelper();
			$question = new ChoiceQuestion(
				'Please select the environment to execute the command:',
				$environment_choices
			);
			$question->setErrorMessage( 'Environment %s is invalid.' );

			$selected_environment_id = $helper->ask( $input, $output, $question );
			$environment             = $this->environment_monitor->get_env_info_by_id( $selected_environment_id );
		}

		// @phpstan-ignore-next-line
		if ( ! $environment ) {
			$output->writeln( '<error>Selected environment not found.</error>' );

			return Command::FAILURE;
		}

		$command_to_run = $input->getArgument( 'command_to_run' );
		// Use "PAGER=more" because we run Alpine images that have a minimalist version of "less".
		$env_vars       = array_merge( [ 'PAGER' => 'more' ], $this->parse_env_vars( $input->getOption( 'env_var' ) ) );
		$user           = $input->getOption( 'user' );
		$timeout        = $input->getOption( 'timeout' ) !== null ? (int) $input->getOption( 'timeout' ) : 300;
		$image          = $input->getOption( 'image' ) ?: 'php';

		$this->docker->run_inside_docker( $environment, explode( ' ', $command_to_run ), $env_vars, $user, $timeout, $image );

		return Command::SUCCESS;
	}

	/**
	 * @param array<string,scalar> $env_var_options
	 *
	 * @return array<string,string>
	 */
	private function parse_env_vars( array $env_var_options ): array {
		$env_vars = [];
		foreach ( $env_var_options as $e ) {
			$parts = explode( '=', $e, 2 );
			if ( count( $parts ) === 2 ) {
				$env_vars[ $parts[0] ] = $parts[1];
			}
		}

		return $env_vars;
	}
}
