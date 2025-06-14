<?php

namespace QIT_CLI\PreCommand;

use QIT_CLI\App;
use QIT_CLI\Config as QITConfig;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\Environment\EnvParser;
use QIT_CLI\Environment\EnvVolumeParser;
use QIT_CLI\Environment\Extension;
use QIT_CLI\PreCommand\Configuration\Parser\QitJsonParser;
use QIT_CLI\PreCommand\Download\CustomTestsDownloader;
use QIT_CLI\PreCommand\Extensions\DependencyResolver;
use QIT_CLI\PreCommand\Extensions\ExtensionResolver;
use QIT_CLI\PreCommand\Extensions\ExtensionSetResolver;
use QIT_CLI\PreCommand\Extensions\VersionResolver;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\normalize_path;

class EnvInfoBuilder {
	public static function get_pluralizable_keys(): array {
		return [
			'plugin'        => 'plugins',
			'theme'         => 'themes',
			'volume'        => 'volumes',
			'php_extension' => 'php_extensions',
			'env'           => 'env_vars',
		];
	}

	public function build_env_info( InputInterface $input, OutputInterface $output, array $merged_options, QitJsonParser $config ): EnvInfo {
		$environment = $input->getOption( 'environment' ) ?: 'default';
		$woo_version = isset( $merged_options['woo_version'] ) ? $merged_options['woo_version'] : null;

		// Parse environment variables
		$env_parser                     = App::make( EnvParser::class );
		$env_vars                       = $env_parser->parse(
			$input->getOption( 'env' ) ?: [],
			$input->getOption( 'env_file' ) ?: []
		);
		$env_vars['WP_CLI_CONFIG_PATH'] = '/qit/wp-cli.yml';

		// Get environment configuration
		$config_section = $config->get_environment( $environment );
		$config_envs    = isset( $config_section['env_vars'] ) && is_array( $config_section['env_vars'] ) ? $config_section['env_vars'] : [];
		$env_vars       = array_merge( $config_envs, $env_vars );

		// Build environment configuration
		$env_config = $this->build_env_config( $config_section, $merged_options, $config, $env_vars );

		// Create EnvInfo object
		$env_info = $this->create_env_info( $env_config );

		// Process SUT if configured
		if ( isset( $config->parsed_config['sut'] ) ) {
			$env_info->sut = $config->parsed_config['sut'];
		}

		// Resolve extension set if configured
		if ( ! empty( $env_config['extension_set'] ) ) {
			$env_info = App::make( ExtensionSetResolver::class )->resolve( $env_info, [ 'overrides' => [ 'extension_set' => $env_config['extension_set'] ] ] );
			// Merge resolved extensions back to env_config for processing
			$env_config['plugins'] = array_merge( $env_config['plugins'], $this->convert_extensions_to_arrays( $env_info->plugins ) );
			$env_config['themes']  = array_merge( $env_config['themes'], $this->convert_extensions_to_arrays( $env_info->themes ) );
		}

		// Convert normalized extensions to Extension objects
		$all_extensions = array_merge(
			$this->convert_to_extension_objects( $env_config['plugins'] ),
			$this->convert_to_extension_objects( $env_config['themes'] )
		);

		// Resolve all extensions (including dependencies)
		$extension_resolver = App::make( ExtensionResolver::class );
		$cache_dir          = normalize_path( QITConfig::get_qit_dir() . 'cache' );

		file_put_contents( '/tmp/qit/qit_debug.log', 'EnvInfoBuilder: Starting extension resolution for ' . count( $all_extensions ) . " extensions\n", FILE_APPEND );

		$resolved_extensions = $extension_resolver->resolve( $all_extensions, $env_info, $cache_dir );

		// Get dependency information for PHP extensions
		$dependency_resolver = App::make( DependencyResolver::class );
		$dependencies        = $dependency_resolver->get_all_dependencies( $resolved_extensions->get_plugins() );

		// Add PHP extensions from dependencies
		$php_extensions = array_unique( array_merge( $env_config['php_extensions'] ?? [], $dependencies['php_extension'] ) );

		// Download custom tests
		$tests_downloader = App::make( CustomTestsDownloader::class );
		$tests_downloader->download( $env_info, $cache_dir, $resolved_extensions->get_plugins(), $resolved_extensions->get_themes() );

		// Handle WooCommerce version override
		$final_plugins = $this->handle_woo_version_override( $resolved_extensions->get_plugins(), $woo_version, $output );

		// Parse volumes
		$env_config['volumes'] = App::make( EnvVolumeParser::class )->parse_volumes( $env_config['volumes'] ?: [] );

		// Validate and process environment configuration
		$this->validate_env_config( $env_config );

		// Update EnvInfo with resolved data
		$this->update_env_info( $env_info, $env_config, $final_plugins, $resolved_extensions->get_themes(), $php_extensions );

		// Add test packages if configured
		$this->add_test_packages( $env_info, $config );

		file_put_contents( '/tmp/qit/qit_debug.log', 'EnvInfoBuilder: Final Env Info: ' . print_r( (array) $env_info, true ) . "\n", FILE_APPEND );

		return $env_info;
	}

