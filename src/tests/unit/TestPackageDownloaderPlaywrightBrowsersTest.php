<?php

namespace QIT_CLI_Tests;

use PHPUnit\Framework\TestCase;
use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\PreCommand\Configuration\Parser\TestPackageManifestParser;
use QIT_CLI\PreCommand\Download\TestPackageDownloader;
use QIT_CLI\Validation\ArtifactValidator;
use QIT_CLI\Zipper;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Playwright before 1.61 can't install browsers on Ubuntu 26.04 (GitHub's ubuntu-latest).
 * The host browser install must say so at the default verbosity instead of hiding it.
 *
 * A fake "npx" on PATH stands in for Playwright: it records its calls and answers
 * "playwright install --dry-run" and "playwright install" from files each test writes.
 */
class TestPackageDownloaderPlaywrightBrowsersTest extends TestCase {
	private const REFERENCE = 'woocommerce/fake-e2e:1.0.0';

	private const CHECKSUM = 'abc123';

	private const PACKAGE_URL = 'https://example.com/fake-e2e.zip';

	private const UBUNTU_2604_ERROR = 'ERROR: Playwright does not support chromium on ubuntu26.04';

	/** @var string|null */
	private $work_dir;

	/** @var string */
	private $fake_bin;

	/** @var string */
	private $package_dir;

	/** @var string|false */
	private $original_path;

	/** @var array<string,mixed> */
	private $cache_entries = [];

	protected function setUp(): void {
		parent::setUp();

		if ( stripos( PHP_OS, 'WIN' ) === 0 ) {
			$this->markTestSkipped( 'Relies on POSIX shell stubs on PATH.' );
		}

		$this->original_path = getenv( 'PATH' );
		$this->work_dir      = sys_get_temp_dir() . '/qit-playwright-browsers-' . uniqid();
		$this->fake_bin      = $this->work_dir . '/bin';
		$this->package_dir   = $this->work_dir . '/package';

		mkdir( $this->fake_bin, 0755, true );
		mkdir( $this->package_dir . '/node_modules/@playwright/test', 0755, true );
		mkdir( $this->work_dir . '/browsers/chromium-1', 0755, true );

		file_put_contents( $this->package_dir . '/package.json', '{"name":"fake-e2e"}' );
		file_put_contents( $this->package_dir . '/qit-test.json', json_encode( $this->manifest_data() ) );

		$this->write_stub( 'npx', implode( "\n", [
			'dir="$(dirname "$0")"',
			'echo "$*" >> "$dir/calls.log"',
			'case "$*" in',
			'  "playwright install --dry-run") step=dry-run ;;',
			'  "playwright install") step=install ;;',
			'  *) exit 99 ;;',
			'esac',
			'cat "$dir/$step.out"',
			'exit "$(cat "$dir/$step.code")"',
		] ) );
		// The local package path asks "npm ls" whether dependencies are installed.
		$this->write_stub( 'npm', 'echo "{}"; exit 0' );

		putenv( 'PATH=' . $this->fake_bin . ':' . $this->original_path );
	}

	protected function tearDown(): void {
		if ( $this->work_dir === null ) {
			return;
		}

		putenv( 'PATH=' . $this->original_path );
		App::setVar( 'mock_' . self::PACKAGE_URL, null );
		exec( 'rm -rf ' . escapeshellarg( $this->work_dir ) );

		parent::tearDown();
	}

	public function test_failed_install_warns_at_default_verbosity_and_returns_the_manifest(): void {
		$this->fake_dry_run( $this->missing_browser_listing(), 0 );
		$this->fake_install( 'Error: EACCES: permission denied, mkdir <info>/root/.cache/ms-playwright</info>', 1 );

		$output    = new BufferedOutput();
		$manifests = $this->make_downloader( $output )->download( [ $this->package_dir => [] ], $this->work_dir . '/cache' );
		$display   = $output->fetch();

		$this->assertStringContainsString( 'Warning: Playwright could not install its browsers', $display );
		$this->assertStringContainsString( 'EACCES: permission denied, mkdir <info>/root/.cache/ms-playwright</info>', $display );
		$this->assertStringNotContainsString( 'hidden, run with -v', $display );
		$this->assertArrayHasKey( $this->package_dir, $manifests );
		$this->assertSame( 'woocommerce/fake-e2e', $manifests[ $this->package_dir ]->get_package_id() );
	}

