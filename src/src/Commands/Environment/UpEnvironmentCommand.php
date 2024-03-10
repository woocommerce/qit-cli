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

		DynamicCommandCreator::add_schema_to_command( $this, $schemas['e2e'], [
			'compatibility',
			'optional_features',
			'additional_woo_plugins',
			'additional_wordpress_plugins',
		] );

		$this
			->setDescription( 'Creates a temporary local test environment that is completely ephemeral — no data is persisted. Every time you stop and restart the environment, it\'s like starting fresh.' )
			->setHelp( <<<'HELP'
Configure aspects like WordPress and WooCommerce versions, PHP version, and PHP extensions. If run from a plugin/theme directory, the environment automatically maps your plugin/theme. Alternatively, use the --volume flag for manual mapping, e.g.:
<info>qit env:up --volume /home/mycomputer/my-plugin:/var/www/html/wp-content/plugins/my-plugin</info>
Default access is via 'localhost', customizable with the QIT_DOMAIN environment variable."

Example:

<info>qit env:up --wordpress-version=rc --woocommerce-version=rc --php-version=8.3 --php-ext=gd --with-object-cache</info>

This will create a disposable test environment with the latest release candidate versions of WordPress and WooCommerce, PHP 8.3, the GD extension, and Object Cache enabled.
HELP
			)
			->addOption( 'plugin', 'p', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '(Optional) Plugin to activate in the environment. Accepts paths, Woo.com slugs/product IDs, WordPress.org slugs or GitHub URLs.', [] )
			->addOption( 'theme', 't', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '(Optional) Theme install, if multiple provided activates the last. Accepts paths, Woo.com slugs/product IDs, WordPress.org slugs or GitHub URLs.', [] )
			->addOption( 'volumes', 'm', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '(Optional) Additional volume mappings, eg: /home/mycomputer/my-plugin:/var/www/html/wp-content/plugins/my-plugin.', [] )
			->addOption( 'php_extensions', 'x', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'PHP extensions to install in the environment.', [] )
			->addOption( 'object-cache', 'o', InputOption::VALUE_NONE, '(Optional) Whether to enable Object Cache (Redis) in the environment.' )
			->addOption( 'skip-activating-plugins', 's', InputOption::VALUE_NONE, 'Skip activating plugins in the environment.' )
			->addOption( 'json', 'j', InputOption::VALUE_NEGATABLE, 'Whether to return raw JSON format.', false )
			// ->addOption( 'attached', 'a', InputOption::VALUE_NONE, 'Whether to attach to the environment after starting it.' )
			->setAliases( [ 'env:start' ]
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		if ( is_windows() ) {
			$output->writeln( '<comment>Warning: It is highly recommended to run this script from Windows Subsystem for Linux (WSL) when using Windows.</comment>' );
		}

		try {
			$options = $this->parse_options( $input );
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

			return Command::FAILURE;
		}

		if ( $output->isVeryVerbose() ) {
			// Print the current options being used.
			$output->writeln( sprintf( 'Starting environment with options: %s', json_encode( $options ) ) );
		}

		if ( $input->getOption( 'skip-activating-plugins' ) ) {
			$this->e2e_environment->set_skip_activating_plugins( true );
		}

		// Only send to EnvInfo the options that the user specified (non-default values).
		$options_with_non_default_values = [];
		foreach ( $this->getDefinition()->getOptions() as $option ) {
			$name        = $option->getName();
			$input_value = $input->getOption( $name );

			if ( $input_value !== $option->getDefault() ) {
				$options_with_non_default_values[ $name ] = $input_value;
			}
		}

		// Remove options that we don't want to send to EnvInfo.
		$options_to_remove = [
			'skip-activating-plugins',
			'json',
			'help',
			'quiet',
			'verbose',
			'version',
			'ansi',
			'no-interaction',
		];

		$options_with_non_default_values = array_diff_key( $options_with_non_default_values, array_flip( $options_to_remove ) );

		$env_info = App::make( EnvConfigLoader::class )->init_env_info( $options_with_non_default_values );

		$this->output->writeln( json_encode( $env_info, JSON_PRETTY_PRINT ) );

		$this->e2e_environment->init( $env_info );
		$this->e2e_environment->up();

		if ( $input->getOption( 'json' ) ) {
			$output->write( json_encode( $env_info ) );
		}

		// Print the site URL as the last information for easy programmatic integrations.
		$output->writeln( $env_info->site_url );

		return Command::SUCCESS;
	}
}
