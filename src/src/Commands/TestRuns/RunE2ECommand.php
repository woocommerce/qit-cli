<?php

namespace QIT_CLI\Commands\TestRuns;

use QIT_CLI\Cache;
use QIT_CLI\Commands\DynamicCommand;
use QIT_CLI\Commands\DynamicCommandCreator;
use QIT_CLI\Commands\Environment\UpEnvironmentCommand;
use QIT_CLI\Environment\Environments\E2E\E2EEnvironment;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\Environment\ExtensionDownload\ExtensionDownloader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use function QIT_CLI\is_windows;
use function QIT_CLI\normalize_path;

class RunE2ECommand extends DynamicCommand {
	/** @var E2EEnvironment */
	protected $e2e_environment;

	/** @var Cache */
	protected $cache;

	/** @var OutputInterface */
	protected $output;

	protected static $defaultName = 'run:e2e'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

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

		DynamicCommandCreator::add_schema_to_command( $this, $schemas['e2e'] );

		// Extension slug/ID.
		$this->addArgument(
			'woo_extension',
			InputArgument::REQUIRED,
			'A WooCommerce Extension Slug or Marketplace ID.'
		);

		// Extension slug/ID.
		$this->addArgument(
			'path',
			InputArgument::REQUIRED,
			'Path to your E2E tests (Optional, if not set, it will try to download your custom tests that you have previously uploaded to QIT)'
		);
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		if ( is_windows() ) {
			$output->writeln( '<comment>Warning: It is highly recommended to run this script from Windows Subsystem for Linux (WSL) when using Windows.</comment>' );
		}

		try {
			$options        = $this->parse_options( $input );
			$env_up_options = $options['env_up'];
			$other_options  = $options['other'];
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

			return Command::FAILURE;
		}

		foreach ( $other_options as $k => $o ) {
			if ( ! in_array( $k, [ 'compatibility', 'woocommerce_version' ], true ) ) {
				$this->output->writeln( sprintf( '<comment>Option "%s" is not part of the "env:up" command definition and will be ignored.</comment>', $k ) );
			}
		}

		$compatibility_mode  = $other_options['compatibility'];
		$woocommerce_version = $other_options['woocommerce_version'];

		$additional_volumes = [];

		$path = $input->getArgument( 'path' );

		if ( ! file_exists( $path ) ) {
			$output->writeln( sprintf( '<error>We can only run local tests for now. Path "%s" does not exist.</error>', $path ) );

			return Command::FAILURE;
		}

		$woo_extension = $input->getArgument( 'woo_extension' );

		if ( ! ExtensionDownloader::is_valid_plugin_slug( $woo_extension ) ) {
			$output->writeln( sprintf( '<error>Invalid WooCommerce Extension Slug or Marketplace ID: "%s"</error>', $woo_extension ) );

			return Command::FAILURE;
		}

		// Mount the tests as read-only.
		$additional_volumes[] = normalize_path( $path ) . ':' . "/qit/tests/$woo_extension/e2e/";

		$env_up_options['--volumes'] = $additional_volumes;

		$env_up_options['--json'] = true;

		if ( $output->isVerbose() ) {
			$env_up_options['--verbose'] = true;
		} elseif ( $output->isVeryVerbose() ) {
			$env_up_options['--very-verbose'] = true;
		}

		// Invoke the "env:up" Command.
		$env_up_command = $this->getApplication()->find( UpEnvironmentCommand::getDefaultName() );

		// Schedule a catch-all for this environment to be terminated (ungracefully).
		register_shutdown_function( function () {
			// Env not up or could not parse the "up" JSON.
			if ( empty( $GLOBALS['env_to_shutdown'] ) || ! $GLOBALS['env_to_shutdown'] instanceof EnvInfo ) {
				return;
			}
			try {
				Environment::down( $GLOBALS['env_to_shutdown'], new NullOutput() );
			} catch ( \Exception $e ) {
				// no-op.
			}
		} );

		$resource_stream = fopen( 'php://temp', 'w+' );

		$up_exit_status_code = $env_up_command->run( new ArrayInput( $env_up_options ), new StreamOutput( $resource_stream ) );

		// Read from the stream.
		$up_output = stream_get_contents( $resource_stream, - 1, 0 );
		$env_json  = json_decode( $up_output, true );

		if ( ! is_array( $env_json ) || empty( $env_json['env_id'] ) ) {
			$this->output->writeln( sprintf( '<error>Failed to parse the environment JSON. Output: %s</error>', $up_output ) );

			return Command::FAILURE;
		}

		$env_info                   = EnvInfo::from_array( $env_json );
		$GLOBALS['env_to_shutdown'] = $env_info;

		if ( $up_exit_status_code !== Command::SUCCESS ) {
			$this->output->writeln( sprintf( '<error>Failed to start the environment. Output: %s</error>', stream_get_contents( $resource_stream, - 1, 0 ) ) );
			Environment::down( $env_json['env_id'], new NullOutput() );

			return Command::FAILURE;
		}

		$this->output->writeln( 'Running E2E...' );

		return Command::SUCCESS;
	}

	protected function parse_options( InputInterface $input ): array {
		$options = parent::parse_options( $input );

		// Iterate over all options of UpEnvironmentCommand
		// Remote keys in $options array that are not part of the definition
		// Notify user if that happens
		$up_command_option_names = array_map( function ( $option ) {
			return $option->getName();
		}, $this->getApplication()->find( UpEnvironmentCommand::getDefaultName() )->getDefinition()->getOptions() );

		$parsed_options = [
			'env_up' => [],
			'other'  => [],
		];

		foreach ( $options as $option_name => $option_value ) {
			if ( ! in_array( $option_name, $up_command_option_names, true ) ) {
				$parsed_options['other'][ $option_name ] = $option_value;
			} else {
				$parsed_options['env_up']["--$option_name"] = $option_value;
			}
		}

		return $parsed_options;
	}
}
