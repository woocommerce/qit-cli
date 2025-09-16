<?php

namespace QIT_CLI\Environment;

use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Trait that provides a helper method to find an environment
 * by ID (or default to the only one if exactly one is running).
 */
trait EnvironmentSelectorTrait {

	/**
	 * Attempts to locate an environment by $env_id among $running_envs.
	 *
	 * If $env_id is null and there is exactly one running environment, return that one.
	 * If multiple exist but $env_id is not specified, prints an error and returns null.
	 * If no matching env_id is found, prints an error and returns null.
	 *
	 * @param object[]     $running_envs Array of environment objects.
	 * @param string|null  $env_id The environment ID requested.
	 * @param SymfonyStyle $io For printing error messages.
	 *
	 * @return object|null     Returns the matched environment object or null on error
	 */
	protected function find_environment_or_error( array $running_envs, ?string $env_id, SymfonyStyle $io ) {
		// If no environments are running, just inform and return null
		if ( empty( $running_envs ) ) {
			$io->writeln( '<info>No environments running.</info>' );

			return null;
		}

		// If the user provided an $env_id, filter by that
		if ( $env_id ) {
			$matched = array_filter( $running_envs, function ( $env ) use ( $env_id ) {
				return isset( $env->env_id ) && $env->env_id === $env_id;
			} );

			if ( empty( $matched ) ) {
				$io->error( sprintf( 'Environment "%s" not found.', $env_id ) );

				return null;
			}

			// Assume only one environment can match a specific ID
			return array_shift( $matched );
		}

		// If no $env_id was given but exactly one environment is running, return it
		if ( count( $running_envs ) === 1 ) {
			return array_shift( $running_envs );
		}

		// Otherwise, multiple envs but no ID => user must specify which one
		$io->error( 'Multiple environments are running. Please specify which env_id to use.' );

		return null;
	}
}
