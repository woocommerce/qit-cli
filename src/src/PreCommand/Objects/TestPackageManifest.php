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
	private string $vendor;
	private string $package;
	private string $version;
	/** @var string[] */
	private array $tags;
	private string $test_type;
	private ?string $test_dir;
	private string $description;

	/** @var array<string,mixed> */
	private array $requires;

	/** @var array{global:array{setup?:array<string,mixed>,teardown?:array<string,mixed>},test:array{setup?:array<string,mixed>,run?:array<string,mixed>,teardown?:array<string,mixed>}} */
	private array $lifecycle;

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
		// — Required —
		if ( empty( $payload['test_type'] ) || empty( $payload['lifecycle'] ) ||
			empty( $payload['vendor'] ) || empty( $payload['package'] ) || empty( $payload['version'] ) ) {
			throw new InvalidArgumentException( 'Manifest missing mandatory keys "test_type", "lifecycle", "vendor", "package", or "version".' );
		}

		$this->vendor      = $payload['vendor'];
		$this->package     = $payload['package'];
		$this->version     = $payload['version'];
		$this->tags        = $payload['tags'] ?? [];
		$this->test_type   = $payload['test_type'];
		$this->test_dir    = $payload['test_dir'] ?? null;
		$this->description = $payload['description'] ?? '';

		$this->requires     = $payload['requires'] ?? [];
		$this->lifecycle    = $payload['lifecycle'];
		$this->test_results = $payload['test_results'] ?? [];
		$this->mu_plugins   = $payload['mu_plugins'] ?? [];
		$this->env_vars     = $this->stringifyEnv( $payload['env_vars'] ?? [] );
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
	public function getVendor(): string {
		return $this->vendor;
	}

	public function getPackage(): string {
		return $this->package;
	}

	public function getVersion(): string {
		return $this->version;
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

	public function getTestDir(): ?string {
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
	 * @return array{global:array{setup?:array<string,mixed>,teardown?:array<string,mixed>},test:array{setup?:array<string,mixed>,run?:array<string,mixed>,teardown?:array<string,mixed>}}
	 */
	public function getLifecycle(): array {
		return $this->lifecycle;
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
	public function getEnvVars(): array {
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

	public function needsPlugin( string $slug ): bool {
		return isset( $this->requires['plugins'][ $slug ] );
	}

	public function needsTheme( string $slug ): bool {
		return isset( $this->requires['themes'][ $slug ] );
	}

	/**
	 * @return string[]|array<string,mixed>[] list of commands or command‑objects
	 */
	public function getLifecycleCommands( string $phase, string $hook ): array {
		return $this->lifecycle[ $phase ][ $hook ] ?? [];
	}

	/**
	 * ----------------------------------------------------------------
	 * JsonSerializable
	 * ----------------------------------------------------------------
	 */
	public function jsonSerialize(): mixed {
		return [
			'vendor'       => $this->vendor,
			'package'      => $this->package,
			'version'      => $this->version,
			'tags'         => $this->tags,
			'test_type'    => $this->test_type,
			'test_dir'     => $this->test_dir,
			'description'  => $this->description,
			'requires'     => $this->requires,
			'lifecycle'    => $this->lifecycle,
			'test_results' => $this->test_results,
			'mu_plugins'   => $this->mu_plugins,
			'env_vars'     => $this->env_vars,
			'timeout'      => $this->timeout,
			'retry'        => $this->retry,
		];
	}

	/**
	 * ----------------------------------------------------------------
	 * Internals
	 * ----------------------------------------------------------------
	 */

	/**
	 * @param array<string,string|int|bool> $env
	 * @return array<string,string>
	 */
	private function stringifyEnv( array $env ): array {
		foreach ( $env as $k => $v ) {
			$env[ $k ] = (string) $v;
		}

		return $env;
	}
}
