<?php

namespace QIT_CLI\Commands;

use QIT_CLI\App;
use QIT_CLI\Auth;
use QIT_CLI\Cache;
use QIT_CLI\IO\Output;
use QIT_CLI\RequestBuilder;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;

class CreateMassTestCommands extends DynamicCommandCreator {

	/** @var Cache $cache */
	protected $cache;

	/** @var Auth $auth */
	protected $auth;

	/** @var OutputInterface $output */
	protected $output;

	public function __construct( Cache $cache, Auth $auth ) {
		$this->cache  = $cache;
		$this->auth   = $auth;
		$this->output = App::make( Output::class );
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

					$json = json_decode( $json, true );
					if ( is_array( $json ) && isset( $json['data'] ) ) {
						$output->writeln( sprintf( '<info>Mass test enqueued on QIT Servers!</info>' ) );
						$output->writeln( $json['data'] );
					} else {
						$output->writeln( sprintf( '<error>Could not enqueue mass test on QIT Servers.</error>' ) );
					}
				} catch ( \Exception $e ) {
					$output->writeln( "<error>{$e->getMessage()}</error>" );

					return Command::FAILURE;
				}

				return Command::SUCCESS;
			}
		};

		$command
			->setName( 'mass-test' );

		try {
			$schema = $this->cache->get_manager_sync_data( 'schemas' )['mass'] ?? null;

			if ( ! is_array( $schema ) ) {
				throw new \Exception();
			}
		} catch ( \Exception $e ) {
			App::make( Output::class )->writeln( '<error>Could not fetch schema from QIT Servers to register Mass Test Run. Please try again later.</error>' );

			return;
		}

		static::add_schema_to_command( $command, $schema );

		$application->add( $command );
	}
}
