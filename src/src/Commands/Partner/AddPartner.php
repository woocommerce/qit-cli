<?php

namespace QIT_CLI\Commands\Partner;

use QIT_CLI\Auth;
use QIT_CLI\Config;
use QIT_CLI\Environment;
use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use function QIT_CLI\get_wccom_url;
use function QIT_CLI\open_in_browser;
use function QIT_CLI\validate_authentication;

class AddPartner extends Command {
	protected static $defaultName = 'partner:add'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

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
			->setDescription( 'Configure a new WooCommerce.com Partner that the QIT CLI can connect to.' )
			->setHelp( sprintf( 'Configure the QIT CLI to be able to interact with %s on behalf of a given partner.', get_wccom_url() ) )
			->addOption( 'user', 'u', InputOption::VALUE_OPTIONAL, '(Optional) WooCommerce.com user with "edit" permission to the extensions that you want to test.' )
			->addOption( 'application_password', 'p', InputOption::VALUE_OPTIONAL, '(Optional) WooCommerce.com application password.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		// User.
		if ( ! empty( $input->getOption( 'user' ) ) ) {
			$user = $input->getOption( 'user' );
		} else {
			$output->writeln( "\n<info>You will need an account at WooCommerce.com with permission to 'edit' the extensions that you want to test. You can check what's your username here: https://woocommerce.com/wp-admin/profile.php</info>\n" );
			$question = new Question( "<question>What's the WooCommerce.com username of the Partner you want to add? </question> " );
			$user     = $this->getHelper( 'question' )->ask( $input, $output, $question );
		}

		$user = strtolower( $user );

		if ( ! preg_match( '#[a-z0-9]#i', $user ) ) {
			throw new \InvalidArgumentException( 'The WooCommerce.com username must contain only letters and numbers.' );
		}

		// Application Password.
		if ( ! empty( $input->getOption( 'application_password' ) ) ) {
			$application_password = $input->getOption( 'application_password' );
		} else {
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

				return $application_password;
			} );

			$application_password = $this->getHelper( 'question' )->ask( $input, $output, $question );
		}

		// Validate credentials.
		$output->writeln( 'Validating your application password with woocommerce.com...' );
		validate_authentication( $user, $application_password );
		$output->writeln( '<fg=green>Validated successfully.</>' );

		if ( $this->environment->environment_exists( "partner-$user" ) ) {
			$this->environment->remove_environment( "partner-$user" );
		}

		$this->environment->create_environment( "partner-$user" );
		$this->environment->switch_to_environment( "partner-$user" );

		$this->auth->set_auth_app_pass( $user, $application_password );
		$this->woo_extensions_list->fetch_woo_extensions_available();

		$output->writeln( sprintf( "Config file written to: %s. Keep this file safe, as it contains your Application Password.\n", $this->environment->get_config_filepath() ) );
		$output->writeln( "Treat this file as you would treat your SSH keys. For more tips on hardening security, check the README of the QIT CLI.\n" );
		$output->writeln( '<fg=green>Initialization complete! You can now start using the QIT CLI!</>' );

		return Command::SUCCESS;
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
