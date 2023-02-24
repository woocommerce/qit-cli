<?php

namespace QIT_CLI\Commands;

use QIT_CLI\App;
use QIT_CLI\Auth;
use QIT_CLI\Environment;
use QIT_CLI\IO\Output;
use QIT_CLI\RequestBuilder;
use QIT_CLI\Upload;
use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;

class CreateRunCommands {
	/** @var Environment $environment */
	protected $environment;

	/** @var Auth $auth */
	protected $auth;

	/** @var OutputInterface $output */
	protected $output;

	/** @var Upload $upload */
	protected $upload;

	/** @var WooExtensionsList $woo_extensions_list */
	protected $woo_extensions_list;

	public function __construct( Environment $environment, Auth $auth, Upload $upload, WooExtensionsList $woo_extensions_list ) {
		$this->environment         = $environment;
		$this->auth                = $auth;
		$this->output              = App::make( Output::class );
		$this->upload              = $upload;
		$this->woo_extensions_list = $woo_extensions_list;
	}

	public function register_run_commands( Application $application ): void {
		foreach ( $this->environment->get_cache()->get_manager_sync_data( 'test_types' ) as $test_type ) {
			$this->register_command_by_schema( $application, $test_type, $this->environment->get_cache()->get_manager_sync_data( 'schemas' )[ $test_type ] );
		}
	}

	/**
	 * Returns a cache key that is tied to the current CD Manager URL, so
	 * that it is invalidated if the CD Manager URL changes.
	 *
	 * @param string $test_type The test type to generate a cache key for.
	 *
	 * @return string
	 */
	protected function make_cache_key( string $test_type ) {
		return sprintf( 'schema_%s_%s', $test_type, md5( get_manager_url() ) );
	}

