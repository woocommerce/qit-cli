<?php

namespace QIT_CLI\PreCommand\ConfigFile\Parsers;

use QIT_CLI\Environment\Extension;
use QIT_CLI\WooExtensionsList;
use QIT_CLI\WPORGExtensionsList;
use QIT_CLI\App;

class EnvironmentParser extends AbstractConfigParser {
	protected WooExtensionsList $woo_extension_list;
	protected WPORGExtensionsList $wporg_extension_list;
	protected SourceParser $source_parser;

	public function __construct( WooExtensionsList $woo_extension_list, WPORGExtensionsList $wporg_extension_list, SourceParser $source_parser ) {
		$this->woo_extension_list   = $woo_extension_list;
		$this->wporg_extension_list = $wporg_extension_list;
		$this->source_parser        = $source_parser;
	}

	public function parse( $value, array $context = [], ?array $sut_config = null ): array {
		if ( ! is_array( $value ) ) {
			throw new \RuntimeException( 'Environments must be an array.' );
		}

		$non_remote_sources = [ 'directory', 'zip', 'url', 'build' ];
		$sut_slug           = $sut_config['slug'] ?? null;
		$sut_source         = $sut_config['source'] ?? null;
		$root_path          = $context['root_path'] ?? getcwd();
		$test_packages      = $context['test_packages'] ?? [];

		$seen_slugs = [];
		foreach ( $value as $env_name => $config ) {
			if ( ! is_string( $env_name ) ) {
				throw new \RuntimeException( "Environment name must be a string in environments configuration." );
			}
			if ( ! is_array( $config ) ) {
				throw new \RuntimeException( "Configuration for environment '$env_name' must be an array." );
			}
			foreach ( $config as $env_key => $env_value ) {
				switch ( $env_key ) {
					case 'extends':
						if ( ! is_string( $env_value ) ) {
							throw new \RuntimeException( "'extends' in environment '$env_name' must be a string." );
						}
						break;
					case 'php_version':
						if ( ! is_string( $env_value ) || ! preg_match( '/^[0-9]+\.[0-9]+(\.[0-9]+)?$/', $env_value ) ) {
							throw new \RuntimeException( "Invalid php_version in environment '$env_name'. Must be a valid PHP version string (e.g., '8.2')." );
						}
						break;
					case 'wp_version':
					case 'woo_version':
						if ( ! is_string( $env_value ) ) {
							throw new \RuntimeException( "'$env_key' in environment '$env_name' must be a string." );
						}
						break;
					case 'object_cache':
						if ( ! is_bool( $env_value ) ) {
							throw new \RuntimeException( "object_cache in environment '$env_name' must be a boolean." );
						}
						break;
					case 'env_vars':
						if ( ! is_array( $env_value ) ) {
							throw new \RuntimeException( "env_vars in environment '$env_name' must be an array." );
						}
						foreach ( $env_value as $var_name => $var_value ) {
							if ( ! is_string( $var_name ) ) {
								throw new \RuntimeException( "Environment variable name in '$env_name' must be a string." );
							}
							if ( ! is_string( $var_value ) ) {
								throw new \RuntimeException( "Environment variable value for '$var_name' in '$env_name' must be a string." );
							}
						}
						break;
					case 'plugins':
					case 'themes':
						if ( ! is_array( $env_value ) ) {
							throw new \RuntimeException( "'$env_key' in environment '$env_name' must be an array." );
						}
						$value[ $env_name ][ $env_key ] = array_map( function ( $item ) {
							return is_string( $item ) ? [ 'slug' => $item ] : $item;
						}, $env_value );
						foreach ( $value[ $env_name ][ $env_key ] as $index => $item ) {
							if ( ! is_array( $item ) || ! isset( $item['slug'] ) || ! is_string( $item['slug'] ) || empty( $item['slug'] ) ) {
								throw new \RuntimeException( "Item at index $index in '$env_key' for environment '$env_name' must have a non-empty 'slug'." );
							}
							$slug = $item['slug'];
							if ( in_array( $slug, $seen_slugs ) ) {
								throw new \RuntimeException( "Duplicate slug '$slug' in '$env_key' for environment '$env_name'." );
							}
							$seen_slugs[] = $slug;
						}
						break;
					case 'setup':
						if ( ! is_array( $env_value ) ) {
							throw new \RuntimeException( "setup in environment '$env_name' must be an array." );
						}
						if ( isset( $env_value['test_packages'] ) ) {
							if ( ! is_array( $env_value['test_packages'] ) ) {
								throw new \RuntimeException( "test_packages in setup for environment '$env_name' must be an array." );
							}
							foreach ( $env_value['test_packages'] as $index => $test_package ) {
								if ( ! is_string( $test_package ) ) {
									throw new \RuntimeException( "Test package at index $index in setup for environment '$env_name' must be a string." );
								}
								// Validate test package reference
								$parts = explode( ':', $test_package, 2 );
								if ( count( $parts ) !== 2 ) {
									throw new \RuntimeException( "Invalid test package format '$test_package' at index $index in setup for environment '$env_name'. Expected 'source:name@version'." );
								}
								[ $source, $name_version ] = $parts;
								$name_parts = explode( '@', $name_version, 2 );
								if ( count( $name_parts ) !== 2 ) {
									throw new \RuntimeException( "Invalid test package name/version '$name_version' at index $index in setup for environment '$env_name'. Expected 'name@version'." );
								}
								[ $name ] = $name_parts;
								$found = false;
								foreach ( $test_packages as $pkg ) {
									if ( $pkg['type'] === $source && $pkg['name'] === $name ) {
										$found = true;
										break;
									}
								}
								if ( ! $found ) {
									throw new \RuntimeException( "Test package '$test_package' at index $index in setup for environment '$env_name' not found in test_packages configuration." );
								}
							}
						}
						break;
					case 'volumes':
						if ( ! is_array( $env_value ) ) {
							throw new \RuntimeException( "volumes in environment '$env_name' must be an array." );
						}
						foreach ( $env_value as $volume ) {
							if ( ! is_string( $volume ) ) {
								throw new \RuntimeException( "Volume in environment '$env_name' must be a string." );
							}
						}
						break;
					case 'extension_set':
						if ( ! is_string( $env_value ) ) {
							throw new \RuntimeException( "extension_set in environment '$env_name' must be a string." );
						}
						break;
					default:
						throw new \RuntimeException( "Unknown key '$env_key' in environment '$env_name' configuration." );
				}
			}
		}

		$resolved = $this->resolve_extends( $value, 'environment' );

		foreach ( $resolved as $env_name => &$env ) {
			$env['plugins'] = array_map( function ( $item ) use ( $sut_config, $non_remote_sources, $env_name, $root_path ) {
				return $this->create_extension( $item, 'plugin', $sut_config, $non_remote_sources, $env_name, $root_path );
			}, $env['plugins'] ?? [] );

			$env['themes'] = array_map( function ( $item ) use ( $sut_config, $non_remote_sources, $env_name, $root_path ) {
				return $this->create_extension( $item, 'theme', $sut_config, $non_remote_sources, $env_name, $root_path );
			}, $env['themes'] ?? [] );
		}

		return $resolved;
	}