	/**
	 * Convert normalized extension configs to Extension objects.
	 */
	/**
	 * Convert normalized extension configs to Extension objects.
	 */
	protected function convert_to_extension_objects( array $extension_configs ): array {
		$extensions = [];

		foreach ( $extension_configs as $config ) {
			$extension = new Extension( $config['slug'], $config['type'] );
			$source    = $config['source'];

			$extension->from = $source['type'];

			switch ( $source['type'] ) {
				case 'local':
					// For local, path could be either directory or zip file
					$path = $source['path'];
					if ( is_dir( $path ) ) {
						$extension->directory = $path;
						$extension->source    = $path;
					} else {
						// Assume it's a zip file
						$extension->source = $path;
					}
					break;
				case 'url':
					$extension->source = $source['url'];
					break;
				case 'wporg':
				case 'wccom':
					$extension->version = $source['version'] ?? 'stable';
					break;
			}

			$extensions[] = $extension;
		}

		return $extensions;
	}

	/**
	 * Convert Extension objects to normalized arrays for merging.
	 */
	protected function convert_extensions_to_arrays( array $extensions ): array {
		$configs = [];

		foreach ( $extensions as $extension ) {
			$config = [
				'slug'   => $extension->slug,
				'type'   => $extension->type,
				'source' => [ 'type' => $extension->from ],
			];

			switch ( $extension->from ) {
				case 'local':
					$config['source']['path'] = $extension->directory ?? $extension->source;
					break;
				case 'url':
					$config['source']['url'] = $extension->source;
					break;
				case 'wporg':
				case 'wccom':
					$config['source']['version'] = $extension->version ?? 'stable';
					break;
			}

			$configs[] = $config;
		}

		return $configs;
	}

