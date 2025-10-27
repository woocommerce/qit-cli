<?php

namespace QIT_CLI\Environment;

use QIT_CLI\App;
use QIT_CLI\Commands\Environment\UpEnvironmentCommand;
use QIT_CLI\Environment\Environments\EnvInfo;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

class EnvironmentRunner {

	/**
	 * Runs the environment using the given env_up options and returns the EnvInfo object.
	 *
	 * @param array<string,mixed> $env_up_options Array of options to pass to the env:up command.
	 *
	 * @return EnvInfo
	 *
	 * @throws \RuntimeException If the environment fails to start.
	 */
	public function run_environment( array $env_up_options ): EnvInfo {
		$env_up_options['--json'] = true;

		$env_up_command  = App::make( Application::class )->find( UpEnvironmentCommand::getDefaultName() );
		$resource_stream = fopen( 'php://temp', 'w+' );

		// Attach the JSON filter when in JSON mode to filter out non-JSON output
		// The --json option is always set to true above, so we always attach the filter
		// IMPORTANT: Only filter on WRITE to avoid processing data twice
		stream_filter_append( $resource_stream, 'qit_json', STREAM_FILTER_WRITE );

		$exit_status_code = $env_up_command->run(
			new ArrayInput( $env_up_options ),
			new StreamOutput( $resource_stream )
		);

		$up_output = stream_get_contents( $resource_stream, - 1, 0 );
		fclose( $resource_stream );

		if ( $exit_status_code === 137 ) {
			if ( getenv( 'QIT_SELF_TEST' ) === 'env_info' ) {
				$env_json = json_decode( $up_output, true );
				if ( ! is_array( $env_json ) ) {
					throw new \RuntimeException( 'Failed to parse environment JSON in info-only mode. Output: ' . $up_output );
				}

				return EnvInfo::from_array( $env_json );
			}

			throw new \RuntimeException( 'Environment did not start. Received code 137 (info-only).' );
		}

		$env_json = json_decode( $up_output, true );

		// Check if it's an error JSON from env:up
		if ( is_array( $env_json ) && isset( $env_json['error'] ) && isset( $env_json['message'] ) ) {
			// It's a properly formatted error from env:up, throw it with the message
			throw new \RuntimeException( $env_json['message'] );
		}

		if ( ! is_array( $env_json ) || empty( $env_json['env_id'] ) ) {
			throw new \RuntimeException( 'Failed to parse environment JSON. Output: ' . $up_output );
		}

		// Check for invalid package errors (Command::INVALID = 2)
		if ( $exit_status_code === Command::INVALID ) {
			throw new \RuntimeException( 'Environment configuration is invalid. Output: ' . $up_output );
		}

		if ( $exit_status_code !== Command::SUCCESS ) {
			throw new \RuntimeException( 'Failed to start the environment. Output: ' . $up_output );
		}

		// Use the appropriate EnvInfo class based on environment type
		if ( isset( $env_json['environment'] ) && $env_json['environment'] === 'e2e' ) {
			return \QIT_CLI\Environment\Environments\E2E\E2EEnvInfo::from_array( $env_json );
		}

		return EnvInfo::from_array( $env_json );
	}
}
