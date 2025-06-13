<?php

namespace QIT_CLI\PreCommand\Configuration;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Config;
use QIT_CLI\Environment\Extension;
use QIT_CLI\PreCommand\Configuration\Parser\QitJsonParser;
use QIT_CLI\PreCommand\Extensions\ExtensionResolver;
use QIT_CLI\PreCommand\Extensions\ExtensionSetResolver;
use QIT_CLI\PreCommand\Download\CustomTestsDownloader;
use QIT_CLI\PreCommand\Download\TestPackageDownloader;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\normalize_path;
use function QIT_CLI\debug_log;
use function QIT_CLI\debug_log_verbose;
use function QIT_CLI\debug_dump;

/**
 * Main configuration resolver that orchestrates the entire resolution process
 */
class ConfigurationResolver {
	/** @var QitJsonParser */
	protected $parser;

	/** @var ExtensionResolver */
	protected $extension_resolver;

	/** @var ExtensionSetResolver */
	protected $extension_set_resolver;

	/** @var TestPackageDownloader */
	protected $package_downloader;

	/** @var Cache */
	protected $cache;

	/** @var OutputInterface */
	protected $output;

	/** @var string */
	protected $cache_dir;

	public function __construct(
		QitJsonParser $parser,
		ExtensionResolver $extension_resolver,
		ExtensionSetResolver $extension_set_resolver,
		TestPackageDownloader $package_downloader,
		Cache $cache,
		OutputInterface $output
	) {
		$this->parser                 = $parser;
		$this->extension_resolver     = $extension_resolver;
		$this->extension_set_resolver = $extension_set_resolver;
		$this->package_downloader     = $package_downloader;
		$this->cache                  = $cache;
		$this->output                 = $output;
		$this->cache_dir              = normalize_path( Config::get_qit_dir() . 'cache' );
	}

