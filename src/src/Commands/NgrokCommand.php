<?php

namespace QIT_CLI\Commands;

use QIT_CLI\Environment\Docker;
use QIT_CLI\Ngrok\NgrokConfig;
use QIT_CLI\Ngrok\NgrokRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class NgrokCommand extends Command {
	/** @var NgrokConfig $ngrok_config */
	protected $ngrok_config;

	/** @var NgrokRunner $ngrok_runner */
	protected $ngrok_runner;

	/** @var Docker $docker */
	protected $docker;

	public static $defaultName = 'ngrok:setup'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

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
TXT)
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
		/** @var \Symfony\Component\Console\Helper\QuestionHelper $question */
		$question = $this->getHelper( 'question' );

		$token = $input->getOption( 'token' );

		if ( is_null( $token ) ) {
			$token = $question->ask( $input, $output, new Question( 'What is your Ngrok token? ' ) );
		}

		$domain = $input->getOption( 'domain' );

		if ( is_null( $domain ) ) {
			$domain = $question->ask( $input, $output, new Question( 'What is your Ngrok domain? ' ) );
		}

		$webhook_url = $this->ngrok_runner->test_ngrok_connection( $token, $domain );
		$this->ngrok_config->set_ngrok_config( $token, $domain );

		$output->writeln( "Ngrok is now configured with the following public URL: $webhook_url" );

		return Command::SUCCESS;
	}
}