	/**
	 * Build environment configuration from various sources.
	 */
	protected function build_env_config( array $config_section, array $merged_options, QitJsonParser $config, array $env_vars ): array {
		$env_config = [];

		// Process config section
		foreach ( $config_section as $key => $value ) {
			if ( $key === 'env_vars' ) {
				continue;
			}
			$mapped_key                = self::get_pluralizable_keys()[ $key ] ?? $key;
			$env_config[ $mapped_key ] = $value;
		}

		// Process SUT from config
		if ( isset( $config->parsed_config['sut'] ) ) {
			$this->process_sut_config( $env_config, $config->parsed_config['sut'] );
		}

		// Merge with CLI options
		foreach ( $merged_options as $key => $value ) {
			if ( $key === 'env' || $key === 'env_file' ) {
				continue;
			}
			$mapped_key = self::get_pluralizable_keys()[ $key ] ?? $key;
			if ( in_array( $mapped_key, [ 'plugins', 'themes' ], true ) && ! empty( $value ) ) {
				$env_config[ $mapped_key ] = $this->merge_cli_extensions( $env_config[ $mapped_key ] ?? [], $value, $mapped_key, $config );
			} elseif ( ( isset( $env_config[ $mapped_key ] ) && is_array( $env_config[ $mapped_key ] ) ) || is_array( $value ) ) {
				$cli_values                = is_array( $value ) ? $value : [ $value ];
				$config_values             = isset( $env_config[ $mapped_key ] ) ? (array) $env_config[ $mapped_key ] : [];
				$env_config[ $mapped_key ] = array_unique( array_merge( $config_values, $cli_values ), SORT_REGULAR );
			} elseif ( $value !== null ) {
				$env_config[ $mapped_key ] = $value;
			}
		}

		$env_config['env_vars'] = $env_vars;

		// Set defaults
		$env_config = array_merge( [
			'environment'             => 'e2e',
			'php_version'             => '8.0',
			'wp_version'              => 'stable',
			'woo_version'             => null,
			'plugins'                 => [],
			'themes'                  => [],
			'volumes'                 => [],
			'php_extensions'          => [],
			'object_cache'            => false,
			'env_vars'                => [],
			'extension_set'           => null,
			'tunnel'                  => false,
			'tunnel_type'             => 'no_tunnel',
			'runner_args'             => [],
			'domain'                  => 'localhost',
			'skip_activating_plugins' => false,
			'skip_activating_themes'  => false,
			'tests'                   => [],
			'playwright_config'       => [],
			'pw_test_tag'             => '',
			'sut'                     => null,
			'nginx_port'              => null,
			'notify'                  => null,
			'is_development_build'    => false,
		], $env_config );

		// Handle tunnel configuration
		$tunnel                    = $merged_options['tunnel'] ?? 'no_tunnel';
		$env_config['tunnel']      = $tunnel !== 'no_tunnel';
		$env_config['tunnel_type'] = $tunnel;

		return $env_config;
	}

	/**
	 * Process SUT configuration.
	 */
	protected function process_sut_config( array &$env_config, array $sut_config ): void {
		file_put_contents( '/tmp/qit/qit_debug.log', 'EnvInfoBuilder: Processing SUT: ' . print_r( $sut_config, true ) . "\n", FILE_APPEND );

		$source_type = $sut_config['source']['type'] ?? '';
		$target_key  = $sut_config['type'] === 'plugin' ? 'plugins' : 'themes';

		$existing_extensions = $env_config[ $target_key ] ?? [];
		$existing_index      = array_search( $sut_config['slug'], array_map( fn( $e ) => $e['slug'], $existing_extensions ), true );

		if ( $existing_index !== false ) {
			// Update existing extension
			$extension                              = $existing_extensions[ $existing_index ];
			$extension['source']                    = $sut_config['source'];
			$existing_extensions[ $existing_index ] = $extension;
			$env_config[ $target_key ]              = $existing_extensions;
		} else {
			// Add new SUT extension
			$sut_extension               = [
				'slug'   => $sut_config['slug'],
				'type'   => $sut_config['type'],
				'source' => $sut_config['source'],
			];
			$env_config[ $target_key ][] = $sut_extension;
		}
	}

