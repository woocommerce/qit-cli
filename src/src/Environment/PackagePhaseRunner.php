<?php
namespace QIT_CLI\Environment;

use QIT_CLI\App;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\PreCommand\Configuration\Parser\TestPackageManifestParser;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Executes "setup" phase commands contained in a test‑package manifest.
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
	 * Executes the *setup* phase of one test‑package.
	 *
	 * @return int  Number of commands that were actually executed.
	 * @throws \RuntimeException on the first failing command.
	 */
	public function run_setup(
		EnvInfo $env_info,
		string $package_id,
		string $package_path
	): int {
		$manifest_path = $package_path . '/manifest.json';
		if ( ! file_exists( $manifest_path ) ) {
			$this->output->writeln(
				"<comment>Package {$package_id} has no manifest.json – skipping setup phase.</comment>"
			);
			return 0;
		}

		$manifest = $this->parser->parse( $manifest_path );

		// ───── schema compatibility ─────────────────────────────────────
		$setup = $manifest->getPhaseCommands('setup');

		if ( empty( $setup ) ) {
			return 0;
		}

		$workdir = '/qit/packages/' . basename( $package_id );
		$this->output->writeln( "  <info>• {$package_id}</info>" );

		$executed = 0;
		foreach ( $setup as $index => $cmd ) {
			$wrapped = [ '/bin/bash', '-c', "cd {$workdir} && {$cmd}" ];

			$this->docker->run_inside_docker(
				$env_info,
				$wrapped,
				[],          // extra env‑vars
				null,        // user
				300,         // timeout
				'php',       // container
				true         // force_output  → always stream
			);
			$executed ++;
		}

		return $executed;
	}
}