	public function test_unsupported_ubuntu_2604_failure_names_the_fix(): void {
		$this->fake_dry_run( $this->missing_browser_listing(), 0 );
		$this->fake_install( self::UBUNTU_2604_ERROR, 1 );

		$display = $this->download_local_package();

		$this->assertStringContainsString( self::UBUNTU_2604_ERROR, $display );
		$this->assertStringContainsString( "upgrade this package's Playwright (@playwright/test or playwright) to 1.61 or later, or use Ubuntu 24.04", $display );
	}

	/**
	 * @dataProvider other_install_failure_provider
	 */
	public function test_other_install_failures_get_no_ubuntu_2604_hint( string $install_error ): void {
		$this->fake_dry_run( $this->missing_browser_listing(), 0 );
		$this->fake_install( $install_error, 1 );

		$display = $this->download_local_package();

		$this->assertStringContainsString( 'Warning: Playwright could not install its browsers', $display );
		$this->assertStringContainsString( $install_error, $display );
		$this->assertStringNotContainsString( 'does not support Ubuntu 26.04', $display );
	}

	/**
	 * @return array<string,array{string}>
	 */
	public function other_install_failure_provider(): array {
		return [
			'network error'              => [ 'Error: getaddrinfo ENOTFOUND playwright.azureedge.net' ],
			'another unsupported distro' => [ 'ERROR: Playwright does not support chromium on ubuntu20.04' ],
		];
	}

	public function test_a_failed_dry_run_goes_on_to_the_install(): void {
		// The listed directory exists, so only the exit code says the browsers aren't confirmed.
		$this->fake_dry_run( self::UBUNTU_2604_ERROR . "\n  Install location:    {$this->work_dir}/browsers/chromium-1", 1 );
		$this->fake_install( '', 0 );

		$display = $this->download_local_package();

		$this->assertContains( 'playwright install', $this->npx_calls() );
		$this->assertStringNotContainsString( 'Playwright browsers already installed', $display );
	}

	/**
	 * @dataProvider unconfirmed_dry_run_provider
	 */
	public function test_a_dry_run_listing_no_install_location_goes_on_to_the_install( string $dry_run_output ): void {
		$this->fake_dry_run( $dry_run_output, 0 );
		$this->fake_install( '', 0 );

		$display = $this->download_local_package();

		$this->assertContains( 'playwright install', $this->npx_calls() );
		$this->assertStringNotContainsString( 'Playwright browsers already installed', $display );
	}

	/**
	 * @return array<string,array{string}>
	 */
	public function unconfirmed_dry_run_provider(): array {
		return [
			'no output'               => [ '' ],
			'no location line'        => [ 'browser: chromium version 140.0' ],
			'empty location'          => [ "browser: chromium version 140.0\n  Install location:    " ],
		];
	}

	public function test_a_cached_package_in_a_batch_download_gets_the_browser_check(): void {
		$this->cache_package();
		$this->fake_dry_run( $this->missing_browser_listing(), 0 );
		$this->fake_install( self::UBUNTU_2604_ERROR, 1 );

		$output    = new BufferedOutput();
		$manifests = $this->make_downloader( $output )->download( [ self::REFERENCE => [] ], $this->work_dir . '/cache' );

		$this->assertContains( 'playwright install --dry-run', $this->npx_calls() );
		$this->assertStringContainsString( 'Warning: Playwright could not install its browsers', $output->fetch() );
		$this->assertArrayHasKey( self::REFERENCE, $manifests );
	}

	public function test_a_cached_single_package_gets_the_browser_check(): void {
		$this->cache_package();
		$this->fake_dry_run( $this->missing_browser_listing(), 0 );
		$this->fake_install( self::UBUNTU_2604_ERROR, 1 );

		$output   = new BufferedOutput();
		$manifest = $this->make_downloader( $output )->download_single( self::REFERENCE, $this->work_dir . '/cache' );

		$this->assertContains( 'playwright install --dry-run', $this->npx_calls() );
		$this->assertStringContainsString( 'Warning: Playwright could not install its browsers', $output->fetch() );
		$this->assertSame( 'woocommerce/fake-e2e', $manifest->get_package_id() );
	}