	/**
	 * Merge CLI extensions with configuration.
	 */
	protected function merge_cli_extensions( array $config_extensions, $cli_value, string $type, QitJsonParser $config ): array {
		$cli_extensions = [];
		$cli_values     = is_array( $cli_value ) ? $cli_value : [ $cli_value ];
		$seen_slugs     = array_map( fn( $e ) => $e['slug'], $config_extensions );
		$type_singular  = $type === 'plugins' ? 'plugin' : 'theme';

		foreach ( $cli_values as $item ) {
			if ( is_array( $item ) && isset( $item['slug'] ) ) {
				if ( ! in_array( $item['slug'], $seen_slugs, true ) ) {
					$cli_extensions[] = $item;
					$seen_slugs[]     = $item['slug'];
				}
			} elseif ( is_string( $item ) ) {
				if ( ! in_array( $item, $seen_slugs, true ) ) {
					$extension        = [
						'slug'   => $item,
						'type'   => $type_singular,
						'source' => [ 'type' => 'wporg' ],
					];
					$cli_extensions[] = $extension;
					$seen_slugs[]     = $item;
				}
			} else {
				throw new \RuntimeException( "CLI $type_singular must be a string or array with slug" );
			}
		}

		return array_merge( $config_extensions, $cli_extensions );
	}

	/**
	 * Create EnvInfo object.
	 */
	protected function create_env_info( array $env_config ): E2EEnvInfo {
		$env_info                = new E2EEnvInfo();
		$env_info->env_id        = uniqid();
		$env_info->environment   = $env_config['environment'];
		$env_info->temporary_env = normalize_path( Environment::get_temp_envs_dir() . $env_info->environment . '-' . $env_info->env_id );
		$env_info->created_at    = time();
		$env_info->status        = 'pending';

		return $env_info;
	}

	/**
	 * Handle WooCommerce version override.
	 */
	protected function handle_woo_version_override( array $plugins, ?string $woo_version, OutputInterface $output ): array {
		if ( empty( $woo_version ) ) {
			return $plugins;
		}

		$filtered_plugins = array_values( array_filter( $plugins, fn( $p ) => $p->slug !== 'woocommerce' ) );
		if ( count( $filtered_plugins ) < count( $plugins ) ) {
			$output->writeln( "<comment>Overriding WooCommerce version to: $woo_version</comment>" );
		}

		// Create WooCommerce extension
		$woo_plugin                      = new Extension( 'woocommerce', 'plugin' );
		$woo_plugin->added_automatically = 'Added due to specified WooCommerce version';

		// Use version resolver for special versions
		$version_resolver = App::make( VersionResolver::class );

		if ( $version_resolver->can_resolve( 'woocommerce', $woo_version ) ) {
			$woo_plugin->source = $version_resolver->resolve( 'woocommerce', $woo_version );
			$woo_plugin->from   = 'url';
		} elseif ( filter_var( $woo_version, FILTER_VALIDATE_URL ) ) {
			$woo_plugin->source = $woo_version;
			$woo_plugin->from   = 'url';
		} else {
			// Default to wporg with specific version
			$woo_plugin->version = $woo_version;
			$woo_plugin->from    = 'wporg';
		}

		$filtered_plugins[] = $woo_plugin;

		return array_values( $filtered_plugins );
	}

	/**
	 * Validate environment configuration.
	 */
	protected function validate_env_config( array &$env_config ): void {
		foreach ( $env_config as $key => &$value ) {
			switch ( $key ) {
				case 'php_extensions':
					if ( ! is_array( $value ) ) {
						throw new \RuntimeException( 'PHP extensions must be an array' );
					}
					$value = array_unique( array_filter( $value, 'is_string' ) );
					foreach ( $value as $ext_name ) {
						if ( ! preg_match( '/^[a-z0-9_-]+$/i', $ext_name ) ) {
							throw new \RuntimeException( 'Invalid PHP extension name: ' . $ext_name );
						}
						if ( strlen( $ext_name ) > 50 ) {
							throw new \RuntimeException( 'PHP extension name too long: ' . $ext_name );
						}
					}
					break;
				case 'wp_version':
					// Inline WordPress version resolution
					if ( $value === 'stable' ) {
						$value = 'latest';  // WP CLI uses 'latest' for stable
					} elseif ( $value === 'rc' ) {
						throw new \InvalidArgumentException( 'WordPress RC versions not supported. Please specify a version like "6.5-RC1".' );
					}
					// Otherwise pass through as-is (nightly, specific versions, etc.)
					break;
			}
		}
	}

