<?php
declare( strict_types=1 );

namespace QIT_CLI\PreCommand\Objects;

use InvalidArgumentException;

/**
 * Anti-corruption layer between JSON manifest formats and domain model.
 * Adapts any version of the manifest JSON into a consistent internal representation.
 *
 * @see https://raw.githubusercontent.com/woocommerce/qit-cli/trunk/src/src/PreCommand/Schemas/test-package-manifest-schema.json
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
	private string $package_type;
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
	private bool $requires_network;
	private bool $requires_tunnel;

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
		$this->tags         = $data['tags'] ?? [];
		$this->package_type = $data['package_type'] ?? $this->derive_package_type();
		$this->test_type    = $data['test_type'] ?? 'e2e';
		$this->test_dir     = $data['test_dir'] ?? './';
		$this->description  = $data['description'] ?? '';
		$this->requires     = $data['requires'] ?? [];

		// Validate secrets format if present (security check)
		if ( isset( $this->requires['secrets'] ) && is_array( $this->requires['secrets'] ) ) {
			// Check if secrets is an associative array (key-value pairs)
			$first_key = array_key_first( $this->requires['secrets'] );
			if ( $first_key !== null && ! is_int( $first_key ) ) {
				throw new \RuntimeException(
					"Invalid secrets format\n\n" .
					"Secrets must be an array of environment variable names, not key-value pairs.\n\n" .
					"Wrong:   \"secrets\": {\"API_KEY\": \"value\"}\n" .
					"Correct: \"secrets\": [\"API_KEY\"]\n\n" .
					'The actual values should be provided as environment variables when running the test.'
				);
			}
		}
		$this->mu_plugins     = $data['mu_plugins'] ?? [];
		$this->env_vars       = $this->stringify_env( $data['envs'] ?? [] );
		$this->timeout        = $data['timeout'] ?? 1800;
		$this->retry          = $data['retry'] ?? [
			'times' => 0,
			'delay' => 0,
		];
		$this->subpackages    = $data['subpackages'] ?? [];
		$this->parent_package = $data['parent_package'] ?? null;

		// Validate subpackages only override allowed phases
		$this->validate_subpackages();

		// Network requirement - only support new format (requires.network)
		if ( isset( $data['requires']['network'] ) ) {
			if ( is_string( $data['requires']['network'] ) ) {
				$this->requires_network = filter_var( $data['requires']['network'], FILTER_VALIDATE_BOOLEAN );
			} else {
				$this->requires_network = (bool) $data['requires']['network'];
			}
		} else {
			// Default to offline (false) when not specified
			$this->requires_network = false;
		}

		// Tunnel requirement
		// @phan-suppress-next-line PhanTypeInvalidDimOffset -- 'tunnel' is an optional field in requires
		if ( isset( $data['requires']['tunnel'] ) ) {
			// @phan-suppress-next-line PhanTypeInvalidDimOffset
			if ( is_string( $data['requires']['tunnel'] ) ) {
				// @phan-suppress-next-line PhanTypeInvalidDimOffset
				$this->requires_tunnel = filter_var( $data['requires']['tunnel'], FILTER_VALIDATE_BOOLEAN );
			} else {
				// @phan-suppress-next-line PhanTypeInvalidDimOffset
				$this->requires_tunnel = (bool) $data['requires']['tunnel'];
			}
		} else {
			// Default to no tunnel required
			$this->requires_tunnel = false;
		}
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
		$this->package_type   = $data['package_type'] ?? $this->derive_package_type();
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
		// For normalized data, only support new format (requires.network)
		if ( isset( $data['requires']['network'] ) ) {
			if ( is_string( $data['requires']['network'] ) ) {
				$this->requires_network = filter_var( $data['requires']['network'], FILTER_VALIDATE_BOOLEAN );
			} else {
				$this->requires_network = (bool) $data['requires']['network'];
			}
		} else {
			$this->requires_network = false;
		}

		// Tunnel requirement
		// @phan-suppress-next-line PhanTypeInvalidDimOffset -- 'tunnel' is an optional field in requires
		if ( isset( $data['requires']['tunnel'] ) ) {
			// @phan-suppress-next-line PhanTypeInvalidDimOffset
			if ( is_string( $data['requires']['tunnel'] ) ) {
				// @phan-suppress-next-line PhanTypeInvalidDimOffset
				$this->requires_tunnel = filter_var( $data['requires']['tunnel'], FILTER_VALIDATE_BOOLEAN );
			} else {
				// @phan-suppress-next-line PhanTypeInvalidDimOffset
				$this->requires_tunnel = (bool) $data['requires']['tunnel'];
			}
		} else {
			$this->requires_tunnel = false;
		}
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
			'_normalized'      => true, // Flag to skip adaptation on reload
			'package_id'       => $this->package_id,
			'namespace'        => $this->namespace,
			'package_name'     => $this->package_name,
			'package_type'     => $this->package_type,
			'tags'             => $this->tags,
			'test_type'        => $this->test_type,
			'test_dir'         => $this->test_dir,
			'description'      => $this->description,
			'requires'         => $this->requires,
			'phases'           => $this->phases,
			'test_results'     => $this->test_results,
			'mu_plugins'       => $this->mu_plugins,
			'env_vars'         => $this->env_vars,
			'timeout'          => $this->timeout,
			'retry'            => $this->retry,
			'subpackages'      => $this->subpackages,
			'parent_package'   => $this->parent_package,
			'requires_network' => $this->requires_network,
			'requires_tunnel'  => $this->requires_tunnel,
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

	public function requires_network(): bool {
		return $this->requires_network;
	}

	public function requires_tunnel(): bool {
		return $this->requires_tunnel;
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
		return $this->package_type === PackageType::UTILITY || empty( $this->phases['run'] );
	}

	/**
	 * Derive package type from manifest phases (fallback for backwards compatibility).
	 *
	 * @return string Package type: PackageType::TEST or PackageType::UTILITY.
	 */
	private function derive_package_type(): string {
		// A package is a utility if it has NO run phase, otherwise it's a test package.
		return empty( $this->phases['run'] ) ? PackageType::UTILITY : PackageType::TEST;
	}

	/**
	 * Get the package type.
	 *
	 * @return string Package type: PackageType::TEST or PackageType::UTILITY.
	 */
	public function get_package_type(): string {
		return $this->package_type;
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
		// Now plugins is an array, check if slug is in the array
		return isset( $this->requires['plugins'] ) &&
				in_array( $slug, $this->requires['plugins'], true );
	}

	public function requires_theme( string $slug ): bool {
		// Now themes is an array, check if slug is in the array
		return isset( $this->requires['themes'] ) &&
				in_array( $slug, $this->requires['themes'], true );
	}

	/**
	 * Get the list of required plugin slugs.
	 *
	 * @return array<string> Array of plugin slugs.
	 */
	public function get_required_plugins(): array {
		return isset( $this->requires['plugins'] )
			? $this->requires['plugins']
			: [];
	}

	/**
	 * Get the list of required theme slugs.
	 *
	 * @return array<string> Array of theme slugs.
	 */
	public function get_required_themes(): array {
		return isset( $this->requires['themes'] )
			? $this->requires['themes']
			: [];
	}

	/**
	 * Get the filesystem-safe container directory name for this package.
	 *
	 * This is the canonical method for generating container paths.
	 * All components MUST use this for consistency.
	 *
	 * Rules:
	 * 1. Format: namespace-package[-version]
	 * 2. Slash (/) becomes hyphen to separate namespace from package
	 * 3. Preserve valid characters: a-z, A-Z, 0-9, hyphen, underscore, period
	 * 4. Convert to lowercase for consistency
	 * 5. Colon (:) in version becomes hyphen
	 *
	 * Examples:
	 * - woocommerce/my-package -> woocommerce-my-package
	 * - woocommerce/my.package -> woocommerce-my.package
	 * - woocommerce/my_package -> woocommerce-my_package
	 * - some_namespace/some.package -> some_namespace-some.package
	 * - woocommerce/package:1.0.0 -> woocommerce-package-1.0.0
	 *
	 * @param string|null $version Optional version to append (e.g., "1.0.0" becomes "-1.0.0").
	 * @return string Filesystem-safe directory name.
	 */
	public function get_container_directory_name( ?string $version = null ): string {
		// Convert to lowercase but preserve valid characters
		$safe_namespace = strtolower( $this->namespace );
		$safe_package   = strtolower( $this->package_name );

		// Combine with hyphen separator
		$base_name = $safe_namespace . '-' . $safe_package;

		// Add version if provided (colon becomes hyphen)
		if ( $version !== null && $version !== '' ) {
			$safe_version = strtolower( $version );
			$base_name   .= '-' . $safe_version;
		}

		return $base_name;
	}

	/**
	 * Get the full container path for this package.
	 *
	 * @param string|null $version Optional version to include.
	 * @return string Full container path (e.g., /qit/packages/woocommerce-my-package-1-0-0).
	 */
	public function get_container_path( ?string $version = null ): string {
		return '/qit/packages/' . $this->get_container_directory_name( $version );
	}

	/**
	 * Create a container directory name from a package ID string.
	 *
	 * Static helper for when you don't have a manifest object.
	 *
	 * @param string $package_id Package ID in format "namespace/package[:version]".
	 * @return string Filesystem-safe directory name.
	 * @throws InvalidArgumentException If package ID format is invalid.
	 */
	public static function create_container_directory_name( string $package_id ): string {
		// Parse the package ID - must match schema validation pattern
		if ( ! preg_match( '/^([a-zA-Z0-9_.-]+)\/([a-zA-Z0-9_.-]+)(?::([a-zA-Z0-9_.-]+))?$/', $package_id, $matches ) ) {
			throw new InvalidArgumentException( "Invalid package ID format: {$package_id}" );
		}

		$namespace = $matches[1];
		$package   = $matches[2];
		$version   = isset( $matches[3] ) ? $matches[3] : null;

		// Convert to lowercase but preserve valid characters
		$safe_namespace = strtolower( $namespace );
		$safe_package   = strtolower( $package );

		// Combine with hyphen separator
		$base_name = $safe_namespace . '-' . $safe_package;

		// Add version if present
		if ( $version !== null && $version !== '' ) {
			$safe_version = strtolower( $version );
			$base_name   .= '-' . $safe_version;
		}

		return $base_name;
	}

	/**
	 * Create a container path from a package ID string.
	 *
	 * @param string $package_id Package ID in format "namespace/package[:version]".
	 * @return string Full container path.
	 */
	public static function create_container_path( string $package_id ): string {
		return '/qit/packages/' . self::create_container_directory_name( $package_id );
	}

	/**
	 * Validate that subpackages only override allowed phases.
	 * Subpackages must be pure subsets that only differ in test selection.
	 *
	 * @throws InvalidArgumentException If subpackage overrides disallowed phases.
	 */
	private function validate_subpackages(): void {
		if ( empty( $this->subpackages ) ) {
			return;
		}

		$disallowed_phases = [ 'globalSetup', 'globalTeardown', 'setup', 'teardown' ];

		foreach ( $this->subpackages as $subpackage_id => $subpackage_config ) {
			// Skip if no test configuration
			if ( ! isset( $subpackage_config['test']['phases'] ) ) {
				continue;
			}

			$phases = $subpackage_config['test']['phases'];

			// Check for disallowed phase overrides
			foreach ( $disallowed_phases as $phase ) {
				if ( isset( $phases[ $phase ] ) && ! empty( $phases[ $phase ] ) ) {
					throw new InvalidArgumentException(
						"Subpackage '{$subpackage_id}' cannot override '{$phase}' phase. " .
						"Subpackages are pure subsets and can only override the 'run' phase to select which tests to execute. " .
						'If you need different setup/teardown, create a separate test package instead.'
					);
				}
			}

			// Ensure run phase is specified (it's the only thing they should override)
			if ( ! isset( $phases['run'] ) || empty( $phases['run'] ) ) {
				throw new InvalidArgumentException(
					"Subpackage '{$subpackage_id}' must specify a 'run' phase. " .
					'Subpackages exist to run a subset of tests from the parent package.'
				);
			}
		}
	}

	/**
	 * Serialize to JSON.
	 *
	 * @return array<string, mixed>
	 */
	public function jsonSerialize() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid -- Required by JsonSerializable interface.
		// Return current schema format for external use
		return [
			'package'        => $this->package_id,
			'tags'           => $this->tags,
			'package_type'   => $this->package_type,
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
