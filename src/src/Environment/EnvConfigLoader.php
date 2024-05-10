<?php

namespace QIT_CLI\Environment;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Environment\Environments\EnvInfo;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Serializer;

class EnvConfigLoader {
	/** @var Serializer */
	protected $serializer;

	/** @var Cache */
	protected $cache;

	/** @var OutputInterface */
	protected $output;

	/** @var PluginsAndThemesParser */
	protected $plugins_and_themes_parser;

	public function __construct( Serializer $serializer, Cache $cache, OutputInterface $output, PluginsAndThemesParser $plugins_and_themes_parser ) {
		$this->serializer                = $serializer;
		$this->cache                     = $cache;
		$this->output                    = $output;
		$this->plugins_and_themes_parser = $plugins_and_themes_parser;
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

			// If it's an array, append, otherwise replace.
			if ( is_array( $value ) && array_key_exists( $key, $env_config ) && is_array( $env_config[ $key ] ) ) {
				$env_config[ $key ] = array_merge( $env_config[ $key ], $value );
			} else {
				$env_config[ $key ] = $value;
			}
		}

		// Set the defaults.
		foreach ( $options['defaults'] as $key => $value ) {
			if ( ! array_key_exists( $key, $env_config ) ) {
				$env_config[ $key ] = $value;
			}
		}

		$this->normalize_plural_to_singular( $env_config );

		// Plugins and Themes.
		$env_config['plugin'] = $this->plugins_and_themes_parser->parse_extensions(
			$env_config['plugin'] ?? [],
			Extension::TYPES['plugin'],
			getenv( 'QIT_UP_AND_TEST' ) ? Extension::ACTIONS['bootstrap'] : Extension::ACTIONS['activate']
		);

		$env_config['theme'] = $this->plugins_and_themes_parser->parse_extensions(
			$env_config['theme'] ?? [],
			Extension::TYPES['theme'],
			getenv( 'QIT_UP_AND_TEST' ) ? Extension::ACTIONS['bootstrap'] : Extension::ACTIONS['activate']
		);

		// Requires.
		foreach ( $env_config['require'] ?? [] as $file ) {
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
		unset( $env_config['require'] );

		// Volumes can be mapped automatically depending on the working directory.
		$env_config['volume'] = App::make( EnvVolumeParser::class )->parse_volumes( $env_config['volume'] ?? [] );

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
				case 'wp':
					$value = EnvironmentVersionResolver::resolve_wp( $value );
					break;
				case 'woo':
					$value = EnvironmentVersionResolver::resolve_woo( $value );
					break;
			}
		}

		$this->normalize_singular_to_plural( $env_config );

		$env_info = EnvInfo::from_array( $env_config );

		return $env_info;
	}

	/**
	 * @return array<mixed> Multidimensional array of scalars.
	 */
	public function load_config(): array {
		// If it's an override (user passed --config) parameter, load it and return.
		if ( ! empty( App::getVar( 'QIT_CONFIG_OVERRIDE' ) ) ) {
			$config_override = App::getVar( 'QIT_CONFIG_OVERRIDE' );

			if ( ! file_exists( $config_override ) ) {
				throw new \RuntimeException( "Config file '$config_override' does not exist." );
			}

			$this->output->writeln( 'Loading environment config from override parameter ' . $config_override . '...' );

			try {
				$env_config = $this->serializer->decode( file_get_contents( $config_override ), pathinfo( $config_override, PATHINFO_EXTENSION ) );

				if ( ! is_array( $env_config ) ) {
					throw new \Exception( 'Invalid config file' );
				}

				return $env_config;
			} catch ( \Exception $e ) {
				throw new \RuntimeException( 'Failed to load environment config: ' . $e->getMessage() );
			}
		}

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
	 * @param array<string, mixed> $config
	 *
	 * @return void
	 */
	protected function normalize_plural_to_singular( array &$config ): void {
		/*
		 * These values make sense to be plural in config, but we want to normalize them to singular.
		 * This way the user can do:
		 * plugins:
		 *  - foo
		 *  - bar
		 * On the config file, and --plugin foo --plugin bar in the CLI parameters.
		 */
		$plurals = [ 'plugins', 'themes', 'volumes', 'php_extensions' ];

		// Convert scalars to arrays.
		foreach ( $plurals as $plural ) {
			if ( array_key_exists( $plural, $config ) && ! is_array( $config[ $plural ] ) ) {
				$config[ $plural ] = [ $config[ $plural ] ];
			}
		}

		// Normalize plural to singular. If it already exists, throw.
		foreach ( $plurals as $plural ) {
			$singular = rtrim( $plural, 's' );
			if ( array_key_exists( $plural, $config ) && ! array_key_exists( $singular, $config ) ) {
				$config[ $singular ] = $config[ $plural ];
				unset( $config[ $plural ] );
			} elseif ( array_key_exists( $plural, $config ) && array_key_exists( $singular, $config ) ) {
				// If both exist, both should be array and should be merged.
				if ( ! is_array( $config[ $plural ] ) || ! is_array( $config[ $singular ] ) ) {
					throw new \RuntimeException( "Both '$singular' and '$plural' keys exist in the environment config, but one of them is not an array." );
				}

				$config[ $singular ] = array_merge( $config[ $plural ], $config[ $singular ] );
				unset( $config[ $plural ] );
			}
		}
	}

	/**
	 * @param array<string, mixed> $config
	 *
	 * @return void
	 */
	protected function normalize_singular_to_plural( array &$config ): void {
		/*
		 * These values make sense to be singular in config, but we want to normalize them to plural.
		 * This way the user can do:
		 * plugin: foo
		 * On the config file, and --plugins foo in the CLI parameters.
		 */
		$singulars = [ 'plugin', 'theme', 'volume', 'php_extension' ];

		// Normalize singular to plural. If it already exists, throw.
		foreach ( $singulars as $singular ) {
			$plural = $singular . 's';
			if ( array_key_exists( $singular, $config ) && ! array_key_exists( $plural, $config ) ) {
				$config[ $plural ] = $config[ $singular ];
				unset( $config[ $singular ] );
			} elseif ( array_key_exists( $singular, $config ) && array_key_exists( $plural, $config ) ) {
				throw new \RuntimeException( "Both '$singular' and '$plural' keys exist in the environment config." );
			}
		}
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
