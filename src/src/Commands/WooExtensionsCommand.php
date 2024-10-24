<?php

namespace QIT_CLI\Commands;

use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WooExtensionsCommand extends Command {
	protected static $defaultName = 'extensions'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/** @var WooExtensionsList $woo_extensions_list */
	protected $woo_extensions_list;

	public function __construct( WooExtensionsList $woo_extensions_list ) {
		$this->woo_extensions_list = $woo_extensions_list;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setDescription( 'List the WooExtensions you have access to test.' )
			->addOption( 'refresh', 'r', InputOption::VALUE_NONE, '(Optional) Manually refresh the list of available Woo Extensions to test (This happens automatically once a day).' )
			->addOption( 'deps', 'd', InputOption::VALUE_NONE, '(Optional) Include dependencies in the list.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		if ( $input->getOption( 'refresh' ) === true ) {
			$this->woo_extensions_list->fetch_woo_extensions_available();

			$output->writeln( 'Woo Extensions list updated.' );

			return Command::SUCCESS;
		}

		$woo_extensions = $this->woo_extensions_list->get_woo_extension_list();

		if ( $input->getOption( 'deps' ) ) {
			$headers        = [ 'ID', 'Slug', 'Type', 'WPOrg Dependencies', 'Woo Dependencies' ];
			$woo_extensions = array_map( function ( $extension ) {
				$extension[] = implode( ', ', $extension['dependencies']['wporg'] );
				$extension[] = implode( ', ', $extension['dependencies']['woo'] );
				unset( $extension['dependencies'] );

				return $extension;
			}, $woo_extensions );
		} else {
			$headers        = [ 'ID', 'Slug', 'Type' ];
			$woo_extensions = array_map( function ( $extension ) {
				unset( $extension['dependencies'] );

				return $extension;
			}, $woo_extensions );
		}

		$table = new Table( $output );
		$table
			->setHeaders( $headers )
			->setRows( $woo_extensions );
		$table->render();

		return Command::SUCCESS;
	}
}
