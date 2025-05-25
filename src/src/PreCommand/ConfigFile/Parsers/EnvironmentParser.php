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
					case 'envs':
						if ( ! is_array( $env_value ) ) {
							throw new \RuntimeException( "envs in environment '$env_name' must be an array." );
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
								if ( ! isset( $item['source'] ) || ! is_array( $item['source'] ) || ! isset( $item['source']['from'] ) ) {
									throw new \RuntimeException( "Item '{$item['slug']}' in $env_key for environment '$env_name' must have a 'source' object with a 'from' field." );
								}
								$from       = $item['source']['from'];
								$valid_from = [ 'wporg', 'wccom', 'local', 'zip' ];
								if ( ! in_array( $from, $valid_from, true ) ) {
									throw new \RuntimeException( "Invalid 'from' value '$from' for '{$item['slug']}' in $env_key. Must be one of: " . implode( ', ', $valid_from ) );
								}
								if ( $from === 'local' && ( ! isset( $item['source']['path'] ) || ! is_string( $item['source']['path'] ) || empty( $item['source']['path'] ) ) ) {
									throw new \RuntimeException( "Local source for '{$item['slug']}' in $env_key must have a non-empty 'path' string." );
								}
								if ( $from === 'zip' && ( ! isset( $item['source']['url'] ) || ! is_string( $item['source']['url'] ) || empty( $item['source']['url'] ) ) ) {
									throw new \RuntimeException( "Zip source for '{$item['slug']}' in $env_key must have a non-empty 'url' string." );
								}
							} else {
								throw new \RuntimeException( "Item at index $index in $env_key for environment '$env_name' must be a string or an object with 'slug' and 'source'." );
							}
						}
						break;
					case 'bootstrap':
						if ( ! is_array( $env_value ) ) {
							throw new \RuntimeException( "bootstrap in environment '$env_name' must be an array." );
						}
						foreach ( $env_value as $bootstrap_item ) {
							if ( ! is_array( $bootstrap_item ) || ! isset( $bootstrap_item['slug'], $bootstrap_item['test_package'] ) ) {
								throw new \RuntimeException( "Bootstrap item in environment '$env_name' must be an object with 'slug' and 'test_package' fields." );
							}
							if ( ! is_string( $bootstrap_item['slug'] ) || ! is_string( $bootstrap_item['test_package'] ) ) {
								throw new \RuntimeException( "Bootstrap slug and test_package in environment '$env_name' must be strings." );
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

	/**
	 * Creates an Extension object from a plugin or theme item (string or object).
	 *
	 * @param string|array<string, mixed> $item The plugin or theme item (string slug or object with slug and source).
	 * @param string                      $type The type ('plugin' or 'theme').
	 *
	 * @return Extension The created Extension object.
	 */
	protected function create_extension( $item, string $type ): Extension {
		if ( is_string( $item ) ) {
			$slug = $item;
			$ext  = new Extension( $slug, $type );

			// Try wccom first.
			try {
				$ext->wccom_id = $this->woo_extension_list->get_woo_extension_id_by_slug( $slug );

				return $ext;
			} catch ( \UnexpectedValueException $e ) {
				// Not in wccom, try wporg.
			}

			// Try wporg.
			try {
				if ( $type === 'plugin' && $this->wporg_extension_list->is_wporg_plugin( $slug ) ) {
					$info         = $this->wporg_extension_list->get_plugin_download_info( $slug );
					$ext->source  = $info['url'];
					$ext->version = $info['version'];

					return $ext;
				} elseif ( $type === 'theme' && $this->wporg_extension_list->is_wporg_theme( $slug ) ) {
					$info         = $this->wporg_extension_list->get_theme_download_info( $slug );
					$ext->source  = $info['url'];
					$ext->version = $info['version'];

					return $ext;
				}
			} catch ( \Exception $e ) {
				// Not in wporg, fail.
			}

			throw new \RuntimeException( "Extension '$slug' ($type) not found in WooCommerce.com or WordPress.org." );
		}

		// Object case.
		if ( ! is_array( $item ) || ! isset( $item['slug'], $item['source']['from'] ) ) {
			throw new \RuntimeException( "Extension object must have 'slug' and 'source' with 'from'." );
		}

		$slug = $item['slug'];
		$from = $item['source']['from'];
		$ext  = new Extension( $slug, $type );

		switch ( $from ) {
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
					throw new \RuntimeException( "Failed to fetch WordPress.org info for '$slug' ($type): " . $e->getMessage() );
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
				if ( ! isset( $item['source']['path'] ) || ! is_string( $item['source']['path'] ) || empty( $item['source']['path'] ) ) {
					throw new \RuntimeException( "Local extension '$slug' ($type) must have a non-empty 'path'." );
				}
				$ext->directory = $item['source']['path'];
				break;
			case 'zip':
				if ( ! isset( $item['source']['url'] ) || ! is_string( $item['source']['url'] ) || empty( $item['source']['url'] ) ) {
					throw new \RuntimeException( "Zip extension '$slug' ($type) must have a non-empty 'url'." );
				}
				$ext->source = $item['source']['url'];
				break;
			default:
				throw new \RuntimeException( "Invalid 'from' value '$from' for extension '$slug' ($type)." );
		}

		return $ext;
	}
}
