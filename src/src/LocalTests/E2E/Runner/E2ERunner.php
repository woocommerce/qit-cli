<?php

namespace QIT_CLI\LocalTests\E2E\Runner;

use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\LocalTests\E2E\Result\TestResult;
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
	 * @throws \RuntimeException If it can't find a valid runner type.
	 */
	public static function find_runner_type( string $e2e_test_path ): string {
		if ( ! file_exists( $e2e_test_path ) || ! is_dir( $e2e_test_path ) ) {
			throw new \Exception( "Invalid E2E Test Path: $e2e_test_path" );
		}

		/** @var \SplFileInfo $file */
		foreach ( new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $e2e_test_path ) ) as $file ) {
			if ( $file->getBasename() === '.' || $file->getBasename() === '..' || $file->isLink() || ! $file->isFile() ) {
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
			if ( in_array( $file->getExtension(), [ 'php' ], true ) ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
				// no-op for now.
			}
		}

		throw new \RuntimeException( sprintf( 'Could not find a valid runner type in %s', $e2e_test_path ) );
	}

	// phpcs:disable
	/**
	 * @param E2EEnvInfo          $env_info
	 * @param array<string,array{
	 *      extension:string,
	 *      type:string,
	 *      test_tag:string,
	 *      path_in_container:string,
	 *      path_in_host:string
	 *  }>                        $test_infos
	 * @param TestResult          $test_result
	 * @param string              $test_mode
	 *
	 * @return void
	 */
	// phpcs:enable
	abstract public function run_test( E2EEnvInfo $env_info, array $test_infos, TestResult $test_result, string $test_mode ): void;
}
