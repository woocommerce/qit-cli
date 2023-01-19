<?php

namespace QIT_CLI\Commands;

use QIT_CLI\Auth;
use QIT_CLI\Config;
use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class InitA8CCommand extends Command {
	protected static $defaultName = 'init-a8c'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/** @var Config $config */
	protected $config;

	/** @var Auth $auth */
	protected $auth;

	/** @var WooExtensionsList $woo_extensions_list */
	protected $woo_extensions_list;

	public function __construct( Config $config, Auth $auth, WooExtensionsList $woo_extensions_list ) {
		$this->config              = $config;
		$this->auth                = $auth;
		$this->woo_extensions_list = $woo_extensions_list;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setDescription( 'Initialize the QIT CLI as an A8C.' )
			->setHidden( false ) // Todo: Hide this once the QIT CLI goes public.
			->addOption( 'cd_secret', 's', InputOption::VALUE_OPTIONAL, '(Optional) The CD Secret to use.', '' )
			->addOption( 'cd_manager_url', 'u', InputOption::VALUE_OPTIONAL, '(Optional) The CD Manager URL to use. Eg: http://cd_manager.loc (local), or CD Manager Staging/Prod.', '' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$cd_secret      = $input->getOption( 'cd_secret' );
		$cd_manager_url = $input->getOption( 'cd_manager_url' );

		if ( empty( $cd_manager_url ) ) {
			$question = new ChoiceQuestion(
				'Please choose which CD Manager to use: 1 or 2.',
				[
					1 => '(Recommended) https://compatibilitydashboard.wpcomstaging.com',
					2 => 'Other (Staging, Localhost, etc)',
				],
				1
			);

			$cd_manager_url = ltrim( $this->getHelper( 'question' )->ask( $input, $output, $question ), '(Recommended) ' );

			if ( $cd_manager_url === 'Other (Staging, Localhost, etc)' ) {
				$question = new Question( "<question>What's the URL of the CD Manager you'd like to use? (Eg: http://cd_manager.loc:8081)</question> " );
				$question->setMaxAttempts( 3 );
				$question->setValidator( function ( $cd_manager_url ) {
					// Remove underscores before validating because they are not allowed in a hostname. We should change cd_manager.loc to something else upstream.
					if ( filter_var( str_replace( '_', '-', $cd_manager_url ), FILTER_VALIDATE_URL ) === false ) {
						throw new \UnexpectedValueException( 'Invalid URL.' );
					}

					return $cd_manager_url;
				} );

				$cd_manager_url = $this->getHelper( 'question' )->ask( $input, $output, $question );
			}
		}

		$cd_manager_url = rtrim( $cd_manager_url, '/' );

		if ( $cd_manager_url === 'https://compatibilitydashboard.wpcomstaging.com' ) {
			$secret_store_id = '(Secret ID: 9769)';
		} else {
			$secret_store_id = '';

		}

		if ( empty( $cd_secret ) ) {
			$question = new Question( "<question>What's the CD Secret of that CD Manager? $secret_store_id</question> " );
			$question->setHidden( true );
			$question->setHiddenFallback( false );
			$cd_secret = $this->getHelper( 'question' )->ask( $input, $output, $question );
		}

		$this->auth->set_cd_secret( $cd_secret );
		$this->config->set_cache( 'cd_manager_url', $cd_manager_url, - 1 );

		$output->writeln( "Validating your CD Secret against $cd_manager_url..." );
		try {
			$this->woo_extensions_list->fetch_woo_extensions_available();
		} catch ( \Exception $e ) {
			$this->auth->delete_cd_secret();
			$this->config->delete_cache( 'cd_manager_url' );
			$output->writeln( sprintf( '<error>We could not authenticate to %s using the provided CD Secret.</error>', escapeshellarg( $cd_manager_url ) ) );
			$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

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
		$output->writeln( '<comment>[Developer Setup] CD Secret and CD Manager URL saved.</comment>' );

		return Command::SUCCESS;
	}
}
