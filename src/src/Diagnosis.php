<?php

namespace QIT_CLI;

use Symfony\Component\Console\Output\OutputInterface;

class Diagnosis {
	public function run( OutputInterface $output ): void {
		// Only run this every 5 minutes if we can't connect to the Manager.
		if ( time() - Config::get_last_diagnosis() < 60 * 5 ) {
			return;
		} else {
			Config::set_last_diagnosis( time() );
		}

		$connection_to_internet = false;
		$connection_to_manager  = false;

		// Check connection with internet.
		$output->write( 'Checking internet connection... ' );

		$connected = fsockopen( 'www.google.com', 80 );

		if ( $connected ) {
			$output->writeln( 'https://www.google.com [OK]' );
			$connection_to_internet = true;
			fclose( $connected );
		} else {
			$output->writeln( 'https://www.google.com [Error]' );
		}

		// Check connection with Manager.
		$output->write( 'Checking QIT server connection... ' );

		$parsed_url = parse_url( \QIT_CLI\get_manager_url() );

		if ( ! is_array( $parsed_url ) || empty( $parsed_url['host'] ) || empty( $parsed_url['scheme'] ) ) {
			$output->writeln( 'QIT server URL seems invalid. Try resetting the environment by deleting the folder: ' . Config::get_qit_dir() );
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
			$output->writeln( 'Both the internet and QIT server connections were successful. This is likely an internal issue with the QIT servers. Please try again later.' );
		}

		if ( $connection_to_internet && ! $connection_to_manager ) {
			$output->writeln( 'Unable to connect to QIT server. Your internet connection is working, but the QIT server connection failed. This is likely an internal issue with the QIT servers. Please try again later.' );
		}

		if ( ! $connection_to_internet && ! $connection_to_manager ) {
			$output->writeln( 'The CLI tool was unable to connect to the internet or the QIT servers. Please check your internet connection and try again.' );
		}
	}
}
