<?php

namespace QIT_CLI\Commands;

use QIT_CLI\App;
use QIT_CLI\Auth;
use QIT_CLI\ManagerBackend;
use QIT_CLI\IO\Output;
use QIT_CLI\RequestBuilder;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;

class CreateMassTestCommands extends DynamicCommandCreator {

	/** @var ManagerBackend $environment */
	protected $environment;

	/** @var Auth $auth */
	protected $auth;

	/** @var OutputInterface $output */
	protected $output;

	public function __construct( ManagerBackend $environment, Auth $auth ) {
		$this->environment = $environment;
		$this->auth        = $auth;
		$this->output      = App::make( Output::class );
	}

	public function register_commands( Application $application ): void {
		$command = new class() extends DynamicCommand {
			public function execute( InputInterface $input, OutputInterface $output ) {
				try {
					$options = $this->parse_options( $input );

					$output->writeln( sprintf( 'Running mass test...' ) );

					$json = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/mass-enqueue' ) )
						->with_method( 'POST' )
						->with_post_body( $options )
						->request();
				} catch ( \Exception $e ) {
					$output->writeln( "<error>{$e->getMessage()}</error>" );

					return Command::FAILURE;
				}

				$output->writeln( sprintf( '<info>Mass test enqueued on QIT Servers!</info>' ) );

				return Command::SUCCESS;
			}
		};

		$command
			->setName( 'mass-test' );

		try {
			$schema = $this->environment->get_cache()->get_manager_sync_data( 'schemas' )['mass'] ?? null;

			if ( ! is_array( $schema ) ) {
				throw new \Exception();
			}
		} catch ( \Exception $e ) {
			App::make( Output::class )->writeln( '<error>Could not fetch schema from QIT Servers to register Mass Test Run. Please try again later.</error>' );

			return;
		}

		$this->add_schema_to_command( $command, $schema );

		$application->add( $command );
	}
}
