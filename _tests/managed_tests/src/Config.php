<?php

class Config {
	private $params;
	private $logger;
	private $tests_based_on_custom_tests = [ 'activation' ];
	public $one_of_each = false;

	public function __construct( $argv, Logger $logger ) {
		$this->params = $argv;
		$this->logger = $logger;
	}

	public function parse() {
		$this->logger->log( "Script started with params: " . implode( ' ', $this->params ) );

		/**
		 * We’ll separate flags (anything starting with "--")
		 * from positional arguments (action, test types, scenarios).
		 *
		 * By convention, $argv[0] is the script name. We skip that and
		 * parse everything else from $argv[1], $argv[2], etc.
		 */
		$flags      = [];
		$positional = [];
		$allButZero = array_slice( $this->params, 1 ); // skip $argv[0] (the script name)

		foreach ( $allButZero as $arg ) {
			// If argument starts with "--", treat it as an option/flag
			if ( strpos( $arg, '--' ) === 0 ) {
				$flags[] = $arg;
			} else {
				$positional[] = $arg;
			}
		}

		// ----------------------------------------------------
		// 1) Parse flags (e.g. --debug, --one-of-each, --env_filter, etc.)
		// ----------------------------------------------------

		// --debug => enable debug mode
		if ( in_array( '--debug', $flags, true ) ) {
			Context::$debug_mode = true;
			$this->logger->log( "Debug mode enabled" );
		}

		// --one-of-each => run only one scenario per test type (prefer no_op)
		if ( in_array( '--one-of-each', $flags, true ) ) {
			$this->one_of_each = true;
			$this->logger->log( "one-of-each mode enabled" );
		}

		/**
		 * --env_filter=key=value => Example: --env_filter=wp=6.1
		 * We might have multiple of these. We'll parse them all.
		 */
		Context::$env_filters = [];
		foreach ( $flags as $flag ) {
			if ( strpos( $flag, '--env_filter=' ) === 0 ) {
				// e.g. "--env_filter=wp=6.0" => want to parse "wp=6.0"
				$raw = substr( $flag, strlen( '--env_filter=' ) );
				[ $key, $value ] = explode( '=', $raw, 2 );

				if ( array_key_exists( $key, Context::$env_filters ) ) {
					$this->logger->log( "Duplicate env filter key '{$key}' found." );
					maybe_echo( "Duplicate env filter key '{$key}' found.\n" );
					die( 1 );
				}
				Context::$env_filters[ $key ] = $value;
				$this->logger->log( "Env filter: $key = $value" );
			}
		}

		// ----------------------------------------------------
		// 2) Parse positional args: [0] => action, [1] => test types, [2] => scenarios
		// ----------------------------------------------------

		// Action (e.g. "run", "update")
		Context::$action = $positional[0] ?? 'run';
		$this->logger->log( "Action: " . Context::$action );

		// Test types (comma-separated)
		if ( isset( $positional[1] ) && $positional[1] !== '' ) {
			Context::$test_types = array_map( 'trim', explode( ',', $positional[1] ) );
			$this->logger->log( "Requested test types: " . implode( ',', Context::$test_types ) );

			// If multiple test types, ensure none is a "custom-test" type
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
			// If not specified, treat as "run all test types"
			Context::$test_types = null;
			$this->logger->log( "No specific test types requested" );
		}

		// Mark whether we are running a custom test
		Context::$running_test_based_on_custom_test = ! is_null( Context::$test_types )
		                                              && count( array_intersect( Context::$test_types, $this->tests_based_on_custom_tests ) ) > 0;

		// Scenarios (comma-separated)
		if ( isset( $positional[2] ) && $positional[2] !== '' ) {
			$scenarios = array_map( 'trim', explode( ',', $positional[2] ) );
			/**
			 * If you want to strip out anything that starts with "--",
			 * just in case, but realistically we've already separated them.
			 */
			$scenarios = array_filter( $scenarios, static function ( $v ) {
				return strpos( $v, "--" ) !== 0;
			} );

			Context::$scenarios = empty( $scenarios ) ? null : $scenarios;
			$this->logger->log( "Scenarios requested: " . implode( ',', Context::$scenarios ?? [] ) );
		} else {
			Context::$scenarios = null;
			$this->logger->log( "No specific scenarios requested" );
		}
	}
}