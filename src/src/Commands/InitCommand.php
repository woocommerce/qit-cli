<?php

namespace QIT_CLI\Commands;

use QIT_CLI\Auth;
use QIT_CLI\Config;
use QIT_CLI\Environment;
use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use function QIT_CLI\get_wccom_url;
use function QIT_CLI\open_in_browser;
use function QIT_CLI\validate_authentication;

class InitCommand extends Command {
	protected static $defaultName = 'init'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/** @var Config $config */
	protected $config;

	/** @var Auth $auth */
	protected $auth;

	/** @var WooExtensionsList $woo_extensions_list */
	protected $woo_extensions_list;

	/** @var Environment $environment */
	protected $environment;

	public function __construct( Config $config, Environment $environment, Auth $auth, WooExtensionsList $woo_extensions_list ) {
		$this->config              = $config;
		$this->environment         = $environment;
		$this->auth                = $auth;
		$this->woo_extensions_list = $woo_extensions_list;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setDescription( 'Initialize the QIT CLI.' )
			->setHelp( sprintf( 'Initialize and authenticate the QIT CLI against %s. This command needs to be executed only once. It can either run interactively to guide you through the generation of the application password, or non-interactively (such as in CI) by passing the user and application password as parameters of this command.', get_wccom_url() ) )
			->addOption( 'user', 'u', InputOption::VALUE_OPTIONAL, '(Optional) WooCommerce.com user with "edit" permission to the extensions that you want to test.' )
			->addOption( 'application_password', 'p', InputOption::VALUE_OPTIONAL, '(Optional) WooCommerce.com application password.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		// Make sure people understands this only needs to be done once.
		if ( $this->environment->environment_exists( Environment::$allowed_environments['vendor'] ) ) {
			if ( ! $this->getHelper( 'question' )->ask( $input, $output, new ConfirmationQuestion( '<question>QIT CLI has already been initialized. Continue? (y/n) </question>', false ) ) ) {
				return Command::SUCCESS;
			}

			$this->environment->delete_environment( 'vendor' );
		}

		// Non-interactive early bail: User and application password passed as arguments.
		if ( ! empty( $input->getOption( 'user' ) ) && ! empty( $input->getOption( 'application_password' ) ) ) {
			try {
				validate_authentication( $input->getOption( 'user' ), $input->getOption( 'application_password' ) );

				$this->switch_to_vendor_environment( $output );

				$this->auth->set_auth_app_pass( $input->getOption( 'user' ), $input->getOption( 'application_password' ) );
				$this->woo_extensions_list->fetch_woo_extensions_available();

				return Command::SUCCESS;
			} catch ( \Exception $e ) {
				$output->writeln( '<error>Could not authenticate to woocommerce.com using the provided username and application password.</error>' );

				return Command::FAILURE;
			}
		}

		// If someone wants to run this non-interactively, they need to provide the data.
		if ( ! $input->isInteractive() ) {
			$output->writeln( '<error>To run this command with --no-interaction, please provide the --user and --application-password parameters.</error>' );

			return Command::FAILURE;
		}

		// Ask for WooCommerce username.
		$output->writeln( "\n<info>You will need an account at WooCommerce.com with permission to 'edit' the extensions that you want to test. You can check what's your username here: https://woocommerce.com/wp-admin/profile.php</info>\n" );
		$question = new Question( "<question>What's your WooCommerce.com username? </question> " );
		$user     = $this->getHelper( 'question' )->ask( $input, $output, $question );

		$authorize_url = $this->get_authorize_url( $output );

		/*
		 * We try to open the URL on the default browser. If that doesn't work for any reason out of our control,
		 * no error output should be generated, and the URL will still be in the text to be opened manually.
		 */
		open_in_browser( $authorize_url );

		$output->writeln( <<<TEXT

1. Go to $authorize_url
2. Authorize the connection, copy the Application Password that will be generated and paste it here.
Note: The input is protected, so you won't be able to see it on your terminal.

TEXT
		);

		$question = new Question( "<question>Please paste the Application Password here and press 'Enter'</question> " );
		$question->setHidden( true );
		$question->setHiddenFallback( false );
		$question->setMaxAttempts( 1 );

		$question->setValidator( function ( $application_password ) use ( $output, $user ) {
			if ( empty( $application_password ) ) {
				throw new \RuntimeException( 'Invalid Application Password.' );
			}

			if ( ! preg_match( '#^[a-z0-9 ]+$#i', $application_password ) ) {
				throw new \RuntimeException( 'An application password should consist of alpha-numeric characters and spaces.' );
			}

			$output->writeln( 'Validating your application password with woocommerce.com...' );
			validate_authentication( $user, $application_password );
			$output->writeln( '<fg=green>Validated successfully.</>' );

			return $application_password;
		} );

		$application_password = $this->getHelper( 'question' )->ask( $input, $output, $question );

		$this->switch_to_vendor_environment( $output );

		$this->auth->set_auth_app_pass( $user, $application_password );
		$this->woo_extensions_list->fetch_woo_extensions_available();

		$output->writeln( sprintf( "CD Config file written to: %s. Keep this file safe, as it contains your Application Password.\n", $this->environment->get_config_filepath() ) );
		$output->writeln( '<fg=green>Initialization complete! You can now start using the QIT CLI!</>' );

		return Command::SUCCESS;
	}

	protected function switch_to_vendor_environment( OutputInterface $output ) {
		if ( $this->environment->is_development_mode() && ! in_array( $this->environment->get_current_environment(), [
			Environment::$allowed_environments['vendor'],
			Environment::$allowed_environments['undefined'],
		], true ) ) {
			$output->writeln( "<info>[Dev Mode] Switching to environment 'vendor'.</info>" );
		}

		$this->environment->create_environment( 'vendor' );
		$this->environment->switch_to_environment( 'vendor' );
	}

	protected function get_authorize_url( OutputInterface $output ): string {
		$result = file_get_contents( get_wccom_url() . '/wp-json' );

		/*
		 * A random, hard-coded UUID. This is used to identify the QIT CLI as the application
		 * that's requesting an application password. This value is public and not a secret.
		 */
		$qit_cli_app_id = 'bf17c635-6e7f-4d04-9072-c9d31849ddf8';

		$json = json_decode( $result, true );

		if ( is_array( $json ) && isset( $json['authentication']['application-passwords']['endpoints']['authorization'] ) ) {
			$base_url = $json['authentication']['application-passwords']['endpoints']['authorization'];
		} else {
			$output->writeln( "Could not get Authorization URL from woocommerce.com. Using fallback...\n" );
			$base_url = get_wccom_url() . '/wp-admin/authorize-application.php';
		}

		$separator = stripos( $base_url, '?' ) === false ? '?' : '&';

		return $base_url . "{$separator}app_name=WooCommerce+Quality+Insights+Toolkit+CLI+(QIT+CLI)&app_id=$qit_cli_app_id";
	}
}
