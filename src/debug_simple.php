<?php

require_once 'vendor/autoload.php';

use QIT_CLI\App;
use QIT_CLI\Commands\TestPackages\PackageDownloadCommand;
use QIT_CLI\TestPackageDownloader;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use function QIT_CLI\get_manager_url;

// Initialize the app
$container = new \Illuminate\Container\Container();
require_once 'src/bootstrap.php';

$downloader = App::make( TestPackageDownloader::class );
$command = new PackageDownloadCommand( $downloader );

$application = new Application();
$application->add( $command );

$command_tester = new CommandTester( $command );

$temp_dir = sys_get_temp_dir() . '/' . uniqid( 'qit_test_download_' );
mkdir( $temp_dir, 0755, true );

// Mock the download URLs API response
App::setVar( sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/test-package-download-urls' ), json_encode( [
'urls' => [
		'vendor/test-package:1.0.0' => [
			'url' => 'https://example.com/test-package.zip',
			'checksum' => hash( 'sha256', 'test-content' ),
			'size' => 1024,
			'version' => '1.0.0',
		],
	],
] ) );

// Mock the actual file download
App::setVar( 'mock_https://example.com/test-package.zip', 'test-content' );

// Execute command
try {
	$exit_code = $command_tester->execute( [
'packages' => [ 'vendor/test-package:1.0.0' ],
		'--output-dir' => $temp_dir,
		'--no-install' => true,
	] );

	echo "Exit code: $exit_code\n";
	echo "Output:\n" . $command_tester->getDisplay() . "\n";
} catch ( Exception $e ) {
	echo "Exception: " . $e->getMessage() . "\n";
	echo "Trace: " . $e->getTraceAsString() . "\n";
}

// Clean up
function recursive_rmdir( string $dir ): void {
	if ( ! is_dir( $dir ) ) {
		return;
	}

	$files = array_diff( scandir( $dir ), [ '.', '..' ] );
	foreach ( $files as $file ) {
		$path = $dir . '/' . $file;
		is_dir( $path ) ? recursive_rmdir( $path ) : unlink( $path );
	}
	rmdir( $dir );
}

recursive_rmdir( $temp_dir );
