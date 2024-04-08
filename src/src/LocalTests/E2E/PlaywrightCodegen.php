<?php

namespace QIT_CLI\LocalTests\E2E;

use QIT_CLI\App;
use QIT_CLI\Config;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\IO\Input;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class PlaywrightCodegen {
	/** @var OutputInterface $output */
	protected $output;

	public function __construct( OutputInterface $output ) {
		$this->output = $output;
	}

	public function open_codegen( E2EEnvInfo $env_info ): void {
		if ( ! file_exists( Config::get_qit_dir() . 'playwright-codegen' ) ) {
			if ( ! mkdir( Config::get_qit_dir() . 'playwright-codegen', 0755, true ) ) {
				throw new \RuntimeException( 'Could not create the playwright-codegen directory: ' . Config::get_qit_dir() . 'playwright-codegen' );
			}
		}

		// Check if node, npm and npx are available.
		$process = new Process( [ 'node', '--version' ] );
		$process->run();
		if ( ! $process->isSuccessful() ) {
			throw new \RuntimeException( 'npm is not available. Please install npm.' );
		}

		$node_version = trim( ltrim( $process->getOutput(), 'v' ) );

		// Minimum Node 18.
		if ( version_compare( $node_version, '18', '<' ) ) {
			$this->output->writeln( sprintf( '<comment>Playwright requires Node 18 or higher. Detected Node version: %s. Please upgrade Node.js.</comment>', $node_version ) );

			// We bail here without an error, which will keep the environment up so the user can connect to it using other means.
			return;
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
		$process = new Process( [ 'npm', 'install', 'playwright', '--verbose' ], Config::get_qit_dir() . 'playwright-codegen' );
		$process->setTimeout( 60 );
		$process->setIdleTimeout( 60 );
		$process->run( function ( $type, $buffer ) {
			if ( $this->output->isVerbose() ) {
				$this->output->write( $buffer );
			}
		} );

		if ( ! $process->isSuccessful() ) {
			/**
			 * Sometimes, "npm install" might fail due to network issues that are completely out of our control, such as:
			 *
			 * - NPM Registry using IPV6 and the host being unable to handle it.
			 * - The host having incorrect clock time that might cause SSL handshake to fail.
			 * - Network issues.
			 * - Proxy.
			 * - VPN.
			 * - Being blocked by the NPM registry.
			 *
			 * So we give them an alternative in this scenario.
			 *
			 * @link https://stackoverflow.com/questions/16873973/npm-install-hangs
			 * @link https://github.com/npm/cli/issues/3257
			 * @link https://stackoverflow.com/questions/70678583/stop-npm-install-at-idealtreeregal-sill-idealtree-bui
			 */
			$this->output->writeln( '<comment>Playwright installation failed (timeout after 60 seconds). Run Playwright Codegen manually and connect to the environment above. You can also manually install Playwright in the expected directory by running `npm install playwright` in ' . Config::get_qit_dir() . 'playwright-codegen. For detailed output, reattempt with "--verbose". This issue may be network-related (e.g., proxy, VPN).</comment>' );

			// Hold the environment up until user interacts.
			( new QuestionHelper() )->ask( App::make( Input::class ), $this->output, new ConfirmationQuestion( 'Press Enter to continue...', false ) );

			return;
		}

		// Playwright install.
		$process = new Process( [ 'npx', 'playwright', 'install', 'chromium' ], Config::get_qit_dir() . 'playwright-codegen' );
		$process->setTimeout( null );
		$process->setIdleTimeout( null );
		$process->run( function ( $type, $buffer ) {
			$this->output->write( $buffer );
		} );

		// Open Codegen.
		$process = new Process( [ 'npx', 'playwright', 'codegen', $env_info->site_url ], Config::get_qit_dir() . 'playwright-codegen' );
		$process->setTimeout( null );
		$process->setIdleTimeout( null );
		$process->run( function ( $type, $buffer ) {
			$this->output->write( $buffer );
		} );
	}
}
