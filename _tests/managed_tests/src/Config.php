<?php

class Config {
	private $params;
	private $logger;
	private $tests_based_on_custom_tests = [ 'activation' ];
	private $one_of_each = false;

	public function __construct( $argv, Logger $logger ) {
		$this->params = $argv;
		$this->logger = $logger;
	}

	public function parse() {
		$this->logger->log( "Script started with params: " . implode( ' ', $this->params ) );

		// Handle debug mode
		if ( ( $debugKey = array_search( '--debug', $this->params, true ) ) !== false ) {
			Context::$debug_mode = true;
			unset( $this->params[ $debugKey ] );
			$this->logger->log( "Debug mode enabled" );
		}

		Context::$action = $this->params[1] ?? 'run';
		$this->logger->log( "Action: " . Context::$action );

		// Test types
		if ( isset( $this->params[2] ) ) {
			Context::$test_types = array_map( 'trim', explode( ',', $this->params[2] ) );
			$this->logger->log( "Requested test types: " . implode( ',', Context::$test_types ) );
			if ( count( Context::$test_types ) > 1 ) {
				foreach ( $this->tests_based_on_custom_tests as $custom_test ) {
					if ( in_array( $custom_test, Context::$test_types, true ) ) {
						$this->logger->log( "Cannot run tests based on custom tests in parallel with other tests." );
						maybe_echo( "Cannot run tests based on custom tests in parallel with other tests.\n" );
						die( 1 );
					}
				}
			}
		} else {
			Context::$test_types = null;
			$this->logger->log( "No specific test types requested" );
		}

		Context::$running_test_based_on_custom_test = ! is_null( Context::$test_types )
		                                              && count( array_intersect( Context::$test_types, $this->tests_based_on_custom_tests ) ) > 0;

		// Scenarios
		if ( isset( $this->params[3] ) ) {
			$scenarios          = array_map( 'trim', explode( ',', $this->params[3] ) );
			$scenarios          = array_filter( $scenarios, static function ( $v ) {
				return strpos( $v, "--" ) !== 0;
			} );
			Context::$scenarios = empty( $scenarios ) ? null : $scenarios;
			$this->logger->log( "Scenarios requested: " . implode( ',', Context::$scenarios ?? [] ) );
		} else {
			Context::$scenarios = null;
			$this->logger->log( "No specific scenarios requested" );
		}

		// Env filters
		Context::$env_filters = [];
		foreach (
			array_filter( $this->params, static function ( $param ) {
				return strpos( $param, '--env_filter=' ) === 0;
			} ) as $env_filter
		) {
			[ $key, $value ] = explode( '=', substr( $env_filter, 13 ), 2 );

			if ( array_key_exists( $key, Context::$env_filters ) ) {
				$this->logger->log( "Duplicate key '{$key}' found in env filters." );
				maybe_echo( "Duplicate key '{$key}' found in env filters." );
				die( 1 );
			}

			Context::$env_filters[ $key ] = $value;
			$this->logger->log( "Env filter: $key = $value" );
		}

		// One of each will run only one test of each test type.
		if ( in_array( '--one-of-each', $this->params, true ) ) {
			$this->one_of_each = true;
			$this->logger->log( "one-of-each mode enabled" );
		}
	}
}