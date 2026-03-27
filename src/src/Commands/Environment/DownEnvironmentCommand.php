<?php

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\Environment\EnvironmentMonitor;
use QIT_CLI\Environment\Environments\E2E\E2EEnvironment;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\Commands\QITCommand;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use function QIT_CLI\format_elapsed_time;

class DownEnvironmentCommand extends QITCommand {
	protected E2EEnvironment $e2e_environment;

	protected EnvironmentMonitor $environment_monitor;

	protected static $defaultName = 'env:down'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	public function __construct( E2EEnvironment $e2e_environment, EnvironmentMonitor $environment_monitor ) {
		$this->e2e_environment     = $e2e_environment;
		$this->environment_monitor = $environment_monitor;
		parent::__construct( static::$defaultName ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Stops a local test environment.' )
			->setAliases( [ 'env:stop' ] )
			->addArgument( 'environment', InputArgument::OPTIONAL, 'Environment ID to stop, or "all" to stop all environments' )
			->addOption( 'all', null, InputOption::VALUE_NONE, 'Stop all running environments' );
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		$running_environments = $this->environment_monitor->get();

		if ( empty( $running_environments ) ) {
			$output->writeln( '<info>No environments running.</info>' );

			return self::SUCCESS;
		}

		$selected_environment = null;
		$environment_arg      = $input->getArgument( 'environment' );

		// --all flag or "all" argument both mean stop everything.
		if ( $input->getOption( 'all' ) ) {
			$selected_environment = 'all';
		} elseif ( $environment_arg ) {
			if ( $environment_arg === 'all' ) {
				$selected_environment = 'all';
			} else {
				// Try to find the environment by ID
				$found = false;
				foreach ( $running_environments as $env_id => $env_info ) {
					if ( $env_id === $environment_arg ) {
						$selected_environment = $env_info;
						$found                = true;
						break;
					}
				}

				if ( ! $found ) {
					$output->writeln( sprintf( '<error>Environment "%s" not found.</error>', $environment_arg ) );
					$output->writeln( '<info>Running environments:</info>' );
					foreach ( $running_environments as $env_id => $env_info ) {
						$output->writeln( sprintf( '  - %s (Created: %s)',
							$env_id,
							format_elapsed_time( time() - $env_info->created_at )
						) );
					}
					return self::FAILURE;
				}
			}
		} elseif ( count( $running_environments ) === 1 ) {
			// If no argument and only one environment, select it
			$selected_environment = array_shift( $running_environments );
		} else {
			// Multiple environments and no argument - show interactive menu
			$environment_choices = array_map( function ( EnvInfo $environment ) {
				return sprintf( 'Created: %s, Status: %s',
					format_elapsed_time( time() - $environment->created_at ),
				$environment->status );
			}, $running_environments );

			$environment_choices['all'] = 'Stop all environments';

			// More than one environment running, let user choose which one to stop.
			$helper   = new QuestionHelper();
			$question = new ChoiceQuestion(
				'Please select the environment to stop (or choose to stop all):',
				$environment_choices,
				'all'
			);
			$question->setErrorMessage( 'Environment %s is invalid.' );

			$selected_environment = $helper->ask( $input, $output, $question );
		}

		if ( $selected_environment === 'all' ) {
			$total_environments = count( $running_environments );
			$counter            = 1;

			foreach ( $running_environments as $environment ) {
				$output->write( "\r<info>Stopping all environments... [{$counter}/{$total_environments}]</info>" );
				$this->stop_environment( $environment, $output->isVerbose() ? $output : new NullOutput() );
				++$counter;
			}
		} else {
			$total_environments = 1;
			if ( ! $selected_environment instanceof EnvInfo ) {
				$selected_environment = $this->environment_monitor->get_env_info_by_id( $selected_environment );
			}
			$output->write( "\r<info>Stopping all environments... [1/1]</info>" );
			$this->stop_environment( $selected_environment, $output->isVerbose() ? $output : new NullOutput() );
		}

		$output->write( "\r<info>Stopped all environments [{$total_environments}/{$total_environments}].</info>" );

		return self::SUCCESS;
	}

	private function stop_environment( EnvInfo $environment, OutputInterface $output ): void {
		Environment::down( $environment, $output );
		$environment_id = $environment->env_id;
		$output->writeln( "<info>Environment '$environment_id' stopped.</info>" );
	}
}