	/**
	 * Resolve a configuration file into a complete ResolvedConfiguration object
	 */
	public function resolve( string $config_file ): ResolvedConfiguration {
		$this->output->writeln( '<info>Resolving configuration...</info>' );
		debug_log( "Starting resolution of config file: $config_file", 'info' );

		// Step 1: Parse the configuration file
		debug_log( 'Step 1: Parsing qit.json...' );
		$parsed_config = $this->parser->parse( $config_file );
		debug_dump( $parsed_config, 'Parsed configuration' );

		// Create the resolved configuration object
		$resolved                          = new ResolvedConfiguration( $parsed_config );
		$resolved->metadata['config_file'] = $config_file;
		$resolved->cache_dir               = $this->cache_dir;
		debug_log( "Cache directory: {$this->cache_dir}" );

		// Step 2: Process SUT
		debug_log( 'Step 2: Processing System Under Test...' );
		$resolved->sut           = $parsed_config['sut'];
		$resolved->sut_extension = $this->create_sut_extension( $parsed_config['sut'] );
		debug_dump( $resolved->sut_extension, 'SUT Extension' );

		// Step 3: Copy basic configuration
		debug_log( 'Step 3: Copying basic configuration...' );
		$resolved->environments = $parsed_config['environments'] ?? [];
		$resolved->test_types   = $parsed_config['test_types'] ?? [];
		$resolved->groups       = $parsed_config['groups'] ?? [];
		debug_log( sprintf( 'Found %d environments, %d test types, %d groups',
			count( $resolved->environments ),
			count( $resolved->test_types ),
			count( $resolved->groups )
		) );

		// Step 4: Process test packages
		debug_log( 'Step 4: Loading test packages...' );
		$resolved->test_packages = $this->parser->get_config()['test_packages'] ?? [];
		debug_log( sprintf( 'Found %d test packages', count( $resolved->test_packages ) ) );
		debug_dump( array_keys( $resolved->test_packages ), 'Test package references' );

		// Step 5: Download remote test packages
		debug_log( 'Step 5: Downloading remote test packages...' );
		$this->download_remote_test_packages( $resolved );

		// Step 6: Collect all extensions from configuration
		debug_log( 'Step 6: Collecting all extensions from configuration...' );
		$all_extensions = $this->collect_all_extensions( $resolved );
		debug_log( sprintf( 'Collected %d extensions total', count( $all_extensions ) ) );
		foreach ( $all_extensions as $ext ) {
			debug_log_verbose( sprintf( '  - %s (%s) from %s', $ext->slug, $ext->type, $ext->from ) );
		}

		// Step 7: Resolve extension sets if any
		if ( isset( $parsed_config['extension_set'] ) ) {
			debug_log( 'Step 7: Resolving extension sets...' );
			// This would be handled by ExtensionSetResolver
		}

		// Step 8: Resolve all extensions (including dependencies)
		debug_log( 'Step 8: Resolving extensions and dependencies...' );
		$resolved_extensions = $this->extension_resolver->resolve(
			$all_extensions,
			$this->create_temp_env_info( $resolved ),
			$this->cache_dir
		);

		$resolved->resolved_plugins = $resolved_extensions->get_plugins();
		$resolved->resolved_themes  = $resolved_extensions->get_themes();
		$resolved->php_extensions   = $resolved_extensions->get_php_extensions();

		debug_log( sprintf( 'Resolved: %d plugins, %d themes, %d PHP extensions',
			count( $resolved->resolved_plugins ),
			count( $resolved->resolved_themes ),
			count( $resolved->php_extensions )
		) );

		// Step 9: Collect requirements from test packages
		debug_log( 'Step 9: Collecting requirements from test packages...' );
		$this->collect_test_package_requirements( $resolved );
		debug_log( sprintf( 'Requirements: %d secrets, %d services, %d PHP extensions',
			count( $resolved->required_secrets ),
			count( $resolved->required_services ),
			count( $resolved->php_extensions )
		) );

		// Step 10: Validate the configuration
		debug_log( 'Step 10: Validating configuration...' );
		$errors = $resolved->validate();
		if ( ! empty( $errors ) ) {
			debug_log( 'Validation errors found:', 'error' );
			foreach ( $errors as $error ) {
				debug_log( "  - $error", 'error' );
			}
			throw new \RuntimeException(
				"Configuration validation failed:\n" . implode( "\n", array_map( fn( $e ) => "  - $e", $errors ) )
			);
		}

		$this->output->writeln( '<info>Configuration resolved successfully!</info>' );
		debug_log( 'Resolution completed successfully', 'info' );

		return $resolved;
	}

	/**
	 * Create SUT extension object
	 */
	protected function create_sut_extension( array $sut ): Extension {
		debug_log( "Creating SUT extension for: {$sut['slug']} ({$sut['type']})" );
		debug_dump( $sut, 'SUT configuration' );

		$extension = new Extension( $sut['slug'], $sut['type'] );

		// Map source configuration
		switch ( $sut['source']['type'] ) {
			case 'local':
				$extension->from      = 'local';
				$extension->directory = $sut['source']['resolved_path'] ?? $sut['source']['path'];
				$extension->source    = $extension->directory;
				debug_log( "SUT source: local at {$extension->directory}" );
				break;

			case 'build':
				$extension->from   = 'build';
				$extension->source = $sut['source'];
				debug_log( "SUT source: build command '{$sut['source']['command']}'" );
				break;

			case 'url':
				$extension->from   = 'url';
				$extension->source = $sut['source']['url'];
				debug_log( "SUT source: URL {$extension->source}" );
				break;

			case 'wporg':
				$extension->from    = 'wporg';
				$extension->version = $sut['source']['version'] ?? 'stable';
				debug_log( "SUT source: wporg version {$extension->version}" );
				break;

			case 'wccom':
				$extension->from    = 'wccom';
				$extension->version = $sut['source']['version'] ?? 'stable';
				debug_log( "SUT source: wccom version {$extension->version}" );
				break;

			default:
				debug_log( "Unknown SUT source type: {$sut['source']['type']}", 'error' );
		}

		$extension->action   = Extension::ACTIONS['test'];
		$extension->priority = Extension::PRIORITY_HIGH;

		return $extension;
	}

