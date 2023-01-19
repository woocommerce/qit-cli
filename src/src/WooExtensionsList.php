<?php

namespace QIT_CLI;

use QIT_CLI\IO\Output;
use Symfony\Component\Console\Output\OutputInterface;

class WooExtensionsList {
	/** @var Config $config */
	protected $config;

	/** @var Auth $auth */
	protected $auth;

	/** @var OutputInterface $output */
	protected $output;

	/** @var string $woo_extensions_cache_key */
	protected $woo_extensions_cache_key;

	public function __construct( Config $config, Auth $auth ) {
		$this->config                   = $config;
		$this->auth                     = $auth;
		$this->output                   = App::make( Output::class );
		$this->woo_extensions_cache_key = sprintf( 'woo_extensions_%s', md5( get_cd_manager_url() ) );
	}

	/**
	 * Store a local info of what Woo Extensions the current user has permission to manage.
	 * This is purely for convenience of the developer, and is pretty much read-only.
	 *
	 * @throws \Exception|\RuntimeException When could not retrieve list of WooCommerce extensions.
	 */
	public function fetch_woo_extensions_available(): void {
		$is_using_cd_secret = $this->config->get_cache( 'cd_secret' );

		if ( $is_using_cd_secret ) {
			$this->fetch_all_woo_extensions();

			return;
		}

		// Todo.
		throw new \RuntimeException( 'Todo: We need to fetch what WooExtensionsList a user has access to when authenticating using user:application_passwords.' );
	}

	/**
	 * When authenticating using the CD Secret,
	 * all extensions will be available.
	 *
	 * @throws \Exception When the request returned an error.
	 * @throws \RuntimeException When could not retrieve list of WooCommerce extensions.
	 */
	protected function fetch_all_woo_extensions(): void {
		try {
			$response = ( new RequestBuilder( get_cd_manager_url() . '/wp-json/cd/v1/get_extensions' ) )
				->with_method( 'POST' )
				->request();
		} catch ( \Exception $e ) {
			throw $e;
		}

		$woo_extensions = json_decode( $response, true );

		if ( ! is_array( $woo_extensions ) ) {
			throw new \RuntimeException( 'Could not retrieve list of WooCommerce extensions.' );
		}

		$this->config->set_cache( $this->woo_extensions_cache_key, $woo_extensions, 86400 );
	}

	/**
	 * @throws \RuntimeException When it can't get the WooExtensions list.
	 * @return array<mixed> Gets the Woo Extensions list that the current authenticated user has access to.
	 *                      If not available, try to sync. If that doesn't work, throw.
	 */
	public function get_woo_extension_list(): array {
		$woo_extensions = $this->config->get_cache( $this->woo_extensions_cache_key );

		if ( is_null( $woo_extensions ) ) {
			$this->fetch_woo_extensions_available();
		}

		$woo_extensions = $this->config->get_cache( $this->woo_extensions_cache_key );

		if ( is_null( $woo_extensions ) ) {
			throw new \RuntimeException( 'Could not get the list of WooExtensions available to run tests.' );
		}

		return $woo_extensions;
	}

	public function get_woo_extension_id_by_slug( string $woo_extension_slug ): int {
		$extensions = $this->get_woo_extension_list();

		foreach ( $extensions as $e ) {
			if ( $e['slug'] === $woo_extension_slug ) {
				return (int) $e['id'];
			}
		}

		throw new \UnexpectedValueException( "Could not find Woo Extension with slug $woo_extension_slug." );
	}
}
