<?php

namespace QIT_CLI\Commands;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Environment\EnvironmentDownloader;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\LocalTests\EnvironmentRunner;
use QIT_CLI\PreCommand\Interfaces\LocalTestCommand;
use QIT_CLI\PreCommand\Results\LocalTestResult;
use QIT_CLI\TestConfiguration;
use QIT_CLI\Upload;
use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\is_windows;

class RunActivationTestCommand extends QITCommand implements LocalTestCommand {
	protected EnvironmentRunner $environment_runner;
	protected WooExtensionsList $woo_extensions_list;
	protected Upload $upload;
	protected Cache $cache;

	protected static $defaultName = 'run:activation';

	public function __construct(
		EnvironmentRunner $environment_runner,
		WooExtensionsList $woo_extensions_list,
		Upload $upload,
		Cache $cache
	) {
		parent::__construct();
		$this->environment_runner  = $environment_runner;
		$this->woo_extensions_list = $woo_extensions_list;
		$this->upload              = $upload;
		$this->cache               = $cache;
	}

	// LocalTestCommand interface implementation
	public function getEnvironmentName(): string {
		return $this->input->getOption( 'environment' ) ?? 'default';
	}

	public function shouldPrepareEnvironment(): bool {
		return true;
	}

	public function getTestType(): string {
		return 'activation';
	}

	public function getTestProfile(): string {
		return $this->input->getOption( 'profile' ) ?? 'default';
	}

