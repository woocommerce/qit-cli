<?php

namespace QIT_CLI\Commands;

use QIT_CLI\Environment\Docker;
use QIT_CLI\Tunnel\NgrokConfig;
use QIT_CLI\Tunnel\NgrokRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class NgrokCommand extends Command {
	/** @var NgrokConfig $ngrok_config */
	protected $ngrok_config;

	/** @var NgrokRunner $ngrok_runner */
	protected $ngrok_runner;

	/** @var Docker $docker */
	protected $docker;

	public static $defaultName = 'ngrok'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	public function __construct( NgrokConfig $ngrok_config, NgrokRunner $ngrok_runner, Docker $docker ) {
		$this->ngrok_config = $ngrok_config;
		$this->ngrok_runner = $ngrok_runner;
		$this->docker       = $docker;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setDescription( 'Sets up your Ngrok configuration for enabling live site URLs.' )
			->setHelp( <<<TXT
This command sets up your Ngrok configuration for enabling live site URLs.

Some plugins, especially payment methods plugins and SaaS might require a live site and a SSL connection to work properly.

1. Register for a free account at ngrok.com.
2. Grab your auth token and paste it here when requested.
3. Create a free domain in Ngrok.com and paste it here when requested.
4. Now when running tests that support Ngrok, just pass the "--ngrok" parameter.
TXT
			)
			->addOption( 'token', 't', InputOption::VALUE_OPTIONAL, 'The Ngrok token to use.' )
			->addOption( 'domain', 'd', InputOption::VALUE_OPTIONAL, 'The Ngrok domain to use.' )
			->addOption( 'force', 'f', InputOption::VALUE_NONE, 'Force the setup, even if Ngrok is already configured.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		if ( $input->getOption( 'force' ) ) {
			return $this->ngrok_setup_flow( $input, $output );
		}

		try {
			$this->ngrok_config->get_ngrok_config();

			return $this->ngrok_already_configured( $input, $output );
		} catch ( \Exception $e ) {
			return $this->ngrok_setup_flow( $input, $output );
		}
	}

	protected function ngrok_already_configured( InputInterface $input, OutputInterface $output ): int {
		if ( ! $this->getHelper( 'question' )->ask( $input, $output, new ConfirmationQuestion( '<question>Ngrok is already configured. Continue? (y/n) </question>', false ) ) ) {
			return Command::SUCCESS;
		}

		return $this->ngrok_setup_flow( $input, $output );
	}

	protected function ngrok_setup_flow( InputInterface $input, OutputInterface $output ): int {
		$io = new SymfonyStyle( $input, $output );

		$io->section( 'Configure Ngrok' );
		$io->writeln( 'Some extensions such as Payment Gateways and SaaS might require a live site URL to work properly.' );
		$io->writeln( 'To run tests with a live site URL, you must configure Ngrok. It\'s really easy, takes no longer than 2 minutes.' );
		$io->newLine();
		$io->writeln( <<<'TXT'
1. Register for a free account at ngrok.com.
2. Grab your auth token and paste it here when requested.
3. Go to "Domains", create a free domain and paste it here when requested.
4. Now, when running tests that support Ngrok, just pass the "--ngrok" parameter to test extensions that require a live site URL.

For detailed instructions, refer to https://qit.woo.com/docs/cli/ngrok
TXT
		);
		$io->newLine();

		/** @var \Symfony\Component\Console\Helper\QuestionHelper $question */
		$question = $this->getHelper( 'question' );
		$token    = $input->getOption( 'token' );

		if ( is_null( $token ) ) {
			$token_question = new Question( '<info>What is your Ngrok token? (Input hidden)</info> ' );
			$token_question->setHidden( true );
			$token = $question->ask( $input, $output, $token_question );
		}

		$domain = $input->getOption( 'domain' );

		if ( is_null( $domain ) ) {
			$domain_question = new Question( '<info>What is your Ngrok domain?</info> ' );
			$domain_question->setValidator( function ( $answer ) {
				if ( substr( $answer, 0, 4 ) === 'http' ) {
					throw new \RuntimeException( 'Please provide the domain without the protocol.' );
				}

				return $answer;
			} );
			$domain = $question->ask( $input, $output, $domain_question );
		}

		$io->newLine();
		$io->section( 'Testing your Ngrok connection...' );

		try {
			$webhook_url = $this->ngrok_runner->test_ngrok_connection( $token, $domain );
		} catch ( \Exception $e ) {
			$io->error( 'Could not connect to Ngrok. Please check the error message and try again.' );
			$io->writeln( '<error>' . trim( $e->getMessage() ) . '</error>' );

			return Command::FAILURE;
		}
		$this->ngrok_config->set_ngrok_config( $token, $domain );

		$io->success( 'Ngrok is now configured. You can now use the "--ngrok" parameter to run tests using a live URL.' );

		return Command::SUCCESS;
	}
}
