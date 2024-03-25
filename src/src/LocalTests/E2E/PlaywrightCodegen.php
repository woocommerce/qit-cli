<?php

namespace QIT_CLI\LocalTests\E2E;

use QIT_CLI\Config;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use Symfony\Component\Process\Process;

class PlaywrightCodegen {
	public function open_codegen( E2EEnvInfo $env_info ) {
		if ( ! file_exists( Config::get_qit_dir() . 'playwright-codegen' ) ) {
			if ( ! mkdir( Config::get_qit_dir() . 'playwright-codegen', 0755, true ) ) {
				throw new \RuntimeException( 'Could not create the playwright-codegen directory: ' . Config::get_qit_dir() . 'playwright-codegen' );
			}
		}

		// Check if npm and npx are available.
		$process = new Process( [ 'npm', '--version' ] );
		$process->run();
		if ( ! $process->isSuccessful() ) {
			throw new \RuntimeException( 'npm is not available. Please install npm.' );
		}

		$process = new Process( [ 'npx', '--version' ] );
		$process->run();
		if ( ! $process->isSuccessful() ) {
			throw new \RuntimeException( 'npx is not available. Please install npx.' );
		}

		// Install Playwright in the config directory.
		$process = new Process( [ 'npm', 'install', 'playwright' ], Config::get_qit_dir() . 'playwright-codegen' );
		$process->setTimeout( 600 );
		$process->setIdleTimeout( 600 );
		$process->run();

		// Open Codegen;
		$process = new Process( [ 'npx', 'playwright', 'codegen', $env_info->site_url ], Config::get_qit_dir() . 'playwright-codegen' );
		$process->setTimeout( null );
		$process->setIdleTimeout( null );
		$process->run();
	}
}