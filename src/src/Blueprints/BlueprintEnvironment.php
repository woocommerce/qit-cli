<?php

namespace QIT_CLI\Blueprints;

use QIT_CLI\Config;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Shared entry point for the commands that accept `--blueprint`.
 *
 * Turning a Blueprint into an environment happens in two places: `env:up`
 * folds the declarative half into the environment config, and whichever
 * command owns the run (env:up itself, or run:e2e) executes the imperative
 * half. Both need the same transpilation and the same package directory, so
 * it lives here rather than in either command.
 */
class BlueprintEnvironment {

	public function prepare( string $blueprint_path ): TranspiledBlueprint {
		$blueprint = ( new BlueprintParser() )->from_file( $blueprint_path );

		return ( new BlueprintTranspiler() )->transpile( $blueprint, $blueprint_path );
	}

	/**
	 * Write the Blueprint steps as a utility package.
	 *
	 * The directory is derived from the Blueprint path, so every command in a
	 * run resolves the same package and env:up deduplicates it against the
	 * references it was given.
	 *
	 * @return string|null Null when the Blueprint has neither steps nor bundled files.
	 */
	public function materialize( string $blueprint_path, TranspiledBlueprint $result ): ?string {
		if ( ! $result->needs_package() ) {
			return null;
		}

		return $result->write_utility_package( $this->package_dir( $blueprint_path ) );
	}

	public function package_dir( string $blueprint_path ): string {
		return sprintf(
			'%s/blueprints/%s',
			rtrim( Config::get_qit_dir(), '/' ),
			substr( md5( realpath( $blueprint_path ) ?: $blueprint_path ), 0, 12 )
		);
	}

	/**
	 * Print what the translation lost or changed.
	 */
	public function report( string $blueprint_path, TranspiledBlueprint $result, OutputInterface $output ): void {
		if ( $output->isQuiet() ) {
			return;
		}

		$output->writeln( sprintf( '<info>Using Blueprint:</info> %s', $blueprint_path ) );

		foreach ( $result->warnings as $warning ) {
			$output->writeln( sprintf( '<comment>Blueprint: %s</comment>', $warning ) );
		}
	}
}
