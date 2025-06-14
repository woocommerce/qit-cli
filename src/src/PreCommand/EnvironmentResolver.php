<?php

namespace QIT_CLI\PreCommand;

use QIT_CLI\App;
use QIT_CLI\Config;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Environments\E2E\E2EEnvironment;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\Environment\EnvParser;
use QIT_CLI\Environment\EnvVolumeParser;
use QIT_CLI\Environment\Extension;
use QIT_CLI\PreCommand\Configuration\ResolvedConfiguration;
use QIT_CLI\PreCommand\Extensions\ExtensionResolver;
use QIT_CLI\PreCommand\Results\EnvironmentResult;
use Symfony\Component\Console\Input\InputInterface;
use function QIT_CLI\normalize_path;

class EnvironmentResolver {
	protected ExtensionResolver $extension_resolver;
	protected EnvParser $env_parser;
	protected EnvVolumeParser $volume_parser;

	public function __construct(
		ExtensionResolver $extension_resolver,
		EnvParser $env_parser,
		EnvVolumeParser $volume_parser
	) {
		$this->extension_resolver = $extension_resolver;
		$this->env_parser         = $env_parser;
		$this->volume_parser      = $volume_parser;
	}

	/**
	 * Resolve environment configuration and optionally prepare it
	 */
	public function resolve(
		ResolvedConfiguration $config,
		string $environment_name,
		bool $should_prepare = true
	): EnvironmentResult {
		// Get environment configuration
		$env_config = $config->get_environment( $environment_name );

		// Create EnvInfo
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

	/**
	 * Create the appropriate EnvInfo object
	 */
	protected function createEnvInfo( array $env_config ): E2EEnvInfo {
		$env_info                = new E2EEnvInfo();
		$env_info->env_id        = uniqid();
		$env_info->environment   = 'e2e';
		$env_info->temporary_env = normalize_path(
			Environment::get_temp_envs_dir() . $env_info->environment . '-' . $env_info->env_id
		);
		$env_info->created_at    = time();
		$env_info->status        = 'pending';

		return $env_info;
	}

	/**
	 * Collect all extensions from environment configuration
	 */
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

	/**
	 * Create Extension object from configuration
	 */
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
		}

		return $extension;
	}

	/**
	 * Set additional environment properties
	 */
	protected function setEnvironmentProperties( E2EEnvInfo $env_info, array $env_config ): void {
		$env_info->wp_version   = $env_config['wp_version'] ?? 'latest';
		$env_info->php_version  = $env_config['php_version'] ?? '8.0';
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