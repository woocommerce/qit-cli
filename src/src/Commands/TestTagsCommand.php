<?php

namespace QIT_CLI\Commands;

use QIT_CLI\RequestBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;

class TestTagsCommand extends Command {
	protected static $defaultName = 'test-tags'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure() {
		$this
			->setDescription( 'List the Test Tags you have access to test.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		try {
			$json = ( new RequestBuilder( get_manager_url() . "/wp-json/cd/v1/get_extensions" ) )
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

		$table = new Table( $output );
		$table->setHeaders(['Slug', 'E2E Tests', 'Type']);

		foreach ($test_tags as $tag => $data) {
			$e2eTests = implode(', ', $data['tests']['e2e'] ?? []);
			$table->addRow([$data['slug'], $e2eTests, $data['type']]);
		}

		$table->render();

		return Command::SUCCESS;
	}
}
