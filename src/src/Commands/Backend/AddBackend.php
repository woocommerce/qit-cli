<?php

namespace QIT_CLI\Commands\Backend;

use QIT_CLI\Auth;
use QIT_CLI\Cache;
use QIT_CLI\ManagerBackend;
use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class AddBackend extends Command {
	protected static $defaultName = 'backend:add'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

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
			->setDescription( 'Configure a new environment that the QIT CLI can connect to.' )
			->addOption( 'environment', 'e', InputOption::VALUE_OPTIONAL, '(Optional) The environment to add.', '' )
			->addOption( 'qit_secret', 's', InputOption::VALUE_OPTIONAL, '(Optional) The QIT Secret to use.', '' )
			->addOption( 'manager_url', 'u', InputOption::VALUE_OPTIONAL, '(Optional) The Manager URL to use. Eg: http://manager.loc (local), or Manager Staging/Prod URLs.', '' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$qit_secret      = $input->getOption( 'qit_secret' );
		$manager_url     = $input->getOption( 'manager_url' );
		$manager_backend = $input->getOption( 'environment' );

		if ( empty( $manager_backend ) && ( ! empty( $manager_url ) || ! empty( $qit_secret ) ) ) {
			throw new \UnexpectedValueException( 'When passing Manager URL/QIT Secret as a parameter, you need to also provide a --environment.' );
		}

		$secret_store_id = '';

		if ( empty( $manager_url ) ) {
			$question = new ChoiceQuestion(
				'Please choose an environment to add.',
				[
					1 => 'Production (Default)',
					2 => 'Staging (Only for QIT development)',
					3 => 'Local',
				],
				1
			);

			switch ( $this->getHelper( 'question' )->ask( $input, $output, $question ) ) {
				case 'Production (Default)':
					$manager_url     = 'https://qit.woo.com';
					$secret_store_id = '(Secret ID: 10523)';
					$manager_backend = ManagerBackend::$allowed_manager_backends['production'];
					break;
				case 'Staging (Only for QIT development)':
					$manager_url     = 'https://stagingcompatibilitydashboard.wpcomstaging.com';
					$secret_store_id = '(Secret ID: 10522)';
					$manager_backend = ManagerBackend::$allowed_manager_backends['staging'];
					break;
				case 'Local':
					$question = new Question( "<question>What's the URL of the Manager you'd like to use? (Default: http://qit.test:8081)</question> ", 'http://qit.test:8081' );
					$question->setMaxAttempts( 3 );
					$question->setValidator( function ( $manager_url ) {
						if ( filter_var( $manager_url, FILTER_VALIDATE_URL ) === false ) {
							throw new \UnexpectedValueException( 'Invalid URL.' );
						}

						return $manager_url;
					} );

					$manager_url     = $this->getHelper( 'question' )->ask( $input, $output, $question );
					$manager_backend = ManagerBackend::$allowed_manager_backends['local'];
					break;
			}
		}

		if ( $this->manager_backend->manager_backend_exists( $manager_backend ) ) {
			if ( ! $this->getHelper( 'question' )->ask( $input, $output, new ConfirmationQuestion( "<question>ManagerBackend '$manager_backend' is already configured. Continue? (y/n) </question>", false ) ) ) {
				return Command::SUCCESS;
			}
		}

		$manager_url = rtrim( $manager_url, '/' );

		if ( empty( $qit_secret ) ) {
			$question = new Question( "<question>What's the Manager Secret? $secret_store_id</question> " );
			$question->setHidden( true );
			$question->setHiddenFallback( false );
			$qit_secret = $this->getHelper( 'question' )->ask( $input, $output, $question );
		}

		try {
			$this->manager_backend->add_manager_backend( $manager_backend );
		} catch ( \Exception $e ) {
			$output->writeln( "<error>{$e->getMessage()}</error>" );

			return Command::FAILURE;
		}

		$this->auth->set_manager_secret( $qit_secret );
		$this->cache->set( 'manager_url', $manager_url, - 1 );

		$output->writeln( "Validating your Manager Secret against $manager_url..." );
		try {
			$this->woo_extensions_list->fetch_woo_extensions_available();
			if ( empty( $this->woo_extensions_list->get_woo_extension_list() ) ) {
				throw new \RuntimeException();
			}
		} catch ( \Exception $e ) {
			$this->auth->delete_manager_secret();
			$this->cache->delete( 'manager_url' );
			$output->writeln( sprintf( '<error>We could not authenticate to %s using the provided Manager Secret.</error>', escapeshellarg( $manager_url ) ) );

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
		$output->writeln( "<comment>New environment '$manager_backend' has been successfully setup. The CLI has switched to it.</comment>" );

		return Command::SUCCESS;
	}
}
