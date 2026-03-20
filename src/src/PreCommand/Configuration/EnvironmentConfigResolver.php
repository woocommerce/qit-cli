<?php

namespace QIT_CLI\PreCommand\Configuration;

/**
 * Declarative environment configuration resolver.
 *
 * Collects version pins, extension requests, and scalar config from all sources
 * (CLI flags, qit.json config, test package deps, SUT), then resolves with
 * deterministic precedence in a single pass.
 *
 * Replaces the imperative mutation pipeline in UpEnvironmentCommand::applyCliOverrides().
 */
class EnvironmentConfigResolver {

	/** @var array<string, array{value: string, priority: int}> Scalar values with priority. */
	private array $scalars = [];

	/** @var array<string, array{version: string, priority: int}> Version pins by slug. */
	private array $version_pins = [];

	/** @var array<string, array<string, mixed>> Extension requests by slug (plugins). */
	private array $plugin_requests = [];

	/** @var array<string, array<string, mixed>> Extension requests by slug (themes). */
	private array $theme_requests = [];

	/** @var array<string> PHP extensions. */
	private array $php_extensions = [];

	/** @var array<string> Volumes. */
	private array $volumes = [];

	/** @var array<string, string> Environment variables. */
	private array $envs = [];

	// Priority constants (lower = higher priority)
	const PRIORITY_CLI      = 1;
	const PRIORITY_CONFIG   = 2;
	const PRIORITY_DEFAULT  = 3;
	const PRIORITY_TEST_PKG = 4;

	/** @var array<string, string> Short-form to long-form key aliases. */
	private static array $key_aliases = [
		'php' => 'php_version',
		'wp'  => 'wordpress_version',
		'woo' => 'woocommerce_version',
	];

	/** @var array<string, string> Default values for scalar params. */
	private static array $defaults = [
		'php_version'       => '8.2',
		'wordpress_version' => 'stable',
		// woocommerce_version has NO default — intentionally absent
	];

	/**
	 * Load values from qit.json environment config.
	 *
	 * @param array<string, mixed> $config The environment block from qit.json.
	 * @return self
	 */
	public function loadFromConfig( array $config ): self {
		// Normalize short-form keys
		foreach ( self::$key_aliases as $short => $long ) {
			if ( isset( $config[ $short ] ) && ! isset( $config[ $long ] ) ) {
				$config[ $long ] = $config[ $short ];
				unset( $config[ $short ] );
			}
		}

		// Scalar version params from config
		foreach ( [ 'php_version', 'wordpress_version' ] as $key ) {
			if ( isset( $config[ $key ] ) && $config[ $key ] !== '' ) {
				$this->addScalar( $key, (string) $config[ $key ], self::PRIORITY_CONFIG );
			}
		}

		// WooCommerce version from config scalar = version pin
		if ( isset( $config['woocommerce_version'] ) && $config['woocommerce_version'] !== '' ) {
			$this->addVersionPin( 'woocommerce', (string) $config['woocommerce_version'], self::PRIORITY_CONFIG );
		}

		// Boolean/scalar config
		if ( isset( $config['object_cache'] ) ) {
			$this->addScalar( 'object_cache', $config['object_cache'] ? '1' : '0', self::PRIORITY_CONFIG );
		}

		// Plugins from config
		if ( isset( $config['plugins'] ) && is_array( $config['plugins'] ) ) {
			foreach ( $config['plugins'] as $plugin ) {
				$this->addPluginRequest( $plugin, 'config' );
			}
		}

		// Themes from config
		if ( isset( $config['themes'] ) && is_array( $config['themes'] ) ) {
			foreach ( $config['themes'] as $theme ) {
				$this->addThemeRequest( $theme, 'config' );
			}
		}

		// Simple lists
		if ( isset( $config['php_extensions'] ) && is_array( $config['php_extensions'] ) ) {
			$this->php_extensions = array_merge( $this->php_extensions, $config['php_extensions'] );
		}
		if ( isset( $config['volumes'] ) && is_array( $config['volumes'] ) ) {
			$this->volumes = array_merge( $this->volumes, $config['volumes'] );
		}
		if ( isset( $config['envs'] ) && is_array( $config['envs'] ) ) {
			$this->envs = array_merge( $this->envs, $config['envs'] );
		}

		return $this;
	}