	protected function create_extension( $item, string $type, ?array $sut_config, array $non_remote_sources, string $env_name, string $root_path ): Extension {
		if ( ! is_array( $item ) || ! isset( $item['slug'] ) || ! is_string( $item['slug'] ) || empty( $item['slug'] ) ) {
			throw new \RuntimeException( "Extension must have a non-empty 'slug' for environment '$env_name'." );
		}

		$slug       = $item['slug'];
		$ext        = new Extension( $slug, $type );
		$sut_slug   = $sut_config['slug'] ?? null;
		$sut_source = $sut_config['source'] ?? null;

		if ( $sut_slug === $slug && $sut_source && in_array( $sut_source['type'], $non_remote_sources, true ) ) {
			$ext->from = $sut_source['type'];
			if ( $sut_source['type'] === 'directory' ) {
				$ext->directory = $sut_source['path'] ?? '';
			} elseif ( $sut_source['type'] === 'zip' ) {
				$ext->source = $sut_source['path'] ?? '';
			} elseif ( $sut_source['type'] === 'url' ) {
				$ext->source = $sut_source['url'] ?? '';
			} elseif ( $sut_source['type'] === 'build' ) {
				$ext->source = $sut_source['output'] ?? '';
			}

			return $ext;
		}

		if ( ! isset( $item['source'] ) || ! is_array( $item['source'] ) || ! isset( $item['source']['type'] ) ) {
			try {
				if ( $type === 'plugin' && $this->wporg_extension_list->is_wporg_plugin( $slug ) ) {
					$info         = $this->wporg_extension_list->get_plugin_download_info( $slug );
					$ext->source  = $info['url'];
					$ext->version = $info['version'];
					$ext->from    = 'wporg';

					return $ext;
				} elseif ( $type === 'theme' && $this->wporg_extension_list->is_wporg_theme( $slug ) ) {
					$info         = $this->wporg_extension_list->get_theme_download_info( $slug );
					$ext->source  = $info['url'];
					$ext->version = $info['version'];
					$ext->from    = 'wporg';

					return $ext;
				}
			} catch ( \Exception $e ) {
				// Continue to try wccom
			}
			try {
				$ext->wccom_id = $this->woo_extension_list->get_woo_extension_id_by_slug( $slug );
				$ext->version  = 'stable';
				$ext->from     = 'wccom';

				return $ext;
			} catch ( \UnexpectedValueException $e ) {
				throw new \RuntimeException( "Extension '$slug' ($type) not found in WordPress.org or WooCommerce.com for environment '$env_name'. Use a 'source' object for non-remote sources." );
			}
		}

		$item['source'] = $this->source_parser->parse( $item['source'], [
			'slug'      => $slug,
			'context'   => "environment.$env_name.$type.$slug.source",
			'root_path' => $root_path
		] );

		$ext->from = $item['source']['type'];
		switch ( $ext->from ) {
			case 'wporg':
				try {
					if ( $type === 'plugin' && $this->wporg_extension_list->is_wporg_plugin( $slug ) ) {
						$info         = $this->wporg_extension_list->get_plugin_download_info( $slug );
						$ext->source  = $info['url'];
						$ext->version = $item['source']['version'] ?? $info['version'];
					} elseif ( $type === 'theme' && $this->wporg_extension_list->is_wporg_theme( $slug ) ) {
						$info         = $this->wporg_extension_list->get_theme_download_info( $slug );
						$ext->source  = $info['url'];
						$ext->version = $item['source']['version'] ?? $info['version'];
					} else {
						throw new \RuntimeException( "Extension '$slug' ($type) not found in WordPress.org for environment '$env_name'." );
					}
				} catch ( \Exception $e ) {
					throw new \RuntimeException( "Failed to fetch WordPress.org info for '$slug' ($type) in environment '$env_name': " . $e->getMessage() );
				}
				break;
			case 'wccom':
				try {
					$ext->wccom_id = $this->woo_extension_list->get_woo_extension_id_by_slug( $slug );
					$ext->version  = $item['source']['version'] ?? 'stable';
				} catch ( \UnexpectedValueException $e ) {
					throw new \RuntimeException( "Extension '$slug' ($type) not found in WooCommerce.com for environment '$env_name'." );
				}
				break;
			case 'directory':
				$ext->directory = $item['source']['path'];
				break;
			case 'zip':
				$ext->source = $item['source']['path'];
				break;
			case 'url':
				$ext->source = $item['source']['url'];
				break;
			case 'build':
				$ext->source = $item['source']['output'];
				break;
			default:
				throw new \RuntimeException( "Invalid source type '{$ext->from}' for extension '$slug' ($type) in environment '$env_name'." );
		}

		return $ext;
	}
}