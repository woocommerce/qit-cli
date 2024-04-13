<?php

namespace QIT_CLI\Commands\Tags;

use QIT_CLI\RequestBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;

class ListTestTagsCommand extends Command {
	protected static $defaultName = 'tag:list'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure() {
		$this
			// Add an optional "extension" argument to fetch test tags just for this one.
			->addArgument( 'extension', InputArgument::OPTIONAL, 'If set, will return the test tags of this extension only, in a comma-separated list.' )
			->setDescription( 'List the Test Tags you have access to test.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		try {
			$json = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/get_extensions' ) )
				->with_method( 'POST' )
				->with_post_body( [
					'list_tests' => true,
				] )
				->request();
		} catch ( \Exception $e ) {
			$output->writeln( "<error>{$e->getMessage()}</error>" );

			return Command::FAILURE;
		}

		$test_tags = json_decode( $json, true );

		if ( empty( $test_tags ) ) {
			$output->writeln( '<error>No test tags found.</error>' );

			return Command::FAILURE;
		}

		// Specific extension tags.
		if ( ! empty( $input->getArgument( 'extension' ) ) ) {
			$specific_extension_tags = [];
			// Return only the test-tags for this extension.
			foreach ( $test_tags as $slug => $data ) {
				if ( $slug === $input->getArgument( 'extension' ) ) {
					$specific_extension_tags = $data['tests']['e2e'] ?? [];
					break;
				}
			}

			$output->writeln( implode( ', ', $specific_extension_tags ) );

			return Command::SUCCESS;
		}

		// Tags of all extensions this user has access to.
		$table = new Table( $output );
		$table->setHeaders( [ 'Slug', 'E2E Tests', 'Type' ] );

		foreach ( $test_tags as $tag => $data ) {
			$table->addRow( [ $data['slug'], implode( ', ', $data['tests']['e2e'] ?? [] ), $data['type'] ] );
		}

		$table->render();

		return Command::SUCCESS;
	}
}