	/**
	 * Load values from CLI input.
	 *
	 * @param array<string, mixed> $cli_options Explicitly provided CLI options.
	 *                                          Keys are option names, values are option values.
	 *                                          Only include options that were EXPLICITLY provided (not Symfony defaults).
	 * @return self
	 */
	public function loadFromCli( array $cli_options ): self {
		// Scalar version params
		if ( isset( $cli_options['php_version'] ) && $cli_options['php_version'] !== null ) {
			$this->addScalar( 'php_version', (string) $cli_options['php_version'], self::PRIORITY_CLI );
		}
		if ( isset( $cli_options['wordpress_version'] ) && $cli_options['wordpress_version'] !== null ) {
			$this->addScalar( 'wordpress_version', (string) $cli_options['wordpress_version'], self::PRIORITY_CLI );
		}

		// WooCommerce version from CLI = version pin (highest priority)
		if ( isset( $cli_options['woocommerce_version'] ) && $cli_options['woocommerce_version'] !== null ) {
			$this->addVersionPin( 'woocommerce', (string) $cli_options['woocommerce_version'], self::PRIORITY_CLI );
		}

		// Boolean/scalar
		if ( isset( $cli_options['object_cache'] ) ) {
			$this->addScalar( 'object_cache', $cli_options['object_cache'] ? '1' : '0', self::PRIORITY_CLI );
		}

		// Plugins from CLI --plugin
		if ( isset( $cli_options['plugins'] ) && is_array( $cli_options['plugins'] ) ) {
			foreach ( $cli_options['plugins'] as $plugin ) {
				$this->addPluginRequest( $plugin, 'cli' );
			}
		}

		// Themes from CLI --theme
		if ( isset( $cli_options['themes'] ) && is_array( $cli_options['themes'] ) ) {
			foreach ( $cli_options['themes'] as $theme ) {
				$this->addThemeRequest( $theme, 'cli' );
			}
		}

		// Simple lists
		if ( isset( $cli_options['php_extensions'] ) && is_array( $cli_options['php_extensions'] ) ) {
			$this->php_extensions = array_merge( $this->php_extensions, $cli_options['php_extensions'] );
		}
		if ( isset( $cli_options['volumes'] ) && is_array( $cli_options['volumes'] ) ) {
			$this->volumes = array_merge( $this->volumes, $cli_options['volumes'] );
		}
		if ( isset( $cli_options['envs'] ) && is_array( $cli_options['envs'] ) ) {
			$this->envs = array_merge( $this->envs, $cli_options['envs'] );
		}

		return $this;
	}

	/**
	 * Load plugin/theme requirements from test packages.
	 *
	 * @param array<string|array<string,mixed>> $required_plugins Plugin requirements from test packages.
	 * @param array<string|array<string,mixed>> $required_themes  Theme requirements from test packages.
	 * @return self
	 */
	public function loadFromTestPackages( array $required_plugins, array $required_themes ): self {
		foreach ( $required_plugins as $plugin ) {
			$this->addPluginRequest( $plugin, 'test_package' );
		}
		foreach ( $required_themes as $theme ) {
			$this->addThemeRequest( $theme, 'test_package' );
		}

		return $this;
	}

	/**
	 * Load SUT as a plugin or theme.
	 *
	 * @param array<string, mixed>|null $sut_extension The SUT converted to extension format (from convert_sut_to_extension).
	 * @param string                    $sut_type      'plugin' or 'theme'.
	 * @return self
	 */
	public function loadFromSut( ?array $sut_extension, string $sut_type = 'plugin' ): self {
		if ( $sut_extension === null ) {
			return $this;
		}

		if ( $sut_type === 'plugin' ) {
			$this->addPluginRequest( $sut_extension, 'sut' );
		} else {
			$this->addThemeRequest( $sut_extension, 'theme' );
		}

		return $this;
	}

	/**
	 * Resolve all collected inputs into a final environment config.
	 *
	 * @return array<string, mixed> Resolved config ready for EnvInfo::from_array().
	 */
	public function resolve(): array {
		$config = [];

		// 1. Resolve scalars: highest priority wins, then default
		foreach ( [ 'php_version', 'wordpress_version' ] as $key ) {
			$config[ $key ] = $this->resolveScalar( $key );
		}

		// 2. Resolve object_cache
		$config['object_cache'] = $this->resolveScalar( 'object_cache' ) === '1';

		// 3. Resolve plugins with version pinning
		$config['plugins'] = $this->resolveExtensions( $this->plugin_requests, $this->version_pins );

		// 4. Resolve themes (no version pinning for themes currently)
		$config['themes'] = $this->resolveExtensions( $this->theme_requests, [] );

		// 5. Extract woocommerce_version from resolved plugins
		$config['woocommerce_version'] = '';
		foreach ( $config['plugins'] as $plugin ) {
			$slug = is_string( $plugin ) ? $plugin : ( $plugin['slug'] ?? '' );
			if ( $slug === 'woocommerce' ) {
				$config['woocommerce_version'] = is_array( $plugin ) ? ( $plugin['version'] ?? '' ) : '';
				break;
			}
		}

		// 6. Simple lists
		$config['php_extensions'] = array_values( array_unique( $this->php_extensions ) );
		$config['volumes']        = $this->volumes;
		$config['envs']           = $this->envs;

		return $config;
	}

