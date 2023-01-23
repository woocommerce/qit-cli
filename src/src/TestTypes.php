<?php

namespace QIT_CLI;

use QIT_CLI\Exceptions\DoingAutocompleteException;
use QIT_CLI\IO\Output;
use Symfony\Component\Console\Output\OutputInterface;

class TestTypes {
	/** @var Config $config */
	protected $config;

	/** @var Auth $auth */
	protected $auth;

	/** @var OutputInterface $output */
	protected $output;

	/** @var string $test_types_cache_key */
	protected $test_types_cache_key;

	public function __construct( Config $config, Auth $auth ) {
		$this->config               = $config;
		$this->auth                 = $auth;
		$this->output               = App::make( Output::class );
		$this->test_types_cache_key = sprintf( 'test_types_%s', md5( get_manager_url() ) );
	}

	/**
	 * Get the available test types from the CD Manager.
	 */
	public function maybe_update_test_types(): void {
		$test_types = $this->config->get_cache( $this->test_types_cache_key );

		if ( ! is_null( $test_types ) ) {
			return;
		}

		if ( $this->output->isVerbose() ) {
			App::make( Output::class )->write( '[Info] Fetching Test Types from the Manager... ' );
		}

		$start = microtime( true );

		try {
			$test_types = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/test-types' ) )
				->request();
		} catch ( DoingAutocompleteException $e ) {
			return;
		}

		if ( $this->output->isVerbose() ) {
			App::make( Output::class )->writeln( sprintf( 'Done in %s seconds.', number_format( microtime( true ) - $start, 2 ) ) );
		}

		$test_types = json_decode( $test_types, true );

		if ( ! is_array( $test_types ) || empty( $test_types ) ) {
			if ( App::get( Output::class )->isVerbose() ) {
				App::get( Output::class )->writeln( '[Info] Could not get test types, using fallback...' );
			}

			$this->config->set_cache( $this->test_types_cache_key, [ 'e2e' ], 3600 );

			return;
		}

		if ( $this->output->isVerbose() ) {
			App::make( Output::class )->writeln( '[Info] New TestTypes saved: ' . json_encode( $test_types ) );
		}

		// 1 hour.
		$this->config->set_cache( $this->test_types_cache_key, $test_types, 3600 );
	}

	/**
	 * @return array<string> The test types of the current CD Manager being used.
	 */
	public function get_test_types(): array {
		return $this->config->get_cache( $this->test_types_cache_key, App::getVar( 'doing_autocompletion' ) ) ?? [];
	}
}
