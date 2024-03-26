<?php

namespace QIT_CLI\Commands\CustomTests;


use QIT_CLI\App;
use QIT_CLI\Environment\ExtensionDownload\ExtensionDownloader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Serializer;

class ExtensionDownloaderCommand extends Command {
	protected static $defaultName = 'internal:ext-download'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure() {
		$this
			->addArgument( 'serialized_env_info', InputArgument::REQUIRED )
			->addArgument( 'cache_dir', InputArgument::REQUIRED )
			->addArgument( 'plugins_json', InputArgument::REQUIRED )
			->addArgument( 'themes_json', InputArgument::REQUIRED )
			->setDescription( 'Scaffold an example E2E test.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		if ( ! file_exists( '/.dockerenv' ) ) {
			$output->writeln( '<error>This command is intended to be run inside a Docker container.</error>' );

			return Command::FAILURE;
		}

		$serialized_env_info = $input->getArgument( 'serialized_env_info' );
		$cache_dir           = $input->getArgument( 'cache_dir' );
		$plugins_json        = json_decode( $input->getArgument( 'plugins_json' ), true );
		$themes_json         = json_decode( $input->getArgument( 'themes_json' ), true );

		if ( is_null( $plugins_json ) ) {
			$output->writeln( '<error>Invalid JSON for plugins.</error>' );

			return Command::FAILURE;
		}

		if ( is_null( $themes_json ) ) {
			$output->writeln( '<error>Invalid JSON for themes.</error>' );

			return Command::FAILURE;
		}

		if ( ! file_exists( $cache_dir ) ) {
			$output->writeln( '<error>Cache directory does not exist.</error>' );

			return Command::FAILURE;
		}

		// Use Symfony Unserialize.
		try {
			$serializer = App::make( Serializer::class );
			$env_info   = $serializer->deserialize( $serialized_env_info, 'QIT_CLI\Environment\Environments\EnvInfo', 'json' );
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>Could not deserialize EnvInfo: %s</error>', $e->getMessage() ) );

			return Command::FAILURE;
		}

		$extension_downloader = App::make( ExtensionDownloader::class );

		$extension_downloader->download( $env_info, $cache_dir, $plugins_json, $themes_json );

		echo $serializer->serialize( $env_info, 'json' );

		return Command::SUCCESS;
	}
}
