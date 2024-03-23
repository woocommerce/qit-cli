<?php

namespace QIT_CLI\Commands\Partner;

use QIT_CLI\Auth;
use QIT_CLI\Cache;
use QIT_CLI\ManagerBackend;
use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use function QIT_CLI\get_manager_url;
use function QIT_CLI\get_wccom_url;
use function QIT_CLI\validate_authentication;

class AddPartner extends Command {
	protected static $defaultName = 'partner:add'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/** @var Auth $auth */
	protected $auth;

	/** @var WooExtensionsList $woo_extensions_list */
	protected $woo_extensions_list;

	/** @var ManagerBackend $manager_backend */
	protected $manager_backend;

	/** @var Cache $cache */
	protected $cache;

	public function __construct( ManagerBackend $manager_backend, Cache $cache, Auth $auth, WooExtensionsList $woo_extensions_list ) {
		$this->manager_backend     = $manager_backend;
		$this->auth                = $auth;
		$this->woo_extensions_list = $woo_extensions_list;
		$this->cache               = $cache;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setDescription( 'Configure a new WCCOM Marketplace Partner that the QIT CLI can connect to.' )
			->setHelp( sprintf( "Configure the QIT CLI to be able to interact with %s on behalf of a given partner.\nAuthenticating documentation: https://woocommerce.github.io/qit-documentation/#/authenticating", get_wccom_url() ) )
			->addOption( 'user', 'u', InputOption::VALUE_OPTIONAL, '(Optional) Woo.com Partner Developer username.' )
			->addOption( 'qit_token', 't', InputOption::VALUE_OPTIONAL, '(Optional) Woo.com Partner Developer QIT Token.' )
			->addOption( 'application_password', 'p', InputOption::VALUE_OPTIONAL, '(DEPRECATED) This has been renamed to "QIT Token" and will be removed. A regular application password will not work.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		// User.
		if ( ! empty( $input->getOption( 'user' ) ) ) {
			$user = $input->getOption( 'user' );
		} else {
			$question = new Question( "<question>What's the username/email of the Partner account? </question> " );
			$user     = $this->getHelper( 'question' )->ask( $input, $output, $question );
		}

		$qit_token = null;

		// (Deprecated) Application password.
		if ( ! empty( $input->getOption( 'application_password' ) ) ) {
			$output->writeln( '<comment>"Application Password" has been renamed to "QIT Token" and will be removed in the future.</comment>' );
			$qit_token = $input->getOption( 'application_password' );
		}

		// Or QIT Token.
		if ( ! empty( $input->getOption( 'qit_token' ) ) ) {
			$qit_token = $input->getOption( 'qit_token' );
		}

		// Ask for QIT Token.
		if ( is_null( $qit_token ) ) {
			$authorize_url = $this->get_authorize_url( $output );
			$wccom_url     = get_wccom_url();

			$output->writeln( <<<TEXT

If you don't have a QIT Token yet, please follow these steps to generate one.

1. Login as "$user" in $wccom_url
2. Go to $authorize_url
3. Authorize the connection, copy the generated QIT Token and paste it here.

Note: The input is protected, so you won't be able to see it on your terminal.

TEXT
			);

			$question = new Question( "<question>Please paste the QIT Token here and press 'Enter'</question> " );
			$question->setHidden( true );
			$question->setHiddenFallback( false );
			$question->setMaxAttempts( 1 );

			$question->setValidator( function ( $qit_token ) {
				if ( empty( $qit_token ) ) {
					throw new \RuntimeException( 'Invalid QIT Token. Questions? https://woocommerce.github.io/qit-documentation/#/authenticating' );
				}

				if ( ! preg_match( '#^[a-z0-9 ]+$#i', $qit_token ) ) {
					throw new \RuntimeException( 'A QIT Token should consist of alpha-numeric characters and spaces. Questions? https://woocommerce.github.io/qit-documentation/#/authenticating' );
				}

				return $qit_token;
			} );

			$question->setNormalizer( function ( $qit_token ) {
				return str_replace( ' ', '', $qit_token );
			} );

			$qit_token = $this->getHelper( 'question' )->ask( $input, $output, $question );
		}

		$manager_url = get_manager_url();

		$user = strtolower( $user );

		if ( ! filter_var( $user, FILTER_VALIDATE_EMAIL ) && ! preg_match( '#^[a-z0-9_-]{1,70}$#i', $user ) ) {
			throw new \InvalidArgumentException( 'The username must be either a valid e-mail, or contain only letters, numbers, underscores or dashes. Questions? https://woocommerce.github.io/qit-documentation/#/authenticating' );
		}

		// Remove any non-alphanumeric characters from the username.
		$user_environment = preg_replace( '#[^a-z0-9_-]#i', '', $user );

		// Validate credentials.
		$output->writeln( sprintf( 'Validating your QIT Token with %s...', get_wccom_url() ) );
		$output->writeln( 'Please wait patiently, depending on how many extensions you have, this can take a while.' );
		try {
			validate_authentication( $user, $qit_token );
		} catch(\Exception $e) {
			$output->writeln( sprintf( '<error>Could not authenticate to %s using the provided username and QIT Token.</error>', get_wccom_url() ) );
			$output->writeln( 'Having trouble pasting? You can also run this command with the --qit_token=<your-token-here>.' );
			$output->writeln( 'More info: https://woocommerce.github.io/qit-documentation/#/authenticating' );
			return Command::FAILURE;
		}
		$output->writeln( '<fg=green>Validated successfully.</>' );

		$easter_egg = <<<TEXT
 __          __          _ 
 \ \        / /         | |
  \ \  /\  / /__   ___  | |
   \ \/  \/ / _ \ / _ \ | |
    \  /\  / (_) | (_)  |_|
     \/  \/ \___/ \___/ (_)

TEXT;

		$output->writeln( $easter_egg );

		if ( $this->manager_backend->partner_exists( $user_environment ) ) {
			$this->manager_backend->remove_partner( $user_environment );
		}

		$this->manager_backend->add_partner( $user_environment );

		$this->auth->set_partner_auth( $user, $qit_token );
		$this->cache->set( 'manager_url', $manager_url, - 1 );
		$this->woo_extensions_list->fetch_woo_extensions_available();

		$output->writeln( sprintf( "Cache file written to: %s. Keep this file safe, as it contains your QIT Token.\n", $this->cache->get_cache_file_path() ) );
		$output->writeln( "Treat this file as you would treat your SSH keys. For more tips on hardening security, check the README of the QIT CLI.\n" );
		$output->writeln( '<fg=green>Initialization complete! You can now start using the QIT CLI!</>' );

		$io = new SymfonyStyle( $input, $output );

		$io->section( <<<SECTION
Getting Started:

Documentation: https://woocommerce.github.io/qit-documentation/
Running Tests: https://woocommerce.github.io/qit-documentation/#/cli/running-tests
SECTION
		);

		$io->writeln( '<comment>Examples:</comment>' );

		$io->writeln( "\n<info>Running a Security Test:</info>" );
		$io->writeln( './qit run:security my-extension-slug' );

		$io->writeln( "\n<info>Running a Security Test against a development build:</info>" );
		$io->writeln( './qit run:security my-extension-slug --zip=plugin.zip' );

		$io->writeln( "\n<info>Running a WooCommerce Core E2E test with configurable options using a development build:</info>" );
		$io->writeln(<<<COMMAND
./qit run:woo-e2e my-extension-slug \
	--woocommerce_version=rc \
	--wordpress_version=rc \
	--php_version=8.2 \
	--optional_features=hpos \
	--additional_woo_plugins=woocommerce-shipping \
	--additional_wordpress_plugins=hello-dolly \
	--zip

COMMAND
		);

		return Command::SUCCESS;
	}

	protected function get_authorize_url( OutputInterface $output ): string {
		$result = @file_get_contents( get_wccom_url() . '/wp-json' );

		/*
		 * A random, hard-coded UUID. This is used to identify the QIT CLI as the application
		 * that's requesting an application password. This value is public and not a secret.
		 */
		$qit_cli_app_id = 'bf17c635-6e7f-4d04-9072-c9d31849ddf8';

		$json = json_decode( $result, true );

		if ( is_array( $json ) && isset( $json['authentication']['application-passwords']['endpoints']['authorization'] ) ) {
			$base_url = $json['authentication']['application-passwords']['endpoints']['authorization'];
		} else {
			if ( $output->isVerbose() ) {
				$output->writeln( sprintf( "Could not get Authorization URL from %s. Using fallback...\n", get_wccom_url() ) );
			}
			$base_url = get_wccom_url() . '/wp-admin/authorize-application.php';
		}

		$separator = stripos( $base_url, '?' ) === false ? '?' : '&';

		// Generate a unique app name based on the current timestamp to avoid collision.
		$app_name = rawurlencode( 'Woo Quality Insights Toolkit CLI (QIT) ' . time() );

		return $base_url . "{$separator}app_name=$app_name&app_id=$qit_cli_app_id";
	}
}
