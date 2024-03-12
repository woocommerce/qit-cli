<?php

namespace QITE2E\Environment;

use QIT_CLI\Environment\ExtensionDownloader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\NullOutput;

class ExtensionDownloaderTest extends TestCase {
	public function provider_detect_type() {
		return [
			// Format: ['input', 'expectedOutput']
			[ '123', 'id' ], // Numeric should return 'id'
			[ 'https://example.com', 'url' ], // URL should return 'url'
			[ dirname( __DIR__ ), 'path' ], // Existing file path should return 'path'
			[ 'non-existent-file', 'slug' ], // Non-existent path should return 'slug'
		];
	}

	/**
	 * Test for the detect_type method.
	 *
	 * @dataProvider provider_detect_type
	 *
	 * @param string $input
	 * @param string $expectedOutput
	 */
	public function test_detect_type( $input, $expectedOutput ) {
		$this->markTestSkipped();
		$sut = new class( new NullOutput() ) extends ExtensionDownloader {
			public function detect_type( string $extension ): string {
				return parent::detect_type( $extension );
			}
		};

		$this->assertSame( $expectedOutput, $sut->detect_type( $input ) );
	}

	public function test_group_by_type() {
		$this->markTestSkipped();
		$sut = new class( new NullOutput() ) extends ExtensionDownloader {
			public function group_by_type( array $extensions ): array {
				return parent::group_by_type( $extensions );
			}
		};

		$currentDirectory = dirname( __FILE__ ); // Path to current directory
		$extensions       = [
			'123',                  // ID
			'https://example.com',  // URL
			$currentDirectory,      // Path
			'nonexistentpath',      // Slug (assuming no such file exists)
		];

		$expectedGrouped = [
			'path' => [ $currentDirectory ],
			'slug' => [ 'nonexistentpath' ],
			'id'   => [ '123' ],
			'url'  => [ 'https://example.com' ],
		];

		$grouped = $sut->group_by_type( $extensions );

		$this->assertSame( $expectedGrouped, $grouped );
	}

	public function test_group_by_downloadable() {
		$this->markTestSkipped();
		$sut = new class(new NullOutput()) extends ExtensionDownloader {
			public function group_by_downloadable(array $extensions): array {
				return parent::group_by_downloadable($extensions);
			}
		};

		$currentDirectory = dirname(__FILE__);
		$extensions = [
			'123',                  // ID
			'https://example.com',  // URL
			$currentDirectory,      // Path
			'nonexistentpath',      // Slug
		];

		$expectedGrouped = [
			'slug' => ['nonexistentpath'],
			'id'   => ['123'],
		];

		$grouped = $sut->group_by_downloadable($extensions);

		$this->assertSame($expectedGrouped, $grouped);
	}

}
