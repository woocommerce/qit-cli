<?php

namespace QIT_CLI\Commands\Environment;

use Dotenv\Dotenv;
use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\ExtensionSetResolver;
use QIT_CLI\Commands\DynamicCommand;
use QIT_CLI\Commands\DynamicCommandCreator;
use QIT_CLI\Environment\Environments\E2E\E2EEnvironment;
use QIT_CLI\Environment\EnvironmentVersionResolver;
use QIT_CLI\PluginDependencies;
use QIT_CLI\QITConfig;
use QIT_CLI\Tunnel\TunnelRunner;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\is_option_explicitly_provided;
use function QIT_CLI\is_windows;

class UpEnvironmentCommand extends DynamicCommand {
	protected E2EEnvironment $e2e_environment;
	protected Cache $cache;
	protected OutputInterface $output;
	protected TunnelRunner $tunnel_runner;

	protected static $defaultName = 'env:up'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	public function __construct( E2EEnvironment $e2e_environment, Cache $cache, OutputInterface $output, TunnelRunner $tunnel_runner ) {
		$this->e2e_environment = $e2e_environment;
		$this->cache           = $cache;
		$this->output          = $output;
		$this->tunnel_runner   = $tunnel_runner;
		parent::__construct( static::$defaultName ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	protected function configure(): void {
		parent::configure();

		$schemas = $this->cache->get_manager_sync_data( 'schemas' );

		if ( ! is_array( $schemas['e2e']['properties'] ) ) {
			throw new \RuntimeException( 'E2E schema not set or incomplete.' );
		}

		DynamicCommandCreator::add_schema_to_command( $this, $schemas['e2e'], [], [
			'php_version',
		] );

		$this
			->setDescription( 'Creates a temporary local test environment that is completely ephemeral — no data is persisted. Every time you stop and restart the environment, it\'s like starting fresh.' )
			->addOption( 'wp', null, InputOption::VALUE_OPTIONAL, 'The WordPress version. Accepts "stable", "nightly", "rc", or a version number.', 'stable' )
			->addOption( 'woo', null, InputOption::VALUE_OPTIONAL, 'The WooCommerce Version. Accepts "stable", "nightly", "rc", or a GitHub Tag (eg: 8.6.1).' )
			->addOption( 'plugin', 'p', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '(Optional) Plugin to activate in the environment. Accepts Woo.com slugs/product IDs, WordPress.org slugs or GitHub URLs.', [] )
			->addOption( 'theme', 't', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '(Optional) Theme install, if multiple provided activates the last. Accepts Woo.com slugs/product IDs, WordPress.org slugs or GitHub URLs.', [] )
			->addOption( 'volume', 'l', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '(Optional) Additional volume mappings, eg: /home/mycomputer/my-plugin:/var/www/html/wp-content/plugins/my-plugin.', [] )
			->addOption( 'php_extension', 'x', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'PHP extensions to install in the environment.', [] )
			->addOption( 'require', 'r', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Load PHP file before running the command (may be used more than once).' )
			->addOption( 'object_cache', 'o', InputOption::VALUE_NONE, '(Optional) Whether to enable Object Cache (Redis) in the environment.' )
			->addOption( 'skip_activating_plugins', 's', InputOption::VALUE_NONE, 'Skip activating plugins in the environment.' )
			->addOption( 'skip_activating_themes', 'st', InputOption::VALUE_NONE, 'Skip activating themes in the environment.' )
			->addOption( 'json', 'j', InputOption::VALUE_NEGATABLE, 'Whether to return raw JSON format.', false )
			->addOption( 'tunnel', null, InputOption::VALUE_OPTIONAL, 'Enable tunneling. Optionally specify the tunnel method to use. Valid options: ' . implode( ', ', array_keys( TunnelRunner::$tunnel_map ) ), 'no_tunnel' )
			->addOption( 'env', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Environment variables to pass to the tests.', [] )
			->addOption( 'env_file', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Environment variables to pass to the tests from a file.', [] )
			->addOption( 'dependencies_mode', null, InputOption::VALUE_OPTIONAL, 'How to handle dependencies for recognized WooCommerce plugins. Possible values: ' . implode( ', ', PluginDependencies::DEPENDENCY_MODES['env_only'] ), PluginDependencies::DEPENDENCY_MODES['env_only']['activate'] )
			->addOption( 'environment', null, InputOption::VALUE_OPTIONAL, 'The environment to use from qit.json configuration.', 'default' )
			->setAliases( [ 'env:start' ] );

		DynamicCommandCreator::add_schema_to_command( $this, $schemas['activation'], [], [
			'extension_set',
		] );
	}

	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		if ( is_windows() ) {
			$output->writeln( '<comment>To use QIT Environments on Windows, please use WSL. Check our guide here: https://qit.woo.com/docs/environment/getting-started#getting-started---windows</comment>' );

			return Command::FAILURE;
		}

		$environment             = $input->getOption( 'environment' );
		$woo                     = $input->getOption( 'woo' );
		$skip_activating_plugins = $input->getOption( 'skip_activating_plugins' );
		$skip_activating_themes  = $input->getOption( 'skip_activating_themes' );
		$input->setOption( 'woo', null );
		$input->setOption( 'skip_activating_plugins', null );
		$input->setOption( 'skip_activating_themes', null );
		$this->parse_env_vars( $input->getOption( 'env' ), $input->getOption( 'env_file' ) );

		$tunnel = TunnelRunner::get_tunnel_value( $input );

		try {
			$options_to_env_info = $this->parse_options( $input );
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

			return Command::FAILURE;
		}

		// Load QITConfig from qit.json in working directory
		$config_file = getcwd() . '/qit.json';
		$qit_config  = new QITConfig( $config_file, new Application() );

		// Get environment settings
		$env_config         = [];
		$environment_exists = false;
		try {
			$env_config         = $qit_config->get_environment( $environment );
			$environment_exists = true;
		} catch ( \RuntimeException $e ) {
			if ( is_option_explicitly_provided( $input, 'environment' ) || $environment === 'default' ) {
				$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

				return Command::FAILURE;
			}
			// If environment doesn't exist and wasn't explicitly requested, use CLI defaults
		}

		// Map QITConfig environment settings to EnvInfo structure
		if ( $environment_exists ) {
			$env_config = array_merge( $options_to_env_info['defaults'], [
				'php_version'    => $env_config['php_version'] ?? $input->getOption( 'php_version' ),
				'wp'             => $env_config['wordpress_version'] ?? $input->getOption( 'wp' ),
				'woo'            => $env_config['woocommerce_version'] ?? null,
				'plugins'        => $env_config['plugins'] ?? [],
				'themes'         => $env_config['themes'] ?? [],
				'volumes'        => $env_config['volumes'] ?? [],
				'php_extensions' => $env_config['php_extensions'] ?? [],
				'object_cache'   => $env_config['object_cache'] ?? false,
				'env'            => $env_config['env_vars'] ?? [],
			] );
		} else {
			$env_config = $options_to_env_info['defaults'];
		}

		// Apply command-line overrides
		foreach ( $options_to_env_info['overrides'] as $key => $value ) {
			if ( ! is_null( $value ) ) {
				if ( is_array( $value ) && array_key_exists( $key, $env_config ) && is_array( $env_config[ $key ] ) ) {
					$env_config[ $key ] = array_merge( $env_config[ $key ], $value );
				} else {
					$env_config[ $key ] = $value;
				}
			}
		}

		// Handle WooCommerce version
		if ( ! empty( $woo ) ) {
			$env_config['plugins'][] = EnvironmentVersionResolver::resolve_woo( $woo, $input->getOption( 'plugin' ) );
			foreach ( $env_config['plugins'] as $k => $p ) {
				if ( is_string( $p ) && strpos( $p, 'woocommerce:' ) === 0 ) {
					foreach ( $env_config['plugins'] as $k2 => $p2 ) {
						if ( is_array( $p2 ) && ! empty( $p2['slug'] ) && $p2['slug'] === 'woocommerce' ) {
							unset( $env_config['plugins'][ $k ] );
						}
					}
				}
			}
		}

		// Process require files
		foreach ( $env_config['require'] ?? [] as $file ) {
			if ( file_exists( $file ) ) {
				if ( $output->isVerbose() ) {
					$output->writeln( sprintf( 'Loading file %s', $file ) );
				}
				$prefix = null;
				foreach ( explode( '\\', static::class ) as $namespace ) {
					if ( strpos( $namespace, 'HumbugBox' ) !== false ) {
						$prefix = $namespace;
						break;
					}
				}
				if ( ! is_null( $prefix ) ) {
					$tmp_file = sys_get_temp_dir() . '/' . pathinfo( $file, PATHINFO_FILENAME ) . uniqid( 'prefixed' ) . '.php';
					if ( file_put_contents( $tmp_file, str_replace( 'use QIT_CLI\\', "use $prefix\\QIT_CLI\\", file_get_contents( $file ) ) ) === false ) {
						throw new \RuntimeException( 'Failed to write to the temporary file' );
					}
					if ( $output->isVeryVerbose() ) {
						$output->writeln( sprintf( 'Loading file %s', $tmp_file ) );
					}
					require_once $tmp_file;
				} else {
					require_once $file;
				}
			} else {
				$output->writeln( sprintf( '<error>File %s does not exist.</error>', $file ) );
				throw new \RuntimeException( sprintf( 'File %s does not exist.', $file ) );
			}
		}
		unset( $env_config['require'] );

		// Validate plugins and themes
		if ( isset( $env_config['plugins'] ) ) {
			foreach ( $env_config['plugins'] as $plugin ) {
				if ( ! is_string( $plugin ) ) {
					throw new \RuntimeException( 'Plugin must be a string: ' . json_encode( $plugin ) );
				}
			}
		}
		if ( isset( $env_config['themes'] ) ) {
			foreach ( $env_config['themes'] as $theme ) {
				if ( ! is_string( $theme ) ) {
					throw new \RuntimeException( 'Theme must be a string: ' . json_encode( $theme ) );
				}
			}
		}

		// Handle dependencies
		$deps = App::make( PluginDependencies::class )->get_dependencies(
			$env_config['plugins'] ?? [],
			$env_config['themes'] ?? [],
			$env_config['dependencies_mode'] ?? 'activate'
		);
		App::make( PluginDependencies::class )->maybe_add_plugin_dependencies( $deps['plugin'], $env_config['plugins'] );
		App::make( PluginDependencies::class )->maybe_add_theme_dependencies( $deps['theme'], $env_config['themes'] );
		if ( empty( $env_config['php_extensions'] ) ) {
			$env_config['php_extensions'] = [];
		}
		App::make( PluginDependencies::class )->maybe_add_php_extensions( $deps['php_extension'], $env_config['php_extensions'] );

		// Parse volumes
		$env_config['volumes'] = App::make( \QIT_CLI\Environment\EnvVolumeParser::class )->parse_volumes( $env_config['volumes'] ?? [] );

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
				case 'woo':
					$value = EnvironmentVersionResolver::resolve_woo( $value, $env_config['plugins'] ?? [] );
					break;
			}
		}

		$env_info = \QIT_CLI\Environment\Environments\EnvInfo::from_array( $env_config );

		// Parse extension set
		if ( ! empty( $options_to_env_info['overrides']['extension_set'] ) ) {
			$env_info = App::make( ExtensionSetResolver::class )->resolve( $env_info, $options_to_env_info );
		}

		if ( $tunnel !== 'no_tunnel' ) {
			try {
				$this->tunnel_runner->check_tunnel_support( $tunnel );
				$env_info->tunnel = true;
			} catch ( \Exception $e ) {
				$output->writeln( '<error>' . $e->getMessage() . '</error>' );

				return Command::FAILURE;
			}
		} else {
			$env_info->tunnel = false;
		}

		if ( $output->isVeryVerbose() ) {
			$this->output->writeln( 'Environment info: ' . json_encode( $env_info, JSON_PRETTY_PRINT ) );
		}

		if ( $skip_activating_plugins ) {
			$this->e2e_environment->set_skip_activating_plugins( true );
		}

		if ( $skip_activating_themes ) {
			$this->e2e_environment->set_skip_activating_themes( true );
		}

		$this->e2e_environment->init( $env_info );

		// Helper utility to test the environment
		if ( getenv( 'QIT_SELF_TEST' ) === 'env_info' ) {
			$output->write( json_encode( $env_info ) );

			return 137;
		}

		// "up_and_test" is for custom tests, "up" is for spinning up the environment
		$this->e2e_environment->up( getenv( 'QIT_UP_AND_TEST' ) ? 'up_and_test' : 'up' );

		if ( $input->getOption( 'json' ) ) {
			$output->write( json_encode( $env_info ) );
		} else {
			$output->writeln( $env_info->site_url );
		}

		return Command::SUCCESS;
	}

	protected function parse_options( InputInterface $input, bool $filter_to_send = true ): array {
		$options = parent::parse_options( $input, false );

		$options_to_env_info = [
			'defaults'  => [],
			'overrides' => [],
		];

		$shortcuts = [];
		foreach ( $this->getDefinition()->getOptions() as $o ) {
			$shortcuts[ $o->getShortcut() ] = $o->getName();
		}

		$user_input = ! empty( App::getVar( 'QIT_ENV_UP_OPTIONS' ) ) ? array_keys( App::getVar( 'QIT_ENV_UP_OPTIONS' ) ) : $GLOBALS['argv'];

		foreach ( $options as $key => $value ) {
			$found_override = false;
			foreach ( $user_input as $arg ) {
				$normalized_arg = ltrim( $arg, '-' );
				$normalized_arg = preg_match( '/^([a-zA-Z0-9_]+)=/', $normalized_arg, $matches ) ? $matches[1] : $normalized_arg;
				if ( $normalized_arg === $key || ( isset( $shortcuts[ $normalized_arg ] ) && $shortcuts[ $normalized_arg ] === $key ) ) {
					$options_to_env_info['overrides'][ $key ] = $value;
					$found_override                           = true;
					break;
				}
			}
			if ( ! $found_override ) {
				$options_to_env_info['defaults'][ $key ] = $value;
			}
		}

		return $options_to_env_info;
	}

	/**
	 * Parse environment variables from --env and --env_file options.
	 *
	 * @param array<string> $env_vars
	 * @param array<string> $env_files
	 *
	 * @return void
	 */
	protected function parse_env_vars( array $env_vars, array $env_files ): void {
		$parsed_vars = [];

		foreach ( $env_files as $env_file ) {
			if ( ! file_exists( $env_file ) ) {
				throw new \RuntimeException( sprintf( 'Environment file "%s" does not exist.', $env_file ) );
			}
			$parsed_vars = array_merge( $parsed_vars, Dotenv::parse( file_get_contents( $env_file ) ) );
		}

		foreach ( $env_vars as $env_var ) {
			$env_var = explode( '=', $env_var, 2 );
			if ( count( $env_var ) !== 2 ) {
				throw new \RuntimeException( 'Invalid environment variable format. Should be in the format "--env FOO=bar".' );
			}
			$key   = trim( $env_var[0] );
			$value = trim( $env_var[1] );
			if ( ! preg_match( '/^[A-Za-z0-9_]+$/', $key ) ) {
				throw new \RuntimeException( 'Invalid environment variable name. Must contain only letters, numbers, and underscores.' );
			}
			$parsed_vars[ $key ] = $value;
		}

		$parsed_vars['WP_CLI_CONFIG_PATH'] = '/qit/wp-cli.yml';
		App::setVar( 'QIT_DOCKER_ENV_VARS', $parsed_vars );
	}
}