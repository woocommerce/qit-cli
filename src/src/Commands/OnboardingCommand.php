<?php

namespace QIT_CLI\Commands;

use QIT_CLI\Commands\Environment\AddEnvironment;
use QIT_CLI\Commands\Partner\AddPartner;
use QIT_CLI\Config;
use QIT_CLI\RequestBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Terminal;
use function QIT_CLI\get_manager_url;

class OnboardingCommand extends Command {
	protected static $defaultName = 'onboarding'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure() {
		$this
			->setDescription( 'Runs the QIT CLI onboarding.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$io = new SymfonyStyle( $input, $output );
		$io->title( 'Woo Quality Insights Toolkit CLI' );

		$io->section( <<<SECTION
Documentation: https://woocommerce.github.io/qit-documentation/
Running Tests: https://woocommerce.github.io/qit-documentation/#/cli/running-tests
SECTION
		);

		$io->writeln( '<comment>Examples:</comment>' );

		$io->writeln( "\n<info>Running a Security Test:</info>" );
		$io->writeln( './qit run:security my-extension-slug' );

		$io->writeln( "\n<info>Running a Security Test against a development build:</info>" );
		$io->writeln( './qit run:security my-extension-slug --zip=my-extension-slug.zip' );

		$io->writeln( "\n<info>Running a WooCommerce Core E2E test with configurable options against a dev build:</info>" );
		$io->writeln(<<<COMMAND
./qit run:e2e my-extension-slug \
	--woocommerce_version=7.6.0-rc.2 \
	--php_version=8.2 \
	--wordpress_version=6.2 \
	--optional_features=hpos \
	--additional_woo_plugins=woocommerce-shipping \
	--additional_wordpress_plugins=hello-dolly \
	--zip=my-extension-slug.zip

COMMAND
		);

		$io->writeln( sprintf( '<comment>%s</comment>', str_repeat( '-', ( new Terminal() )->getWidth() ) ) );

		$output->write( "\n<comment>Connecting to QIT servers... </comment>" );

		try {
			[ $is_proxied, $proxied_instructions ] = $this->is_proxied();
		} catch ( \Exception $e ) {
			$output->writeln( "<error>{$e->getMessage()}</error>" );

			return Command::FAILURE;
		}

		$output->write( "<fg=green>✔</>\n" );

		$question_helper = $this->getHelper( 'question' );

		if ( $is_proxied ) {
			$io->section( 'Onboarding as Automattician (Connected to Proxy)' );

			$question = new Question( $proxied_instructions );
			$question->setHidden( true );
			$answer = $question_helper->ask( $input, $output, $question );

			$command = $this->getApplication()->find( AddEnvironment::getDefaultName() );

			Config::set_development_mode( true );

			return $command->run( new ArrayInput( [
				'--environment' => 'production',
				'--manager_url' => 'https://compatibilitydashboard.wpcomstaging.com/',
				'--qit_secret'  => $answer,
			] ), $output );
		} else {
			$io->section( 'Onboarding as Partner of the Woo Marketplace' );
			$io->info('If you are an Automattic employee, please connect to the proxy and run the CLI again.');

			$command = $this->getApplication()->find( AddPartner::getDefaultName() );

			return $command->run( new ArrayInput( [] ), $output );
		}
	}

	/**
	 * @return array{bool, string} Whether it's a proxied request, and instructions if so.
	 */
	protected function is_proxied(): array {
		try {
			$json = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/proxied' ) )
				->with_method( 'POST' )
				->with_onboarding( true )
				->request();
		} catch ( \Exception $e ) {
			return [ false, '' ];
		}

		$data = json_decode( $json, true );

		if ( ! is_array( $data ) ) {
			throw new \UnexpectedValueException( 'Response is not a JSON.' );
		}

		foreach ( [ 'proxied', 'proxied_instructions' ] as $required ) {
			if ( ! array_key_exists( $required, $data ) ) {
				throw new \UnexpectedValueException( sprintf( 'Response is in an unexpected format. Missing "%s".', $required ) );
			}
		}

		return [ $data['proxied'] === 'yes', $data['proxied_instructions'] ];
	}
}
