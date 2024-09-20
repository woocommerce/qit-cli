<?php

namespace QIT_CLI\Ngrok;

use QIT_CLI\Cache;
use QIT_CLI\Commands\NgrokCommand;

class NgrokConfig {
	/** @var Cache $cache */
	protected $cache;

	public function __construct( Cache $cache ) {
		$this->cache = $cache;
	}

	/**
	 * @return array{
	 *     token: string,
	 *     domain: string
	 * }
	 * @throws \Exception When the Ngrok configuration is not found.
	 */
	public function get_ngrok_config(): array {
		$ngrok_config = $this->cache->get( 'ngrok_config' );

		if ( ! $ngrok_config ) {
			throw new \Exception( sprintf( 'Ngrok configuration not found. Please run "%s" to configure it first.', NgrokCommand::$defaultName ) );
		}

		return $ngrok_config;
	}

	public function set_ngrok_config( string $token, string $domain ): void {
		$this->cache->set( 'ngrok_config', [
			'token'  => $token,
			'domain' => $domain,
		], -1 );
	}
}