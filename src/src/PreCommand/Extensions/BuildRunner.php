<?php

namespace QIT_CLI\PreCommand\Extensions;

use QIT_CLI\PreCommand\Objects\Extension;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\debug_log;

/**
 * Executes build commands for extensions that require compilation before use.
 *
 * When an extension has a `build` property in its qit.json configuration,
 * this class runs the specified command before the extension is used.
 * Follows the fail-fast philosophy: if a build fails, execution stops immediately.
 */
class BuildRunner {
	protected OutputInterface $output;

	public function __construct( OutputInterface $output ) {
		$this->output = $output;
	}

	/**
	 * Run the build command for an extension if one is configured.
	 *
	 * @param Extension $extension The extension to build.
	 *
	 * @throws \RuntimeException If the build command fails or produces no output.
	 */
	public function run_build_if_needed( Extension $extension ): void {
		if ( empty( $extension->build_command ) ) {
			return;
		}

		$cwd = $extension->build_cwd;
		if ( empty( $cwd ) ) {
			throw new \RuntimeException(
				sprintf( "Extension '%s' has a build command but no build working directory.", $extension->slug )
			);
		}

		// Resolve to absolute path. If the path is relative, realpath() resolves it
		// against the process CWD (which should be the qit.json directory).
		// If it doesn't exist, fail fast with a clear message.
		$resolved_cwd = realpath( $cwd );
		if ( $resolved_cwd === false || ! is_dir( $resolved_cwd ) ) {
			throw new \RuntimeException(
				sprintf(
					"Build working directory does not exist for '%s': %s (resolved from: %s, process cwd: %s)",
					$extension->slug,
					$cwd,
					$resolved_cwd ?: 'unresolvable',
					getcwd() ?: 'unknown'
				)
			);
		}
		$cwd = $resolved_cwd;

		$command = $extension->build_command;

		$this->output->writeln( sprintf( 'Running build for %s: <info>%s</info>', $extension->slug, $command ) );
		debug_log( "BuildRunner: Running build for '{$extension->slug}': $command (cwd: $cwd)" );

		$descriptors = [
			0 => [ 'pipe', 'r' ],
			1 => [ 'pipe', 'w' ],
			2 => [ 'pipe', 'w' ],
		];

		$process = proc_open( $command, $descriptors, $pipes, $cwd ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_proc_open -- Build commands require process execution.

		if ( ! is_resource( $process ) ) {
			throw new \RuntimeException(
				sprintf( "Failed to start build command for '%s': %s", $extension->slug, $command )
			);
		}

		fclose( $pipes[0] );

		$stdout = stream_get_contents( $pipes[1] );
		fclose( $pipes[1] );

		$stderr = stream_get_contents( $pipes[2] );
		fclose( $pipes[2] );

		$exit_code = proc_close( $process );

		if ( $this->output->isVerbose() ) {
			if ( ! empty( $stdout ) ) {
				$this->output->writeln( $stdout );
			}
		}

		if ( $exit_code !== 0 ) {
			$error_output = ! empty( $stderr ) ? "\n$stderr" : '';
			if ( ! empty( $stdout ) ) {
				$error_output .= "\n$stdout";
			}

			throw new \RuntimeException(
				sprintf(
					"Build command failed for '%s' (exit code %d): %s%s",
					$extension->slug,
					$exit_code,
					$command,
					$error_output
				)
			);
		}

		debug_log( "BuildRunner: Build completed successfully for '{$extension->slug}'" );
	}
}
