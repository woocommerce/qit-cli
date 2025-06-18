<?php

namespace QIT_CLI\PreCommand;

use QIT_CLI\App;
use QIT_CLI\Config;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\Environment\EnvParser;
use QIT_CLI\Environment\EnvVolumeParser;
use QIT_CLI\Environment\Extension;
use QIT_CLI\PreCommand\Configuration\ResolvedConfiguration;
use QIT_CLI\PreCommand\Extensions\ExtensionResolver;
use QIT_CLI\PreCommand\Extensions\VersionResolver;
use QIT_CLI\PreCommand\Results\EnvironmentResult;
use Symfony\Component\Console\Input\InputInterface;
use function QIT_CLI\normalize_path;

class EnvironmentResolver {
	protected ExtensionResolver $extension_resolver;
	protected EnvParser $env_parser;
	protected EnvVolumeParser $volume_parser;
	protected VersionResolver $version_resolver;

	public function __construct(
		ExtensionResolver $extension_resolver,
		EnvParser $env_parser,
		EnvVolumeParser $volume_parser,
		VersionResolver $version_resolver = null
	) {
		$this->extension_resolver = $extension_resolver;
		$this->env_parser         = $env_parser;
		$this->volume_parser      = $volume_parser;
		$this->version_resolver   = $version_resolver ?: App::make( VersionResolver::class );
	}

	public function resolve(
		ResolvedConfiguration $config,
		string $environment_name,
		bool $should_prepare = true,
		array $cli_overrides = [],
		InputInterface $input
	): EnvironmentResult {
		// Get environment configuration from qit.json, if it exists
		try {
			$env_config = $config->get_environment( $environment_name );
		} catch ( \RuntimeException $e ) {
			$env_config = [];
		}

		// Apply CLI overrides (CLI > JSON)
		$env_config = $this->applyOverrides( $env_config, $cli_overrides, $input );

		// Create EnvInfo with merged configuration
		$env_info = $this->createEnvInfo( $env_config );

		// Collect all extensions from environment
		$extensions = $this->collectExtensions( $env_config, $config );

		if ( $should_prepare ) {
			// Resolve and download all extensions
			$cache_dir           = normalize_path( Config::get_qit_dir() . 'cache' );
			$resolved_extensions = $this->extension_resolver->resolve(
				$extensions,
				$env_info,
				$cache_dir
			);

			$env_info->plugins        = $resolved_extensions->get_plugins();
			$env_info->themes         = $resolved_extensions->get_themes();
			$env_info->php_extensions = array_unique( array_merge(
				$env_config['php_extensions'] ?? [],
				$resolved_extensions->get_php_extensions()
			) );
		} else {
			// Just set the extensions without downloading
			$env_info->plugins        = array_filter( $extensions, fn( $ext ) => $ext->type === 'plugin' );
			$env_info->themes         = array_filter( $extensions, fn( $ext ) => $ext->type === 'theme' );
			$env_info->php_extensions = $env_config['php_extensions'] ?? [];
		}

		// Parse volumes
		$env_info->volumes = $this->volume_parser->parse_volumes( $env_config['volumes'] ?? [] );

		// Set other environment properties
		$this->setEnvironmentProperties( $env_info, $env_config );

		return new EnvironmentResult( $config, $env_info );
	}

	protected function applyOverrides( array $env_config, array $overrides, InputInterface $input ): array {
		// Start with defaults from InputInterface
		$defaults = [
			'php_version'    => $input->getOption( 'php_version' ),
			'wp_version'     => $input->getOption( 'wp_version' ),
			'woo_version'    => $input->getOption( 'woo_version' ),
			'object_cache'   => $input->getOption( 'object_cache' ),
			'plugins'        => $input->getOption( 'plugin' ),
			'themes'         => $input->getOption( 'theme' ),
			'volumes'        => $input->getOption( 'volume' ),
			'php_extensions' => $input->getOption( 'php_extension' ),
			'env_vars'       => $input->getOption( 'env' ),
			'env_files'      => $input->getOption( 'env_file' ),
		];

		// Merge JSON config over defaults
		$env_config = array_merge( $defaults, $env_config );

		// Merge explicit CLI overrides last
		foreach ( [ 'php_version', 'wp_version', 'woo_version', 'object_cache' ] as $key ) {
			if ( isset( $overrides[ $key ] ) ) {
				$env_config[ $key ] = $overrides[ $key ];
			}
		}

		// Use VersionResolver for WordPress RC versions only
		if ( isset( $env_config['wp_version'] ) && strtolower( $env_config['wp_version'] ) === 'rc' ) {
			$env_config['wp_version'] = $this->version_resolver->resolveWordPressVersion( $env_config['wp_version'] );
		}

		// No conversion for WooCommerce - keep everything as is

		// Merge arrays (append CLI values to JSON/defaults)
		foreach ( [ 'plugins', 'themes', 'volumes', 'php_extensions', 'env_vars', 'env_files' ] as $key ) {
			if ( isset( $overrides[ $key ] ) ) {
				$env_config[ $key ] = array_merge(
					$env_config[ $key ] ?? [],
					is_array( $overrides[ $key ] ) ? $overrides[ $key ] : [ $overrides[ $key ] ]
				);
			}
		}

		// Handle environment variables
		if ( isset( $env_config['env_vars'] ) || isset( $env_config['env_files'] ) ) {
			$env_parser             = App::make( EnvParser::class );
			$env_vars               = $env_parser->parse(
				$env_config['env_vars'] ?? [],
				$env_config['env_files'] ?? []
			);
			$env_config['env_vars'] = array_merge(
				$env_config['env_vars'] ?? [],
				$env_vars
			);
		}

		return $env_config;
	}

