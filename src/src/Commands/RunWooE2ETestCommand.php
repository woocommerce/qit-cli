<?php

namespace QIT_CLI\Commands;

use QIT_CLI\QITInput;
use Symfony\Component\Console\Output\OutputInterface;

class RunWooE2ETestCommand extends RunE2ECommand {
	use ExtensionSetTrait;

	protected static $defaultName = 'run:woo-e2e'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase
	protected string $test_type   = 'e2e';

	protected function configure(): void {
		parent::configure();
		$this->setDescription( 'Run WooCommerce Core E2E tests' );
		$this->configure_extension_set_option();
		$this->configure_remote_option();
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		$remote_result = $this->run_remote_if_requested( $input, $output, 'woo-e2e', 'woo-e2e' );
		if ( $remote_result !== null ) {
			return $remote_result;
		}

		$extension_set_error = $this->resolve_extension_set( $input, $output );
		if ( $extension_set_error !== null ) {
			return $extension_set_error;
		}

		if ( empty( $input->getOption( 'test-package' ) ) ) {
			$input->setOption( 'test-package', [ 'woocommerce/core-e2e-tests:latest' ] );
		}

		return parent::doExecute( $input, $output );
	}
}
