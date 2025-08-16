<?php
declare( strict_types=1 );

namespace QIT_CLI\PreCommand\Objects;

use InvalidArgumentException;

/**
 * Anti-corruption layer between JSON manifest formats and domain model.
 * Adapts any version of the manifest JSON into a consistent internal representation.
 *
 * @see https://qit.woo.com/json-schema/test-package
 */
final class TestPackageManifest {
	/**
	 * @var string The package ID.
	 */
	private string $package_id;
	private string $namespace;
	private string $package_name;
	/** @var array<string> */
	private array $tags;
	private string $test_type;
	private string $test_dir;
	private string $description;
	/** @var array<string, array<string, mixed>> */
	private array $requires;
	/** @var array<string, array<string|array<string,mixed>>> */
	private array $phases;
	/** @var array<string, string> */
	private array $test_results;
	/** @var array<string> */
	private array $mu_plugins;
	/** @var array<string, string> */
	private array $env_vars;
	private int $timeout;
	/** @var array{retries?: int, flaky_retries?: int} */
	private array $retry;
	/** @var array<string, array<string, mixed>> */
	private array $subpackages;
	private ?string $parent_package;

	/**
	 * Construct from external data (JSON manifest or cached normalized data).
	 *
	 * @param array<string,mixed> $external_data External representation.
	 */
	public function __construct( array $external_data ) {
		$this->adapt_from_external( $external_data );
	}

	/**
	 * Anti-corruption layer - adapts any external format to internal model.
	 * This is the ONLY place that knows about JSON structure variations.
	 *
	 * @param array<string,mixed> $data External data.
	 */
	private function adapt_from_external( array $data ): void {
		// Check if this is already normalized data from cache
		if ( isset( $data['_normalized'] ) && $data['_normalized'] === true ) {
			$this->load_from_normalized( $data );
			return;
		}

		// Adapt from external JSON formats

		// Package identification - handle v1 vs v2 format
		if ( isset( $data['package'] ) && str_contains( $data['package'], '/' ) ) {
			// v2 format: "package": "woocommerce/checkout"
			$this->package_id                         = $data['package'];
			[ $this->namespace, $this->package_name ] = explode( '/', $this->package_id, 2 );
		} elseif ( isset( $data['namespace'] ) && isset( $data['package'] ) ) {
			// v1 format: separate namespace and package fields
			$this->namespace    = $data['namespace'];
			$this->package_name = $data['package'];
			$this->package_id   = $this->namespace . '/' . $this->package_name;
		} else {
			throw new InvalidArgumentException( 'Cannot determine package identity from manifest data' );
		}

		// Test configuration
		if ( empty( $data['test'] ) ) {
			throw new InvalidArgumentException( 'Manifest missing "test" configuration' );
		}

		$this->phases       = $data['test']['phases'] ?? [];
		$this->test_results = $data['test']['results'] ?? [];

		// Apply defaults for fields that might not exist in older manifests
		$this->phases['globalSetup']    = $this->phases['globalSetup'] ?? [];
		$this->phases['globalTeardown'] = $this->phases['globalTeardown'] ?? [];
		$this->phases['setup']          = $this->phases['setup'] ?? [];
		$this->phases['run']            = $this->phases['run'] ?? [];
		$this->phases['teardown']       = $this->phases['teardown'] ?? [];

		// Optional fields with defaults
		$this->tags           = $data['tags'] ?? [];
		$this->test_type      = $data['test_type'] ?? 'e2e';
		$this->test_dir       = $data['test_dir'] ?? './';
		$this->description    = $data['description'] ?? '';
		$this->requires       = $data['requires'] ?? [];
		$this->mu_plugins     = $data['mu_plugins'] ?? [];
		$this->env_vars       = $this->stringify_env( $data['envs'] ?? [] );
		$this->timeout        = $data['timeout'] ?? 1800;
		$this->retry          = $data['retry'] ?? [
			'times' => 0,
			'delay' => 0,
		];
		$this->subpackages    = $data['subpackages'] ?? [];
		$this->parent_package = $data['parent_package'] ?? null;
	}

	/**
	 * Load from normalized cache data.
	 *
	 * @param array<string,mixed> $data Normalized data.
	 */
	private function load_from_normalized( array $data ): void {
		$this->package_id     = $data['package_id'];
		$this->namespace      = $data['namespace'];
		$this->package_name   = $data['package_name'];
		$this->tags           = $data['tags'];
		$this->test_type      = $data['test_type'];
		$this->test_dir       = $data['test_dir'];
		$this->description    = $data['description'];
		$this->requires       = $data['requires'];
		$this->phases         = $data['phases'];
		$this->test_results   = $data['test_results'];
		$this->mu_plugins     = $data['mu_plugins'];
		$this->env_vars       = $data['env_vars'];
		$this->timeout        = $data['timeout'];
		$this->retry          = $data['retry'];
		$this->subpackages    = $data['subpackages'];
		$this->parent_package = $data['parent_package'];
	}

