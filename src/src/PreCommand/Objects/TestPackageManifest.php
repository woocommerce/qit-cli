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
	private string $namespace;
	private string $package;
	/** @var string[] */
	private array $tags;
	private string $test_type;
	private string $test_dir;
	private string $description;

	/** @var array<string,mixed> */
	private array $requires;

	/** @var array{globalSetup?:array<string,mixed>,setup?:array<string,mixed>,run:array<string,mixed>,teardown?:array<string,mixed>,globalTeardown?:array<string,mixed>} */
	private array $phases;

	/** @var array<string,string> */
	private array $test_results;

	/** @var string[] */
	private array $mu_plugins;

	/** @var array<string,string> */
	private array $env_vars;

	private int $timeout;
	/** @var array{times:int,delay:int} */
	private array $retry;

	/**
	 * @param array<string,mixed> $payload – already validated & normalised by parser.
	 */
	public function __construct( array $payload ) {
		if ( empty( $payload['test'] ) || empty( $payload['package'] ) ) {
			throw new InvalidArgumentException( 'Manifest missing mandatory keys "test" or "package".' );
		}

		// Parse package identifier into namespace and name
		if ( ! str_contains( $payload['package'], '/' ) ) {
			throw new InvalidArgumentException( 'Package must be in format "namespace/name" (e.g., "woocommerce/checkout-tests").' );
		}

		[ $this->namespace, $this->package ] = explode( '/', $payload['package'], 2 );
		$this->tags        = $payload['tags'] ?? [];
		$this->test_type   = $payload['test_type'] ?? 'e2e';
		$this->test_dir    = $payload['test_dir'] ?? './';
		$this->description = $payload['description'] ?? '';

		$this->requires     = $payload['requires'] ?? [];
		$this->phases       = $payload['test']['phases'] ?? [];
		$this->test_results = $payload['test']['results'] ?? [];
		$this->mu_plugins   = $payload['mu_plugins'] ?? [];
		$this->env_vars     = $this->stringifyEnv( $payload['envs'] ?? [] );
		$this->timeout      = (int) ( $payload['timeout'] ?? 1800 );
		$this->retry        = $payload['retry'] ?? [
			'times' => 0,
			'delay' => 0,
		];
	}

	/**
	 * ----------------------------------------------------------------
	 * Simple getters
	 * ----------------------------------------------------------------
	 */
	public function getNamespace(): string {
		return $this->namespace;
	}

	/**
	 * Get the package name (without namespace).
	 * @deprecated Use getPackageName() for clarity
	 */
	public function getPackage(): string {
		return $this->package;
	}

	/**
	 * Get the package name (without namespace).
	 */
	public function getPackageName(): string {
		return $this->package;
	}

	/**
	 * Get the full package identifier (namespace/name).
	 */
	public function getPackageId(): string {
		return $this->namespace . '/' . $this->package;
	}

	/**
	 * @return string[]
	 */
	public function getTags(): array {
		return $this->tags;
	}

	public function getTestType(): string {
		return $this->test_type;
	}

	public function getTestDir(): string {
		return $this->test_dir;
	}

	public function getDescription(): string {
		return $this->description;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function getRequires(): array {
		return $this->requires;
	}

	/**
	 * @return array{globalSetup?:array<string,mixed>,setup?:array<string,mixed>,run:array<string,mixed>,teardown?:array<string,mixed>,globalTeardown?:array<string,mixed>}
	 */
	public function getPhases(): array {
		return $this->phases;
	}

	/**
	 * @return array<string,string>
	 */
	public function getTestResults(): array {
		return $this->test_results;
	}

	/**
	 * @return string[]
	 */
	public function getMuPlugins(): array {
		return $this->mu_plugins;
	}

	/**
	 * @return array<string,string>
	 */
	public function getEnv(): array {
		return $this->env_vars;
	}

	public function getTimeout(): int {
		return $this->timeout;
	}

	/**
	 * @return array{times:int,delay:int}
	 */
	public function getRetry(): array {
		return $this->retry;
	}

	/**
	 * ----------------------------------------------------------------
	 * Convenience helpers
	 * ----------------------------------------------------------------
	 */
	public function isE2E(): bool {
		return $this->test_type === 'e2e';
	}


	/**
	 * @return string[]|array<string,mixed>[] list of commands or command‑objects
	 */
	public function getPhaseCommands( string $phase ): array {
		return $this->phases[ $phase ] ?? [];
	}

	/**
	 * Check if a specific phase is defined
	 */
	public function hasPhase( string $phase ): bool {
		return isset( $this->phases[ $phase ] ) && ! empty( $this->phases[ $phase ] );
	}

	/**
	 * Check if results are defined
	 */
	public function hasResults(): bool {
		return ! empty( $this->test_results );
	}

	/**
	 * ----------------------------------------------------------------
	 * JsonSerializable
	 * ----------------------------------------------------------------
	 */
	public function jsonSerialize(): mixed {
		$result = [
			'namespace' => $this->namespace,
			'package'   => $this->package,
			'test_type' => $this->test_type,
			'test_dir'  => $this->test_dir,
			'test'      => [
				'phases'  => $this->phases,
				'results' => $this->test_results,
			],
		];

		// Only include non-empty optional fields to keep the file slimmer
		if ( ! empty( $this->tags ) ) {
			$result['tags'] = $this->tags;
		}
		if ( ! empty( $this->description ) ) {
			$result['description'] = $this->description;
		}
		if ( ! empty( $this->requires ) ) {
			$result['requires'] = $this->requires;
		}
		if ( ! empty( $this->mu_plugins ) ) {
			$result['mu_plugins'] = $this->mu_plugins;
		}
		if ( ! empty( $this->env_vars ) ) {
			$result['envs'] = $this->env_vars;
		}
		if ( $this->timeout !== 1800 ) { // Only include if not default
			$result['timeout'] = $this->timeout;
		}
		if ( $this->retry['times'] !== 0 || $this->retry['delay'] !== 0 ) { // Only include if not default
			$result['retry'] = $this->retry;
		}

		return $result;
	}

	/**
	 * ----------------------------------------------------------------
	 * Internals
	 * ----------------------------------------------------------------
	 */

	/**
	 * @param array<string,string|int|bool> $env
	 *
	 * @return array<string,string>
	 */
	private function stringifyEnv( array $env ): array {
		foreach ( $env as $k => $v ) {
			$env[ $k ] = (string) $v;
		}

		return $env;
	}
}
