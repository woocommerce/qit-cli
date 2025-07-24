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
/** @var string[] */
private array $tags;
	private string $test_type;
	private string $test_dir;
	private string $description;

	/** @var array<string,mixed> */
	private array $requires;

	/** @var array{beforeAllPlugins?:array<string,mixed>,setup?:array<string,mixed>,run:array<string,mixed>,teardown?:array<string,mixed>,afterAllPlugins?:array<string,mixed>} */
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
		if ( empty( $payload['test_type'] ) || empty( $payload['test'] ) ||
			 empty( $payload['vendor'] ) || empty( $payload['package'] ) ) {
			throw new InvalidArgumentException( 'Manifest missing mandatory keys "test_type", "test", "vendor", or "package".' );
		}

		// Add defensive check for required run phase
		if ( ! isset( $payload['test']['phases']['run'] ) ) {
			throw new InvalidArgumentException( 'Manifest missing mandatory "test.phases.run" key.' );
		}

		$this->vendor      = $payload['vendor'];
		$this->package     = $payload['package'];
		$this->tags        = $payload['tags'] ?? [];
		$this->test_type   = $payload['test_type'];
		$this->test_dir    = $payload['test_dir'] ?? './';
		$this->description = $payload['description'] ?? '';

		$this->requires     = $payload['requires'] ?? [];
		$this->phases       = $payload['test']['phases'] ?? [];
		$this->test_results = $payload['test']['results'] ?? [];
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
	 * @return array{beforeAllPlugins?:array<string,mixed>,setup?:array<string,mixed>,run:array<string,mixed>,teardown?:array<string,mixed>,afterAllPlugins?:array<string,mixed>}
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
	public function getPhaseCommands( string $phase ): array {
		return $this->phases[ $phase ] ?? [];
	}

	/**
	 * ----------------------------------------------------------------
	 * JsonSerializable
	 * ----------------------------------------------------------------
	 */
	public function jsonSerialize(): mixed {
		$result = [
			'vendor'    => $this->vendor,
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
			$result['env_vars'] = $this->env_vars;
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
	 * @return array<string,string>
	 */
	private function stringifyEnv( array $env ): array {
		foreach ( $env as $k => $v ) {
			$env[ $k ] = (string) $v;
		}

		return $env;
	}
}
