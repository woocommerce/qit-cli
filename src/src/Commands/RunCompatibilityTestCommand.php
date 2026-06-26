<?php

namespace QIT_CLI\Commands;

use QIT_CLI\App;
use QIT_CLI\QITInput;
use QIT_CLI\RemoteTestRunner;
use Symfony\Component\Console\Output\OutputInterface;

class RunCompatibilityTestCommand extends RunE2ECommand {
	protected static $defaultName = 'run:compatibility'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase
	protected string $test_type   = 'compatibility';

	protected function configure(): void {
		parent::configure();
		$this->setDescription( 'Run compatibility smoke tests' );
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		if ( getenv( 'QIT_SELF_TEST' ) === 'remote_test' ) {
			$remote_test_runner = App::make( RemoteTestRunner::class );

			return $remote_test_runner->execute(
				$this,
				'compatibility',
				$remote_test_runner->get_options_to_send_for_schema( 'compatibility' ),
				$input,
				$output
			);
		}

		if ( empty( $input->getOption( 'test-package' ) ) ) {
			$input->setOption( 'test-package', [ 'woocommerce/compatibility-smoke-tests:latest' ] );
		}

		return parent::doExecute( $input, $output );
	}
}
