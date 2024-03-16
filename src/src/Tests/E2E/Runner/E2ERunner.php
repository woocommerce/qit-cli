<?php

namespace QIT_CLI\Tests\E2E\Runner;

use QIT_CLI\Environment\Environments\EnvInfo;
use Symfony\Component\Console\Output\OutputInterface;

abstract class E2ERunner {
	/** @var OutputInterface */
	protected $output;

	public function __construct( OutputInterface $output ) {
		$this->output = $output;
	}

	/**
	 * @param string $e2e_test_path The path to the E2E tests.
	 *
	 * @return string Only "playwright" supported right now.
	 * @throws \Exception If it can't find the runner type.
	 */
	public static function find_runner_type( string $e2e_test_path ): string {
		if ( ! file_exists( $e2e_test_path ) || ! is_dir( $e2e_test_path ) ) {
			throw new \Exception( "Invalid E2E Test Path: $e2e_test_path" );
		}

		if ( is_dir( $e2e_test_path . '/tests' ) ) {
			/** @var \DirectoryIterator $file */
			foreach ( new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $e2e_test_path . '/tests' ) ) as $file ) {
				if ( $file->isDot() || $file->isLink() || ! $file->isFile() ) {
					continue;
				}

				// Fetch the first 4kb of the file.
				$contents = file_get_contents( $file->getPathname(), false, null, 0, 4096 );

				// Playwright.
				if ( in_array( $file->getExtension(), [ 'js', 'ts', 'tsx' ], true ) ) {
					// Search for playwright module imports.
					if ( strpos( $contents, '@playwright' ) !== false ) {
						return 'playwright';
					}
				}

				// Codeception.
				if ( in_array( $file->getExtension(), [ 'php' ], true ) ) {
					// no-op for now.
				}
			}
		}

		throw new \RuntimeException( sprintf( 'Could not find a valid runner type in %s', $e2e_test_path ) );
	}

	abstract public function bootstrap_plugin( EnvInfo $env_info, string $plugin ): void;

	abstract public function run_test( EnvInfo $env_info, string $plugin ): void;
}