<?php

namespace QIT_CLI\Commands;

use Dotenv\Dotenv;
use QIT_CLI\App;
use QIT_CLI\Config\ConfigFileLoader;
use QIT_CLI\Config\InputPriorityHandler;
use QIT_CLI\Config\ParserFactory;
use QIT_CLI\Config\PluginDependencies;
use QIT_CLI\Config\QITConfig;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\Environment\EnvironmentVersionResolver;
use QIT_CLI\Environment\ExtensionSetResolver;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\is_option_explicitly_provided;
use function QIT_CLI\normalize_path;

abstract class QITCommand extends Command {
	/** @var string The root section in qit.json (e.g., 'tests', 'environments') */
	protected string $config_root_section = '';

	/** @var string The test type for test commands (e.g., 'e2e') */
	protected string $test_type = '';

	protected function configure(): void {
		$this->addOption(
			'config',
			null,
			InputOption::VALUE_OPTIONAL,
			'Path to the qit.json configuration file.',
			'qit.json'
		);

		if ( static::$defaultName && str_starts_with( static::$defaultName, 'run:' ) ) {
			$this->config_root_section = 'tests';
			$this->test_type           = substr( static::$defaultName, 4 );
			self::add_profile_option( $this );
		}
	}

	public static function add_profile_option( Command $command ): void {
		$command->addOption(
			'profile',
			null,
			InputOption::VALUE_OPTIONAL,
			'The profile to use for the test. If not set, will use the default profile.',
			'default'
		);
	}

	public function execute( InputInterface $input, OutputInterface $output ): int {
		$config_file = $input->getOption( 'config' );
		try {
			$config = new QITConfig( $config_file, App::make( ConfigFileLoader::class ), App::make( ParserFactory::class ) );
		} catch ( \RuntimeException $e ) {
			$output->writeln( "<error>Error loading config: {$e->getMessage()}</error>" );

			return Command::FAILURE;
		}

		try {
			$config_section = $this->get_config_section( $config, $input );
		} catch ( \RuntimeException $e ) {
			$output->writeln( "<error>Error accessing config section: {$e->getMessage()}</error>" );

			return Command::FAILURE;
		}

		$command_defaults       = $this->get_command_defaults();
		$input_priority_handler = new InputPriorityHandler();
		$merged_options         = $input_priority_handler->get_config_from_input( $input, $config_section, $command_defaults );

		$env_info = null;
		if ( $this->config_root_section === 'environments' ) {
			try {
				$env_info = $this->build_env_info( $input, $output, $merged_options, $config, $config_section );
			} catch ( \Exception $e ) {
				$output->writeln( "<error>Error creating EnvInfo: {$e->getMessage()}</error>" );

				return Command::FAILURE;
			}
		}

		if ( getenv( 'QIT_TESTING_ENV_INFO' ) ) {
			$output->writeln( json_encode( $env_info ) );

			return Command::SUCCESS;
		}

		try {
			return $this->doExecute( $input, $output, $env_info );
		} catch ( \RuntimeException $e ) {
			$output->writeln( "<error>{$e->getMessage()}</error>" );

			return Command::FAILURE;
		}
	}

