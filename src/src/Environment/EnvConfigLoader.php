<?php

namespace QIT_CLI\Environment;

use QIT_CLI\App;
use QIT_CLI\Environment\Environments\EnvInfo;
use Symfony\Component\Serializer\Serializer;

class EnvConfigLoader {
	/** @var Serializer */
	protected $serializer;

	public function __construct( Serializer $serializer ) {
		$this->serializer = $serializer;
	}

	public function init_env_info_from_config(): EnvInfo {
		$env_config = $this->load_config();

		$env_info = EnvInfo::from_array( $env_config );

		foreach ( $env_config as $key => $value ) {
			$disallowed = [ 'docker_images', 'temporary_env', 'env_id', 'created_at', 'status', 'env_id' ];
			if ( property_exists( $env_info, $key ) && ! in_array( $key, $disallowed, true ) && ! isset( $env_info->$key ) ) {
				$env_info->$key = $value;
			}
		}

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
	 * @param array<mixed> $array Multidimensional array of scalars.
	 *
	 * @return bool
	 */
	protected function validate_all_values_are_scalars( array $array ) {
		foreach ( $array as $value ) {
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
