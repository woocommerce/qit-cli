<?php

namespace QIT_CLI\Commands\Tags;

use QIT_CLI\RequestBuilder;
use QIT_CLI\Upload;
use QIT_CLI\WooExtensionsList;
use QIT_CLI\Zipper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;

class DeleteTestTagsCommand extends Command {
	protected static $defaultName = 'tag:delete'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/** @var Zipper */
	protected $zipper;

	/** @var Upload */
	protected $uploader;

	/** @var WooExtensionsList */
	protected $woo_extensions_list;

	public function __construct( Zipper $zipper, Upload $uploader, WooExtensionsList $woo_extensions_list ) {
		parent::__construct();
		$this->zipper              = $zipper;
		$this->uploader            = $uploader;
		$this->woo_extensions_list = $woo_extensions_list;
	}

	protected function configure() {
		$this
			->addArgument( 'test_tag', InputArgument::REQUIRED, 'The test tag to delete, example: "qit-beaver:rc", or "foo:default", etc. Accepts one test tag only, not comma-separated.' )
			->addArgument( 'test_type', InputArgument::OPTIONAL, 'The test type.', 'e2e' )
			->setDescription( /** @lang text */ 'Delete your custom test from QIT.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$test_tag  = $input->getArgument( 'test_tag' );
		$test_type = $input->getArgument( 'test_type' );

		// Early bail: We only support E2E for now.
		if ( $test_type !== 'e2e' ) {
			$output->writeln( '<error>Invalid test type.</error>' );

			return Command::FAILURE;
		}

		// Validate that test tag has two parts once we explode it by ":", and that the first part is a valid extension slug.
		$parts = explode( ':', $test_tag );

		if ( count( $parts ) !== 2 ) {
			$output->writeln( '<error>Invalid test tag. Expected format: slug:tag</error>' );

			return Command::FAILURE;
		}

		$extension = $parts[0];

		// Woo Extension ID / Slug. Bail if not found.
		if ( is_numeric( $extension ) ) {
			// Map ID to slug.
			$extension = $this->woo_extensions_list->get_woo_extension_slug_by_id( (int) $extension );
		} else {
			try {
				// Validate provided slug.
				$this->woo_extensions_list->get_woo_extension_id_by_slug( $extension );
			} catch ( \Exception $e ) {
				$output->writeln( "<error>{$e->getMessage()}</error>" );

				return Command::FAILURE;
			}
		}

		try {
			$request = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/delete-test' ) )
				->with_method( 'POST' )
				->with_post_body( [
					'test_tag'  => $test_tag,
					'test_type' => $test_type,
				] )
				->request();

			// Print success message.
			$output->writeln( '<info>Test deleted successfully.</info>' );

			return Command::SUCCESS;
		} catch ( \Exception $e ) {
			$output->writeln( "<error>{$e->getMessage()}</error>" );

			return Command::FAILURE;
		}
	}
}
