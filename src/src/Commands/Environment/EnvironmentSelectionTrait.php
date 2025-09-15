<?php

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\Environment\EnvironmentMonitor;
use QIT_CLI\Environment\Environments\EnvInfo;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use function QIT_CLI\format_elapsed_time;

/**
 * Trait for commands that need to select an environment.
 *
 * Provides common functionality for:
 * - Checking QIT_ENV_ID environment variable
 * - Selecting single environment automatically
 * - Prompting user to choose from multiple environments
 */
trait EnvironmentSelectionTrait {
	/**
	 * Get the environment ID from command input (argument or option) or QIT_ENV_ID environment variable.
	 *
	 * @param InputInterface  $input  The input interface.
	 * @param OutputInterface $output The output interface.
	 * @param string|null     $input_name The name of the command argument or option for env_id (if any).
	 * @param bool            $is_argument Whether the input is an argument (true) or option (false).
	 * @return string|null The environment ID or null if not set.
	 */
	protected function get_env_id_from_input_or_env( InputInterface $input, OutputInterface $output, ?string $input_name = 'env_id', bool $is_argument = false ): ?string {
		// First check if command has an env_id argument/option and it's set
		$env_id = null;
		if ( $input_name ) {
			if ( $is_argument ) {
				if ( $input->hasArgument( $input_name ) ) {
					$env_id = $input->getArgument( $input_name );
				}
			} else {
				if ( $input->hasOption( $input_name ) ) {
					$env_id = $input->getOption( $input_name );
				}
			}
		}

		// Fall back to QIT_ENV_ID environment variable if not provided
		if ( empty( $env_id ) ) {
			$env_from_var = getenv( 'QIT_ENV_ID' );
			if ( ! empty( $env_from_var ) ) {
				$env_id = $env_from_var;
				if ( $output->isVerbose() ) {
					$output->writeln( sprintf( '<comment>Using QIT_ENV_ID from environment: %s</comment>', $env_id ) );
				}
			}
		}

		return ! empty( $env_id ) ? $env_id : null;
	}

	/**
	 * Select an environment based on user input, QIT_ENV_ID, or interactive prompt.
	 *
	 * @param EnvironmentMonitor $monitor The environment monitor.
	 * @param InputInterface     $input   The input interface.
	 * @param OutputInterface    $output  The output interface.
	 * @param string|null        $input_name The name of the command input for env_id (if any).
	 * @param bool               $is_argument Whether the input is an argument (true) or option (false).
	 * @return EnvInfo|null The selected environment or null if none selected.
	 */
	protected function select_environment( EnvironmentMonitor $monitor, InputInterface $input, OutputInterface $output, ?string $input_name = 'env_id', bool $is_argument = false ): ?EnvInfo {
		$running_environments = $monitor->get();

		if ( empty( $running_environments ) ) {
			$output->writeln( '<info>No environments running.</info>' );
			return null;
		}

		// Check for env_id from input or QIT_ENV_ID environment variable
		$env_id = $this->get_env_id_from_input_or_env( $input, $output, $input_name, $is_argument );

		// Filter by env_id if provided
		if ( ! empty( $env_id ) ) {
			$running_environments = array_filter( $running_environments, function ( EnvInfo $env ) use ( $env_id ) {
				return $env->env_id === $env_id;
			} );

			if ( empty( $running_environments ) ) {
				$output->writeln( sprintf( '<error>Environment with ID "%s" not found or not running.</error>', $env_id ) );
				return null;
			}
		}

		// If only one environment, use it
		if ( count( $running_environments ) === 1 ) {
			return array_shift( $running_environments );
		}

		// Multiple environments - prompt user to choose
		$environment_choices = array_map( function ( EnvInfo $environment ) {
			return sprintf( 'ID: %s, Created: %s, Status: %s',
				$environment->env_id,
				format_elapsed_time( time() - $environment->created_at ),
				$environment->status
			);
		}, $running_environments );

		$helper   = new QuestionHelper();
		$question = new ChoiceQuestion(
			'Please select the environment:',
			$environment_choices
		);
		$question->setErrorMessage( 'Environment %s is invalid.' );

		$selected_environment_id = $helper->ask( $input, $output, $question );

		try {
			return $monitor->get_env_info_by_id( $selected_environment_id );
		} catch ( \Exception $e ) {
			$output->writeln( '<error>Selected environment not found.</error>' );
			return null;
		}
	}
}