	protected function createEnvInfo( array $env_config ): E2EEnvInfo {
		$env_info                = new E2EEnvInfo();
		$env_info->env_id        = uniqid();
		$env_info->environment   = 'e2e';
		$env_info->temporary_env = normalize_path(
			Environment::get_temp_envs_dir() . $env_info->environment . '-' . $env_info->env_id
		);
		$env_info->created_at    = time();
		$env_info->status        = 'pending';

		// Set properties based on merged configuration
		$this->setEnvironmentProperties( $env_info, $env_config );

		return $env_info;
	}

	protected function collectExtensions( array $env_config, ResolvedConfiguration $config ): array {
		$extensions = [];

		// Add SUT if present
		if ( $config->sut_extension ) {
			$extensions[] = $config->sut_extension;
		}

		// Add plugins from environment
		foreach ( $env_config['plugins'] ?? [] as $plugin_config ) {
			$extensions[] = $this->createExtensionFromConfig( $plugin_config, 'plugin' );
		}

		// Add themes from environment
		foreach ( $env_config['themes'] ?? [] as $theme_config ) {
			$extensions[] = $this->createExtensionFromConfig( $theme_config, 'theme' );
		}

		return $extensions;
	}

	protected function createExtensionFromConfig( $config, string $type ): Extension {
		if ( is_string( $config ) ) {
			// Simple string format - defaults to wporg
			$extension          = new Extension( $config, $type );
			$extension->from    = 'wporg';
			$extension->version = 'stable';

			return $extension;
		}

		// Array format with details
		$extension = new Extension( $config['slug'], $type );

		if ( isset( $config['source'] ) ) {
			$source          = $config['source'];
			$extension->from = $source['type'];
			switch ( $source['type'] ) {
				case 'local':
					$extension->directory = $source['path'];
					$extension->source    = $source['path'];
					break;
				case 'url':
					$extension->source = $source['url'];
					break;
				case 'wporg':
				case 'wccom':
					$extension->version = $source['version'] ?? 'stable';
					break;
			}
		} elseif ( isset( $config['from'] ) ) {
			$extension->from = $config['from'];
			switch ( $config['from'] ) {
				case 'local':
					$extension->directory = $config['path'];
					$extension->source    = $config['path'];
					break;
				case 'url':
					$extension->source = $config['url'];
					break;
				case 'wporg':
				case 'wccom':
					$extension->version = $config['version'] ?? 'stable';
					break;
			}
		}

		return $extension;
	}

	protected function setEnvironmentProperties( E2EEnvInfo $env_info, array $env_config ): void {
		// Keep versions as-is - no conversion
		$env_info->wp_version   = $env_config['wp_version'] ?? 'stable';
		$env_info->php_version  = $env_config['php_version'] ?? '8.2';
		$env_info->woo_version  = $env_config['woo_version'] ?? '';
		$env_info->object_cache = $env_config['object_cache'] ?? false;
		$env_info->env          = $env_config['env_vars'] ?? [];

		// Set domain based on environment
		if ( getenv( 'QIT_EXPOSE_ENVIRONMENT_TO' ) === 'DOCKER' ) {
			$env_info->domain = "qitenvnginx{$env_info->env_id}";
		} else {
			$env_info->domain = getenv( 'QIT_DOMAIN' ) ?: 'localhost';
		}

		// Docker configuration
		$env_info->docker_images  = [];
		$env_info->docker_network = '';
	}
}