	/**
	 * @param Application  $application An instance of the current DI.
	 * @param string       $test_type The test type.
	 * @param array<mixed> $schema The test type schema.
	 *
	 * @return void
	 */
	protected function register_command_by_schema( Application $application, string $test_type, array $schema ): void {
		$command = new class( $test_type, $this->auth, $this->upload, $this->woo_extensions_list ) extends Command {
			/** @var Auth $auth */
			protected $auth;

			/** @var WooExtensionsList $woo_extensions_list */
			protected $woo_extensions_list;

			/** @var string $test_type */
			protected $test_type;

			/** @var array<mixed> $options_to_send */
			protected $options_to_send = [];

			/** @var Upload $upload */
			protected $upload;

			public function __construct( string $test_type, Auth $auth, Upload $upload, WooExtensionsList $woo_extensions_list ) {
				$this->auth                = $auth;
				$this->test_type           = $test_type;
				$this->woo_extensions_list = $woo_extensions_list;
				$this->upload              = $upload;
				parent::__construct();
			}

			public function add_option_to_send( string $option_name ): void {
				$this->options_to_send[ $option_name ] = '';
			}

			/**
			 * Symfony considers options as optional, and only enforces
			 * the "required" if the option is used but no value is passed.
			 *
			 * This method changes this behavior to ensure that a required option
			 * needs to be passed, otherwise the command is not executed.
			 *
			 * @throws \InvalidArgumentException When a required option is empty.
			 */
			protected function validate_required_options( InputInterface $input ): void {
				$options = $this->getDefinition()->getOptions();
				foreach ( $options as $option ) {
					$name  = $option->getName();
					$value = $input->getOption( $name );
					if ( $option->isValueRequired() && empty( $value ) ) {
						throw new \InvalidArgumentException( sprintf( 'The required option "%s" is not set. Run the command with --help for more information.', $name ) );
					}
				}
			}

			public function execute( InputInterface $input, OutputInterface $output ) {
				try {
					$this->validate_required_options( $input );
				} catch ( \Exception $e ) {
					$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

					return Command::FAILURE;
				}

				// Filter from the available options, those that we want to send.
				$options = array_intersect_key( $input->getOptions(), $this->options_to_send );

				// Filter empty options without a default.
				$options = array_filter( $options, static function ( $v ) {
					return ! is_null( $v );
				} );

				// Woo Extension ID / Slug.
				if ( is_numeric( $input->getArgument( 'woo_extension' ) ) ) {
					$options['woo_id'] = $input->getArgument( 'woo_extension' );
				} else {
					$options['woo_id'] = $this->woo_extensions_list->get_woo_extension_id_by_slug( $input->getArgument( 'woo_extension' ) );
				}

				// Upload zip.
				if ( ! empty( $options['zip'] ) ) {
					$options['upload_id'] = $this->upload->upload_build( $options['woo_id'], $input->getArgument( 'woo_extension' ), $options['zip'], $output );
					$options['event']     = 'cli_development_extension_test';
					unset( $options['zip'] );
				} else {
					$options['event'] = 'cli_published_extension_test';
				}

				// Convert "Additional Woo Plugins" Slugs to IDs.
				if ( ! empty( $options['additional_woo_plugins'] ) ) {
					$additional_woo_plugins = explode( ',', $options['additional_woo_plugins'] );
					foreach ( $additional_woo_plugins as &$awp ) {
						$awp = trim( $awp );
						if ( ! is_numeric( $awp ) ) {
							$awp = $this->woo_extensions_list->get_woo_extension_id_by_slug( $awp );
						}
					}

					$options['additional_woo_plugins'] = implode( ',', $additional_woo_plugins );
				}

				try {
					$response = ( new RequestBuilder( get_manager_url() . "/wp-json/cd/v1/enqueue-{$this->test_type}" ) )
						->with_method( 'POST' )
						->with_post_body( $options )
						->request();
				} catch ( \Exception $e ) {
					$output->writeln( "<error>{$e->getMessage()}</error>" );

					return Command::FAILURE;
				}

				$output->writeln( sprintf( '<info>Test started! You can see more details using the "%s" command.</info>', ListCommand::getDefaultName() ) );

				return Command::SUCCESS;
			}
		};

		$command
			->setName( "run:$test_type" );

		if ( ! empty( $schema['description'] ) ) {
			$command->setDescription( $schema['description'] );
		}

		if ( ! empty( $schema['properties'] ) && is_array( $schema['properties'] ) ) {
			foreach ( $schema['properties'] as $property_name => $property_schema ) {
				$ignore = [ 'client', 'event', 'woo_id' ];

				if ( in_array( $property_name, $ignore, true ) ) {
					continue;
				}

				if ( isset( $property_schema['required'] ) && $property_schema['required'] ) {
					$required = InputOption::VALUE_REQUIRED;
				} else {
					$required = InputOption::VALUE_OPTIONAL;
				}

				$description = $property_schema['description'] ?? '';
				$default     = $property_schema['default'] ?? null;

				if ( $required === InputOption::VALUE_OPTIONAL && ! empty( $description ) ) {
					$description = '(Optional) ' . $description;
				}

				if ( $this->output->isVerbose() ) {
					$schema_to_show = $property_schema;
					unset( $schema_to_show['description'] );
					$description = sprintf( '%s (Schema: %s)', $description, json_encode( $schema_to_show ) );
				}

				// So that we know inside the command that this option should be sent.
				$command->add_option_to_send( $property_name );

				$command
					->addOption(
						$property_name,
						null,
						$required,
						$description,
						$default
					);
			}
		}

		// Extension slug/ID.
		$command->addArgument(
			'woo_extension',
			InputArgument::REQUIRED,
			'A WooCommerce Extension Slug or Marketplace ID.'
		);

		// Upload zip.
		$command->addOption(
			'zip',
			null,
			InputOption::VALUE_OPTIONAL,
			'(Optional) Run the test using a local zip file of the plugin. Useful for running the tests before publishing it to the Marketplace.'
		);
		$command->add_option_to_send( 'zip' );

		$application->add( $command );
	}
}
