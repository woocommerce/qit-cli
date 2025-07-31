<?php

namespace QIT_CLI\PreCommand\Configuration;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Config;
use QIT_CLI\Environment\Extension;
use QIT_CLI\PreCommand\Configuration\Parser\QitJsonParser;
use QIT_CLI\PreCommand\Extensions\ExtensionResolver;
use QIT_CLI\PreCommand\Download\TestPackageDownloader;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\normalize_path;
use function QIT_CLI\debug_log;
use function QIT_CLI\debug_dump;

class ConfigurationResolver {
	protected $parser;
	protected $extension_resolver;
	protected $package_downloader;
	protected $cache;
	protected $output;
	protected $cache_dir;

	public function __construct(
		QitJsonParser $parser,
		ExtensionResolver $extension_resolver,
		TestPackageDownloader $package_downloader,
		Cache $cache,
		OutputInterface $output
	) {
		$this->parser             = $parser;
		$this->extension_resolver = $extension_resolver;
		$this->package_downloader = $package_downloader;
		$this->cache              = $cache;
		$this->output             = $output;
		$this->cache_dir          = normalize_path( Config::get_qit_dir() . 'cache' );
	}

	public function resolve( ?string $config_file, ?string $sut_slug = null, ?string $sut_type = null ): ResolvedConfiguration {
		// Debug: trace resolve() method entry
		file_put_contents( '/tmp/resolver_entry_debug.json', json_encode( [
			'config_file' => $config_file,
			'file_exists' => $config_file ? file_exists( $config_file ) : false,
			'timestamp'   => gmdate( 'Y-m-d H:i:s' ),
		], JSON_PRETTY_PRINT ) );

		$this->output->writeln( '<info>Resolving configuration...</info>' );
		debug_log( 'Starting resolution of config file: ' . ( $config_file ?? 'none' ), 'info' );

		// Validate explicit config file
		if ( $config_file !== null && ! file_exists( $config_file ) ) {
			throw new \RuntimeException( "Specified configuration file does not exist: $config_file" );
		}

		// Initialize parsed configuration
		$parsed_config = [
			'sut'           => null,
			'environments'  => [],
			'test_types'    => [],
			'groups'        => [],
			'test_packages' => [],
		];

		// Parse qit.json if provided
		if ( $config_file ) {
			debug_log( 'Step 1: Parsing qit.json...' );
			try {
				$parsed_config = $this->parser->parse( $config_file );
				// Debug: dump parsed config immediately after parsing
				file_put_contents( '/tmp/parser_result_debug.json', json_encode( $parsed_config, JSON_PRETTY_PRINT ) );
				debug_dump( $parsed_config, 'Parsed configuration' );
			} catch ( \Exception $e ) {
				// Debug: capture parsing exception
				file_put_contents( '/tmp/parser_exception_debug.json', json_encode( [
					'error'       => $e->getMessage(),
					'file'        => $e->getFile(),
					'line'        => $e->getLine(),
					'config_file' => $config_file,
				], JSON_PRETTY_PRINT ) );
				throw $e;
			}
		} else {
			debug_log( 'No qit.json provided, relying on CLI inputs and defaults' );
		}

		// Create resolved configuration
		$resolved                          = new ResolvedConfiguration( $parsed_config );
		$resolved->metadata['config_file'] = $config_file;
		$resolved->cache_dir               = $this->cache_dir;

		// Process SUT
		debug_log( 'Step 2: Processing System Under Test...' );
		if ( $sut_slug ) {
			// CLI-provided SUT takes precedence
			$resolved->sut           = [
				'slug'   => $sut_slug,
				'type'   => $sut_type ?? 'plugin',
				'source' => [ 'type' => 'wporg' ],
			];
			$resolved->sut_extension = $this->create_sut_extension( $resolved->sut );
		} elseif ( isset( $parsed_config['sut'] ) ) {
			// Fall back to qit.json SUT
			$resolved->sut           = $parsed_config['sut'];
			$resolved->sut_extension = $this->create_sut_extension( $parsed_config['sut'] );
		}

		// Apply default fallbacks before copying configuration
		debug_log( 'Step 3: Applying default fallbacks...' );

		// Auto-create environments.default if absent
		if ( ! isset( $parsed_config['environments']['default'] ) ) {
			$parsed_config['environments']['default'] = [];
			debug_log( 'Auto-created environments.default (empty)' );
		}

		// Auto-materialize e2e.default test profile if e2e test type is missing
		if ( ! isset( $parsed_config['test_types']['e2e']['default'] ) ) {
			$parsed_config['test_types']['e2e']['default'] = [];
			debug_log( 'Auto-created test_types.e2e.default (empty)' );
		}

		// Copy basic configuration
		debug_log( 'Step 4: Copying basic configuration...' );
		file_put_contents( '/tmp/parsed_config_debug.json', json_encode( $parsed_config, JSON_PRETTY_PRINT ) );
		$resolved->environments = $parsed_config['environments'] ?? [];
		$resolved->test_types   = $parsed_config['test_types'] ?? [];
		$resolved->groups       = $parsed_config['groups'] ?? [];
		debug_log( sprintf( 'Found %d environments, %d test types, %d groups',
			count( $resolved->environments ),
			count( $resolved->test_types ),
			count( $resolved->groups )
		) );

		// Load test packages
		debug_log( 'Step 4: Loading test packages...' );
		$resolved->test_packages         = $parsed_config['test_packages'] ?? [];
		$resolved->test_package_metadata = $parsed_config['test_package_metadata'] ?? [];

		// Skip downloading remote test packages
		debug_log( 'Step 5: Downloading remote test packages (skipped)...' );

		// Collect all extensions
		debug_log( 'Step 6: Collecting all extensions from configuration...' );
		$all_extensions = $this->collect_all_extensions( $resolved );

		// Resolve extensions
		debug_log( 'Step 7: Resolving extensions and dependencies...' );
		$resolved_extensions = $this->extension_resolver->resolve(
			$all_extensions,
			$this->create_temp_env_info( $resolved ),
			$this->cache_dir
		);

		$resolved->resolved_plugins = $resolved_extensions->get_plugins();
		$resolved->resolved_themes  = $resolved_extensions->get_themes();
		$resolved->php_extensions   = $resolved_extensions->get_php_extensions();

		// Skip collecting test package requirements
		debug_log( 'Step 8: Collecting requirements from test packages (skipped)...' );

		// Validate configuration
		debug_log( 'Step 9: Validating configuration...' );
		$errors = $resolved->validate();
		if ( ! empty( $errors ) ) {
			throw new \RuntimeException( "Configuration validation failed:\n" . implode( "\n", array_map( fn( $e ) => "  - $e", $errors ) ) );
		}

		$this->output->writeln( '<info>Configuration resolved successfully!</info>' );

		return $resolved;
	}

