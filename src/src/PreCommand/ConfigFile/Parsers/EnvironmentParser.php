<?php

namespace QIT_CLI\PreCommand\ConfigFile\Parsers;

use QIT_CLI\Environment\Extension;
use QIT_CLI\WooExtensionsList;
use QIT_CLI\WPORGExtensionsList;
use QIT_CLI\App;

class EnvironmentParser extends AbstractConfigParser {
	protected WooExtensionsList $woo_extension_list;
	protected WPORGExtensionsList $wporg_extension_list;

	public function __construct( WooExtensionsList $woo_extension_list, WPORGExtensionsList $wporg_extension_list ) {
		$this->woo_extension_list   = $woo_extension_list;
		$this->wporg_extension_list = $wporg_extension_list;
	}

	public function parse( $value, array $context = [], ?array $sut_config = null ): array {
		file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Parsing environments: " . print_r( $value, true ) . "\n", FILE_APPEND );
		file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: SUT config: " . print_r( $sut_config ?? [], true ) . "\n", FILE_APPEND );

		if ( ! is_array( $value ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Environments must be an array\n", FILE_APPEND );
			throw new \RuntimeException( 'Environments must be an array.' );
		}

		$non_remote_sources = [ 'directory', 'zip', 'url', 'build' ];
		$sut_slug           = $sut_config['slug'] ?? null;
		$sut_source_type    = $sut_config['source_type'] ?? null;

		$seen_slugs = [];
		foreach ( $value as $env_name => $config ) {
			if ( ! is_string( $env_name ) ) {
				file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Environment name must be a string\n", FILE_APPEND );
				throw new \RuntimeException( 'Environment name must be a string.' );
			}
			if ( ! is_array( $config ) ) {
				file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Configuration for environment '$env_name' must be an array\n", FILE_APPEND );
				throw new \RuntimeException( "Configuration for environment '$env_name' must be an array." );
			}
			foreach ( $config as $env_key => $env_value ) {
				switch ( $env_key ) {
					case 'extends':
						if ( ! is_string( $env_value ) ) {
							file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: 'extends' in environment '$env_name' must be a string\n", FILE_APPEND );
							throw new \RuntimeException( "'extends' in environment '$env_name' must be a string." );
						}
						break;
					case 'php_version':
						if ( ! is_string( $env_value ) || ! preg_match( '/^[0-9]+\.[0-9]+(\.[0-9]+)?$/', $env_value ) ) {
							file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Invalid php_version in environment '$env_name'\n", FILE_APPEND );
							throw new \RuntimeException( "Invalid php_version in environment '$env_name'. Must be a valid PHP version string (e.g., '8.2')." );
						}
						break;
					case 'wp_version':
					case 'woo_version':
						if ( ! is_string( $env_value ) ) {
							file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: $env_key in environment '$env_name' must be a string\n", FILE_APPEND );
							throw new \RuntimeException( "$env_key in environment '$env_name' must be a string." );
						}
						break;
					case 'object_cache':
						if ( ! is_bool( $env_value ) ) {
							file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: object_cache in environment '$env_name' must be a boolean\n", FILE_APPEND );
							throw new \RuntimeException( "object_cache in environment '$env_name' must be a boolean." );
						}
						break;
					case 'env_vars':
						if ( ! is_array( $env_value ) ) {
							file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: env_vars in environment '$env_name' must be an array\n", FILE_APPEND );
							throw new \RuntimeException( "env_vars in environment '$env_name' must be an array." );
						}
						foreach ( $env_value as $var_name => $var_value ) {
							if ( ! is_string( $var_name ) ) {
								file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Environment variable name in '$env_name' must be a string\n", FILE_APPEND );
								throw new \RuntimeException( "Environment variable name in '$env_name' must be a string." );
							}
							if ( ! is_string( $var_value ) ) {
								file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Environment variable value for '$var_name' in '$env_name' must be a string\n", FILE_APPEND );
								throw new \RuntimeException( "Environment variable value for '$var_name' in '$env_name' must be a string." );
							}
						}
						break;
					case 'plugins':
					case 'themes':
						if ( ! is_array( $env_value ) ) {
							file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: $env_key in environment '$env_name' must be an array\n", FILE_APPEND );
							throw new \RuntimeException( "$env_key in environment '$env_name' must be an array." );
						}
						foreach ( $env_value as $index => $item ) {
							$slug = is_string( $item ) ? $item : ( is_array( $item ) && isset( $item['slug'] ) ? $item['slug'] : null );
							if ( ! $slug ) {
								file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Item at index $index in $env_key for environment '$env_name' must have a 'slug'\n", FILE_APPEND );
								throw new \RuntimeException( "Item at index $index in $env_key for environment '$env_name' must have a 'slug'." );
							}
							if ( in_array( $slug, $seen_slugs ) ) {
								file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Duplicate slug '$slug' in $env_key for environment '$env_name'\n", FILE_APPEND );
								throw new \RuntimeException( "Duplicate slug '$slug' in $env_key for environment '$env_name'." );
							}
							$seen_slugs[] = $slug;

							if ( is_string( $item ) ) {
								if ( empty( $item ) ) {
									file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Empty slug at index $index in $env_key for environment '$env_name'\n", FILE_APPEND );
									throw new \RuntimeException( "Empty slug at index $index in $env_key for environment '$env_name'." );
								}
								// Strings imply wporg/wccom, but check if it's the SUT with non-remote source
								if ( $sut_slug === $slug && $sut_source_type && in_array( $sut_source_type, $non_remote_sources, true ) ) {
									file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Treating string '$slug' as SUT with source_type: $sut_source_type\n", FILE_APPEND );
									continue;
								}
								continue;
							}
							if ( is_array( $item ) ) {
								if ( ! isset( $item['slug'] ) || ! is_string( $item['slug'] ) || empty( $item['slug'] ) ) {
									file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Item at index $index in $env_key for environment '$env_name' must have a non-empty 'slug' string\n", FILE_APPEND );
									throw new \RuntimeException( "Item at index $index in $env_key for environment '$env_name' must have a non-empty 'slug' string." );
								}
								if ( isset( $item['source_type'] ) ) {
									$valid_source_types = [ 'wporg', 'wccom', 'directory', 'zip', 'url', 'build' ];
									if ( ! in_array( $item['source_type'], $valid_source_types, true ) ) {
										file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Invalid source_type '{$item['source_type']}' for '{$item['slug']}' in $env_key\n", FILE_APPEND );
										throw new \RuntimeException( "Invalid source_type '{$item['source_type']}' for '{$item['slug']}' in $env_key. Must be one of: " . implode( ', ', $valid_source_types ) );
									}
									if ( $item['source_type'] === 'directory' && ( ! isset( $item['path'] ) || ! is_string( $item['path'] ) || empty( $item['path'] ) ) ) {
										file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Directory source for '{$item['slug']}' in $env_key must have a non-empty 'path' string\n", FILE_APPEND );
										throw new \RuntimeException( "Directory source for '{$item['slug']}' in $env_key must have a non-empty 'path' string." );
									}
									if ( $item['source_type'] === 'zip' && ( ! isset( $item['path'] ) || ! is_string( $item['path'] ) || empty( $item['path'] ) ) ) {
										file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Zip source for '{$item['slug']}' in $env_key must have a non-empty 'path' string\n", FILE_APPEND );
										throw new \RuntimeException( "Zip source for '{$item['slug']}' in $env_key must have a non-empty 'path' string." );
									}
									if ( $item['source_type'] === 'url' && ( ! isset( $item['url'] ) || ! is_string( $item['url'] ) || empty( $item['url'] ) ) ) {
										file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: URL source for '{$item['slug']}' in $env_key must have a non-empty 'url' string\n", FILE_APPEND );
										throw new \RuntimeException( "URL source for '{$item['slug']}' in $env_key must have a non-empty 'url' string." );
									}
									if ( $item['source_type'] === 'url' && ! preg_match( '/^https?:\/\/.+\/.+\.zip$/', $item['url'] ) ) {
										file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Invalid URL format for '{$item['slug']}' in $env_key\n", FILE_APPEND );
										throw new \RuntimeException( "Invalid URL format for '{$item['slug']}' in $env_key. Must be a valid HTTPS URL ending in .zip." );
									}
									if ( $item['source_type'] === 'build' ) {
										if ( ! isset( $item['command'] ) || ! is_string( $item['command'] ) || empty( $item['command'] ) ) {
											file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Build source for '{$item['slug']}' in $env_key must have a non-empty 'command' string\n", FILE_APPEND );
											throw new \RuntimeException( "Build source for '{$item['slug']}' in $env_key must have a non-empty 'command' string." );
										}
										if ( ! isset( $item['output'] ) || ! is_string( $item['output'] ) || empty( $item['output'] ) ) {
											file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Build source for '{$item['slug']}' in $env_key must have a non-empty 'output' string\n", FILE_APPEND );
											throw new \RuntimeException( "Build source for '{$item['slug']}' in $env_key must have a non-empty 'output' string." );
										}
										if ( ! preg_match( '/\.zip$/', $item['output'] ) ) {
											file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Build source output for '{$item['slug']}' in $env_key must be a .zip file\n", FILE_APPEND );
											throw new \RuntimeException( "Build source output for '{$item['slug']}' in $env_key must be a .zip file." );
										}
									}
								}
							} else {
								file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Item at index $index in $env_key for environment '$env_name' must be a string or an object with 'slug' and optional 'source_type'\n", FILE_APPEND );
								throw new \RuntimeException( "Item at index $index in $env_key for environment '$env_name' must be a string or an object with 'slug' and optional 'source_type'." );
							}
						}
						break;
					case 'setup':
						if ( ! is_array( $env_value ) ) {
							file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: setup in environment '$env_name' must be an array\n", FILE_APPEND );
							throw new \RuntimeException( "setup in environment '$env_name' must be an array." );
						}
						if ( isset( $env_value['test_packages'] ) ) {
							if ( ! is_array( $env_value['test_packages'] ) ) {
								file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: test_packages in setup for environment '$env_name' must be an array\n", FILE_APPEND );
								throw new \RuntimeException( "test_packages in setup for environment '$env_name' must be an array." );
							}
							foreach ( $env_value['test_packages'] as $index => $test_package ) {
								if ( ! is_string( $test_package ) ) {
									file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Test package at index $index in setup for environment '$env_name' must be a string\n", FILE_APPEND );
									throw new \RuntimeException( "Test package at index $index in setup for environment '$env_name' must be a string." );
								}
								if ( isset( $context['test_packages'] ) ) {
									$parts = explode( '/', $test_package, 2 );
									if ( count( $parts ) === 2 ) {
										[ $package_source, $package_name ] = $parts;
										if ( $package_source !== 'directory' && ! isset( $context['test_packages'][ $package_source ][ $package_name ] ) ) {
											file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Test package '$test_package' in setup for environment '$env_name' not found in test_packages\n", FILE_APPEND );
											throw new \RuntimeException( "Test package '$test_package' in setup for environment '$env_name' not found in test_packages." );
										}
									}
								}
							}
						}
						break;
					case 'volumes':
						if ( ! is_array( $env_value ) ) {
							file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: volumes in environment '$env_name' must be an array\n", FILE_APPEND );
							throw new \RuntimeException( "volumes in environment '$env_name' must be an array." );
						}
						foreach ( $env_value as $volume ) {
							if ( ! is_string( $volume ) ) {
								file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Volume in environment '$env_name' must be a string\n", FILE_APPEND );
								throw new \RuntimeException( "Volume in environment '$env_name' must be a string." );
							}
						}
						break;
					case 'extension_set':
						if ( ! is_string( $env_value ) ) {
							file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: extension_set in environment '$env_name' must be a string\n", FILE_APPEND );
							throw new \RuntimeException( "extension_set in environment '$env_name' must be a string." );
						}
						break;
					default:
						file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Unknown key '$env_key' in environment '$env_name' configuration\n", FILE_APPEND );
						throw new \RuntimeException( "Unknown key '$env_key' in environment '$env_name' configuration." );
				}
			}
		}

		$resolved = $this->resolve_extends( $value, 'environment' );

		foreach ( $resolved as &$env ) {
			$env['plugins'] = array_map( function ( $item ) use ( $sut_config ) {
				return $this->create_extension( $item, 'plugin', $sut_config );
			}, $env['plugins'] ?? [] );

			$env['themes'] = array_map( function ( $item ) use ( $sut_config ) {
				return $this->create_extension( $item, 'theme', $sut_config );
			}, $env['themes'] ?? [] );
		}

		file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Environments parsing completed\n", FILE_APPEND );

		return $resolved;
	}

	protected function create_extension( $item, string $type, ?array $sut_config = null ): Extension {
		file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Creating extension: item=" . print_r( $item, true ) . ", type=$type\n", FILE_APPEND );

		$non_remote_sources = [ 'directory', 'zip', 'url', 'build' ];
		$sut_slug           = $sut_config['slug'] ?? null;
		$sut_source_type    = $sut_config['source_type'] ?? null;

		if ( is_string( $item ) ) {
			$slug = $item;
			$ext  = new Extension( $slug, $type );

			// If this is the SUT with a non-remote source, use its source_type
			if ( $sut_slug === $slug && $sut_source_type && in_array( $sut_source_type, $non_remote_sources, true ) ) {
				file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Skipping remote validation for SUT '$slug' with source_type: $sut_source_type\n", FILE_APPEND );
				$ext->from = $sut_source_type;
				if ( $sut_source_type === 'directory' ) {
					$ext->directory = $sut_config['path'] ?? '';
				} elseif ( $sut_source_type === 'zip' ) {
					$ext->source = $sut_config['path'] ?? '';
				} elseif ( $sut_source_type === 'url' ) {
					$ext->source = $sut_config['url'] ?? '';
				} elseif ( $sut_source_type === 'build' ) {
					$ext->source = $sut_config['output'] ?? '';
				}

				return $ext;
			}

			// Check wporg first if a mock response exists
			$wporg_mock_key = sprintf( 'mock_%s', "https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&request[slug]={$slug}" );
			$wporg_mock     = App::getVar( $wporg_mock_key, null );
			if ( $wporg_mock !== null ) {
				try {
					if ( $type === 'plugin' && $this->wporg_extension_list->is_wporg_plugin( $slug ) ) {
						$info         = $this->wporg_extension_list->get_plugin_download_info( $slug );
						$ext->source  = $info['url'];
						$ext->version = $info['version'];
						$ext->from    = 'wporg';
						file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Extension '$slug' found in WordPress.org\n", FILE_APPEND );

						return $ext;
					} elseif ( $type === 'theme' && $this->wporg_extension_list->is_wporg_theme( $slug ) ) {
						$info         = $this->wporg_extension_list->get_theme_download_info( $slug );
						$ext->source  = $info['url'];
						$ext->version = $info['version'];
						$ext->from    = 'wporg';
						file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Extension '$slug' found in WordPress.org\n", FILE_APPEND );

						return $ext;
					}
				} catch ( \Exception $e ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Extension '$slug' not found in WordPress.org: " . $e->getMessage() . "\n", FILE_APPEND );
				}
			}

			// Fallback to wccom
			try {
				$ext->wccom_id = $this->woo_extension_list->get_woo_extension_id_by_slug( $slug );
				$ext->from     = 'wccom';
				file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Extension '$slug' found in WooCommerce.com\n", FILE_APPEND );

				return $ext;
			} catch ( \UnexpectedValueException $e ) {
				file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Extension '$slug' not found in WooCommerce.com: " . $e->getMessage() . "\n", FILE_APPEND );
			}

			file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Extension '$slug' ($type) not found in WooCommerce.com or WordPress.org\n", FILE_APPEND );
			throw new \RuntimeException( "Extension '$slug' ($type) not found in WooCommerce.com or WordPress.org. Use an array with 'source_type' for non-remote sources." );
		}

		if ( ! is_array( $item ) || ! isset( $item['slug'] ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Extension object must have 'slug'\n", FILE_APPEND );
			throw new \RuntimeException( "Extension object must have 'slug'." );
		}

		$slug      = $item['slug'];
		$ext       = new Extension( $slug, $type );
		$ext->from = $item['source_type'] ?? 'wporg';

		switch ( $ext->from ) {
			case 'wporg':
				try {
					if ( $type === 'plugin' && $this->wporg_extension_list->is_wporg_plugin( $slug ) ) {
						$info         = $this->wporg_extension_list->get_plugin_download_info( $slug );
						$ext->source  = $info['url'];
						$ext->version = $item['version'] ?? $info['version'];
					} elseif ( $type === 'theme' && $this->wporg_extension_list->is_wporg_theme( $slug ) ) {
						$info         = $this->wporg_extension_list->get_theme_download_info( $slug );
						$ext->source  = $info['url'];
						$ext->version = $item['version'] ?? $info['version'];
					} else {
						file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Extension '$slug' ($type) not found in WordPress.org\n", FILE_APPEND );
						throw new \RuntimeException( "Extension '$slug' ($type) not found in WordPress.org." );
					}
					file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Extension '$slug' validated in WordPress.org\n", FILE_APPEND );
				} catch ( \Exception $e ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Failed to fetch WordPress.org info for '$slug' ($type): " . $e->getMessage() . "\n", FILE_APPEND );
					throw new \RuntimeException( "Failed to fetch WordPress.org info for '$slug' ($type): " . $e->getMessage() );
				}
				break;
			case 'wccom':
				try {
					$ext->wccom_id = $this->woo_extension_list->get_woo_extension_id_by_slug( $slug );
					$ext->version  = $item['version'] ?? 'stable';
					file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Extension '$slug' validated in WooCommerce.com\n", FILE_APPEND );
				} catch ( \UnexpectedValueException $e ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Extension '$slug' ($type) not found in WooCommerce.com\n", FILE_APPEND );
					throw new \RuntimeException( "Extension '$slug' ($type) not found in WooCommerce.com." );
				}
				break;
			case 'directory':
				if ( ! isset( $item['path'] ) || ! is_string( $item['path'] ) || empty( $item['path'] ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Directory extension '$slug' ($type) must have a non-empty 'path'\n", FILE_APPEND );
					throw new \RuntimeException( "Directory extension '$slug' ($type) must have a non-empty 'path'." );
				}
				$ext->directory = $item['path'];
				file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Directory extension '$slug' validated with path: {$item['path']}\n", FILE_APPEND );
				break;
			case 'zip':
				if ( ! isset( $item['path'] ) || ! is_string( $item['path'] ) || empty( $item['path'] ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Zip extension '$slug' ($type) must have a non-empty 'path'\n", FILE_APPEND );
					throw new \RuntimeException( "Zip extension '$slug' ($type) must have a non-empty 'path'." );
				}
				$ext->source = $item['path'];
				file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Zip extension '$slug' validated with path: {$item['path']}\n", FILE_APPEND );
				break;
			case 'url':
				if ( ! isset( $item['url'] ) || ! is_string( $item['url'] ) || empty( $item['url'] ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: URL extension '$slug' ($type) must have a non-empty 'url'\n", FILE_APPEND );
					throw new \RuntimeException( "URL extension '$slug' ($type) must have a non-empty 'url'." );
				}
				$ext->source = $item['url'];
				file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: URL extension '$slug' validated with url: {$item['url']}\n", FILE_APPEND );
				break;
			case 'build':
				if ( ! isset( $item['command'] ) || ! is_string( $item['command'] ) || empty( $item['command'] ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Build extension '$slug' ($type) must have a non-empty 'command'\n", FILE_APPEND );
					throw new \RuntimeException( "Build extension '$slug' ($type) must have a non-empty 'command'." );
				}
				if ( ! isset( $item['output'] ) || ! is_string( $item['output'] ) || empty( $item['output'] ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Build extension '$slug' ($type) must have a non-empty 'output'\n", FILE_APPEND );
					throw new \RuntimeException( "Build extension '$slug' ($type) must have a non-empty 'output'." );
				}
				$ext->source = $item['output'];
				file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Build extension '$slug' validated with output: {$item['output']}\n", FILE_APPEND );
				break;
			default:
				file_put_contents( '/tmp/qit/qit_debug.log', "EnvironmentParser: Invalid source_type '{$ext->from}' for extension '$slug' ($type)\n", FILE_APPEND );
				throw new \RuntimeException( "Invalid source_type '{$ext->from}' for extension '$slug' ($type)." );
		}

		return $ext;
	}
}