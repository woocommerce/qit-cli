<?php

namespace QIT_CLI;

use QIT_CLI\Exceptions\DoingAutocompleteException;
use QIT_CLI\IO\Output;
use Symfony\Component\Console\Output\OutputInterface;

class ManagerSync {
	/** @var Config $config */
	protected $config;

	/** @var Auth $auth */
	protected $auth;

	/** @var Environment $environment */
	protected $environment;

	/** @var OutputInterface $output */
	protected $output;

	/** @var string $sync_cache_key */
	public static $sync_cache_key;

	public function __construct( Config $config, Environment $environment, Auth $auth ) {
		$this->config           = $config;
		$this->auth             = $auth;
		$this->environment      = $environment;
		$this->output           = App::make( Output::class );
		static::$sync_cache_key = sprintf( 'manager_sync_%s', md5( get_manager_url() ) );
	}

	public function maybe_sync() {
		$manager_sync = $this->config->get_cache( static::$sync_cache_key );

		if ( ! is_null( $manager_sync ) ) {
			return;
		}

		if ( $this->output->isVerbose() ) {
			App::make( Output::class )->write( '[Info] Syncing with Manager... ' );
		}

		$start = microtime( true );

		try {
			$manager_sync = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/sync' ) )
				->request();
		} catch ( DoingAutocompleteException $e ) {
			return;
		} catch ( \Exception $e ) {
			$this->output->writeln( sprintf( '<error>Failed to sync with Manager (%s): %s</error>', get_manager_url(), $e->getMessage() ) );

			throw new \RuntimeException();
		}

		if ( $this->output->isVerbose() ) {
			App::make( Output::class )->writeln( sprintf( 'Done in %s seconds.', number_format( microtime( true ) - $start, 2 ) ) );
		}

		$manager_sync = json_decode( $manager_sync, true );

		if ( ! is_array( $manager_sync ) || empty( $manager_sync ) ) {
			$this->output->writeln( sprintf( '<error>Failed to sync with Manager (%s). Not a valid JSON.</error>', get_manager_url() ) );

			throw new \RuntimeException();
		}

		if ( $this->output->isVerbose() ) {
			App::make( Output::class )->writeln( '[Info] New sync with Manager done.' );
		}

		// 1 hour.
		$this->config->set_cache( static::$sync_cache_key, $manager_sync, 3600 );
	}
}