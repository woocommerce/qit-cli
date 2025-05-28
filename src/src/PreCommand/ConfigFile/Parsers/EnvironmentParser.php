<?php

namespace QIT_CLI\PreCommand\ConfigFile\Parsers;

use QIT_CLI\Environment\Extension;
use QIT_CLI\WooExtensionsList;
use QIT_CLI\WPORGExtensionsList;

class EnvironmentParser extends AbstractConfigParser {
	protected WooExtensionsList $woo_extension_list;
	protected WPORGExtensionsList $wporg_extension_list;

	public function __construct( WooExtensionsList $woo_extension_list, WPORGExtensionsList $wporg_extension_list ) {
		$this->woo_extension_list   = $woo_extension_list;
		$this->wporg_extension_list = $wporg_extension_list;
	}

	public function parse( $value, array $context = [] ): array {
		if ( ! is_array( $value ) ) {
			throw new \RuntimeException( 'Environments must be an array.' );
		}

		$seen_slugs = [];
		foreach ( $value as $env_name => $config ) {
			if ( ! is_string( $env_name ) ) {
				throw new \RuntimeException( 'Environment name must be a string.' );
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
							throw new \RuntimeException( "$env_key in environment '$env_name' must be a string." );
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
							throw new \RuntimeException( "$env_key in environment '$env_name' must be an array." );
						}
						foreach ( $env_value as $index => $item ) {
							$slug = is_string( $item ) ? $item : ( is_array( $item ) && isset( $item['slug'] ) ? $item['slug'] : null );
							if ( ! $slug ) {
								throw new \RuntimeException( "Item at index $index in $env_key for environment '$env_name' must have a 'slug'." );
							}
							if ( in_array( $slug, $seen_slugs ) ) {
								throw new \RuntimeException( "Duplicate slug '$slug' in $env_key for environment '$env_name'." );
							}
							$seen_slugs[] = $slug;

							if ( is_string( $item ) ) {
								if ( empty( $item ) ) {
									throw new \RuntimeException( "Empty slug at index $index in $env_key for environment '$env_name'." );
								}
								continue;
							}
							if ( is_array( $item ) ) {
								if ( ! isset( $item['slug'] ) || ! is_string( $item['slug'] ) || empty( $item['slug'] ) ) {
									throw new \RuntimeException( "Item at index $index in $env_key for environment '$env_name' must have a non-empty 'slug' string." );
								}
								if ( isset( $item['from'] ) ) {
									$valid_from = [ 'wporg', 'wccom', 'local', 'url', 'zip' ];
									if ( ! in_array( $item['from'], $valid_from, true ) ) {
										throw new \RuntimeException( "Invalid 'from' value '{$item['from']}' for '{$item['slug']}' in $env_key. Must be one of: " . implode( ', ', $valid_from ) );
									}
									if ( $item['from'] === 'local' && ( ! isset( $item['path'] ) || ! is_string( $item['path'] ) || empty( $item['path'] ) ) ) {
										throw new \RuntimeException( "Local source for '{$item['slug']}' in $env_key must have a non-empty 'path' string." );
									}
									if ( in_array( $item['from'], [
											'url',
											'zip'
										] ) && ( ! isset( $item['url'] ) || ! is_string( $item['url'] ) || empty( $item['url'] ) ) ) {
										throw new \RuntimeException( "URL or zip source for '{$item['slug']}' in $env_key must have a non-empty 'url' string." );
									}
									if ( in_array( $item['from'], [
											'url',
											'zip'
										] ) && isset( $item['url'] ) && ! preg_match( '/^https?:\/\/.+\/.+\.zip$/', $item['url'] ) ) {
										throw new \RuntimeException( "Invalid URL format for '{$item['slug']}' in $env_key. Must be a valid HTTPS URL ending in .zip." );
									}
								}
							} else {
								throw new \RuntimeException( "Item at index $index in $env_key for environment '$env_name' must be a string or an object with 'slug' and optional 'from'." );
							}
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
								if ( isset( $context['test_packages'] ) ) {
									$parts = explode( '/', $test_package, 2 );
									if ( count( $parts ) === 2 ) {
										[ $package_source, $package_name ] = $parts;
										if ( $package_source !== 'local' && ! isset( $context['test_packages'][ $package_source ][ $package_name ] ) ) {
											throw new \RuntimeException( "Test package '$test_package' in setup for environment '$env_name' not found in test_packages." );
										}
									}
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

		foreach ( $resolved as &$env ) {
			$env['plugins'] = array_map( function ( $item ) {
				return $this->create_extension( $item, 'plugin' );
			}, $env['plugins'] ?? [] );

			$env['themes'] = array_map( function ( $item ) {
				return $this->create_extension( $item, 'theme' );
			}, $env['themes'] ?? [] );
		}

		return $resolved;
	}

	protected function create_extension( $item, string $type ): Extension {
		if ( is_string( $item ) ) {
			$slug = $item;
			$ext  = new Extension( $slug, $type );

			try {
				$ext->wccom_id = $this->woo_extension_list->get_woo_extension_id_by_slug( $slug );
				$ext->from     = 'wccom';

				return $ext;
			} catch ( \UnexpectedValueException $e ) {
				// Not in wccom, try wporg
			}

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
				// Not in wporg
			}

			throw new \RuntimeException( "Extension '$slug' ($type) not found in WooCommerce.com or WordPress.org." );
		}

		if ( ! is_array( $item ) || ! isset( $item['slug'] ) ) {
			throw new \RuntimeException( "Extension object must have 'slug'." );
		}

		$slug      = $item['slug'];
		$ext       = new Extension( $slug, $type );
		$ext->from = isset( $item['from'] ) ? $item['from'] : 'wporg';

		switch ( $ext->from ) {
			case 'wporg':
				try {
					if ( $type === 'plugin' && $this->wporg_extension_list->is_wporg_plugin( $slug ) ) {
						$info         = $this->wporg_extension_list->get_plugin_download_info( $slug );
						$ext->source  = $info['url'];
						$ext->version = $info['version'];
					} elseif ( $type === 'theme' && $this->wporg_extension_list->is_wporg_theme( $slug ) ) {
						$info         = $this->wporg_extension_list->get_theme_download_info( $slug );
						$ext->source  = $info['url'];
						$ext->version = $info['version'];
					} else {
						throw new \RuntimeException( "Extension '$slug' ($type) not found in WordPress.org." );
					}
				} catch ( \Exception $e ) {
					throw new \RuntimeException( "2 Failed to fetch WordPress.org info for '$slug' ($type): " . $e->getMessage() );
				}
				break;
			case 'wccom':
				try {
					$ext->wccom_id = $this->woo_extension_list->get_woo_extension_id_by_slug( $slug );
				} catch ( \UnexpectedValueException $e ) {
					throw new \RuntimeException( "Extension '$slug' ($type) not found in WooCommerce.com." );
				}
				break;
			case 'local':
				if ( ! isset( $item['path'] ) || ! is_string( $item['path'] ) || empty( $item['path'] ) ) {
					throw new \RuntimeException( "Local extension '$slug' ($type) must have a non-empty 'path'." );
				}
				$ext->directory = $item['path'];
				break;
			case 'url':
			case 'zip':
				if ( ! isset( $item['url'] ) || ! is_string( $item['url'] ) || empty( $item['url'] ) ) {
					throw new \RuntimeException( "URL or zip extension '$slug' ($type) must have a non-empty 'url'." );
				}
				$ext->source = $item['url'];
				break;
			default:
				throw new \RuntimeException( "Invalid 'from' value '{$ext->from}' for extension '$slug' ($type)." );
		}

		return $ext;
	}
}