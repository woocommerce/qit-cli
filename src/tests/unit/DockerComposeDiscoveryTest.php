<?php

namespace QIT_CLI_Tests;

use PHPUnit\Framework\TestCase;
use QIT_CLI\Environment\Docker;

/**
 * Coverage for QIT-1003: falling back to the legacy standalone "docker-compose" (v1)
 * surfaced as "Failed to parse environment JSON" for vendors. Compose v1 resolves our
 * version-less environment files to Compose file format 1, where it interpolates with
 * Python's stdlib string.Template and rejects "${FIXUID:-1000}" outright.
 *
 * We now refuse v1 explicitly rather than limping along on it.
 */
class DockerComposeDiscoveryTest extends TestCase {
	/** @var string */
	private $fake_bin;

	/** @var string|false */
	private $original_path;

	protected function setUp(): void {
		parent::setUp();

		if ( stripos( PHP_OS, 'WIN' ) === 0 ) {
			$this->markTestSkipped( 'Relies on POSIX shell stubs on PATH.' );
		}

		$this->original_path = getenv( 'PATH' );
		$this->fake_bin      = sys_get_temp_dir() . '/qit-1003-' . uniqid();

		mkdir( $this->fake_bin );
	}

	protected function tearDown(): void {
		foreach ( glob( $this->fake_bin . '/*' ) ?: [] as $stub ) {
			unlink( $stub );
		}

		if ( is_dir( $this->fake_bin ) ) {
			rmdir( $this->fake_bin );
		}

		putenv( 'PATH=' . $this->original_path );

		parent::tearDown();
	}

	/**
	 * Write an executable stub onto a PATH that contains nothing else, so discovery
	 * only ever sees the binaries a given test declares.
	 */
	private function stub( string $name, string $body ): void {
		$path = $this->fake_bin . '/' . $name;

		file_put_contents( $path, "#!/bin/sh\n" . $body . "\n" );
		chmod( $path, 0755 );
	}

	private function find_docker_compose(): array {
		putenv( 'PATH=' . $this->fake_bin );

		$docker = ( new \ReflectionClass( Docker::class ) )->newInstanceWithoutConstructor();

		return $docker->find_docker_compose();
	}

	public function test_it_prefers_docker_compose_v2(): void {
		$this->stub( 'docker', '[ "$1" = "compose" ] && exit 0; exit 0' );

		$this->assertSame( [ 'docker', 'compose' ], $this->find_docker_compose() );
	}

	public function test_it_refuses_the_legacy_v1_binary_instead_of_falling_back(): void {
		// The vendor's box: modern Docker Engine, no compose plugin, standalone v1 present.
		$this->stub( 'docker', '[ "$1" = "compose" ] && exit 1; exit 0' );
		$this->stub( 'docker-compose', 'echo "docker-compose version 1.25.0, build unknown"; exit 0' );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessageMatches( '/legacy "docker-compose" \(v1\)/' );

		$this->find_docker_compose();
	}

	public function test_it_explains_how_to_install_when_nothing_is_found(): void {
		$this->stub( 'docker', '[ "$1" = "compose" ] && exit 1; exit 0' );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessageMatches( '/Could not find Docker Compose v2/' );

		$this->find_docker_compose();
	}
}