	public function test_a_cached_package_with_its_browsers_present_is_not_reinstalled(): void {
		$this->cache_package();
		$this->fake_dry_run( $this->present_browser_listing(), 0 );

		$output = new BufferedOutput();
		$this->make_downloader( $output )->download_single( self::REFERENCE, $this->work_dir . '/cache' );

		$this->assertSame( [ 'playwright install --dry-run' ], $this->npx_calls() );
		$this->assertSame( "Checking for Playwright browsers...\nPlaywright browsers already installed\n", $output->fetch() );
	}

	public function test_a_cached_package_without_playwright_prints_nothing_about_browsers(): void {
		$this->cache_package();
		exec( 'rm -rf ' . escapeshellarg( $this->package_dir . '/node_modules' ) );

		$output = new BufferedOutput();
		$this->make_downloader( $output )->download_single( self::REFERENCE, $this->work_dir . '/cache' );

		$this->assertSame( [], $this->npx_calls() );
		$this->assertSame( '', $output->fetch() );
	}

	public function test_a_package_downloaded_again_in_the_same_run_is_checked_once(): void {
		$this->cache_package();
		$this->fake_dry_run( $this->missing_browser_listing(), 0 );
		$this->fake_install( self::UBUNTU_2604_ERROR, 1 );

		// env:up downloads each remote package once for its requirements and again for setup.
		$output = new BufferedOutput();
		$this->make_downloader( $output )->download( [ self::REFERENCE => [] ], $this->work_dir . '/cache' );
		$this->make_downloader( $output )->download_single( self::REFERENCE, $this->work_dir . '/cache' );

		$this->assertSame( [ 'playwright install --dry-run', 'playwright install' ], $this->npx_calls() );
		$this->assertSame( 1, substr_count( $output->fetch(), 'Warning: Playwright could not install its browsers' ) );
	}

	public function test_a_package_downloaded_again_into_the_same_directory_is_checked_again(): void {
		$this->fake_dry_run( $this->missing_browser_listing(), 0 );
		$this->fake_install( self::UBUNTU_2604_ERROR, 1 );
		App::setVar( 'mock_' . self::PACKAGE_URL, 'fake zip' );
		$this->cache_package_urls();

		// A cache dir behind a symlink makes the package dir's real path differ from the one it is downloaded to.
		mkdir( $this->work_dir . '/cache-real' );
		symlink( $this->work_dir . '/cache-real', $this->work_dir . '/cache' );

		// Without a cached checksum each download wipes packages/<md5> and extracts a fresh copy, whose browsers nobody has checked.
		$output     = new BufferedOutput();
		$downloader = $this->make_downloader( $output, $this->zipper_extracting_the_package() );
		$downloader->download_single( self::REFERENCE, $this->work_dir . '/cache' );
		$downloader->download_single( self::REFERENCE, $this->work_dir . '/cache' );

		$this->assertSame( [ 'playwright install --dry-run', 'playwright install', 'playwright install --dry-run', 'playwright install' ], $this->npx_calls() );
	}

	public function test_different_packages_in_the_same_cache_are_each_checked(): void {
		$this->fake_dry_run( $this->missing_browser_listing(), 0 );
		$this->fake_install( '', 0 );
		exec( 'cp -R ' . escapeshellarg( $this->package_dir ) . ' ' . escapeshellarg( $this->work_dir . '/other-package' ) );

		$output = new BufferedOutput();
		$this->make_downloader( $output )->download( [ $this->package_dir => [], $this->work_dir . '/other-package' => [] ], $this->work_dir . '/cache' );

		$this->assertSame( [ 'playwright install --dry-run', 'playwright install', 'playwright install --dry-run', 'playwright install' ], $this->npx_calls() );
	}

	public function test_the_same_package_through_another_path_is_checked_once(): void {
		$this->fake_dry_run( $this->missing_browser_listing(), 0 );
		$this->fake_install( '', 0 );

		$this->download_local_package();
		$output = new BufferedOutput();
		$this->make_downloader( $output )->download( [ $this->work_dir . '/bin/../package' => [] ], $this->work_dir . '/cache' );

		$this->assertSame( [ 'playwright install --dry-run', 'playwright install' ], $this->npx_calls() );
	}

