<?php

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Commands\DynamicCommand;
use QIT_CLI\Commands\DynamicCommandCreator;
use QIT_CLI\Environment\EnvConfigLoader;
use QIT_CLI\Environment\Environments\E2E\E2EEnvironment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\is_windows;

class UpEnvironmentCommand extends DynamicCommand {
	/** @var E2EEnvironment */
	protected $e2e_environment;

	/** @var Cache */
	protected $cache;

	/** @var OutputInterface */
	protected $output;

	protected static $defaultName = 'env:up'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	public function __construct( E2EEnvironment $e2e_environment, Cache $cache, OutputInterface $output ) {
		$this->e2e_environment = $e2e_environment;
		$this->cache           = $cache;
		$this->output          = $output;
		parent::__construct( static::$defaultName ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	protected function configure() {
		$schemas = $this->cache->get_manager_sync_data( 'schemas' );

		if ( ! is_array( $schemas['e2e']['properties'] ) ) {
			throw new \RuntimeException( 'E2E schema not set or incomplete.' );
		}

		DynamicCommandCreator::add_schema_to_command( $this, $schemas['e2e'], [], [
			'wordpress_version',
			'php_version',
		] );

		$this
			->setDescription( 'Creates a temporary local test environment that is completely ephemeral — no data is persisted. Every time you stop and restart the environment, it\'s like starting fresh.' )
			->addOption( 'plugins', 'p', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '(Optional) Plugin to activate in the environment. Accepts paths, Woo.com slugs/product IDs, WordPress.org slugs or GitHub URLs.', [] )
			->addOption( 'themes', 't', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '(Optional) Theme install, if multiple provided activates the last. Accepts paths, Woo.com slugs/product IDs, WordPress.org slugs or GitHub URLs.', [] )
			->addOption( 'volumes', 'l', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '(Optional) Additional volume mappings, eg: /home/mycomputer/my-plugin:/var/www/html/wp-content/plugins/my-plugin.', [] )
			->addOption( 'php_extensions', 'x', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'PHP extensions to install in the environment.', [] )
			->addOption( 'requires', 'r', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Load PHP file before running the command (may be used more than once).' )
			->addOption( 'object_cache', 'o', InputOption::VALUE_NONE, '(Optional) Whether to enable Object Cache (Redis) in the environment.' )
			->addOption( 'skip_activating_plugins', 's', InputOption::VALUE_NONE, 'Skip activating plugins in the environment.' )
			->addOption( 'json', 'j', InputOption::VALUE_NEGATABLE, 'Whether to return raw JSON format.', false )
			// ->addOption( 'attached', 'a', InputOption::VALUE_NONE, 'Whether to attach to the environment after starting it.' )
			->setAliases( [ 'env:start' ]
			);

		$this->add_option_to_send( 'plugins' );
		$this->add_option_to_send( 'themes' );
		$this->add_option_to_send( 'volumes' );
		$this->add_option_to_send( 'requires' );
		$this->add_option_to_send( 'php_extensions' );
		$this->add_option_to_send( 'object_cache' );

		$options_example = [];

		foreach ( $this->options_to_send as $option => $value ) {
			$opt = $this->getDefinition()->getOption( $option );

			switch ( $opt->getName() ) {
				case 'plugins':
					$options_example[ $opt->getName() ] = [
						'woocommerce',
						'wordpress-importer',
						'automatewoo',
					];
					break;
				case 'themes':
					$options_example[ $opt->getName() ] = [
						'storefront',
					];
					break;
				case 'volumes':
					$options_example[ $opt->getName() ] = [
						'/home/mycomputer/my-plugin:/var/www/html/wp-content/plugins/my-plugin',
					];
					break;
				case 'php_extensions':
					$options_example[ $opt->getName() ] = [
						'gd',
						'imagick',
					];
					break;
				case 'wordpress_version':
					$options_example[ $opt->getName() ] = 'rc';
					break;
				default:
					$options_example[ $opt->getName() ] = $opt->getDefault();
					break;
			}
		}

		$possible_options = implode( "\n- ", array_keys( $options_example ) );
		$json_example     = json_encode( $options_example, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		$yml_example      = App::make( \Symfony\Component\Yaml\Yaml::class )->dump( $options_example, 2, 2 );

		$this
			->setHelp( <<<HELP
Creates a configurable, temporary, disposable test environment.

<comment>Usage</comment>
<info>qit env:up</info>

<comment>Config File:</comment>
Create a config file (JSON or YML) to set default environment options.
Valid names include qit-env.json, qit-env.yml, .qit-env.json, and .qit-env.yml.
Override defaults with qit-env.override.json, qit-env.override.yml, etc., typically ignored in version control.

The possible options are:

- $possible_options

<comment>Example JSON file</comment>
$json_example

<comment>Example YML file</comment>
$yml_example

<comment>Where to Place Config:</comment>
The command searches for a config file in the current directory from which it's executed. For example:
A config file located at /home/mycomputer/my-plugin/qit-env.json is detected when you run <info>cd /home/mycomputer/my-plugin && qit env:up</info>.
If the config file is placed in the root directory of your plugin, the command automatically includes that plugin in the environment.

<comment>Parameters:</comment>
Parameters specified at runtime override config file settings.
Example: <info>qit env:up --php_version=8.3</info> forces PHP version 8.3 regardless of config files.

<comment>Plugins and Themes</comment>
Install additional plugins in the environment using the --plugins and --themes flag.
Repeat multiple times to install many, e.g:
<info>qit env:up --plugins automatewoo --plugins contact-form-7</info>

PS: To install premium plugins from the Woo.com Marketplace, you need to have access to them.
PS 2: Plugins are activated automatically in the test environment. Themes are only installed.

<comment>PHP Version</comment>
To set the PHP version, use the --php_version flag, e.g.:
<info>qit env:up --php_version=8.3</info>

<comment>WordPress Version</comment>
To set the WordPress version, use the --wordpress_version flag, e.g.:
<info>qit env:up --wordpress_version=rc</info>

<comment>Object Cache</comment>
To enable Object Cache (Redis) in the environment, use the --object_cache flag, e.g.:
<info>qit env:up --object_cache</info>

<comment>Volumes</comment>
To map a local directory to the test environment, use the --volumes flag, e.g.:
<info>qit env:up --volumes=/home/mycomputer/my-plugin:/var/www/html/wp-content/plugins/my-plugin</info>
This will map the local directory /home/mycomputer/my-plugin to the test environment at /var/www/html/wp-content/plugins/my-plugin.

<comment>PHP Extensions</comment>
To install PHP extensions in the test environment, use the --php_extensions flag, e.g.:
<info>qit env:up --php_extensions=gd --php_extensions=imagick</info>

<comment>Accessing the Test Website:</comment>
- URL provided at command completion. Default: "http://localhost:<RANDOM_PORT>"

<comment>Example:</comment>
<info>qit env:up --wordpress_version=rc --php_version=8.3 --php_extensions=gd --object_cache --plugins gutenberg --plugins automatewoo --themes storefront</info>

This will create a disposable test environment with the latest release candidate versions of WordPress, PHP 8.3, the GD extension, Object Cache enabled, Gutenberg from WordPress.org Plugin Repository and AutomateWoo from the Woo.com Marketplace installed and active, and Storefront installed.
HELP
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		if ( is_windows() ) {
			$output->writeln( '<comment>To use QIT Environments on Window, please use WSL. Check our guide here: https://qit.woo.com/docs/windows.</comment>' );

			return Command::FAILURE;
		}

		try {
			$options_to_env_info = $this->parse_options( $input );
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

			return Command::FAILURE;
		}

		if ( $input->getOption( 'skip_activating_plugins' ) ) {
			$this->e2e_environment->set_skip_activating_plugins( true );
		}

		$env_info = App::make( EnvConfigLoader::class )->init_env_info( $options_to_env_info );

		if ( $output->isVeryVerbose() ) {
			$this->output->writeln( 'Environment info: ' . json_encode( $env_info, JSON_PRETTY_PRINT ) );
		}

		$this->e2e_environment->init( $env_info );

		// "up_and_test" is when we are using an environment to run a custom test. "up" is spinning up the environment on-demand.
		$this->e2e_environment->up( getenv( 'QIT_UP_AND_TEST' ) ? 'up_and_test' : 'up' );

		if ( $input->getOption( 'json' ) ) {
			$output->write( json_encode( $env_info ) );
		} else {
			// Print the site URL in the last line for easy scripting with "wc -l" or similar.
			$output->writeln( $env_info->site_url );
		}

		return Command::SUCCESS;
	}

	protected function parse_options( InputInterface $input ): array {
		$options = parent::parse_options( $input );

		$options_to_env_info = [
			'defaults'  => [],
			'overrides' => [],
		];

		$shortcuts = [];

		foreach ( $this->getDefinition()->getOptions() as $o ) {
			$shortcuts[ $o->getShortcut() ] = $o->getName();
		}

		/*
		 * Options can be explicitly set by the user or be a default value.
		 *
		 * This affects the order of precedence that each option gets.
		 *
		 * 1: Option set at runtime (will be in $GLOBALS['argv'])
		 * 2: Option in config file (will be in .?qit-env.(json|yml))
		 * 3. Default value
		 */
		foreach ( $options as $key => $value ) {
			$found_override = false;

			foreach ( $GLOBALS['argv'] as $arg ) {
				$normalized_arg = ltrim( $arg, '-' );

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
}