	/**
	 * Collect all extensions from the configuration
	 */
	protected function collect_all_extensions( ResolvedConfiguration $config ): array {
		debug_log( 'Collecting all extensions from configuration' );
		$extensions = [];

		// Add SUT
		$extensions[] = $config->sut_extension;
		debug_log( "Added SUT extension: {$config->sut_extension->slug}" );

		// Collect from all environments
		foreach ( $config->environments as $env_name => $env ) {
			debug_log( "Processing environment: $env_name" );

			if ( isset( $env['plugins'] ) ) {
				debug_log( sprintf( '  Found %d plugins in environment', count( $env['plugins'] ) ) );
				foreach ( $env['plugins'] as $plugin_config ) {
					// Handle both string and array formats
					if ( is_string( $plugin_config ) ) {
						debug_log( "    Processing string plugin: $plugin_config" );
						// Simple string format - defaults to wporg
						$extension                      = new Extension( $plugin_config, 'plugin' );
						$extension->from                = 'wporg';
						$extension->version             = 'stable';
						$extension->action              = Extension::ACTIONS['activate'];
						$extension->added_automatically = 'Added from environment configuration';
						$extensions[]                   = $extension;
					} else {
						debug_log( "    Processing object plugin: {$plugin_config['slug']}" );
						debug_dump( $plugin_config, '    Plugin config' );
						// Object format
						$extension    = $this->create_extension_from_config( $plugin_config, 'plugin' );
						$extensions[] = $extension;
					}
				}
			}

			if ( isset( $env['themes'] ) ) {
				debug_log( sprintf( '  Found %d themes in environment', count( $env['themes'] ) ) );
				foreach ( $env['themes'] as $theme_config ) {
					// Handle both string and array formats
					if ( is_string( $theme_config ) ) {
						debug_log( "    Processing string theme: $theme_config" );
						// Simple string format - defaults to wporg
						$extension                      = new Extension( $theme_config, 'theme' );
						$extension->from                = 'wporg';
						$extension->version             = 'stable';
						$extension->action              = Extension::ACTIONS['activate'];
						$extension->added_automatically = 'Added from environment configuration';
						$extensions[]                   = $extension;
					} else {
						debug_log( "    Processing object theme: {$theme_config['slug']}" );
						debug_dump( $theme_config, '    Theme config' );
						// Object format
						$extension    = $this->create_extension_from_config( $theme_config, 'theme' );
						$extensions[] = $extension;
					}
				}
			}
		}

		// Remove duplicates by slug
		debug_log( sprintf( 'Total extensions before deduplication: %d', count( $extensions ) ) );
		$unique = [];
		foreach ( $extensions as $ext ) {
			$key = $ext->slug . '_' . $ext->type;
			if ( ! isset( $unique[ $key ] ) ) {
				$unique[ $key ] = $ext;
			} else {
				debug_log( "Duplicate extension removed: $key" );
			}
		}

		$result = array_values( $unique );
		debug_log( sprintf( 'Total unique extensions: %d', count( $result ) ) );

		return $result;
	}