	public function test_a_long_install_failure_shows_its_last_lines_at_default_verbosity(): void {
		$this->fake_dry_run( $this->missing_browser_listing(), 0 );
		$this->fake_install( $this->numbered_lines( 30 ) . "\n" . self::UBUNTU_2604_ERROR, 1 );

		$display = $this->download_local_package();

		$this->assertStringNotContainsString( 'install line 11', $display );
		$this->assertStringContainsString( "install line 12\n", $display );
		$this->assertStringContainsString( self::UBUNTU_2604_ERROR, $display );
		$this->assertStringContainsString( '11 earlier lines hidden, run with -v to see them', $display );
		$this->assertStringContainsString( 'or use Ubuntu 24.04', $display );
	}

	/**
	 * @dataProvider install_failure_length_provider
	 */
	public function test_an_install_failure_is_trimmed_only_past_twenty_lines( int $line_count, string $hidden_note ): void {
		$this->fake_dry_run( $this->missing_browser_listing(), 0 );
		$this->fake_install( $this->numbered_lines( $line_count ), 1 );

		$display = $this->download_local_package();

		$this->assertStringContainsString( "install line $line_count", $display );
		if ( $hidden_note === '' ) {
			$this->assertStringContainsString( "install line 1\n", $display );
			$this->assertStringNotContainsString( 'hidden, run with -v', $display );
		} else {
			$this->assertStringNotContainsString( "install line 1\n", $display );
			$this->assertStringContainsString( $hidden_note, $display );
		}
	}

	/**
	 * @return array<string,array{int,string}>
	 */
	public function install_failure_length_provider(): array {
		return [
			'20 lines' => [ 20, '' ],
			'21 lines' => [ 21, '(1 earlier line hidden, run with -v to see it)' ],
		];
	}

	public function test_the_ubuntu_2604_hint_shows_when_its_error_is_in_the_hidden_lines(): void {
		$this->fake_dry_run( $this->missing_browser_listing(), 0 );
		$this->fake_install( self::UBUNTU_2604_ERROR . "\n" . $this->numbered_lines( 25 ), 1 );

		$display = $this->download_local_package();

		$this->assertStringNotContainsString( self::UBUNTU_2604_ERROR, $display );
		$this->assertStringContainsString( 'or use Ubuntu 24.04', $display );
	}

	public function test_a_long_install_failure_shows_every_line_with_verbose(): void {
		$this->fake_dry_run( $this->missing_browser_listing(), 0 );
		$this->fake_install( $this->numbered_lines( 30 ), 1 );

		$display = $this->download_local_package( OutputInterface::VERBOSITY_VERBOSE );

		$this->assertStringContainsString( "install line 1\n", $display );
		$this->assertStringContainsString( 'install line 30', $display );
		$this->assertStringNotContainsString( 'earlier lines hidden', $display );
	}

	/**
	 * @dataProvider unchanged_output_provider
	 */
	public function test_successful_paths_print_what_they_printed_before( string $case, int $verbosity, array $playwright_lines ): void {
		if ( $case === 'already-installed' ) {
			$this->fake_dry_run( $this->present_browser_listing(), 0 );
		} else {
			$this->fake_dry_run( $this->missing_browser_listing(), 0 );
			$this->fake_install( 'Downloading Chromium...', 0 );
		}

		$display = $this->download_local_package( $verbosity );

		$expected = array_merge( [ "Using local package: {$this->package_dir}" ], $playwright_lines, [ 'Downloaded 1 packages in Ns', '' ] );
		$this->assertSame( implode( "\n", $expected ), preg_replace( '/in [0-9.]+s$/m', 'in Ns', $display ) );
	}

	/**
	 * @return array<string,array{string,int,string[]}>
	 */
	public function unchanged_output_provider(): array {
		$already_installed = [ 'Checking for Playwright browsers...', 'Playwright browsers already installed' ];
		$installing        = [ 'Checking for Playwright browsers...', 'Installing Playwright browsers (this may take a few minutes)...' ];

		return [
			'already installed, default' => [ 'already-installed', OutputInterface::VERBOSITY_NORMAL, $already_installed ],
			'already installed, -v'      => [ 'already-installed', OutputInterface::VERBOSITY_VERBOSE, $already_installed ],
			'installed, default'         => [ 'install', OutputInterface::VERBOSITY_NORMAL, $installing ],
			'installed, -v'              => [ 'install', OutputInterface::VERBOSITY_VERBOSE, array_merge( $installing, [ 'Playwright browsers installed successfully' ] ) ],
		];
	}

