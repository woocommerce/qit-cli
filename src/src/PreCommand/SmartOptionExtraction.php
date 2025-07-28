<?php

namespace QIT_CLI\PreCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;
use function QIT_CLI\is_option_explicitly_provided;

trait SmartOptionExtraction {
	/**
	 * Normalize CLI option name to config key.
	 * For multi-value options use a plural key, otherwise keep original.
	 *
	 * @param string      $cliName CLI option name
	 * @param InputOption $option  Option definition
	 * @return string Normalized config key
	 */
	private static function normaliseKey( string $cliName, InputOption $option ): string {
		// For multi-value options use a plural key, otherwise keep original.
		if ( $option->isArray() ) {
			return str_ends_with( $cliName, 's' ) ? $cliName : $cliName . 's';
		}
		return $cliName;
	}

	/**
	 * Extract all explicitly provided options from input, excluding framework options.
	 *
	 * @param Command        $command The command to get option definitions from.
	 * @param InputInterface $input The input to extract from.
	 *
	 * @return array<string, mixed> Extracted options with proper key names.
	 */
	protected function extract_explicit_options(
		Command $command,
		InputInterface $input
	): array {
		$overrides  = [];
		$definition = $command->getDefinition();

		// Framework options to skip
		$framework_options = [
			'help',
			'quiet',
			'verbose',
			'version',
			'ansi',
			'no-ansi',
			'no-interaction',
			'config',
			'profile',
			'environment',
			'json',
			'tunnel',
		];

		foreach ( $definition->getOptions() as $option ) {
			$option_name = $option->getName();

			// Skip framework options
			if ( in_array( $option_name, $framework_options, true ) ) {
				continue;
			}

			// Check if this option was explicitly provided
			if ( is_option_explicitly_provided( $input, $option_name ) ) {
				$value = $input->getOption( $option_name );

				// Normalize option name to config key
				$config_key = self::normaliseKey( $option_name, $option );

				// Handle special cases
				if ( $option_name === 'phpstan_level' && $value !== null ) {
					$value = (int) $value;
				}

				$overrides[ $config_key ] = $value;
			}
		}

		return $overrides;
	}

	/**
	 * Get all option defaults from a command.
	 *
	 * @param Command $command The command to get defaults from.
	 *
	 * @return array<string, mixed> Default values with proper key names.
	 */
	protected function extract_option_defaults(
		Command $command
	): array {
		$defaults   = [];
		$definition = $command->getDefinition();

		// Framework options to skip
		$framework_options = [
			'help',
			'quiet',
			'verbose',
			'version',
			'ansi',
			'no-ansi',
			'no-interaction',
			'config',
			'profile',
			'environment',
			'json',
			'tunnel',
		];

		foreach ( $definition->getOptions() as $option ) {
			$option_name = $option->getName();

			// Skip framework options
			if ( in_array( $option_name, $framework_options, true ) ) {
				continue;
			}

			// Get default value
			$default = $option->getDefault();

			// Normalize option name to config key
			$config_key = self::normaliseKey( $option_name, $option );

			// Include default value (including null for optional options)
			$defaults[ $config_key ] = $default;
		}

		return $defaults;
	}
}