	/**
	 * Update EnvInfo with resolved data.
	 */
	protected function update_env_info(
		E2EEnvInfo $env_info,
		array $env_config,
		array $plugins,
		array $themes,
		array $php_extensions
	): void {
		$env_info->volumes                 = $env_config['volumes'];
		$env_info->docker_images           = $env_config['docker_images'] ?? [];
		$env_info->docker_network          = $env_config['docker_network'] ?? '';
		$env_info->php_extensions          = $php_extensions;
		$env_info->plugins                 = $plugins;
		$env_info->themes                  = $themes;
		$env_info->tunnel                  = $env_config['tunnel'];
		$env_info->tunnel_type             = $env_config['tunnel_type'];
		$env_info->runner_args             = $env_config['runner_args'];
		$env_info->wp_version              = $env_config['wp_version'];
		$env_info->object_cache            = $env_config['object_cache'];
		$env_info->php_version             = $env_config['php_version'];
		$env_info->domain                  = ( getenv( 'QIT_EXPOSE_ENVIRONMENT_TO' ) === 'DOCKER' ) ? "qitenvnginx{$env_info->env_id}" : ( getenv( 'QIT_DOMAIN' ) ?: 'localhost' );
		$env_info->skip_activating_plugins = $env_config['skip_activating_plugins'];
		$env_info->skip_activating_themes  = $env_config['skip_activating_themes'];
		$env_info->tests                   = $env_config['tests'];
		$env_info->playwright_config       = $env_config['playwright_config'] ?? [];
		$env_info->pw_test_tag             = $env_config['pw_test_tag'];
		$env_info->woo_version             = $env_config['woo_version'] ?? '';
		$env_info->is_development_build    = $env_config['is_development_build'];
		$env_info->notify                  = $env_config['notify'] ?? '';
		$env_info->env                     = $env_config['env_vars'];
	}

	/**
	 * Add test packages to environment info.
	 */
	protected function add_test_packages( E2EEnvInfo $env_info, QitJsonParser $config ): void {
		if ( ! isset( $config->parsed_config['test_packages'] ) || ! is_array( $config->parsed_config['test_packages'] ) ||
		     ! isset( $config->parsed_config['test_types'] ) || ! is_array( $config->parsed_config['test_types'] ) ) {
			return;
		}

		$env_info->test_packages = [];
		$referenced              = [];

		// Build a list of referenced test packages from test_types
		foreach ( $config->parsed_config['test_types'] as $test_type => $profiles ) {
			foreach ( $profiles as $profile_name => $profile ) {
				if ( isset( $profile['run']['test_packages'] ) ) {
					foreach ( $profile['run']['test_packages'] as $ref ) {
						if ( preg_match( '/^local\/(.+)$/', $ref, $matches ) ) {
							$package_name = $matches[1];
							// Remove version if present
							if ( strpos( $package_name, '@' ) !== false ) {
								$package_name = explode( '@', $package_name )[0];
							}
							$referenced["$test_type:$package_name"] = true;
						}
					}
				}
			}
		}

		// Only include referenced test packages in env_info
		foreach ( $config->parsed_config['test_packages'] as $test_type => $packages ) {
			if ( ! isset( $env_info->test_packages[ $test_type ] ) ) {
				$env_info->test_packages[ $test_type ] = [];
			}

			foreach ( $packages as $package_name => $package ) {
				if ( isset( $referenced["$test_type:$package_name"] ) ) {
					$env_info->test_packages[ $test_type ][ $package_name ] = $package;
				}
			}
		}

		file_put_contents( '/tmp/qit/qit_debug.log', 'EnvInfoBuilder: Added filtered test_packages to env_info: ' . print_r( $env_info->test_packages, true ) . "\n", FILE_APPEND );
	}
}
