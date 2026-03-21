<?php

namespace QIT_CLI_Tests\BreakingChanges;

use PHPUnit\Framework\TestCase;
use QIT_CLI\BreakingChanges\PluginSourceResolver;
use QIT_CLI\CachedDownloader;

class PluginSourceResolverTest extends TestCase {
	private PluginSourceResolver $resolver;

	protected function setUp(): void {
		parent::setUp();

		$downloader     = $this->createMock( CachedDownloader::class );
		$this->resolver = new PluginSourceResolver( $downloader );
	}

	public function test_resolves_local_directory(): void {
		$dir = __DIR__ . '/fixtures/sample-plugin-v1';

		$result = $this->resolver->resolve( $dir );

		$this->assertEquals( $dir, $result );
	}

	public function test_resolves_local_directory_strips_trailing_slash(): void {
		$dir = __DIR__ . '/fixtures/sample-plugin-v1/';

		$result = $this->resolver->resolve( $dir );

		$this->assertEquals( rtrim( $dir, '/' ), $result );
	}

	public function test_resolves_local_zip(): void {
		// Create a temporary zip with a plugin directory inside.
		$tmp_dir = sys_get_temp_dir() . '/qit-test-zip-' . uniqid();
		mkdir( $tmp_dir, 0755, true );

		$zip_path = $tmp_dir . '/test-plugin.zip';
		$zip      = new \ZipArchive();
		$zip->open( $zip_path, \ZipArchive::CREATE );
		$zip->addFromString( 'test-plugin/test-plugin.php', '<?php // Plugin' );
		$zip->close();

		try {
			$result = $this->resolver->resolve( $zip_path );

			$this->assertDirectoryExists( $result );
			$this->assertFileExists( $result . '/test-plugin.php' );
		} finally {
			// Cleanup.
			$this->recursive_rmdir( $tmp_dir );
			// Clean up extracted dir.
			$extract_base = sys_get_temp_dir() . '/qit-breaking-changes/';
			if ( is_dir( $extract_base ) ) {
				$this->recursive_rmdir( $extract_base );
			}
		}
	}

	public function test_download_wporg_plugin(): void {
		$zip_path = $this->create_mock_plugin_zip( 'my-plugin' );

		$downloader = $this->createMock( CachedDownloader::class );
		$downloader->expects( $this->once() )
			->method( 'download' )
			->with( 'wporg_plugin', 'my-plugin', $this->anything(), [ 'version' => '1.0.0' ] )
			->willReturn( [
				'path'     => $zip_path,
				'metadata' => [ 'version' => '1.0.0' ],
				'cached'   => false,
			] );

		$resolver = new PluginSourceResolver( $downloader );

		try {
			$result = $resolver->resolve( 'my-plugin', '1.0.0' );

			$this->assertDirectoryExists( $result );
		} finally {
			unlink( $zip_path );
			$extract_base = sys_get_temp_dir() . '/qit-breaking-changes/';
			if ( is_dir( $extract_base ) ) {
				$this->recursive_rmdir( $extract_base );
			}
		}
	}

	private function create_mock_plugin_zip( string $slug ): string {
		$tmp = tempnam( sys_get_temp_dir(), 'zip' );
		$zip = new \ZipArchive();
		$zip->open( $tmp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE );
		$zip->addFromString( "{$slug}/{$slug}.php", "<?php\n// Plugin Name: {$slug}" );
		$zip->close();

		return $tmp;
	}

	private function recursive_rmdir( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}

		$files = array_diff( scandir( $dir ), [ '.', '..' ] );
		foreach ( $files as $file ) {
			$path = $dir . '/' . $file;
			is_dir( $path ) ? $this->recursive_rmdir( $path ) : unlink( $path );
		}
		rmdir( $dir );
	}
}
