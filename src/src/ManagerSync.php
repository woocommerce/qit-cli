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
	public $sync_cache_key;

	public function __construct( Config $config, Environment $environment, Auth $auth ) {
		$this->config         = $config;
		$this->auth           = $auth;
		$this->environment    = $environment;
		$this->output         = App::make( Output::class );
		$this->sync_cache_key = sprintf( 'manager_sync_v%s', App::getVar( 'CLI_VERSION' ) );
	}

	public function maybe_sync( bool $force_resync = false ) {
		if ( $force_resync ) {
			$this->config->delete_cache( $this->sync_cache_key );
		}

		$manager_sync = $this->config->get_cache( $this->sync_cache_key );

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
		} catch ( \Exception $e ) {
			if ( $this->environment->is_development_mode() ) {
				$this->output->writeln( sprintf( '<error>[Dev Mode] Failed to contact Manager at URL %s.</error>', get_manager_url() ) );
				$this->output->writeln( sprintf( '<comment>[Dev Mode] %s</comment>', $e->getMessage() ) );
			}

			$this->output->writeln( '<comment>This CLI tool interacts with external services that are not available at the moment. Please check your internet connection or try again later.</comment>' );

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
		$this->config->set_cache( $this->sync_cache_key, $manager_sync, 3600 );
	}

	public function enforce_latest_version() {
		$current_version = App::getVar( 'CLI_VERSION' );

		// Do not check version on development build.
		if ( $current_version === '@QIT_CLI_VERSION@' ) {
			return;
		}

		$latest_version = $this->config->get_manager_sync_data( 'latest_cli_version' );

		if ( version_compare( $current_version, $latest_version, '<' ) ) {
			if ( ! $this->environment->is_development_mode() ) {
				$this->output->writeln( sprintf( '<error>You are using an outdated version of the CLI. Please update to the latest version (%s).</error>', $latest_version ) );
				throw new \RuntimeException();
			} else {
				$this->output->writeln( sprintf( '<comment>You are using an outdated version of the CLI (%s). Please update to the latest version (%s). Continuing execution as dev mode is enabled.</comment>', $current_version, $latest_version ) );
			}
		}
	}
}