	/**
	 * @param array<string, mixed> $sut
	 */
	protected function create_sut_extension( array $sut ): Extension {
		debug_log( "Creating SUT extension for: {$sut['slug']} ({$sut['type']})" );
		debug_dump( $sut, 'SUT configuration' );

		$extension = new Extension( $sut['slug'], $sut['type'] );

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

		$extension->priority = Extension::PRIORITY_HIGH;

		return $extension;
	}

	/**
	 * @return Extension[]
	 */
	protected function collect_all_extensions( ResolvedConfiguration $config ): array {
		debug_log( 'Collecting all extensions from configuration' );
		$extensions = [];

		if ( $config->sut && $config->sut_extension ) {
			$extensions[] = $config->sut_extension;
			debug_log( "Added SUT extension: {$config->sut_extension->slug}" );
		} else {
			debug_log( 'No SUT extension to add' );
		}

		foreach ( $config->environments as $env_name => $env ) {
			debug_log( "Processing environment: $env_name" );

			if ( isset( $env['plugins'] ) ) {
				debug_log( sprintf( '  Found %d plugins in environment', count( $env['plugins'] ) ) );
				foreach ( $env['plugins'] as $plugin_config ) {
					if ( is_string( $plugin_config ) ) {
						debug_log( "    Processing string plugin: $plugin_config" );
						$extension                      = new Extension( $plugin_config, 'plugin' );
						$extension->from                = 'wporg';
						$extension->version             = 'stable';
						$extension->added_automatically = 'Added from environment configuration';
						$extensions[]                   = $extension;
					} else {
						debug_log( "    Processing object plugin: {$plugin_config['slug']}" );
						debug_dump( $plugin_config, '    Plugin config' );
						$extension    = $this->create_extension_from_config( $plugin_config, 'plugin' );
						$extensions[] = $extension;
					}
				}
			}

			if ( isset( $env['themes'] ) ) {
				debug_log( sprintf( '  Found %d themes in environment', count( $env['themes'] ) ) );
				foreach ( $env['themes'] as $theme_config ) {
					if ( is_string( $theme_config ) ) {
						debug_log( "    Processing string theme: $theme_config" );
						$extension                      = new Extension( $theme_config, 'theme' );
						$extension->from                = 'wporg';
						$extension->version             = 'stable';
						$extension->added_automatically = 'Added from environment configuration';
						$extensions[]                   = $extension;
					} else {
						debug_log( "    Processing object theme: {$theme_config['slug']}" );
						debug_dump( $theme_config, '    Theme config' );
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
	 * @param array<string, mixed> $config
	 */
	protected function create_extension_from_config( array $config, string $type ): Extension {
		debug_log( "Creating extension from config: {$config['slug']} ($type)" );
		debug_dump( $config, 'Extension config' );

		$extension = new Extension( $config['slug'], $type );

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
						'output'  => $config['output'],
					];
					debug_log( "Extension source: build, command: {$config['command']}" );
					break;

				default:
					debug_log( "Unknown 'from' type: {$config['from']}", 'error' );
			}
		} elseif ( isset( $config['source'] ) ) {
			debug_log( "Using legacy 'source' property" );
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

		$extension->added_automatically = 'Added from environment configuration';

		debug_log( "Created extension: {$extension->slug} from {$extension->from}" );

		return $extension;
	}

	// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Parameter required for interface consistency
	protected function create_temp_env_info( ResolvedConfiguration $config ): \QIT_CLI\Environment\Environments\EnvInfo {
		$env_info                = new \QIT_CLI\Environment\Environments\E2E\E2EEnvInfo();
		$env_info->env_id        = uniqid();
		$env_info->temporary_env = normalize_path( sys_get_temp_dir() . '/qit-resolve-' . $env_info->env_id );
		$env_info->created_at    = time();
		$env_info->status        = 'resolving';

		return $env_info;
	}

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

		debug_log( 'Caching resolved configuration for 1 hour' );
		$this->cache->set( $cache_key, $resolved->export(), HOUR_IN_SECONDS );

		return $resolved;
	}
}
