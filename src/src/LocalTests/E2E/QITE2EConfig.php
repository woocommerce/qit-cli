<?php

namespace QIT_CLI\LocalTests\E2E;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * @phpstan-type E2EConfig array{
 * sharedSetup: array<string>,
 * setup: array<string>,
 * teardown: array<string>,
 * sharedTeardown: array<string>,
 * muPlugins: array<string>
 * }
 */
class QITE2EConfig {
	/**
	 * @var Serializer
	 */
	protected $serializer;

	/**
	 * @var OutputInterface
	 */
	protected $output;

	public function __construct( Serializer $serializer, OutputInterface $output ) {
		$this->serializer = $serializer;
		$this->output     = $output;
	}

	/**
	 * @var array Default configuration
	 */
	protected $default = [
		'sharedSetup'    => [ 'bootstrap/shared-setup.sh' ],
		'setup'          => [ 'bootstrap/setup.sh' ],
		'teardown'       => [ 'bootstrap/teardown.sh' ],
		'sharedTeardown' => [ 'bootstrap/shared-teardown.sh' ],
		'muPlugins'      => [ 'bootstrap/mu-plugin.php', 'bootstrap/mu-plugin' ],
	];

	/**
	 * Loads the qit-e2e.json if present, merges with defaults, and returns final config array.
	 *
	 * @param string $base_dir The path where qit-e2e.json|yml might live.
	 *
	 * @return E2EConfig
	 */
	public function load_config( $base_dir ) {
		static $loaded_configs = [];
		if ( isset( $loaded_configs[ $base_dir ] ) ) {
			return $loaded_configs[ $base_dir ];
		}

		$json_file = $base_dir . '/qit-e2e.json';
		$yml_file  = $base_dir . '/qit-e2e.yml';

		// If both exist, throw.
		if ( file_exists( $json_file ) && file_exists( $yml_file ) ) {
			throw new \RuntimeException( 'Both qit-e2e.json and qit-e2e.yml exist. Please remove one.' );
		}

		// If "yml" doesn't exist but "yaml" exists, use it.
		if ( ! file_exists( $yml_file ) && file_exists( $base_dir . '/qit-e2e.yaml' ) ) {
			$yml_file = $base_dir . '/qit-e2e.yaml';
		}

		// If none exist, return defaults.
		if ( ! file_exists( $json_file ) && ! file_exists( $yml_file ) ) {
			$filtered_defaults           = $this->filter_missing_paths( $this->default, $base_dir );
			$loaded_configs[ $base_dir ] = $filtered_defaults;

			return $filtered_defaults;
		}

		// Set "$configFile" to the file that exists.
		$config_file = file_exists( $json_file ) ? $json_file : $yml_file;

		$merged      = $this->default;
		$user_config = $this->serializer->decode( file_get_contents( $config_file ), pathinfo( $config_file, PATHINFO_EXTENSION ) );

		if ( is_array( $user_config ) ) {
			foreach ( $user_config as $key => $value ) {
				$merged[ $key ] = $value;
			}
		}

		$merged = $this->filter_missing_paths( $merged, $base_dir );

		$loaded_configs[ $base_dir ] = $merged;

		return $merged;
	}

	/**
	 * Check each array of paths (sharedSetup, setup, teardown, sharedTeardown, muPlugins).
	 * If user provided the path, but it doesn't exist, we print an informative message and skip it.
	 * If it's just a default path that doesn't exist, we skip it silently.
	 *
	 * @param array  $config
	 * @param string $base_dir
	 *
	 * @return array
	 */
	protected function filter_missing_paths( array $config, string $base_dir ): array {
		$keys_to_check = [ 'sharedSetup', 'setup', 'teardown', 'sharedTeardown', 'muPlugins' ];

		foreach ( $keys_to_check as $key ) {
			if ( ! isset( $config[ $key ] ) || ! is_array( $config[ $key ] ) ) {
				continue;
			}

			$filtered_paths = [];

			foreach ( $config[ $key ] as $path ) {
				$full_path  = rtrim( $base_dir, '/' ) . '/' . $path;
				$is_default = in_array( $path, $this->default[ $key ], true );

				if ( file_exists( $full_path ) ) {
					// File exists -> keep it
					$filtered_paths[] = $path;
				} else {
					// File does not exist -> skip, but maybe print a message if user-defined
					if ( $is_default ) {
						// It's a missing default -> skip silently
					} else {
						// It's user-defined -> we log a warning (or throw).
						$this->output->writeln(
							sprintf(
								'<comment>QIT_E2E: The user-defined file "%s" does not exist in "%s". Skipping.</comment>',
								$path,
								$base_dir
							)
						);
					}
				}
			}

			$config[ $key ] = $filtered_paths;
		}

		return $config;
	}
}
