<?php

namespace QIT_CLI;

use QIT_CLI\Exceptions\DoingAutocompleteException;
use QIT_CLI\Exceptions\NetworkErrorException;
use QIT_CLI\Exceptions\UpdateRequiredException;
use QIT_CLI\IO\Output;
use Symfony\Component\Console\Output\OutputInterface;

class ManagerSync {
	/** @var Cache $cache */
	protected $cache;

	/** @var Auth $auth */
	protected $auth;

	/** @var Environment $environment */
	protected $environment;

	/** @var OutputInterface $output */
	protected $output;

	/** @var string $sync_cache_key */
	public $sync_cache_key;

	public function __construct( Cache $cache, Environment $environment, Auth $auth ) {
		$this->cache         = $cache;
		$this->auth           = $auth;
		$this->environment    = $environment;
		$this->output         = App::make( Output::class );
		$this->sync_cache_key = sprintf( 'manager_sync_v%s', App::getVar( 'CLI_VERSION' ) );
	}

	public function maybe_sync( bool $force_resync = false ): void {
		if ( $force_resync ) {
			$this->cache->delete_cache( $this->sync_cache_key );
		}

		$manager_sync = $this->cache->get_cache( $this->sync_cache_key );

		if ( ! is_null( $manager_sync ) ) {
			return;
		}

		if ( $this->output->isVerbose() ) {
			App::make( Output::class )->write( '[Info] Syncing with Manager... ' );
		}

		$start = microtime( true );

		try {
			$manager_sync = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/sync' ) )
				->with_method( 'POST' )
				->request();
		} catch ( DoingAutocompleteException $e ) {
			return;
		} catch ( NetworkErrorException $e ) {
			if ( Config::is_development_mode() ) {
				$this->output->writeln( sprintf( '<error>[Dev Mode] Failed to contact Manager at URL %s.</error>', get_manager_url() ) );
				$this->output->writeln( sprintf( '<comment>[Dev Mode] %s</comment>', $e->getMessage() ) );
			}

			$this->output->writeln( '<comment>This CLI tool interacts with external services that are not available at the moment. Please check your internet connection or try again later.</comment>' );

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

		$this->cache->set_cache( $this->sync_cache_key, $manager_sync, $expiration );
	}

	public function enforce_latest_version(): void {
		$current_version = App::getVar( 'CLI_VERSION' );

		// Do not check version on development build.
		if ( $current_version === '@QIT_CLI_VERSION@' ) {
			return;
		}

		$latest_version = $this->cache->get_manager_sync_data( 'latest_cli_version' );
		$enforce_latest = $this->cache->get_manager_sync_data( 'enforce_latest' );

		if ( version_compare( $current_version, $latest_version, '<' ) ) {
			if ( $enforce_latest ) {
				if ( ! Config::is_development_mode() ) {
					$this->output->writeln( sprintf( '<error>You are using an outdated version of the CLI. Please update to the latest version (%s).</error>', $latest_version ) );
					throw new UpdateRequiredException();
				} else {
					$this->output->writeln( sprintf( '<comment>You are using an outdated version of the CLI (%s). Please update to the latest version (%s). Continuing execution as dev mode is enabled.</comment>', $current_version, $latest_version ) );
				}
			} else {
				$this->output->writeln( sprintf( "<info>[There's a new version of the QIT CLI available! It's strongly recommended to update. Current version: %s Latest version: %s]</info>\n", $current_version, $latest_version ) );
			}
		}
	}
}
