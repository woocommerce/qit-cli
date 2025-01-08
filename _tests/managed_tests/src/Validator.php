<?php

class Validator {
	private $logger;

	public function __construct( Logger $logger ) {
		$this->logger = $logger;
	}

	public function validate() {
		if ( ! file_exists( __DIR__ . '/../vendor' ) ) {
			$this->logger->log( "vendor directory not found, run composer install" );
			throw new RuntimeException( 'Please run "composer install" on the directory: ' . __DIR__ );
		}

		if ( ! in_array( Context::$action, [ 'run', 'update' ], true ) ) {
			$this->logger->log( "Invalid action: " . Context::$action );
			throw new RuntimeException( 'Invalid action. Please use "run" or "update".' );
		}

		if ( ! file_exists( __DIR__ . '/../../../qit' ) ) {
			$this->logger->log( "qit binary not found" );
			throw new RuntimeException( '"qit" binary does not exist in the parent-parent directory.' );
		}
	}
}
