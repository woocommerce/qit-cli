<?php

namespace QIT_CLI\Environment;

use QIT_CLI\App;
use Symfony\Component\Serializer\Serializer;

class EnvConfigLoader {
	/** @var Serializer */
	protected $serializer;

	public function __construct( Serializer $serializer ) {
		$this->serializer = $serializer;
	}

	public function load_config(): ?array {
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
			'qit-env.json' => file_exists( $working_directory . '/qit-env.json' ),
			'qit-env.yml'  => file_exists( $working_directory . '/qit-env.yml' ),
		];
		$env_override_files = [
			'qit-env.override.json' => file_exists( $working_directory . '/qit-env.override.json' ),
			'qit-env.override.yml'  => file_exists( $working_directory . '/qit-env.override.yml' ),
		];

		// If both "qit-env.json" and "qit-env.yml" exists, throw.
		if ( $env_files['qit-env.json'] && $env_files['qit-env.yml'] ) {
			throw new \RuntimeException( 'Both "qit-env.json" and "qit-env.yml" exists. Please remove one.' );
		}

		// If both "qit-env.override.json" and "qit-env.override.yml" exists, throw.
		if ( $env_override_files['qit-env.override.json'] && $env_override_files['qit-env.override.yml'] ) {
			throw new \RuntimeException( 'Both "qit-env.override.json" and "qit-env.override.yml" exists. Please remove one.' );
		}

		$env_file = null;
		if ( $env_files['qit-env.json'] ) {
			$env_file = 'qit-env.json';
		} elseif ( $env_files['qit-env.yml'] ) {
			$env_file = 'qit-env.yml';
		}

		if ( ! $env_file ) {
			return null;
		}

		$env_override_file = null;
		if ( $env_override_files['qit-env.override.json'] ) {
			$env_override_file = 'qit-env.override.json';
		} elseif ( $env_override_files['qit-env.override.yml'] ) {
			$env_override_file = 'qit-env.override.yml';
		}


		$env_config = $this->serializer->decode( file_get_contents( $working_directory . '/' . $env_file ), pathinfo( $env_file, PATHINFO_EXTENSION ) );

		if ( $env_override_file ) {
			$env_override_config = $this->serializer->decode( file_get_contents( $working_directory . '/' . $env_override_file ), pathinfo( $env_override_file, PATHINFO_EXTENSION ) );
			$env_config          = array_merge_recursive( $env_config, $env_override_config );
		}

		return $env_config;
	}
}