<?php
declare( strict_types=1 );

namespace QIT_CLI\PreCommand\Objects;

use InvalidArgumentException;

/**
 * Immutable representation of a validated test‑package manifest.
 *
 * @see https://qit.woo.com/json-schema/test-package
 */
final class TestPackageManifest implements \JsonSerializable {
	/** @var string[] */
	private array $tags;
	private string $testType;
	private ?string $testDir;
	private string $description;

	/** @var array<string,mixed> */
	private array $requires;

	/** @var array{global:array{setup?:array,teardown?:array},test:array{setup?:array,run?:array,teardown?:array}} */
	private array $lifecycle;

	/** @var array<string,string> */
	private array $testResults;

	/** @var string[] */
	private array $muPlugins;

	/** @var array<string,string> */
	private array $envVars;

	private int $timeout;
	/** @var array{times:int,delay:int} */
	private array $retry;

	/**
	 * @param array<string,mixed> $payload – already validated & normalised by parser
	 */
	public function __construct( array $payload ) {
		// — Required —
		if ( empty( $payload['test_type'] ) || empty( $payload['lifecycle'] ) ) {
			throw new InvalidArgumentException( 'Manifest missing mandatory keys "test_type" or "lifecycle".' );
		}

		$this->tags        = $payload['tags'] ?? [];
		$this->testType    = $payload['test_type'];
		$this->testDir     = $payload['test_dir'] ?? null;
		$this->description = $payload['description'] ?? '';

		$this->requires    = $payload['requires'] ?? [];
		$this->lifecycle   = $payload['lifecycle'];
		$this->testResults = $payload['test_results'] ?? [];
		$this->muPlugins   = $payload['mu_plugins'] ?? [];
		$this->envVars     = $this->stringifyEnv( $payload['env_vars'] ?? [] );
		$this->timeout     = (int) ( $payload['timeout'] ?? 1800 );
		$this->retry       = $payload['retry'] ?? [ 'times' => 0, 'delay' => 0 ];
	}

	/* --------------------------------------------------------------------- */
	/*  Simple getters                                                       */
	/* --------------------------------------------------------------------- */

	public function getTags(): array {
		return $this->tags;
	}

	public function getTestType(): string {
		return $this->testType;
	}

	public function getTestDir(): ?string {
		return $this->testDir;
	}

	public function getDescription(): string {
		return $this->description;
	}

	public function getRequires(): array {
		return $this->requires;
	}

	public function getLifecycle(): array {
		return $this->lifecycle;
	}

	public function getTestResults(): array {
		return $this->testResults;
	}

	public function getMuPlugins(): array {
		return $this->muPlugins;
	}

	public function getEnvVars(): array {
		return $this->envVars;
	}

	public function getTimeout(): int {
		return $this->timeout;
	}

	public function getRetry(): array {
		return $this->retry;
	}

	/* --------------------------------------------------------------------- */
	/*  Convenience helpers                                                  */
	/* --------------------------------------------------------------------- */

	public function isE2E(): bool {
		return $this->testType === 'e2e';
	}

	public function needsPlugin( string $slug ): bool {
		return isset( $this->requires['plugins'][ $slug ] );
	}

	public function needsTheme( string $slug ): bool {
		return isset( $this->requires['themes'][ $slug ] );
	}

	/**
	 * @return string[]|array[] list of commands or command‑objects
	 */
	public function getLifecycleCommands( string $phase, string $hook ): array {
		return $this->lifecycle[ $phase ][ $hook ] ?? [];
	}

	/* --------------------------------------------------------------------- */
	/*  JsonSerializable                                                     */
	/* --------------------------------------------------------------------- */

	public function jsonSerialize(): mixed {
		return [
			'tags'         => $this->tags,
			'test_type'    => $this->testType,
			'test_dir'     => $this->testDir,
			'description'  => $this->description,
			'requires'     => $this->requires,
			'lifecycle'    => $this->lifecycle,
			'test_results' => $this->testResults,
			'mu_plugins'   => $this->muPlugins,
			'env_vars'     => $this->envVars,
			'timeout'      => $this->timeout,
			'retry'        => $this->retry,
		];
	}

	/* --------------------------------------------------------------------- */
	/*  Internals                                                            */
	/* --------------------------------------------------------------------- */

	/** @param array<string,string|int|bool> $env */
	private function stringifyEnv( array $env ): array {
		foreach ( $env as $k => $v ) {
			$env[ $k ] = (string) $v;
		}

		return $env;
	}
}
