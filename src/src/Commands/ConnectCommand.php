<?php

namespace QIT_CLI\Commands;

use QIT_CLI\Commands\Partner\AddPartner;
use QIT_CLI\RequestBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function QIT_CLI\get_manager_url;

class ConnectCommand extends Command {
	protected static $defaultName = 'connect'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure() {
		$this
			->setDescription( 'Connect to manage your extensions and test runs.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$io = new SymfonyStyle( $input, $output );
		$io->title( 'Quality Insights Toolkit (QIT)' );

		$output->write( "\n<comment>Connecting to QIT servers... </comment>" );

		try {
			[ $is_proxied, $proxied_instructions ] = $this->is_proxied();
		} catch ( \Exception $e ) {
			$output->writeln( "<error>{$e->getMessage()}</error>" );

			return Command::FAILURE;
		}

		$output->write( "<fg=green>✔</>\n" );

		if ( $is_proxied ) {
			$io->section( 'Authenticate as Automattician (Connected to Proxy)' );

			$io->writeln( $proxied_instructions . "\n" );
		} else {
			$io->section( 'Authenticate as Woo.com Partner Developer' );
		}

		$command = $this->getApplication()->find( AddPartner::getDefaultName() );

		return $command->run( new ArrayInput( [] ), $output );
	}

	/**
	 * @return array{bool, string} Whether it's a proxied request, and instructions if so.
	 */
	protected function is_proxied(): array {
		try {
			$json = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/proxied' ) )
				->with_method( 'POST' )
				->with_onboarding( true )
				->request();
		} catch ( \Exception $e ) {
			return [ false, '' ];
		}

		$data = json_decode( $json, true );

		if ( ! is_array( $data ) ) {
			throw new \UnexpectedValueException( 'Response is not a JSON.' );
		}

		foreach ( [ 'proxied', 'proxied_instructions' ] as $required ) {
			if ( ! array_key_exists( $required, $data ) ) {
				throw new \UnexpectedValueException( sprintf( 'Response is in an unexpected format. Missing "%s".', $required ) );
			}
		}

		return [ $data['proxied'] === 'yes', $data['proxied_instructions'] ];
	}
}
