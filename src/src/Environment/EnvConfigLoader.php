<?php

namespace QIT_CLI\Environment;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\ExtensionDownload\ExtensionDownloader;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Serializer;

class EnvConfigLoader {
	/** @var Serializer */
	protected $serializer;

	/** @var Cache */
	protected $cache;

	/** @var OutputInterface */
	protected $output;

	public function __construct( Serializer $serializer, Cache $cache, OutputInterface $output ) {
		$this->serializer = $serializer;
		$this->cache      = $cache;
		$this->output     = $output;
	}

	/**
	 * @param array{ defaults: array<string, mixed>, overrides: array<string, mixed> } $options Description of the parameter.
	 *
	 * @return EnvInfo Returns an EnvInfo object.
	 */
	public function init_env_info(
		array $options = [
			'defaults'  => [],
			'overrides' => [],
		]
	): EnvInfo {
		// Load the environment config file..
		$env_config = $this->load_config();

		// Check that config file doesn't contain disallowed keys.
		foreach ( EnvInfo::$not_user_configurable as $d ) {
			if ( array_key_exists( $d, $env_config ) ) {
				throw new \RuntimeException( "Disallowed key '$d' found in environment config" );
			}
		}

		// Load the environment config from user input.
		foreach ( $options['overrides'] as $key => $value ) {
			// Check that options don't contain disallowed keys.
			foreach ( EnvInfo::$not_user_configurable as $d ) {
				if ( $d === $key ) {
					throw new \RuntimeException( "Disallowed key '$d' found in options" );
				}
			}

			$env_config[ $key ] = $value;
		}

		// Set the defaults.
		foreach ( $options['defaults'] as $key => $value ) {
			if ( ! array_key_exists( $key, $env_config ) ) {
				$env_config[ $key ] = $value;
			}
		}

		$parse_plugins_and_themes = function ( &$env_config ): void {
			foreach (
				[
					'plugins' => $env_config['plugins'] ?? [],
					'themes'  => $env_config['themes'] ?? [],
				] as $type => $plugins_or_themes
			) {
				$env_config[ $type ] = [];

				// Normalize non-arrays to arrays.
				foreach ( $plugins_or_themes as &$p_or_t ) {
					if ( ! is_array( $p_or_t ) ) {
						// If it starts with "{" we consider it a JSON.
						if ( strpos( $p_or_t, '{' ) === 0 ) {
							$p_or_t = json_decode( $p_or_t, true );

							if ( is_null( $p_or_t ) ) {
								throw new \RuntimeException( 'Invalid JSON string>' );
							}

							continue;
						}

						// Split source from test tag.
						$last_colon_pos = strrpos( $p_or_t, ':' );

						if ( $last_colon_pos !== false ) {
							// Split the string at the last colon
							$source   = substr( $p_or_t, 0, $last_colon_pos );
							$test_tag = substr( $p_or_t, $last_colon_pos + 1 );

							// Trim and check if test_tag is valid
							$test_tag_trimmed = trim( $test_tag );
							if ( preg_match( '/^[a-zA-Z0-9_-]+$/', $test_tag_trimmed ) ) {
								// Valid test_tag
								$p_or_t = [
									'source'   => trim( $source ),
									'test_tag' => $test_tag_trimmed,
								];
							} else {
								// If invalid test_tag, treat the whole string as the source
								$p_or_t = [
									'source'   => trim( $p_or_t ),
									'test_tag' => 'none',
								];
							}
						} else {
							// No colon, the whole string is the source
							$p_or_t = [
								'source'   => trim( $p_or_t ),
								'test_tag' => 'none',
							];
						}
					}

					unset( $p_or_t );
				}

				// Validate and use.
				foreach ( $plugins_or_themes as $maybe_slug => $p_or_t ) {
					if ( ! is_array( $p_or_t ) ) {
						throw new \LogicException();
					}

					// This can happen if the user passes a manual JSON string without the "source" key.
					if ( empty( $p_or_t['source'] ) && empty( $maybe_slug ) ) {
						throw new \InvalidArgumentException( 'The "source" is required for plugins and themes' );
					}

					// This can happen if the user has a config file without the "source" parameter, which is expected, since we infer from the key.
					if ( empty( $p_or_t['source'] ) ) {
						$p_or_t['source'] = $maybe_slug;
					}

					if ( empty( $p_or_t['slug'] ) ) {
						if ( ! empty( $maybe_slug ) ) {
							$p_or_t['slug'] = $maybe_slug;
						} else {
							$p_or_t['slug'] = $p_or_t['source'];
						}
					}

					if ( ! ExtensionDownloader::is_valid_plugin_slug( $p_or_t['slug'] ) ) {
						throw new \InvalidArgumentException( 'Invalid plugin slug: ' . $p_or_t['slug'] );
					}

					$args = [
						'source'   => trim( $p_or_t['source'] ),
						'slug'     => trim( $p_or_t['slug'] ),
						'test_tag' => trim( $p_or_t['test_tag'] ?? 'none' ),
					];

					$env_config[ $type ][] = $args;
				}
			}
		};

		// Plugins and Themes.
		$parse_plugins_and_themes( $env_config );

		// Requires.
		foreach ( $env_config['requires'] ?? [] as $file ) {
			if ( file_exists( $file ) ) {
				if ( $this->output->isVerbose() ) {
					$this->output->writeln( sprintf( 'Loading file %s', $file ) );
				}

				$prefix = null;

				/**
				 * Since the phar is scoped with php-scoper, we need to prefix the handler as well.
				 *
				 * This essentially means, at runtime, replacing
				 *  - use QIT_CLI\
				 *  - use _HumbugBoxc7c7e1250ee1\QIT_CLI\
				 *
				 * Where the first part is completely random by php-scoper.
				 * "_HumbubBox" is the default prefix of php-scoper.
				 *
				 * @see https://github.com/humbug/php-scoper
				 */
				foreach ( explode( '\\', static::class ) as $namespace ) {
					if ( strpos( $namespace, 'HumbugBox' ) !== false ) {
						$prefix = $namespace;
						break;
					}
				}

				if ( ! is_null( $prefix ) ) {
					// Prefixed phar.
					if ( $this->output->isVeryVerbose() ) {
						$this->output->writeln( sprintf( 'Converting handler to use prefix %s', $prefix ) );
					}

					$tmp_file = sys_get_temp_dir() . '/' . pathinfo( $file, PATHINFO_FILENAME ) . uniqid( 'prefixed' ) . '.php';

					if ( file_put_contents( $tmp_file, str_replace( 'use QIT_CLI\\', "use $prefix\\QIT_CLI\\", file_get_contents( $file ) ) ) === false ) {
						throw new \RuntimeException( 'Failed to write to the temporary file' );
					}

					if ( $this->output->isVeryVerbose() ) {
						$this->output->writeln( sprintf( 'Loading file %s', $tmp_file ) );
					}

					require_once $tmp_file;
				} else {
					// If running outside of Phar context, just require it.
					require_once $file;
				}
			} else {
				$this->output->writeln( sprintf( '<error>File %s does not exist.</error>', $file ) );
				throw new \RuntimeException( sprintf( 'File %s does not exist.', $file ) );
			}
		}

		// No more need for this from now on.
		unset( $env_config['requires'] );

		// Volumes can be mapped automatically depending on the working directory.
		$env_config['volumes'] = App::make( EnvVolumeParser::class )->parse_volumes( $env_config['volumes'] ?? [] );

		// Parse and Validate.
		foreach ( $env_config as $key => &$value ) {
			switch ( $key ) {
				case 'php_extensions':
					$value = array_map( 'trim', $value );
					foreach ( $value as $ext ) {
						if ( ! preg_match( '/^[a-z0-9_-]+$/i', $ext ) ) {
							throw new \RuntimeException( 'Invalid PHP extension name: ' . $ext );
						}
						if ( strlen( $ext ) > 50 ) {
							throw new \RuntimeException( 'PHP extension name too long: ' . $ext );
						}
					}
					break;
				case 'wordpress_version':
					if ( in_array( $value, [ 'stable', 'rc' ], true ) ) {
						$value = $this->cache->get_manager_sync_data( 'versions' )['wordpress'][ $value ];
					}
					break;
				case 'woocommerce_version':
					if ( in_array( $value, [ 'stable', 'rc' ], true ) ) {
						$value = $this->cache->get_manager_sync_data( 'versions' )['woocommerce'][ $value ];
					}
					break;
			}
		}

		$env_info = EnvInfo::from_array( $env_config );

		return $env_info;
	}

