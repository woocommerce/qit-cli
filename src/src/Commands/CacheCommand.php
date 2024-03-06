<?php

namespace QIT_CLI\Commands;

use QIT_CLI\App;
use QIT_CLI\Cache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CacheCommand extends Command {
	protected static $defaultName = 'cache'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure() {
		$this
			->addArgument( 'action', InputArgument::REQUIRED, 'The action to perform. (get, set, delete)' )
			->addArgument( 'key', InputArgument::REQUIRED, 'The key to cache.' )
			->addArgument( 'value', InputArgument::OPTIONAL, 'The value when (set).' )
			->addArgument( 'expiration', InputArgument::OPTIONAL, 'The expiration when (set).' )
			->setDescription( 'Manipulates the QIT Cache' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$action = $input->getArgument( 'action' );
		$key    = $input->getArgument( 'key' );
		$cache  = App::make( Cache::class );

		switch ( $action ) {
			case 'get':
				$output->writeln( $cache->get( $key ) );
				break;
			case 'set':
				// Check expiration is set.
				if ( ! $input->hasArgument( 'expiration' ) ) {
					$output->writeln( 'Expiration is required when setting a cache value.' );

					return Command::FAILURE;
				}
				// Validate expiration is an integer.
				if ( ! is_numeric( $input->getArgument( 'expiration' ) ) ) {
					$output->writeln( 'Expiration must be an integer.' );

					return Command::FAILURE;
				}
				// Check value is set.
				if ( ! $input->hasArgument( 'value' ) ) {
					$output->writeln( 'Value is required when setting a cache value.' );

					return Command::FAILURE;
				}
				$cache->set( $key, $input->getArgument( 'value' ), (int) $input->getArgument( 'expiration' ) );
				break;
			case 'delete':
				$cache->delete( $key );
				break;
			default:
				$output->writeln( 'Invalid action.' );
				break;
		}

		return Command::SUCCESS;
	}
}
