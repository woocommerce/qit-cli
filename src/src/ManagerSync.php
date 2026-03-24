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

	/** @var string Cache key for bootstrap+environment data (always fresh). */
	public $bootstrap_cache_key;

	/** @var string Cache key for environment checksums (same response, separate bucket for get_cache_key_for). */
	public $environments_cache_key;

	/** @var string Cache key for extension list (cached 1h, requires auth). */
	public $extensions_cache_key;

	/**
	 * Default bucket for sync data.
	 *
	 * Bootstrap data (schemas, test_types, versions, environments) all arrive
	 * in a single sync response. Only extensions are routed to a dedicated
	 * bucket because they have their own endpoint and cache policy.
	 */
	const DEFAULT_BUCKET = 'bootstrap';

	/** @var array<string, string> Keys explicitly routed to non-default buckets. */
	private static $key_to_bucket = [
		'environments' => 'environments',
		'extensions'   => 'extensions',
	];

	public function __construct( Cache $cache, Auth $auth ) {
		$this->auth                   = $auth;
		$this->cache                  = $cache;
		$this->output                 = App::make( Output::class );
		$this->bootstrap_cache_key    = sprintf( 'manager_sync_bootstrap_v%s', App::getVar( 'CLI_VERSION' ) );
		$this->environments_cache_key = 'manager_sync_environments';
		$this->extensions_cache_key   = sprintf( 'manager_sync_extensions_v%s', App::getVar( 'CLI_VERSION' ) );
	}

	/**
	 * Main sync entry point. Called on every CLI startup.
	 *
	 * - Bootstrap + Environments: always fresh, one call, no cache.
	 * - Extensions: cached 1 hour, requires auth.
	 *
	 * @param bool $force_resync Only affects extensions cache (bootstrap is always fresh).
	 */
	public function maybe_sync( bool $force_resync = false ): void {
		if ( $force_resync ) {
			$this->cache->delete( $this->bootstrap_cache_key );
			$this->cache->delete( $this->environments_cache_key );
			$this->cache->delete( $this->extensions_cache_key );
		}

		$this->sync();
		$this->maybe_sync_extensions( $force_resync );
	}

	/**
	 * Fetch bootstrap + environment data from Manager.
	 * Cached for 60 seconds — fresh enough to pick up Manager deploys,
	 * fast enough that subprocesses within the same invocation skip the network call.
	 */
	private function sync(): void {
		$cached_bootstrap = $this->cache->get( $this->bootstrap_cache_key );
		$cached_envs      = $this->cache->get( $this->environments_cache_key );

		if ( ! is_null( $cached_bootstrap ) && ! is_null( $cached_envs ) ) {
			return;
		}

		if ( $this->output->isVerbose() ) {
			$this->output->write( '[Info] Syncing with Manager... ' );
		}

		$start    = microtime( true );
		$response = $this->request_v2( 'cli/sync' );

		if ( $this->output->isVerbose() ) {
			$this->output->writeln( sprintf( 'Done in %s seconds.', number_format( microtime( true ) - $start, 2 ) ) );
		}

		$data = json_decode( $response, true );

		if ( ! is_array( $data ) || empty( $data ) ) {
			$this->output->writeln( sprintf( '<error>Failed to sync with Manager (%s). Not a valid JSON.</error>', get_manager_url() ) );
			throw new NetworkErrorException();
		}

		// Split the response into two buckets so get_manager_sync_data() can find each key.
		// Environments go to their own bucket; everything else is bootstrap.
		$env_data = [];
		if ( isset( $data['environments'] ) ) {
			$env_data = $this->normalize_environments_for_version( [ 'environments' => $data['environments'] ] );
			unset( $data['environments'] );
		}

		// Cache for 60 seconds — fresh enough for Manager deploys,
		// avoids redundant network calls from subprocesses.
		$ttl = 60;

		// Bootstrap bucket: schemas, test_types, versions, etc.
		$this->cache->set( $this->bootstrap_cache_key, $data, $ttl );

		// Environments bucket: environment checksums.
		if ( ! empty( $env_data ) ) {
			$this->cache->set( $this->environments_cache_key, $env_data, $ttl );
		}
	}

	/**
	 * Fetch extensions from Manager if not cached.
	 *
	 * Requires authentication (partner or manager secret).
	 * Before backend:add or partner:add there is no auth — skip silently.
	 */
	private function maybe_sync_extensions( bool $force_resync = false ): void {
		if ( $this->auth->get_partner_auth() === null && $this->auth->get_manager_secret() === null ) {
			return;
		}

		if ( ! $force_resync ) {
			$cached = $this->cache->get( $this->extensions_cache_key );

			if ( ! is_null( $cached ) ) {
				return;
			}
		}

		if ( $this->output->isVerbose() ) {
			$this->output->write( '[Info] Syncing extensions with Manager... ' );
		}

		$start    = microtime( true );
		$response = $this->request_v2( 'cli/sync/extensions' );

		if ( $this->output->isVerbose() ) {
			$this->output->writeln( sprintf( 'Done in %s seconds.', number_format( microtime( true ) - $start, 2 ) ) );
		}

		$data = json_decode( $response, true );

		if ( ! is_array( $data ) ) {
			$this->output->writeln( sprintf( '<error>Failed to sync extensions with Manager (%s). Not a valid JSON.</error>', get_manager_url() ) );
			throw new NetworkErrorException();
		}

		$expiration = App::getVar( 'offline_mode' ) ? 0 : 3600;

		$this->cache->set( $this->extensions_cache_key, $data, $expiration );
	}

	/**
	 * Make a POST request to a V2 sync endpoint.
	 *
	 * @param string $route The route path (e.g., 'cli/sync').
	 *
	 * @return string The response body.
	 * @throws NetworkErrorException If the Manager is unreachable.
	 */
	private function request_v2( string $route ): string {
		try {
			return ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v2/' . $route ) )
				->with_retry( 2 )
				->with_method( 'POST' )
				->request();
		} catch ( DoingAutocompleteException $e ) {
			return '{}';
		} catch ( NetworkErrorException $e ) {
			if ( Config::is_development_mode() ) {
				$this->output->writeln( sprintf( '<comment>[Dev Mode] Failed to contact Manager at URL %s.</comment>', get_manager_url() ) );
				$this->output->writeln( sprintf( '<comment>[Dev Mode] %s</comment>', $e->getMessage() ) );
				$this->output->writeln( '<comment>[Dev Mode] Check that the Manager is running, and is able to respond to sync requests.</comment>' );
			}

			throw $e;
		}
	}

	/**
	 * Get the cache key for a given data key.
	 *
	 * @param string $key The data key (e.g., 'schemas', 'environments', 'extensions').
	 *
	 * @return string The cache key for the bucket containing this data key.
	 */
	public function get_cache_key_for( string $key ): string {
		$bucket = self::$key_to_bucket[ $key ] ?? self::DEFAULT_BUCKET;

		switch ( $bucket ) {
			case 'bootstrap':
				return $this->bootstrap_cache_key;
			case 'environments':
				return $this->environments_cache_key;
			case 'extensions':
				return $this->extensions_cache_key;
			default:
				throw new \UnexpectedValueException( "Unknown sync bucket: '$bucket'" );
		}
	}

	public function enforce_latest_version(): void {
		$current_version = App::getVar( 'CLI_VERSION' );

		$is_dev_build_from_src  = base64_encode( $current_version ) === 'QFFJVF9DTElfVkVSU0lPTkA=';
		$is_dev_build_from_phar = $current_version === 'qit_dev_build';

		if ( $is_dev_build_from_src || $is_dev_build_from_phar ) {
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
			$this->output->writeln( "How to update: https://qit.woo.com/docs/getting-started/#updating-the-qit-cli\n" );
			if ( version_compare( $current_version, $minimum_cli_version, '<' ) ) {
				$this->output->writeln( sprintf( '<error>You are using an outdated version of the CLI. Please update to the latest version (%s).</error>', $latest_version ) );
				throw new UpdateRequiredException();
			}
		}
	}

	/**
	 * Normalize environment keys for the current CLI version.
	 *
	 * The Manager returns environment keys with version suffixes (e.g., 'e2e-v3').
	 * This strips the suffix so the CLI uses base names ('e2e', 'performance').
	 *
	 * @param array<mixed> $data The sync data containing 'environments' key.
	 * @return array<mixed> The normalized sync data.
	 */
	private function normalize_environments_for_version( array $data ): array {
		if ( ! isset( $data['environments'] ) || ! is_array( $data['environments'] ) ) {
			return $data;
		}

		$version_suffix          = '-v3';
		$normalized_environments = [];

		foreach ( $data['environments'] as $key => $env_data ) {
			if ( str_ends_with( $key, $version_suffix ) ) {
				$base_key                             = substr( $key, 0, - strlen( $version_suffix ) );
				$normalized_environments[ $base_key ] = $env_data;
			}
		}

		// Fallback: if no v3 keys found, use original keys (for development/testing)
		if ( empty( $normalized_environments ) ) {
			$normalized_environments = $data['environments'];
		}

		$data['environments'] = $normalized_environments;
		return $data;
	}
}