	/**
	 * @return array<mixed> Multidimensional array of scalars.
	 */
	public function load_config(): array {
		/*
		 * Rules:
		 * - Directory is working-directory gwtcwd();
		 * - Filename starts with "qit-env"
		 * - File extension is ".json" or ".yml"
		 * - If a ".override.json" or ".override.yml" file exists, it should add or override the values from the original file.
		 * - We will use App::make(SerializerInterface::class)->unserialize() to unserialize the files we find.
		 * - If both "qit-env.json" and "qit-env.yml" exists, throw.
		 * - If both "qit-env.override.json" and "qit-env.override.yml" exists, throw.
		 */
		if ( defined( 'UNIT_TESTS' ) ) {
			$working_directory = App::getVar( 'QIT_CONFIG_LOADER_DIR' );
		} else {
			$working_directory = getcwd();
		}

		$env_files          = [
			'qit-env.json'  => file_exists( $working_directory . '/qit-env.json' ),
			'qit-env.yml'   => file_exists( $working_directory . '/qit-env.yml' ),
			'.qit-env.json' => file_exists( $working_directory . '/.qit-env.json' ),
			'.qit-env.yml'  => file_exists( $working_directory . '/.qit-env.yml' ),
		];
		$env_override_files = [
			'qit-env.override.json'  => file_exists( $working_directory . '/qit-env.override.json' ),
			'qit-env.override.yml'   => file_exists( $working_directory . '/qit-env.override.yml' ),
			'.qit-env.override.json' => file_exists( $working_directory . '/.qit-env.override.json' ),
			'.qit-env.override.yml'  => file_exists( $working_directory . '/.qit-env.override.yml' ),
		];

		if ( $this->output->isVeryVerbose() ) {
			$this->output->writeln( 'Working directory: ' . $working_directory );
			foreach ( array_merge( $env_files, $env_override_files ) as $file => $exists ) {
				$this->output->writeln( $file . ': ' . ( $exists ? 'Exists' : 'Does not exist' ) );
			}
		}

		// If more than one env file exists, throw.
		if ( count( array_filter( $env_files ) ) > 1 ) {
			throw new \RuntimeException( 'More than one "qit-env" file exists. Please remove one.' );
		}

		// If more than one override file exists, throw.
		if ( count( array_filter( $env_override_files ) ) > 1 ) {
			throw new \RuntimeException( 'More than one "qit-env.override" file exists. Please remove one.' );
		}

		$env_file = array_search( true, $env_files, true );

		if ( ! $env_file ) {
			return [];
		}

		$this->output->writeln( 'Loading environment config from ' . $env_file . '...' );

		try {
			$env_config = $this->serializer->decode( file_get_contents( $working_directory . '/' . $env_file ), pathinfo( $env_file, PATHINFO_EXTENSION ) );

			if ( ! is_array( $env_config ) ) {
				throw new \Exception( 'Invalid config file' );
			}
		} catch ( \Exception $e ) {
			throw new \RuntimeException( 'Failed to load environment config: ' . $e->getMessage() );
		}

		$env_override_file = array_search( true, $env_override_files, true );

		if ( $env_override_file ) {
			$this->output->writeln( 'Loading environment override config from ' . $env_override_file . '...' );
			try {
				$env_override_config = $this->serializer->decode( file_get_contents( $working_directory . '/' . $env_override_file ), pathinfo( $env_override_file, PATHINFO_EXTENSION ) );
				if ( ! is_array( $env_override_config ) ) {
					throw new \Exception( 'Invalid override config file' );
				}
			} catch ( \Exception $e ) {
				throw new \RuntimeException( 'Failed to load environment override config: ' . $e->getMessage() );
			}
			$env_config = $this->merge_config_recursively( $env_config, $env_override_config );
			if ( ! $this->validate_all_values_are_scalars( $env_config ) ) {
				throw new \InvalidArgumentException( 'Configuration contains non-scalar values.' );
			}
		}

		return $env_config;
	}