	private function download_local_package( int $verbosity = OutputInterface::VERBOSITY_NORMAL ): string {
		$output = new BufferedOutput( $verbosity );
		$this->make_downloader( $output )->download( [ $this->package_dir => [] ], $this->work_dir . '/cache' );

		return $output->fetch();
	}

	private function make_downloader( OutputInterface $output, ?Zipper $zipper = null ): TestPackageDownloader {
		$cache = $this->createMock( Cache::class );
		$cache->method( 'get' )->willReturnCallback( function ( string $key ) {
			return $this->cache_entries[ $key ] ?? null;
		} );

		return new TestPackageDownloader(
			$cache,
			$zipper ?? $this->createMock( Zipper::class ),
			$output,
			new TestPackageManifestParser(),
			$this->createMock( ArtifactValidator::class )
		);
	}

	/**
	 * Seed the cache as an earlier run would have left it: the package metadata from the
	 * Manager and the downloaded package, already on disk.
	 */
	private function cache_package(): void {
		$this->cache_package_urls();

		$this->cache_entries[ 'test_package_checksum_' . self::CHECKSUM ] = [
			'manifest' => $this->manifest_data(),
			'metadata' => [
				'reference'       => self::REFERENCE,
				'remote'          => true,
				'downloaded_path' => $this->package_dir,
				'version'         => '1.0.0',
			],
		];
	}

	private function cache_package_urls(): void {
		$this->cache_entries[ 'test_package_urls_' . md5( self::REFERENCE ) ] = [
			'data'      => [
				'urls' => [
					self::REFERENCE => [
						'url'      => self::PACKAGE_URL,
						'checksum' => self::CHECKSUM,
						'version'  => '1.0.0',
					],
				],
			],
			'cached_at' => time(),
		];
	}

	/**
	 * @return array<string,mixed>
	 */
	private function manifest_data(): array {
		return [
			'package'   => 'woocommerce/fake-e2e',
			'test_type' => 'e2e',
			'test'      => [
				'phases'  => [ 'run' => [ 'npx playwright test' ] ],
				'results' => [
					'ctrf-json'  => './results/ctrf.json',
					'allure-dir' => './results/allure',
					'blob-dir'   => './blob-report',
				],
			],
		];
	}

	private function zipper_extracting_the_package(): Zipper {
		$zipper = $this->createMock( Zipper::class );
		$zipper->method( 'extract_zip' )->willReturnCallback( function ( string $zip_file, string $target_dir ) {
			exec( 'cp -R ' . escapeshellarg( $this->package_dir ) . ' ' . escapeshellarg( $target_dir ) );
		} );

		return $zipper;
	}

	private function numbered_lines( int $count ): string {
		return implode( "\n", array_map( function ( int $n ) {
			return "install line $n";
		}, range( 1, $count ) ) );
	}

	private function present_browser_listing(): string {
		return "browser: chromium version 140.0\n  Install location:    {$this->work_dir}/browsers/chromium-1";
	}

	private function missing_browser_listing(): string {
		return "browser: chromium version 140.0\n  Install location:    {$this->work_dir}/browsers/chromium-missing";
	}

	private function fake_dry_run( string $output, int $exit_code ): void {
		file_put_contents( $this->fake_bin . '/dry-run.out', $output );
		file_put_contents( $this->fake_bin . '/dry-run.code', (string) $exit_code );
	}

	private function fake_install( string $output, int $exit_code ): void {
		file_put_contents( $this->fake_bin . '/install.out', $output );
		file_put_contents( $this->fake_bin . '/install.code', (string) $exit_code );
	}

	/**
	 * @return string[]
	 */
	private function npx_calls(): array {
		$log = $this->fake_bin . '/calls.log';

		return file_exists( $log ) ? file( $log, FILE_IGNORE_NEW_LINES ) : [];
	}

	private function write_stub( string $name, string $body ): void {
		$path = $this->fake_bin . '/' . $name;

		file_put_contents( $path, "#!/bin/sh\n" . $body . "\n" );
		chmod( $path, 0755 );
	}
}
