<?php
namespace QIT_CLI\Environment;

use QIT_CLI\App;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\PreCommand\Configuration\Parser\TestPackageManifestParser;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

/**
 * Executes test‑package phase commands with venue-aware execution.
 * – Supports both host and container execution based on command type
 * – Always streams output (unless --quiet was requested on the main CLI).
 * – Aborts by throwing \RuntimeException on the first non‑zero exit status.
 */
class PackagePhaseRunner {
	private Docker                     $docker;
	private OutputInterface            $output;
	private TestPackageManifestParser  $parser;

	public function __construct( Docker $docker, OutputInterface $output ) {
		$this->docker = $docker;
		$this->output = $output;
		$this->parser = App::make( TestPackageManifestParser::class );
	}

	/**
	 * Determine execution venue based on command type.
	 * Rule: *.sh → container | anything else → host
	 *
	 * @param string $cmd The command to analyze
	 * @return string 'container' or 'host'
	 */
	private function determine_execution_venue( string $cmd ): string {
		return str_ends_with( trim( $cmd ), '.sh' ) ? 'container' : 'host';
	}

	/**
	 * Execute a command on the host system
	 *
	 * @param string $cmd Command to execute
	 * @param string $package_path Working directory for the command
	 * @param array $env_vars Environment variables
	 * @throws \RuntimeException on command failure
	 */
	private function runOnHost( string $cmd, string $package_path, array $env_vars = [] ): void {
		$process = new Process( [ 'bash', '-c', $cmd ], $package_path, $env_vars, null, 300 );
		
		$process->run( function ( $type, $buffer ) {
			if ( ! $this->output->isQuiet() ) {
				$this->output->write( $buffer );
			}
		} );

		if ( ! $process->isSuccessful() ) {
			throw new \RuntimeException( 
				"Host command failed: {$cmd}\nExit code: {$process->getExitCode()}\nOutput: {$process->getOutput()}\nError: {$process->getErrorOutput()}" 
			);
		}
	}

	/**
	 * Execute a command inside Docker container
	 *
	 * @param string $cmd Command to execute
	 * @param EnvInfo $env_info Environment information
	 * @param string $package_id Package identifier
	 * @param string $workdir Working directory inside container
	 * @param array $env_vars Environment variables
	 * @throws \RuntimeException on command failure
	 */
	private function runInDocker( string $cmd, EnvInfo $env_info, string $package_id, string $workdir, array $env_vars = [] ): void {
		$wrapped = [ '/bin/bash', '-c', "cd {$workdir} && {$cmd}" ];

		$this->docker->run_inside_docker(
			$env_info,
			$wrapped,
			$env_vars,      // extra env‑vars
			null,           // user
			300,            // timeout
			'php',          // container
			true            // force_output  → always stream
		);
	}

	/**
	 * Execute a specific phase for a test package
	 *
	 * @param EnvInfo $env_info Environment information
	 * @param string $phase Phase name (setup, run, teardown, globalSetup, globalTeardown)
	 * @param string $package_id Package identifier
	 * @param string $package_path Package directory path
	 * @return int Number of commands that were actually executed
	 * @throws \RuntimeException on command failure
	 */
	public function run_phase(
		EnvInfo $env_info,
		string $phase,
		string $package_id,
		string $package_path
	): int {
		$manifest_path = $package_path . '/manifest.json';
		if ( ! file_exists( $manifest_path ) ) {
			$this->output->writeln(
				"<comment>Package {$package_id} has no manifest.json – skipping {$phase} phase.</comment>"
			);
			return 0;
		}

		$manifest = $this->parser->parse( $manifest_path );
		$commands = $manifest->getPhaseCommands( $phase );

		if ( empty( $commands ) ) {
			return 0;
		}

		$workdir = '/qit/packages/' . basename( $package_id );
		$this->output->writeln( "  <info>• {$package_id} ({$phase})</info>" );

		$executed = 0;
		foreach ( $commands as $cmd ) {
			$venue = $this->determine_execution_venue( $cmd );
			
			if ( $venue === 'host' ) {
				$this->runOnHost( $cmd, $package_path, [] );
			} else {
				$this->runInDocker( $cmd, $env_info, $package_id, $workdir, [] );
			}
			
			$executed++;
		}

		return $executed;
	}

	/**
	 * Executes the *setup* phase of one test‑package.
	 * (Backward compatibility method)
	 *
	 * @return int  Number of commands that were actually executed.
	 * @throws \RuntimeException on the first failing command.
	 */
	public function run_setup(
		EnvInfo $env_info,
		string $package_id,
		string $package_path
	): int {
		return $this->run_phase( $env_info, 'setup', $package_id, $package_path );
	}
}