	/**
	 * @param array<mixed> $config_1
	 * @param array<mixed> $config_2
	 *
	 * @return array<mixed> Multidimensional array of scalars.
	 */
	protected function merge_config_recursively( array $config_1, array $config_2 ): array {
		foreach ( $config_2 as $key => $value ) {
			if ( is_array( $value ) ) {
				if ( isset( $config_1[ $key ] ) ) {
					if ( ! is_array( $config_1[ $key ] ) ) {
						// Throw an exception if a scalar in $config_1 is being overridden by an array in $config_2.
						throw new \InvalidArgumentException( "Type mismatch for key '$key': cannot override scalar value with an array." );
					}
					// Merge arrays recursively.
					$config_1[ $key ] = $this->merge_config_recursively( $config_1[ $key ], $value );
				} else {
					// If the key doesn't exist in $config_1, add it as a new array.
					$config_1[ $key ] = $value;
				}
			} else {
				if ( isset( $config_1[ $key ] ) && is_array( $config_1[ $key ] ) ) {
					// Throw an exception if an array in $config_1 is being overridden by a scalar in $config_2.
					throw new \InvalidArgumentException( "Type mismatch for key '$key': cannot override array with a scalar value." );
				}
				// For non-array values (and non-numeric keys), replace the value in $config_1.
				$config_1[ $key ] = $value;
			}
		}

		return $config_1;
	}

	/**
	 * @param array<mixed> $array_to_validate Multidimensional array of scalars.
	 *
	 * @return bool
	 */
	protected function validate_all_values_are_scalars( array $array_to_validate ) {
		foreach ( $array_to_validate as $value ) {
			if ( is_array( $value ) ) {
				// If the value is an array, recurse into it.
				if ( ! $this->validate_all_values_are_scalars( $value ) ) {
					return false;
				}
			} elseif ( ! is_scalar( $value ) ) {
				// If the value is not a scalar, return false.
				return false;
			}
		}

		return true;
	}
}
