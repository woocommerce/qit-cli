<?php

namespace QIT_CLI\Commands;

use QIT_CLI\App;
use QIT_CLI\Config\InputPriorityHandler;
use QIT_CLI\Config\ParserFactory;
use QIT_CLI\Config\PluginDependencies;
use QIT_CLI\Config\QITConfig;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\EnvironmentVersionResolver;
use QIT_CLI\Environment\EnvParser;
use QIT_CLI\Environment\ExtensionSetResolver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\normalize_path;

abstract class QITCommand extends Command {
	protected bool $needs_environment = false;
	private ?EnvInfo $env_info = null;
	protected InputInterface $input;

	private array $pluralizable_keys = [
		'plugin'        => 'plugins',
		'theme'         => 'themes',
		'volume'        => 'volumes',
		'php_extension' => 'php_extensions',
		'env'           => 'envs',
	];

	protected function configure(): void {
		$this->addOption(
			'config',
			null,
			InputOption::VALUE_OPTIONAL,
			'Path to the qit.json configuration file.',
			'qit.json'
		);

		if ( static::$defaultName && str_starts_with( static::$defaultName, 'run:' ) ) {
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
		$this->input = $input;
		$config_file = $input->getOption( 'config' );
		try {
			$config = new QITConfig( $config_file, App::make( ParserFactory::class ) );
		} catch ( \RuntimeException $e ) {
			$output->writeln( "<error>Error loading config: {$e->getMessage()}</error>" );

			return Command::FAILURE;
		}

		if ( $this->needs_environment ) {
			$environment            = $input->getOption( 'environment' ) ?? 'default';
			$config_section         = $config->get_environment( $environment );
			$command_defaults       = $this->get_command_defaults();
			$input_priority_handler = new InputPriorityHandler();
			$merged_options         = $input_priority_handler->get_config_from_input( $input, $config_section, $command_defaults, $this->pluralizable_keys );
			$this->env_info         = $this->build_env_info( $input, $output, $merged_options, $config, $config_section );
		}

		if ( getenv( 'QIT_TESTING_ENV_INFO' ) ) {
			$output->writeln( json_encode( $this->env_info ) );

			return Command::SUCCESS;
		}

		try {
			return $this->doExecute( $input, $output );
		} catch ( \RuntimeException $e ) {
			$output->writeln( "<error>{$e->getMessage()}</error>" );

			return Command::FAILURE;
		}
	}

	protected function get_env_info(): ?EnvInfo {
		return $this->env_info;
	}

	protected function get_command_defaults(): array {
		$defaults = [];
		foreach ( $this->getDefinition()->getOptions() as $option ) {
			$defaults[ $option->getName() ] = $option->getDefault();
		}

		return $defaults;
	}

	protected function build_env_info(
		InputInterface $input,
		OutputInterface $output,
		array $merged_options,
		QITConfig $config,
		array $config_section
	) {
		$environment = $input->getOption( 'environment' ) ?: 'default';
		$woo_version = isset( $merged_options['woo_version'] ) ? $merged_options['woo_version'] : null;

		// Parse CLI and .env file environment variables
		$env_parser                     = App::make( EnvParser::class );
		$env_vars                       = $env_parser->parse(
			$input->getOption( 'env' ) ?: [],
			$input->getOption( 'env_file' ) ?: []
		);
		$env_vars['WP_CLI_CONFIG_PATH'] = '/qit/wp-cli.yml';

		// Merge with config envs (already a key-value array)
		$config_envs = isset( $config_section['envs'] ) && is_array( $config_section['envs'] ) ? $config_section['envs'] : [];
		$env_vars    = array_merge( $config_envs, $env_vars );

		// Initialize env_config with config section, excluding 'envs'
		$env_config = [];
		foreach ( $config_section as $key => $value ) {
			if ( $key === 'envs' ) {
				continue; // Skip envs to avoid unparsed data
			}
			$mapped_key                = $this->pluralizable_keys[ $key ] ?? $key;
			$env_config[ $mapped_key ] = $value;
		}

		// Merge CLI options, preserving config values unless overridden
		foreach ( $merged_options as $key => $value ) {
			if ( $key === 'env' || $key === 'env_file' ) {
				continue; // Skip to avoid re-merging
			}
			$mapped_key = $this->pluralizable_keys[ $key ] ?? $key;
			if ( $mapped_key === 'plugins' && ! empty( $value ) ) {
				$cli_plugins = [];
				$cli_values  = is_array( $value ) ? $value : [ $value ];
				$seen_slugs  = array_map( fn( $p ) => is_object( $p ) ? $p->slug : $p, $env_config['plugins'] ?? [] );
				foreach ( $cli_values as $plugin ) {
					$slug = is_object( $plugin ) && isset( $plugin->slug ) ? $plugin->slug : ( is_string( $plugin ) ? $plugin : null );
					if ( ! is_string( $slug ) ) {
						throw new \RuntimeException( 'CLI plugin must be a string or Extension object, got ' . gettype( $plugin ) );
					}
					if ( ! in_array( $slug, $seen_slugs, true ) ) {
						$cli_plugins[] = is_object( $plugin ) ? $plugin : new \QIT_CLI\Environment\Extension( $slug, 'plugin' );
						$seen_slugs[]  = $slug;
					}
				}
				$env_config['plugins'] = array_merge( $env_config['plugins'] ?? [], $cli_plugins );
			} elseif ( ( isset( $env_config[ $mapped_key ] ) && is_array( $env_config[ $mapped_key ] ) ) || is_array( $value ) ) {
				$cli_values                = is_array( $value ) ? $value : [ $value ];
				$config_values             = isset( $env_config[ $mapped_key ] ) ? (array) $env_config[ $mapped_key ] : [];
				$env_config[ $mapped_key ] = array_unique( array_merge( $config_values, $cli_values ), SORT_REGULAR );
			} elseif ( $value !== null ) {
				$env_config[ $mapped_key ] = $value;
			}
		}

		// Set parsed environment variables
		$env_config['envs'] = $env_vars;

		// Debug: Write to file
		file_put_contents( '/tmp/qit_debug.log', "Config Section: " . print_r( $config_section, true ) . "\n", FILE_APPEND );
		file_put_contents( '/tmp/qit_debug.log', "Merged Options: " . print_r( $merged_options, true ) . "\n", FILE_APPEND );
		file_put_contents( '/tmp/qit_debug.log', "Env Config: " . print_r( $env_config, true ) . "\n", FILE_APPEND );

		// Apply defaults
		$env_config = array_merge( [
			'environment'             => 'e2e',
			'dependencies_mode'       => 'activate',
			'php_version'             => '8.0',
			'wp_version'              => 'stable',
			'woo_version'             => null,
			'plugins'                 => [],
			'themes'                  => [],
			'volumes'                 => [],
			'php_extensions'          => [],
			'object_cache'            => false,
			'envs'                    => [],
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
		if ( ! empty( $woo_version ) ) {
			$woo_plugin = EnvironmentVersionResolver::resolve_woo( $woo_version, $env_config['plugins'] ?: [] );
			$woo_slug   = is_object( $woo_plugin ) ? $woo_plugin->slug : ( is_string( $woo_plugin ) ? $woo_plugin : null );
			if ( ! is_string( $woo_slug ) ) {
				throw new \RuntimeException( 'Woo plugin slug must be a string, got ' . gettype( $woo_slug ) );
			}
			$seen_slugs = array_map( fn( $p ) => is_object( $p ) ? $p->slug : $p, $env_config['plugins'] ?? [] );
			if ( ! in_array( $woo_slug, $seen_slugs, true ) ) {
				$env_config['plugins'][] = is_object( $woo_plugin ) ? $woo_plugin : new \QIT_CLI\Environment\Extension( $woo_slug, 'plugin' );
			}
		}

		// Handle dependencies
		$deps = App::make( PluginDependencies::class )->get_dependencies(
			$env_config['plugins'] ?: [],
			$env_config['themes'] ?: [],
			$env_config['dependencies_mode'] ?: 'activate'
		);

		// Add dependencies, avoiding duplicates
		$seen_slugs = array_map( fn( $p ) => is_object( $p ) ? $p->slug : $p, $env_config['plugins'] ?? [] );
		foreach ( $deps['plugin'] as $dep_plugin ) {
			$dep_slug = $dep_plugin->slug;
			if ( ! in_array( $dep_slug, $seen_slugs, true ) ) {
				$env_config['plugins'][] = $dep_plugin;
				$seen_slugs[]            = $dep_slug;
			}
		}
		App::make( PluginDependencies::class )->maybe_add_theme_dependencies( $deps['theme'], $env_config['themes'] );
		if ( empty( $env_config['php_extensions'] ) ) {
			$env_config['php_extensions'] = [];
		}
		App::make( PluginDependencies::class )->maybe_add_php_extensions( $deps['php_extension'], $env_config['php_extensions'] );

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
				case 'wp_version':
					$value = EnvironmentVersionResolver::resolve_wp( $value );
					break;
				case 'woo_version':
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
		$env_info->wp_version              = $env_config['wp_version'];
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
		$env_info->env                     = $env_config['envs'];

		// Debug: Write final env_info
		file_put_contents( '/tmp/qit_debug.log', "Final Env Info: " . print_r( (array) $env_info, true ) . "\n", FILE_APPEND );

		// Parse extension set
		if ( ! empty( $env_config['extension_set'] ) ) {
			$env_info = App::make( 'QIT_CLI\Environment\ExtensionSetResolver' )->resolve( $env_info, [ 'overrides' => [ 'extension_set' => $env_config['extension_set'] ] ] );
		}

		return $env_info;
	}

	abstract protected function doExecute( InputInterface $input, OutputInterface $output ): int;
}