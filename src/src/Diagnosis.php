<?php

namespace QIT_CLI;

use Symfony\Component\Console\Output\OutputInterface;

class Diagnosis {
	public function run( OutputInterface $output ): void {
		$connection_to_internet = false;
		$connection_to_manager  = false;

		// Check connection with internet.
		$output->write( 'Checking connection to the internet... ' );

		$connected = fsockopen( 'www.google.com', 80 );

		if ( $connected ) {
			$output->writeln( 'https://www.google.com [OK]' );
			$connection_to_internet = true;
			fclose( $connected );
		} else {
			$output->writeln( 'https://www.google.com [Error]' );
		}

		// Check connection with Manager.
		$output->write( 'Checking connection to QIT servers... ' );

		$parsed_url = parse_url( \QIT_CLI\get_manager_url() );

		if ( ! is_array( $parsed_url ) || empty( $parsed_url['host'] ) || empty( $parsed_url['scheme'] ) ) {
			$output->writeln( 'QIT server URL seems invalid. Try resetting the environment by deleting the folder: ' . Environment::get_config_dir() );
			return;
		}

		$connected = fsockopen( $parsed_url['host'], $parsed_url['scheme'] === 'https' ? 443 : 80 );

		if ( $connected ) {
			$output->write( '[OK]' );
			$connection_to_manager = true;
			fclose( $connected );
		} else {
			$output->write( '[Error]' );
		}

		$output->write( PHP_EOL );

		if ( $connection_to_internet && $connection_to_manager ) {
			$output->writeln( 'Connections to both the Internet and the QIT servers were successful. This is likely an internal issue with the QIT servers. Please try again later.' );
		}

		if ( $connection_to_internet && ! $connection_to_manager ) {
			$output->writeln( 'QIT server is down, but your internet connection seems to be working. This is likely an internal issue with the QIT servers. Please try again later.' );
		}

		if ( ! $connection_to_internet && ! $connection_to_manager ) {
			$output->writeln( 'The CLI tool could not connect to the internet nor the QIT servers. Please check your internet connection.' );
		}

		// phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
		if ( ! $connection_to_internet && $connection_to_manager ) {
			// no-op.
		}
	}
}
