<?php

namespace QIT_CLI\Commands;

use QIT_CLI\QITInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunWooApiTestCommand extends RunE2ECommand {

	protected static $defaultName = 'run:woo-api'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase
	protected string $test_type   = 'woo-api';

	protected function configure(): void {
		parent::configure();
		$this->setDescription( 'Run WooCommerce Core API tests' );
		$this->addOption(
			'optional_features',
			null,
			InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
			'(Optional) WooCommerce features to enable in the test environment. [possible values: hpos, new_product_editor]',
			[]
		);
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		if ( empty( $input->getOption( 'test-package' ) ) ) {
			$input->setOption( 'test-package', [ 'woocommerce/core-api-tests:latest' ] );
		}

		return parent::doExecute( $input, $output );
	}
}
