<?php

namespace QIT_CLI\Commands;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\QITInput;
use QIT_CLI\RemoteTestRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Shared `--extension_set` support for locally-handled managed tests
 * (run:activation, run:woo-api, run:woo-e2e).
 *
 * These commands no longer go through the Manager's schema-driven command
 * generation (they are in CreateRunCommands' $ignored_test_types), so the
 * `--extension_set` option must be declared and resolved on each command.
 *
 * For local commands, the set name is expanded into the extensions it contains
 * and merged into `--plugin`, which env:up then resolves like any other plugin
 * slug. Managed package commands also use this trait to opt into the
 * Manager-hosted flow explicitly or when an extension set requires it.
 *
 * This trait is meant to be used in the context of Command classes.
 *
 * @method self addOption(string $name, string|array|null $shortcut = null, ?int $mode = null, string $description = '', mixed $default = null)
 */
trait ExtensionSetTrait {

	/**
	 * Register the `--extension_set` option, listing the available sets
	 * (synced from the Manager) in the description when known.
	 */
	protected function configure_extension_set_option(): void {
		$cache          = App::make( Cache::class );
		$extension_sets = $cache->get_manager_sync_data( 'extension_sets' );
		$available_sets = is_array( $extension_sets ) ? implode( ', ', array_keys( $extension_sets ) ) : '';

		$set_description = '(Optional) The predefined set of extensions to include in the test.';
		if ( ! empty( $available_sets ) ) {
			$set_description .= sprintf( ' <comment>[possible values: %s]</comment>', $available_sets );
		}

		$this->addOption(
			'extension_set',
			null,
			InputOption::VALUE_OPTIONAL,
			$set_description
		);
	}

	/**
	 * Register the explicit Manager-hosted execution option.
	 */
	protected function configure_remote_option(): void {
		$this->addOption(
			'remote',
			null,
			InputOption::VALUE_NONE,
			'Run the managed test on QIT servers instead of locally'
		);
	}

	/**
	 * Resolve `--extension_set` into `--plugin` options.
	 *
	 * @param QITInput        $input  The command input.
	 * @param OutputInterface $output The command output.
	 *
	 * @return int|null Null on success (including when no set is given),
	 *                  or a Command exit code on an unknown set.
	 */
	protected function resolve_extension_set( QITInput $input, OutputInterface $output ): ?int {
		$extension_set_name = $input->getOption( 'extension_set' );
		if ( empty( $extension_set_name ) ) {
			return null;
		}

		$cache          = App::make( Cache::class );
		$extension_sets = $cache->get_manager_sync_data( 'extension_sets' );

		if ( ! is_array( $extension_sets ) || ! isset( $extension_sets[ $extension_set_name ] ) ) {
			$available = is_array( $extension_sets ) ? implode( ', ', array_keys( $extension_sets ) ) : 'none';
			$output->writeln( sprintf( '<error>Unknown extension set "%s". Available sets: %s</error>', $extension_set_name, $available ) );

			return Command::INVALID;
		}

		/** @var array<string> $set_plugins */
		$set_plugins     = $extension_sets[ $extension_set_name ];
		$current_plugins = $input->getOption( 'plugin' ) ?: [];
		$current_plugins = is_array( $current_plugins ) ? $current_plugins : [];
		$merged_plugins  = array_unique( array_merge( $current_plugins, $set_plugins ) );
		$input->setOption( 'plugin', array_values( $merged_plugins ) );

		return null;
	}

	protected function run_remote_if_requested(
		QITInput $input,
		OutputInterface $output,
		string $remote_test_type,
		?string $profile_test_type = null
	): ?int {
		$extension_set_name = $input->getOption( 'extension_set' );
		if ( ! $input->getOption( 'remote' ) && empty( $extension_set_name ) ) {
			return null;
		}

		if ( getenv( 'QIT_TEST_RUN_ID' ) !== false ) {
			$output->writeln( '<error>Cannot start a remote test while QIT_TEST_RUN_ID is set.</error>' );

			return Command::INVALID;
		}

		if ( ! empty( $extension_set_name ) ) {
			$extension_set_error = $this->validate_extension_set_exists( (string) $extension_set_name, $output );
			if ( $extension_set_error !== null ) {
				return $extension_set_error;
			}
		}

		$runner          = App::make( RemoteTestRunner::class );
		$options_to_send = $runner->get_options_to_send_for_schema( $remote_test_type );

		$unsupported_options_error = $this->validate_only_remote_options( $input, $output, array_keys( $options_to_send ) );
		if ( $unsupported_options_error !== null ) {
			return $unsupported_options_error;
		}

		return $runner->execute(
			$this->get_qit_command_for_remote_runner(),
			$remote_test_type,
			$options_to_send,
			$input,
			$output,
			$profile_test_type ?? $remote_test_type
		);
	}

	private function get_qit_command_for_remote_runner(): QITCommand {
		if ( ! $this instanceof QITCommand ) {
			throw new \LogicException( 'ExtensionSetTrait remote fallback must be used by QITCommand instances.' );
		}

		return $this;
	}

	private function validate_extension_set_exists( string $extension_set_name, OutputInterface $output ): ?int {
		$cache          = App::make( Cache::class );
		$extension_sets = $cache->get_manager_sync_data( 'extension_sets' );

		if ( is_array( $extension_sets ) && isset( $extension_sets[ $extension_set_name ] ) ) {
			return null;
		}

		$available = is_array( $extension_sets ) ? implode( ', ', array_keys( $extension_sets ) ) : 'none';
		$output->writeln( sprintf( '<error>Unknown extension set "%s". Available sets: %s</error>', $extension_set_name, $available ) );

		return Command::INVALID;
	}

	/**
	 * @param QITInput        $input          The command input.
	 * @param OutputInterface $output         The command output.
	 * @param array<string>   $schema_options Options the Manager schema accepts.
	 */
	private function validate_only_remote_options( QITInput $input, OutputInterface $output, array $schema_options ): ?int {
		$allowed_options = array_unique( array_merge(
			$schema_options,
			[
				'ansi',
				'async',
				'config',
				'environment',
				'extension_set',
				'group',
				'help',
				'interaction',
				'json',
				'no-interaction',
				'print-report-url',
				'profile',
				'quiet',
				'remote',
				'timeout',
				'verbose',
				'version',
				'wait',
				'zip',
			]
		) );

		$provided = [];
		foreach ( array_keys( $input->getOptions() ) as $option_name ) {
			if ( in_array( $option_name, $allowed_options, true ) ) {
				continue;
			}

			if ( $this->option_was_explicitly_provided( $input, $option_name ) ) {
				$provided[] = '--' . $option_name;
			}
		}

		$passthrough = $input->getArgument( 'passthrough' ) ?: [];
		if ( ! empty( $passthrough ) ) {
			$provided[] = 'passthrough arguments';
		}

		if ( empty( $provided ) ) {
			return null;
		}

		$output->writeln( sprintf(
			'<error>Remote managed tests cannot be combined with local-only option(s): %s.</error>',
			implode( ', ', array_unique( $provided ) )
		) );

		return Command::INVALID;
	}

	private function option_was_explicitly_provided( QITInput $input, string $option_name ): bool {
		$symfony_input = $input->get_symfony_input();
		$flags         = [
			'--' . $option_name,
			'--no-' . $option_name,
		];
		$hyphenated    = str_replace( '_', '-', $option_name );

		if ( $hyphenated !== $option_name ) {
			$flags[] = '--' . $hyphenated;
			$flags[] = '--no-' . $hyphenated;
		}

		if ( $this instanceof Command ) {
			$definition = $this->getDefinition();
			if ( ! $definition->hasOption( $option_name ) ) {
				return $symfony_input->hasParameterOption( $flags, true );
			}

			$shortcut = $definition->getOption( $option_name )->getShortcut();
			if ( ! empty( $shortcut ) ) {
				foreach ( explode( '|', $shortcut ) as $shortcut_name ) {
					$flags[] = '-' . $shortcut_name;
				}
			}
		}

		return $symfony_input->hasParameterOption( $flags, true );
	}
}
