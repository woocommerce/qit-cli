<?php

namespace QIT_CLI\Commands\CustomTests;


use QIT_CLI\App;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\ExtensionDownload\ExtensionDownloader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExtensionDownloaderCommand extends Command {
	protected static $defaultName = 'internal:ext-download'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure() {
		$this
			->addArgument( 'env_info_json', InputArgument::REQUIRED )
			->addOption( 'json', null, InputOption::VALUE_NONE )
			->setDescription( 'Scaffold an example E2E test.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		if ( ! file_exists( '/.dockerenv' ) ) {
			$output->writeln( json_encode( '<error>This command is intended to be run inside a Docker container.</error>' ) );

			return Command::FAILURE;
		}

		$env_info_json = json_decode( base64_decode( $input->getArgument( 'env_info_json' ) ), true );

		if ( is_null( $env_info_json ) ) {
			$output->writeln( json_encode( 'Invalid JSON for env_info.' ) );

			return Command::FAILURE;
		}


		$env_info = EnvInfo::from_array( $env_info_json );

		$extension_downloader = App::make( ExtensionDownloader::class );

		$extension_downloader->download( $env_info, '/qit/home/ext-cache', $env_info->plugins, $env_info->themes );

		$output->write( json_encode( $env_info ) );

		return Command::SUCCESS;
	}
}
