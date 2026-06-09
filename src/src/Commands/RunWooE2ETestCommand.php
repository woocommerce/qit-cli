<?php

namespace QIT_CLI\Commands;

use QIT_CLI\QITInput;
use Symfony\Component\Console\Output\OutputInterface;

class RunWooE2ETestCommand extends RunE2ECommand {

	protected static $defaultName = 'run:woo-e2e'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase
	protected string $test_type   = 'e2e';

	protected function configure(): void {
		parent::configure();
		$this->setDescription( 'Run WooCommerce Core E2E tests' );
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		if ( empty( $input->getOption( 'test-package' ) ) ) {
			$input->setOption( 'test-package', [ 'woocommerce/core-e2e-tests:latest' ] );
		}

		return parent::doExecute( $input, $output );
	}
}