	/**
	 * Build EnvInfo for environment-related commands.
	 *
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @param array<string, mixed> $merged_options
	 * @param QITConfig $config
	 * @param array<string, mixed> $config_section
	 *
	 * @return E2EEnvInfo
	 * @throws \RuntimeException
	 */
	protected function build_env_info(
		InputInterface $input,
		OutputInterface $output,
		array $merged_options,
		QITConfig $config,
		array $config_section
	) {
		$environment = $input->getOption( 'environment' ) ? $input->getOption( 'environment' ) : 'default';
		$woo         = isset( $merged_options['woo'] ) ? $merged_options['woo'] : null;

		// Parse environment variables from CLI
		$cli_env = $this->parse_env_vars(
			$input->getOption( 'env' ) ? $input->getOption( 'env' ) : [],
			$input->getOption( 'env_file' ) ? $input->getOption( 'env_file' ) : []
		);

		// Parse environment variables from config
		$config_env        = isset( $config_section['env'] ) ? $config_section['env'] : [];
		$parsed_config_env = [];
		if ( is_array( $config_env ) ) {
			foreach ( $config_env as $key => $value ) {
				if ( is_string( $key ) && is_string( $value ) ) {
					$parsed_config_env[ $key ] = $value;
				} elseif ( is_int( $key ) && is_string( $value ) ) {
					$parts = explode( '=', $value, 2 );
					if ( count( $parts ) === 2 ) {
						$parsed_config_env[ trim( $parts[0] ) ] = trim( $parts[1] );
					} else {
						throw new \RuntimeException( "Invalid environment variable format in config: $value" );
					}
				} else {
					throw new \RuntimeException( "Invalid environment variable format in config" );
				}
			}
		}

		// Merge config env with CLI env
		$env_vars = array_merge( $parsed_config_env, $cli_env );

		// Map input keys to EnvInfo properties
		$key_mappings = [
			'plugin'              => 'plugins',
			'theme'               => 'themes',
			'volume'              => 'volumes',
			'php_extension'       => 'php_extensions',
			'wordpress_version'   => 'wp',
			'woocommerce_version' => 'woo_version',
			'woo'                 => 'woo_version',
		];

		// Initialize env_config with config section, excluding 'env'
		$env_config = [];
		foreach ( $config_section as $key => $value ) {
			if ( $key === 'env' ) {
				continue; // Skip env to avoid unparsed data
			}
			$mapped_key                = isset( $key_mappings[ $key ] ) ? $key_mappings[ $key ] : $key;
			$env_config[ $mapped_key ] = $value;
		}

		// Set parsed environment variables
		$env_config['env'] = $env_vars;

		// Convert config plugins to Extensions, deduplicating by slug
		$config_plugins = [];
		$seen_slugs     = [];
		foreach ( isset( $env_config['plugins'] ) ? $env_config['plugins'] : [] as $plugin ) {
			if ( is_object( $plugin ) && isset( $plugin->slug ) ) {
				$slug = $plugin->slug;
			} elseif ( is_string( $plugin ) ) {
				$slug = $plugin;
			} else {
				throw new \RuntimeException( 'Plugin must be a string or Extension object, got ' . gettype( $plugin ) );
			}
			if ( ! in_array( $slug, $seen_slugs, true ) ) {
				$config_plugins[] = is_object( $plugin ) ? $plugin : new \QIT_CLI\Environment\Extension( $slug, 'plugin' );
				$seen_slugs[]     = $slug;
			}
		}
		$env_config['plugins'] = $config_plugins;

		// Debug: Write to file
		file_put_contents( '/tmp/qit_debug.log', "Config Section: " . print_r( $config_section, true ) . "\n", FILE_APPEND );
		file_put_contents( '/tmp/qit_debug.log', "Merged Options: " . print_r( $merged_options, true ) . "\n", FILE_APPEND );
		file_put_contents( '/tmp/qit_debug.log', "Env Config Before CLI Merge: " . print_r( $env_config, true ) . "\n", FILE_APPEND );

		// Merge CLI options, preserving config values unless overridden, skip 'env' and 'env_file'
		foreach ( $merged_options as $key => $value ) {
			if ( $key === 'env' || $key === 'env_file' ) {
				continue; // Skip to avoid re-merging unparsed env data
			}
			$mapped_key = isset( $key_mappings[ $key ] ) ? $key_mappings[ $key ] : $key;
			if ( $mapped_key === 'plugins' && ! empty( $value ) ) {
				$cli_plugins = [];
				$cli_values  = is_array( $value ) ? $value : [ $value ];
				foreach ( $cli_values as $plugin ) {
					if ( is_object( $plugin ) && isset( $plugin->slug ) ) {
						$slug = $plugin->slug;
					} elseif ( is_string( $plugin ) ) {
						$slug = $plugin;
					} else {
						throw new \RuntimeException( 'CLI plugin must be a string or Extension object, got ' . gettype( $plugin ) );
					}
					if ( ! in_array( $slug, $seen_slugs, true ) ) {
						$cli_plugins[] = is_object( $plugin ) ? $plugin : new \QIT_CLI\Environment\Extension( $slug, 'plugin' );
						$seen_slugs[]  = $slug;
					}
				}
				$env_config['plugins'] = array_merge( $env_config['plugins'], $cli_plugins );
			} elseif ( ( isset( $env_config[ $mapped_key ] ) && is_array( $env_config[ $mapped_key ] ) ) || is_array( $value ) ) {
				$cli_values                = is_array( $value ) ? $value : [ $value ];
				$config_values             = isset( $env_config[ $mapped_key ] ) ? (array) $env_config[ $mapped_key ] : [];
				$env_config[ $mapped_key ] = array_unique( array_merge( $config_values, $cli_values ), SORT_REGULAR );
			} elseif ( $value !== null ) {
				$env_config[ $mapped_key ] = $value;
			}
		}

		// Debug: Write after CLI merge
		file_put_contents( '/tmp/qit_debug.log', "Env Config After CLI Merge: " . print_r( $env_config, true ) . "\n", FILE_APPEND );

		// Apply defaults
		$env_config = array_merge( [
			'environment'             => 'e2e',
			'dependencies_mode'       => 'activate',
			'php_version'             => '8.0',
			'wp'                      => 'stable',
			'woo_version'             => null,
			'plugins'                 => [],
			'themes'                  => [],
			'volumes'                 => [],
			'php_extensions'          => [],
			'object_cache'            => false,
			'env'                     => [],
			'extension_set'           => null,
			'tunnel'                  => false,
			'tunnel_type'             => 'no_tunnel',
			'runner_args'             => [],
			'domain'                  => 'localhost',
			'skip_activating_plugins' => false,
			'skip_activating_themes'  => false,
			'tests'                   => [],
			'playwright_config'       => [],
			'pw_test_tag'             => '',
			'sut_slug'                => null,
			'sut_type'                => null,
			'sut_entrypoint'          => null,
			'sut_path'                => null,
			'sut_id'                  => null,
			'nginx_port'              => null,
			'notify'                  => null,
			'is_development_build'    => null
		], $env_config );

		// Set tunnel and tunnel_type
		$tunnel                    = $merged_options['tunnel'] ?? 'no_tunnel';
		$env_config['tunnel']      = $tunnel !== 'no_tunnel';
		$env_config['tunnel_type'] = $tunnel;

		// Handle WooCommerce version
		if ( ! empty( $woo ) ) {
			$woo_plugin = EnvironmentVersionResolver::resolve_woo( $woo, $env_config['plugins'] ?: [] );
			$woo_slug   = is_object( $woo_plugin ) ? $woo_plugin->slug : ( is_string( $woo_plugin ) ? $woo_plugin : null );
			if ( ! is_string( $woo_slug ) ) {
				throw new \RuntimeException( 'Woo plugin slug must be a string, got ' . gettype( $woo_slug ) );
			}
			if ( ! in_array( $woo_slug, $seen_slugs, true ) ) {
				$env_config['plugins'][] = is_object( $woo_plugin ) ? $woo_plugin : new \QIT_CLI\Environment\Extension( $woo_slug, 'plugin' );
				$seen_slugs[]            = $woo_slug;
			}
		}

		// Handle dependencies
		$deps = App::make( 'QIT_CLI\Config\PluginDependencies' )->get_dependencies(
			$env_config['plugins'] ?: [],
			$env_config['themes'] ?: [],
			$env_config['dependencies_mode'] ?: 'activate'
		);

		// Add dependencies, avoiding duplicates
		foreach ( $deps['plugin'] as $dep_plugin ) {
			$dep_slug = $dep_plugin->slug;
			if ( ! in_array( $dep_slug, $seen_slugs, true ) ) {
				$env_config['plugins'][] = $dep_plugin;
				$seen_slugs[]            = $dep_slug;
			}
		}
		App::make( 'QIT_CLI\Config\PluginDependencies' )->maybe_add_theme_dependencies( $deps['theme'], $env_config['themes'] );
		if ( empty( $env_config['php_extensions'] ) ) {
			$env_config['php_extensions'] = [];
		}
		App::make( 'QIT_CLI\Config\PluginDependencies' )->maybe_add_php_extensions( $deps['php_extension'], $env_config['php_extensions'] );

		// Parse volumes
		$env_config['volumes'] = App::make( 'QIT_CLI\Environment\EnvVolumeParser' )->parse_volumes( $env_config['volumes'] ?: [] );

		// Normalize and validate
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
				case 'woo_version':
					// $value = EnvironmentVersionResolver::resolve_woo($value, $env_config['plugins'] ?? []);
					break;
			}
		}

		// Mock for now
		$env_config['is_development_build'] = false;
		$env_config['notify']               = 'no';

		// Construct E2EEnvInfo
		$env_info                          = new E2EEnvInfo();
		$env_info->environment             = $env_config['environment'];
		$env_info->dependencies_mode       = $env_config['dependencies_mode'];
		$env_info->env_id                  = uniqid();
		$env_info->temporary_env           = normalize_path( Environment::get_temp_envs_dir() . $env_info->environment . '-' . $env_info->env_id );
		$env_info->created_at              = time();
		$env_info->status                  = 'pending';
		$env_info->volumes                 = $env_config['volumes'];
		$env_info->docker_images           = isset( $env_config['docker_images'] ) ? $env_config['docker_images'] : [];
		$env_info->docker_network          = isset( $env_config['docker_network'] ) ? $env_config['docker_network'] : '';
		$env_info->php_extensions          = $env_config['php_extensions'];
		$env_info->plugins                 = $env_config['plugins'];
		$env_info->themes                  = $env_config['themes'];
		$env_info->tunnel                  = $env_config['tunnel'];
		$env_info->tunnel_type             = $env_config['tunnel_type'];
		$env_info->runner_args             = $env_config['runner_args'];
		$env_info->wp                      = $env_config['wp'];
		$env_info->object_cache            = $env_config['object_cache'];
		$env_info->php_version             = $env_config['php_version'];
		$env_info->domain                  = ( getenv( 'QIT_EXPOSE_ENVIRONMENT_TO' ) === 'DOCKER' ) ? "qitenvnginx{$env_info->env_id}" : ( getenv( 'QIT_DOMAIN' ) ? getenv( 'QIT_DOMAIN' ) : 'localhost' );
		$env_info->skip_activating_plugins = $env_config['skip_activating_plugins'];
		$env_info->skip_activating_themes  = $env_config['skip_activating_themes'];
		$env_info->tests                   = $env_config['tests'];
		$env_info->playwright_config       = isset( $env_config['playwright_config'] ) ? $env_config['playwright_config'] : [];
		$env_info->pw_test_tag             = $env_config['pw_test_tag'];
		$env_info->woo_version             = $env_config['woo_version'] ?? '';
		$env_info->is_development_build    = $env_config['is_development_build'];
		$env_info->notify                  = $env_config['notify'];
		$env_info->env                     = $env_config['env'];

		// Debug: Write final env_info
		file_put_contents( '/tmp/qit_debug.log', "Final Env Info: " . print_r( (array) $env_info, true ) . "\n", FILE_APPEND );

		// Parse extension set
		if ( ! empty( $env_config['extension_set'] ) ) {
			$env_info = App::make( 'QIT_CLI\Environment\ExtensionSetResolver' )->resolve( $env_info, [ 'overrides' => [ 'extension_set' => $env_config['extension_set'] ] ] );
		}

		return $env_info;
	}

	protected function parse_env_vars( array $env_vars, array $env_files ): array {
		$parsed_vars = [];

		// Parse .env files
		foreach ( $env_files as $env_file ) {
			if ( ! file_exists( $env_file ) ) {
				throw new \RuntimeException( sprintf( 'Environment file "%s" does not exist.', $env_file ) );
			}
			$parsed_vars = array_merge( $parsed_vars, Dotenv::parse( file_get_contents( $env_file ) ) );
		}

		// Parse CLI --env variables
		foreach ( $env_vars as $env_var ) {
			if ( ! is_string( $env_var ) ) {
				throw new \RuntimeException( 'Environment variable must be a string, got ' . gettype( $env_var ) );
			}
			$parts = explode( '=', $env_var, 2 );
			if ( count( $parts ) !== 2 ) {
				throw new \RuntimeException( 'Invalid environment variable format. Should be in the format "--env FOO=bar".' );
			}
			$key   = trim( $parts[0] );
			$value = trim( $parts[1] );
			if ( ! preg_match( '/^[A-Za-z0-9_]+$/', $key ) ) {
				throw new \RuntimeException( 'Invalid environment variable name. Must contain only letters, numbers, and underscores.' );
			}
			$parsed_vars[ $key ] = $value;
		}

		// Set default WP_CLI_CONFIG_PATH
		$parsed_vars['WP_CLI_CONFIG_PATH'] = '/qit/wp-cli.yml';

		return $parsed_vars;
	}

	/**
	 * Get the relevant section of the config based on the command’s needs.
	 */
	protected function get_config_section( QITConfig $config, InputInterface $input ): array {
		if ( empty( $this->config_root_section ) ) {
			return [];
		}

		if ( $this->config_root_section === 'tests' ) {
			if ( empty( $this->test_type ) ) {
				throw new \RuntimeException( "Test type must be set for commands using 'tests' config." );
			}
			$profile = $input->getOption( 'profile' ) ?? 'default';

			return $config->get_test_config( $this->test_type, $profile );
		} elseif ( $this->config_root_section === 'environments' ) {
			$environment = $input->getOption( 'environment' ) ?? 'default';

			return $config->get_environment( $environment );
		}

		throw new \RuntimeException( "Unknown config root section: {$this->config_root_section}" );
	}

	/**
	 * Extract default values from the command’s option definitions.
	 */
	protected function get_command_defaults(): array {
		$defaults = [];
		foreach ( $this->getDefinition()->getOptions() as $option ) {
			$defaults[ $option->getName() ] = $option->getDefault();
		}

		return $defaults;
	}

	/**
	 * Abstract method for child commands to implement their logic.
	 *
	 * @param InputInterface $input Original input for arguments or rare cases.
	 * @param OutputInterface $output For writing output.
	 * @param E2EEnvInfo|null $env_info Environment information, if applicable.
	 *
	 * @return int Command exit code.
	 */
	abstract protected function doExecute( InputInterface $input, OutputInterface $output, ?E2EEnvInfo $env_info ): int;
}