<?php

namespace QIT_CLI\Commands;

use QIT_CLI\QITInput;
use QIT_CLI\RequestBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;

class FeedbackCommand extends QITCommand {
	protected static $defaultName = 'feedback'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Send feedback to the QIT team.' )
			->addArgument( 'message', InputArgument::REQUIRED, 'Your feedback message.' );
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		$message = $input->getArgument( 'message' );

		if ( strlen( $message ) > 2000 ) {
			$output->writeln( '<error>Feedback message exceeds 2000 characters.</error>' );
			$output->writeln( '<comment>For detailed reports, please open an issue at https://github.com/woocommerce/qit-cli/issues</comment>' );

			return Command::FAILURE;
		}

		try {
			( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/feedback' ) )
				->with_method( 'POST' )
				->with_post_body( [
					'message'     => $message,
					'php_version' => phpversion(),
					'os'          => PHP_OS_FAMILY,
				] )
				->request();

			$output->writeln( '<info>Feedback sent. Thank you!</info>' );

			return Command::SUCCESS;
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

			return Command::FAILURE;
		}
	}
}
