<?php

namespace QIT_CLI\Commands;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\QITInput;
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
 * Resolution is entirely CLI-side: the set name is expanded into the
 * extensions it contains and merged into `--plugin`, which env:up then
 * resolves like any other plugin slug.
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
}
