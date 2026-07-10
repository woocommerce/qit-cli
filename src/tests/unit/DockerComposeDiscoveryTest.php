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
 * We refuse v1 explicitly rather than limping along on it. Note that the binary name does
 * not identify the major version -- Compose v2 also ships as a standalone "docker-compose"
 * binary -- so discovery gates on the reported version, not on which binary answered.
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

	/**
	 * Stub "docker", where "docker compose version" prints $version_output. A null
	 * $version_output means the CLI plugin is absent, which Docker reports as a non-zero exit.
	 */
	private function stub_docker( ?string $version_output ): void {
		$compose = is_null( $version_output )
			? 'echo "docker: \'compose\' is not a docker command." >&2; exit 1'
			: sprintf( 'echo "%s"; exit 0', $version_output );

		$this->stub( 'docker', sprintf( 'if [ "$1" = "compose" ]; then %s; fi; exit 0', $compose ) );
	}

	private function stub_docker_compose( string $version_output ): void {
		$this->stub( 'docker-compose', sprintf( 'echo "%s"; exit 0', $version_output ) );
	}

	private function find_docker_compose(): array {
		putenv( 'PATH=' . $this->fake_bin );

		$docker = ( new \ReflectionClass( Docker::class ) )->newInstanceWithoutConstructor();

		return $docker->find_docker_compose();
	}

	public function test_it_prefers_the_v2_cli_plugin(): void {
		$this->stub_docker( 'Docker Compose version v2.24.5' );
		$this->stub_docker_compose( 'docker-compose version 1.29.2, build 5becea4c' );

		$this->assertSame( [ 'docker', 'compose' ], $this->find_docker_compose() );
	}

	/**
	 * The regression the version gate exists to prevent: modern Docker Engine without the CLI
	 * plugin, but with the standalone Compose v2 binary. Gating on the binary name would
	 * hard-fail this working setup while claiming it was running EOL v1.
	 */
	public function test_it_accepts_the_standalone_v2_binary(): void {
		$this->stub_docker( null );
		$this->stub_docker_compose( 'Docker Compose version v2.24.5' );

		$this->assertSame( [ 'docker-compose' ], $this->find_docker_compose() );
	}

	/**
	 * We reject v1 specifically, not "everything that isn't v2" -- a future major must not
	 * start failing against an equality check.
	 */
	public function test_it_accepts_a_future_major_version(): void {
		$this->stub_docker( 'Docker Compose version v3.0.1' );

		$this->assertSame( [ 'docker', 'compose' ], $this->find_docker_compose() );
	}

	public function test_it_refuses_the_legacy_v1_binary_instead_of_falling_back(): void {
		// The vendor's box: modern Docker Engine, no compose plugin, standalone v1 present.
		$this->stub_docker( null );
		$this->stub_docker_compose( 'docker-compose version 1.25.0, build unknown' );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessageMatches( '/"docker-compose" reports v1/' );

		$this->find_docker_compose();
	}

	/**
	 * A v1 answering under one name must not mask a usable v2 under the other.
	 */
	public function test_it_keeps_probing_past_a_v1_plugin_to_find_a_v2_binary(): void {
		$this->stub_docker( 'docker-compose version 1.29.2, build 5becea4c' );
		$this->stub_docker_compose( 'Docker Compose version v2.24.5' );

		$this->assertSame( [ 'docker-compose' ], $this->find_docker_compose() );
	}

	public function test_it_explains_how_to_install_when_nothing_is_found(): void {
		$this->stub_docker( null );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessageMatches( '/Could not find Docker Compose v2 or higher/' );

		$this->find_docker_compose();
	}
}