	/**
	 * Convert environment variables to strings.
	 *
	 * @param array<string,string|int|bool> $env Environment variables.
	 * @return array<string,string>
	 */
	private function stringify_env( array $env ): array {
		$result = [];
		foreach ( $env as $key => $value ) {
			if ( is_bool( $value ) ) {
				$result[ $key ] = $value ? 'true' : 'false';
			} else {
				$result[ $key ] = (string) $value;
			}
		}
		return $result;
	}

	/**
	 * Export normalized internal state for caching.
	 *
	 * @return array<string,mixed> Normalized representation.
	 */
	public function to_array(): array {
		return [
			'_normalized'    => true, // Flag to skip adaptation on reload
			'package_id'     => $this->package_id,
			'namespace'      => $this->namespace,
			'package_name'   => $this->package_name,
			'tags'           => $this->tags,
			'test_type'      => $this->test_type,
			'test_dir'       => $this->test_dir,
			'description'    => $this->description,
			'requires'       => $this->requires,
			'phases'         => $this->phases,
			'test_results'   => $this->test_results,
			'mu_plugins'     => $this->mu_plugins,
			'env_vars'       => $this->env_vars,
			'timeout'        => $this->timeout,
			'retry'          => $this->retry,
			'subpackages'    => $this->subpackages,
			'parent_package' => $this->parent_package,
		];
	}

	/**
	 * Get the package ID.
	 *
	 * @return string The package ID.
	 */
	public function get_package_id(): string {
		return $this->package_id;
	}

	public function get_namespace(): string {
		return $this->namespace;
	}

	public function get_package_name(): string {
		return $this->package_name;
	}

	/**
	 * @return array<string>
	 */
	public function get_tags(): array {
		return $this->tags;
	}

	public function get_test_type(): string {
		return $this->test_type;
	}

	public function get_test_dir(): string {
		return $this->test_dir;
	}

	public function get_description(): string {
		return $this->description;
	}

	/**
	 * @return array<string, array<string, mixed>>
	 */
	public function get_requires(): array {
		return $this->requires;
	}

	/**
	 * @return array<string, array<string|array<string,mixed>>>
	 */
	public function get_phases(): array {
		return $this->phases;
	}

	/**
	 * @return array<string|array<string,mixed>>
	 */
	public function get_phase_commands( string $phase ): array {
		return $this->phases[ $phase ] ?? [];
	}

	/**
	 * @return array<string, string>
	 */
	public function get_test_results(): array {
		return $this->test_results;
	}

	/**
	 * @return array<string>
	 */
	public function get_mu_plugins(): array {
		return $this->mu_plugins;
	}

	/**
	 * @return array<string, string>
	 */
	public function get_env(): array {
		return $this->env_vars;
	}

	public function get_timeout(): int {
		return $this->timeout;
	}

	/**
	 * @return array{retries?: int, flaky_retries?: int}
	 */
	public function get_retry(): array {
		return $this->retry;
	}

	/**
	 * @return array<string, array<string, mixed>>
	 */
	public function get_subpackages(): array {
		return $this->subpackages;
	}

	public function get_parent_package(): ?string {
		return $this->parent_package;
	}

	/**
	 * Check if this is a subpackage.
	 *
	 * @return bool True if this is a subpackage, false otherwise.
	 */
	public function is_subpackage(): bool {
		return $this->parent_package !== null;
	}

	public function is_utility_package(): bool {
		return empty( $this->phases['run'] );
	}

	public function has_subpackages(): bool {
		return ! empty( $this->subpackages );
	}

	public function has_global_setup(): bool {
		return ! empty( $this->phases['globalSetup'] );
	}

	public function has_global_teardown(): bool {
		return ! empty( $this->phases['globalTeardown'] );
	}

	public function has_phase( string $phase ): bool {
		return isset( $this->phases[ $phase ] ) && ! empty( $this->phases[ $phase ] );
	}

	public function has_results(): bool {
		return ! empty( $this->test_results );
	}

	/**
	 * @return array<string, mixed>|null
	 */
	public function get_subpackage( string $identifier ): ?array {
		return $this->subpackages[ $identifier ] ?? null;
	}

	public function requires_plugin( string $slug ): bool {
		return isset( $this->requires['plugins'][ $slug ] );
	}

	public function requires_theme( string $slug ): bool {
		return isset( $this->requires['themes'][ $slug ] );
	}

	public function jsonSerialize(): mixed { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid -- Required by JsonSerializable interface.
		// Return current schema format for external use
		return [
			'package'        => $this->package_id,
			'tags'           => $this->tags,
			'test_type'      => $this->test_type,
			'test_dir'       => $this->test_dir,
			'description'    => $this->description,
			'requires'       => $this->requires,
			'test'           => [
				'phases'  => $this->phases,
				'results' => $this->test_results,
			],
			'mu_plugins'     => $this->mu_plugins,
			'envs'           => $this->env_vars,
			'timeout'        => $this->timeout,
			'retry'          => $this->retry,
			'subpackages'    => $this->subpackages,
			'parent_package' => $this->parent_package,
		];
	}
}