	/**
	 * Create extension from configuration array
	 */
	protected function create_extension_from_config( array $config, string $type ): Extension {
		debug_log( "Creating extension from config: {$config['slug']} ($type)" );
		debug_dump( $config, 'Extension config' );

		$extension = new Extension( $config['slug'], $type );

		// Handle the new 'from' property structure
		if ( isset( $config['from'] ) ) {
			debug_log( "Using 'from' property: {$config['from']}" );
			$extension->from = $config['from'];

			switch ( $config['from'] ) {
				case 'wporg':
					$extension->version = $config['version'] ?? 'stable';
					debug_log( "Extension source: wporg, version: {$extension->version}" );
					break;

				case 'wccom':
					$extension->version = $config['version'] ?? 'stable';
					debug_log( "Extension source: wccom, version: {$extension->version}" );
					break;

				case 'local':
					$extension->from      = 'local';
					$extension->directory = $config['path'];
					$extension->source    = $config['path'];
					debug_log( "Extension source: local, path: {$config['path']}" );
					// Check if path exists
					$full_path = realpath( $config['path'] );
					if ( ! $full_path || ! is_dir( $full_path ) ) {
						debug_log( "WARNING: Local path does not exist or is not a directory: {$config['path']}", 'error' );
					}
					break;

				case 'url':
					$extension->source = $config['url'];
					debug_log( "Extension source: url, {$config['url']}" );
					break;

				case 'build':
					$extension->source = [
						'type'    => 'build',
						'command' => $config['command'],
						'output'  => $config['output']
					];
					debug_log( "Extension source: build, command: {$config['command']}" );
					break;

				default:
					debug_log( "Unknown 'from' type: {$config['from']}", 'error' );
			}
		} elseif ( isset( $config['source'] ) ) {
			debug_log( "Using legacy 'source' property" );
			// Handle old source format for backward compatibility
			$source = $config['source'];

			switch ( $source['type'] ) {
				case 'wporg':
					$extension->from    = 'wporg';
					$extension->version = $source['version'] ?? 'stable';
					debug_log( "Extension source: wporg (legacy), version: {$extension->version}" );
					break;

				case 'wccom':
					$extension->from    = 'wccom';
					$extension->version = $source['version'] ?? 'stable';
					debug_log( "Extension source: wccom (legacy), version: {$extension->version}" );
					break;

				case 'local':
					$extension->from      = 'local';
					$extension->directory = $source['path'];
					$extension->source    = $source['path'];
					debug_log( "Extension source: local (legacy), path: {$source['path']}" );
					break;

				case 'url':
					$extension->from   = 'url';
					$extension->source = $source['url'];
					debug_log( "Extension source: url (legacy), {$source['url']}" );
					break;

				default:
					debug_log( "Unknown source type: {$source['type']}", 'error' );
			}
		} else {
			debug_log( "Extension config missing both 'from' and 'source' properties!", 'error' );
		}

		$extension->action              = Extension::ACTIONS['activate'];
		$extension->added_automatically = 'Added from environment configuration';

		debug_log( "Created extension: {$extension->slug} from {$extension->from}" );

		return $extension;
	}

	/**
	 * Download remote test packages
	 */
	protected function download_remote_test_packages( ResolvedConfiguration $config ): void {
		debug_log( 'Checking for remote test packages' );

		$remote_packages = [];

		foreach ( $config->test_packages as $ref => $package ) {
			if ( $package['remote'] ?? false ) {
				$remote_packages[ $ref ] = $package;
				debug_log( "Found remote package: $ref" );
			}
		}

		if ( empty( $remote_packages ) ) {
			debug_log( 'No remote test packages to download' );

			return;
		}

		$this->output->writeln( sprintf( 'Downloading %d remote test packages...', count( $remote_packages ) ) );
		debug_log( 'Remote packages to download: ' . implode( ', ', array_keys( $remote_packages ) ) );

		// Use TestPackageDownloader to fetch remote packages
		$downloaded = $this->package_downloader->download( $remote_packages, $this->cache_dir );

		// Update the test packages with downloaded content
		foreach ( $downloaded as $ref => $manifest ) {
			debug_log( "Downloaded package $ref successfully" );
			debug_dump( $manifest, "Downloaded manifest for $ref" );
			$config->test_packages[ $ref ] = array_merge( $config->test_packages[ $ref ], $manifest );
		}
	}

