<?php

namespace QIT_CLI\PreCommand\ConfigFile\Parsers;

use QIT_CLI\Environment\Extension;
use QIT_CLI\WooExtensionsList;

class EnvironmentParser extends AbstractConfigParser {
	protected WooExtensionsList $woo_extension_list;

	public function __construct( WooExtensionsList $woo_extension_list ) {
		$this->woo_extension_list = $woo_extension_list;
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
								continue;
							}
							if ( is_array( $item ) ) {
								if ( ! isset( $item['slug'] ) || ! is_string( $item['slug'] ) ) {
									throw new \RuntimeException( "Item at index $index in $env_key for environment '$env_name' must have a 'slug' string." );
								}
								if ( isset( $item['marketplace'] ) && ! is_string( $item['marketplace'] ) ) {
									throw new \RuntimeException( "Marketplace for $env_key item '{$item['slug']}' in environment '$env_name' must be a string." );
								}
								if ( isset( $item['directory'] ) && ! is_string( $item['directory'] ) ) {
									throw new \RuntimeException( "Directory for $env_key item '{$item['slug']}' in environment '$env_name' must be a string." );
								}
								if ( isset( $item['zip'] ) && ! is_string( $item['zip'] ) ) {
									throw new \RuntimeException( "Zip for $env_key item '{$item['slug']}' in environment '$env_name' must be a string." );
								}
							} else {
								throw new \RuntimeException( "Item at index $index in $env_key for environment '$env_name' must be a string or an object with 'slug'." );
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
	 * @param string|array<string, string> $item The plugin or theme item (string slug or object).
	 * @param string $type The type ('plugin' or 'theme').
	 *
	 * @return Extension The created Extension object.
	 */
	protected function create_extension( $item, string $type ): Extension {
		$slug = is_string( $item ) ? $item : $item['slug'];
		$ext  = new Extension( $slug, $type );

		try {
			$ext->wccom_id = $this->woo_extension_list->get_woo_extension_id_by_slug( $slug );
		} catch ( \UnexpectedValueException $e ) {
			// Skip if slug not found in woocommerce.com
		}

		return $ext;
	}
}