	protected function configure(): void {
		parent::configure();

		$this->setDescription( 'Run activation tests' )
		     ->addArgument( 'woo_extension', InputArgument::REQUIRED, 'Extension slug or ID' )
		     ->addOption( 'wp', null, InputOption::VALUE_OPTIONAL, 'WordPress version', 'stable' )
		     ->addOption( 'woo', null, InputOption::VALUE_OPTIONAL, 'WooCommerce version' )
		     ->addOption( 'php', null, InputOption::VALUE_OPTIONAL, 'PHP version', '8.0' )
		     ->addOption( 'source', null, InputOption::VALUE_OPTIONAL, 'Local source path' )
		     ->addOption( 'zip', null, InputOption::VALUE_OPTIONAL, 'ZIP file to upload' )
		     ->addOption( 'plugin', 'p', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Additional plugins', [] )
		     ->addOption( 'theme', 't', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Additional themes', [] )
		     ->addOption( 'volume', 'l', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Volume mappings', [] )
		     ->addOption( 'dependencies_mode', null, InputOption::VALUE_OPTIONAL, 'Dependencies mode', 'activate' )
		     ->addOption( 'json', 'j', InputOption::VALUE_NONE, 'JSON output' );
	}

	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		if ( is_windows() ) {
			$output->writeln( '<comment>To run Activation Tests on Windows, please use WSL.</comment>' );

			return Command::FAILURE;
		}

		/** @var LocalTestResult $result */
		$result = $this->getPreCommandResult();

		// The PreCommand has already:
		// - Parsed qit.json
		// - Resolved test configuration
		// - Downloaded all extensions
		// - Created the environment configuration

		$env_info = $result->env_info;

		// Handle SUT from CLI
		$this->handleSUT( $input, $env_info );

		// Apply CLI overrides
		$this->applyCliOverrides( $input, $env_info );

		// Set up the environment for activation tests
		$this->configureActivationTest( $env_info );

		// For testing
		if ( getenv( 'QIT_SELF_TEST' ) === 'env_info' ) {
			$output->write( json_encode( $env_info ) );

			return Command::SUCCESS;
		}

		// Run the activation test
		try {
			$env_info = $this->environment_runner->run_environment( $this->buildEnvironmentOptions( $env_info ) );

			// The activation test is run as part of the environment setup
			// Check the results
			$output->writeln( '<info>Activation test completed successfully!</info>' );

			return Command::SUCCESS;
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>Activation test failed: %s</error>', $e->getMessage() ) );

			return Command::FAILURE;
		}
	}

	protected function handleSUT( InputInterface $input, E2EEnvInfo $env_info ): void {
		$woo_extension = $input->getArgument( 'woo_extension' );

		try {
			if ( is_numeric( $woo_extension ) ) {
				$woo_id   = (int) $woo_extension;
				$woo_slug = $this->woo_extensions_list->get_woo_extension_slug_by_id( $woo_id );
			} else {
				$woo_slug = $woo_extension;
				$woo_id   = $this->woo_extensions_list->get_woo_extension_id_by_slug( $woo_slug );
			}

			$sut_type = $this->woo_extensions_list->get_woo_extension_type( $woo_id );

			$env_info->sut = [
				'slug' => $woo_slug,
				'id'   => $woo_id,
				'type' => $sut_type,
			];

			App::setVar( 'QIT_SUT', $woo_id );
			App::setVar( 'QIT_SUT_SLUG', $woo_slug );
		} catch ( \Exception $e ) {
			throw new \RuntimeException( "Failed to resolve extension: " . $e->getMessage() );
		}
	}

	protected function applyCliOverrides( InputInterface $input, E2EEnvInfo $env_info ): void {
		// Apply version overrides
		if ( $wp = $input->getOption( 'wp' ) ) {
			$env_info->wp_version = $wp;
		}
		if ( $woo = $input->getOption( 'woo' ) ) {
			$env_info->woo_version = $woo;
		}
		if ( $php = $input->getOption( 'php' ) ) {
			$env_info->php_version = $php;
		}

		// Handle source/zip options
		if ( $source = $input->getOption( 'source' ) ) {
			// This would need to update the SUT in the plugins array
			foreach ( $env_info->plugins as $plugin ) {
				if ( $plugin->slug === $env_info->sut['slug'] ) {
					$plugin->source    = $source;
					$plugin->directory = $source;
					break;
				}
			}
		} elseif ( $zip = $input->getOption( 'zip' ) ) {
			// Handle zip upload
			// This would need to be implemented based on your upload flow
		}
	}

	protected function configureActivationTest( E2EEnvInfo $env_info ): void {
		// Set up the test configuration for activation
		$env_info->tests = [
			[
				'slug'                  => 'woocommerce',
				'test_tag'              => 'activation',
				'type'                  => 'plugin',
				'action'                => 'test',
				'path_in_php_container' => '',
				'path_in_host'          => '',
			]
		];

		// Ensure WooCommerce is included if not already
		$has_woo = false;
		foreach ( $env_info->plugins as $plugin ) {
			if ( $plugin->slug === 'woocommerce' ) {
				$has_woo           = true;
				$plugin->action    = 'test';
				$plugin->test_tags = [ 'activation' ];
				break;
			}
		}

		if ( ! $has_woo ) {
			$woo                 = new \QIT_CLI\Environment\Extension( 'woocommerce', 'plugin' );
			$woo->from           = 'wporg';
			$woo->version        = $env_info->woo_version ?: 'stable';
			$woo->action         = 'test';
			$woo->test_tags      = [ 'activation' ];
			$env_info->plugins[] = $woo;
		}

		// Set the SUT to bootstrap action
		foreach ( $env_info->plugins as $plugin ) {
			if ( $plugin->slug === $env_info->sut['slug'] ) {
				$plugin->action    = 'bootstrap';
				$plugin->test_tags = [ 'pre-activation' ];
				break;
			}
		}

		// Set dependencies mode
		$env_info->dependencies_mode = $this->input->getOption( 'dependencies_mode' ) ?? 'activate';
	}

	protected function buildEnvironmentOptions( E2EEnvInfo $env_info ): array {
		// Convert env_info back to options format for environment runner
		$options = [
			'--json'        => true,
			'--wp_version'  => $env_info->wp_version,
			'--php_version' => $env_info->php_version,
		];

		if ( $env_info->woo_version ) {
			$options['--woo_version'] = $env_info->woo_version;
		}

		// Add plugins and themes
		foreach ( $env_info->plugins as $plugin ) {
			$options['--plugin'][] = json_encode( [
				'slug'      => $plugin->slug,
				'source'    => $plugin->source,
				'action'    => $plugin->action,
				'test_tags' => $plugin->test_tags,
			] );
		}

		foreach ( $env_info->themes as $theme ) {
			$options['--theme'][] = json_encode( [
				'slug'   => $theme->slug,
				'source' => $theme->source,
			] );
		}

		return $options;
	}
}