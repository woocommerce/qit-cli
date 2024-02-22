<?php

namespace QIT_CLI;

use QIT_CLI\Exceptions\DoingAutocompleteException;
use QIT_CLI\Exceptions\NetworkErrorException;
use QIT_CLI\Exceptions\UpdateRequiredException;
use QIT_CLI\IO\Output;
use Symfony\Component\Console\Output\OutputInterface;

class ManagerSync {
	/** @var Auth $auth */
	protected $auth;

	/** @var Cache $cache */
	protected $cache;

	/** @var OutputInterface $output */
	protected $output;

	/** @var string $sync_cache_key */
	public $sync_cache_key;

	public function __construct( Cache $cache, Auth $auth ) {
		$this->auth           = $auth;
		$this->cache          = $cache;
		$this->output         = App::make( Output::class );
		$this->sync_cache_key = sprintf( 'manager_sync_v%s', App::getVar( 'CLI_VERSION' ) );
	}

	public function maybe_sync( bool $force_resync = false ): void {
		if ( $force_resync ) {
			$this->cache->delete( $this->sync_cache_key );
		}

		$manager_sync = $this->cache->get( $this->sync_cache_key );

		if ( ! is_null( $manager_sync ) ) {
			return;
		}

		if ( $this->output->isVerbose() ) {
			App::make( Output::class )->write( '[Info] Syncing with Manager... ' );
		}

		$start = microtime( true );

		try {
			$manager_sync = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/sync' ) )
				->with_retry( 2 )
				->with_method( 'POST' )
				->request();
		} catch ( DoingAutocompleteException $e ) {
			return;
		} catch ( NetworkErrorException $e ) {
			if ( Config::is_development_mode() ) {
				$this->output->writeln( sprintf( '<comment>[Dev Mode] Failed to contact Manager at URL %s.</comment>', get_manager_url() ) );
				$this->output->writeln( sprintf( '<comment>[Dev Mode] %s</comment>', $e->getMessage() ) );
				$this->output->writeln( sprintf( '<comment>[Dev Mode] Check that the Manager is running, and is able to respond to sync requests.</comment>' ) );
			}

			throw new NetworkErrorException();
		}

		if ( $this->output->isVerbose() ) {
			App::make( Output::class )->writeln( sprintf( 'Done in %s seconds.', number_format( microtime( true ) - $start, 2 ) ) );
		}

		$manager_sync = json_decode( $manager_sync, true );

		if ( ! is_array( $manager_sync ) || empty( $manager_sync ) ) {
			$this->output->writeln( sprintf( '<error>Failed to sync with Manager (%s). Not a valid JSON.</error>', get_manager_url() ) );

			throw new NetworkErrorException();
		}

		if ( $this->output->isVerbose() ) {
			App::make( Output::class )->writeln( '[Info] New sync with Manager done.' );
		}

		// 1 hour if we can connect to the Manager.
		$expiration = App::getVar( 'offline_mode' ) ? 0 : 3600;

		$this->cache->set( $this->sync_cache_key, $manager_sync, $expiration );
	}

	public function enforce_latest_version(): void {
		$current_version = App::getVar( 'CLI_VERSION' );

		// Do not check version on development build.
		if ( $current_version === '@QIT_CLI_VERSION@' ) {
			return;
		}

		$latest_version      = $this->cache->get_manager_sync_data( 'latest_cli_version' );
		$minimum_cli_version = $this->cache->get_manager_sync_data( 'minimum_cli_version' );

		if ( version_compare( $current_version, $latest_version, '<' ) ) {
			$header = 'There\'s a new version of the QIT CLI available!';

			$this->output->writeln( '<bg=red;fg=white>' . str_repeat( ' ', strlen( $header ) ) );
			$this->output->writeln( $header );
			$this->output->writeln( str_repeat( ' ', strlen( $header ) ) . '</>' );

			$this->output->writeln( sprintf( "<bg=red;fg=white>Current version: %s</>\n<bg=red;fg=white;options=bold>Latest version: %s</>", $current_version, $latest_version ) );
			$this->output->writeln( "\nUpdate today to take advantage of the latest features!" );
			$this->output->writeln( "How to update: https://woocommerce.github.io/qit-documentation/#/cli/getting-started?id=updating-qit\n" );
			if ( version_compare( $current_version, $minimum_cli_version, '<' ) ) {
				if ( ! Config::is_development_mode() ) {
					$this->output->writeln( sprintf( '<error>You are using an outdated version of the CLI. Please update to the latest version (%s).</error>', $latest_version ) );
					throw new UpdateRequiredException();
				} else {
					$this->output->writeln( sprintf( '<comment>You are using an outdated version of the CLI (%s). Please update to the latest version (%s). Continuing execution as dev mode is enabled.</comment>', $current_version, $latest_version ) );
				}
			}
		}
	}
}