	/**
	 * Collect requirements from all test packages
	 */
	protected function collect_test_package_requirements( ResolvedConfiguration $config ): void {
		debug_log( 'Collecting requirements from test packages' );

		foreach ( $config->test_packages as $ref => $package ) {
			if ( isset( $package['requires'] ) ) {
				debug_log( "Processing requirements for package: $ref" );
				debug_dump( $package['requires'], "Requirements for $ref" );

				// Collect secrets
				if ( isset( $package['requires']['secrets'] ) ) {
					$before                   = count( $config->required_secrets );
					$config->required_secrets = array_merge(
						$config->required_secrets,
						$package['requires']['secrets']
					);
					$added                    = count( $config->required_secrets ) - $before;
					debug_log( "  Added $added secrets from $ref" );
				}

				// Collect external services
				if ( isset( $package['requires']['external_services'] ) ) {
					$before                    = count( $config->required_services );
					$config->required_services = array_merge(
						$config->required_services,
						$package['requires']['external_services']
					);
					$added                     = count( $config->required_services ) - $before;
					debug_log( "  Added $added external services from $ref" );
				}

				// Collect PHP extensions
				if ( isset( $package['requires']['php_extensions'] ) ) {
					$before                 = count( $config->php_extensions );
					$config->php_extensions = array_merge(
						$config->php_extensions,
						$package['requires']['php_extensions']
					);
					$added                  = count( $config->php_extensions ) - $before;
					debug_log( "  Added $added PHP extensions from $ref" );
				}
			}
		}

		// Remove duplicates
		$before_secrets  = count( $config->required_secrets );
		$before_services = count( $config->required_services );
		$before_php      = count( $config->php_extensions );

		$config->required_secrets  = array_unique( $config->required_secrets );
		$config->required_services = array_unique( $config->required_services );
		$config->php_extensions    = array_unique( $config->php_extensions );

		debug_log( sprintf(
			'Deduplication removed: %d secrets, %d services, %d PHP extensions',
			$before_secrets - count( $config->required_secrets ),
			$before_services - count( $config->required_services ),
			$before_php - count( $config->php_extensions )
		) );

		debug_log( 'Final requirements:' );
		if ( ! empty( $config->required_secrets ) ) {
			debug_log( '  Secrets: ' . implode( ', ', $config->required_secrets ) );
		}
		if ( ! empty( $config->required_services ) ) {
			debug_log( '  Services: ' . implode( ', ', $config->required_services ) );
		}
		if ( ! empty( $config->php_extensions ) ) {
			debug_log( '  PHP Extensions: ' . implode( ', ', $config->php_extensions ) );
		}
	}

	/**
	 * Create temporary EnvInfo for extension resolver
	 */
	protected function create_temp_env_info( ResolvedConfiguration $config ): \QIT_CLI\Environment\Environments\EnvInfo {
		// This is a minimal EnvInfo just for the extension resolver
		$env_info                = new \QIT_CLI\Environment\Environments\E2E\E2EEnvInfo();
		$env_info->env_id        = uniqid();
		$env_info->temporary_env = normalize_path( sys_get_temp_dir() . '/qit-resolve-' . $env_info->env_id );
		$env_info->created_at    = time();
		$env_info->status        = 'resolving';

		return $env_info;
	}

	/**
	 * Resolve configuration with caching
	 */
	public function resolve_with_cache( string $config_file ): ResolvedConfiguration {
		$cache_key = 'resolved_config_' . md5_file( $config_file );
		debug_log( "Checking cache for key: $cache_key" );

		$cached = $this->cache->get( $cache_key );

		if ( $cached && is_array( $cached ) ) {
			debug_log( 'Using cached configuration', 'info' );

			return ResolvedConfiguration::import( $cached );
		}

		debug_log( 'No cache found, resolving fresh' );
		$resolved = $this->resolve( $config_file );

		// Cache for 1 hour
		debug_log( 'Caching resolved configuration for 1 hour' );
		$this->cache->set( $cache_key, $resolved->export(), HOUR_IN_SECONDS );

		return $resolved;
	}
}