	/**
	 * Add a scalar value with priority. Lower priority number wins.
	 */
	private function addScalar( string $key, string $value, int $priority ): void {
		if ( ! isset( $this->scalars[ $key ] ) || $priority < $this->scalars[ $key ]['priority'] ) {
			$this->scalars[ $key ] = [
				'value'    => $value,
				'priority' => $priority,
			];
		}
	}

	/**
	 * Resolve a scalar value: prioritized input, then default.
	 */
	private function resolveScalar( string $key ): string {
		if ( isset( $this->scalars[ $key ] ) ) {
			return $this->scalars[ $key ]['value'];
		}

		return self::$defaults[ $key ] ?? '';
	}

	/**
	 * Add a version pin for a plugin slug. Lower priority number wins.
	 */
	private function addVersionPin( string $slug, string $version, int $priority ): void {
		if ( ! isset( $this->version_pins[ $slug ] ) || $priority < $this->version_pins[ $slug ]['priority'] ) {
			$this->version_pins[ $slug ] = [
				'version'  => $version,
				'priority' => $priority,
			];
		}
	}

	/**
	 * Add a plugin request. For same slug, prefer the entry with more information.
	 *
	 * @param string|array<string, mixed> $plugin Plugin slug or config array.
	 * @param string                      $source Source identifier ('config', 'cli', 'test_package', 'sut').
	 */
	private function addPluginRequest( $plugin, string $source ): void {
		$slug = is_string( $plugin ) ? $plugin : ( $plugin['slug'] ?? null );
		if ( $slug === null ) {
			return;
		}

		$entry            = is_string( $plugin ) ? [ 'slug' => $plugin ] : $plugin;
		$entry['_source'] = $source;

		// SUT goes first (prepend)
		if ( $source === 'sut' && ! isset( $this->plugin_requests[ $slug ] ) ) {
			$this->plugin_requests = [ $slug => $entry ] + $this->plugin_requests;
			return;
		}

		// For duplicate slugs: prefer the entry with more info (has version, has 'from', etc.)
		if ( isset( $this->plugin_requests[ $slug ] ) ) {
			$existing = $this->plugin_requests[ $slug ];
			// Keep the richer entry (more keys = more info)
			if ( count( $entry ) > count( $existing ) ) {
				$this->plugin_requests[ $slug ] = $entry;
			}
			// If same richness, CLI overwrites config (but not SUT)
			elseif ( $source === 'cli' && ( $existing['_source'] ?? '' ) !== 'sut' ) {
				$this->plugin_requests[ $slug ] = $entry;
			}
			return;
		}

		$this->plugin_requests[ $slug ] = $entry;
	}

	/**
	 * Add a theme request.
	 *
	 * @param string|array<string, mixed> $theme Theme slug or config array.
	 * @param string                      $source Source identifier.
	 */
	private function addThemeRequest( $theme, string $source ): void {
		$slug = is_string( $theme ) ? $theme : ( $theme['slug'] ?? null );
		if ( $slug === null ) {
			return;
		}

		$entry            = is_string( $theme ) ? [ 'slug' => $theme ] : $theme;
		$entry['_source'] = $source;

		if ( $source === 'sut' && ! isset( $this->theme_requests[ $slug ] ) ) {
			$this->theme_requests = [ $slug => $entry ] + $this->theme_requests;
			return;
		}

		if ( isset( $this->theme_requests[ $slug ] ) ) {
			$existing = $this->theme_requests[ $slug ];
			if ( count( $entry ) > count( $existing ) ) {
				$this->theme_requests[ $slug ] = $entry;
			} elseif ( $source === 'cli' && ( $existing['_source'] ?? '' ) !== 'sut' ) {
				$this->theme_requests[ $slug ] = $entry;
			}
			return;
		}

		$this->theme_requests[ $slug ] = $entry;
	}

	/**
	 * Resolve extension requests with version pins applied.
	 *
	 * @param array<string, array<string, mixed>>                  $requests    Extension requests by slug.
	 * @param array<string, array{version: string, priority: int}> $pins Version pins by slug.
	 * @return array<int, string|array<string, mixed>> Resolved extension list.
	 */
	private function resolveExtensions( array $requests, array $pins ): array {
		// If a pin exists for a slug that's not in the request list, add it
		foreach ( $pins as $slug => $pin ) {
			if ( ! isset( $requests[ $slug ] ) ) {
				$requests[ $slug ] = [
					'slug'                => $slug,
					'from'                => 'wporg',
					'version'             => $pin['version'],
					'added_automatically' => 'Added via version pin',
					'_source'             => 'pin',
				];
			}
		}

		// Apply pins to existing entries
		$resolved = [];
		foreach ( $requests as $slug => $entry ) {
			// Strip internal _source metadata
			unset( $entry['_source'] );

			if ( isset( $pins[ $slug ] ) ) {
				// Apply the version pin.
				$entry['version'] = $pins[ $slug ]['version'];
			}

			$resolved[] = $entry;
		}

		return $resolved;
	}
}
