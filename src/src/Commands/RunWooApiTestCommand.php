<?php

namespace QIT_CLI\Commands;

use QIT_CLI\QITInput;
use Symfony\Component\Console\Output\OutputInterface;

class RunWooApiTestCommand extends RunE2ECommand {

	protected static $defaultName = 'run:woo-api'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase
	protected string $test_type   = 'woo-api';

	protected function configure(): void {
		parent::configure();
		$this->setDescription( 'Run WooCommerce Core API tests' );
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		$input->setOption( 'test-package', [ 'woocommerce/core-api-tests:latest' ] );

		return parent::doExecute( $input, $output );
	}
}
