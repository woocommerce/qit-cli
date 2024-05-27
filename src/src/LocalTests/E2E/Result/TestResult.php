<?php

namespace QIT_CLI\LocalTests\E2E\Result;

use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use function QIT_CLI\normalize_path;

class TestResult {
	/** @var string */
	public $status;

	/** @var array<mixed> */
	public $results = [];

	/** @var array<mixed> */
	public $bootstrap = [];

	/** @var string */
	protected $results_dir;

	/** @var E2EEnvInfo */
	protected $env_info;

	public $no_upload_report = false;

	protected function __construct() {
	}

	public static function init_from( E2EEnvInfo $env_info ): TestResult {
		$instance              = new self();
		$instance->status      = 'pending';
		$instance->env_info    = $env_info;
		$instance->results_dir = normalize_path( sys_get_temp_dir() ) . "qit-results-{$env_info->env_id}";

		if ( ! mkdir( $instance->results_dir, 0755, false ) ) {
			throw new \RuntimeException( sprintf( 'Could not create the results directory: %s', $instance->results_dir ) );
		}

		return $instance;
	}

	public function get_results_dir(): string {
		return $this->results_dir;
	}

	public function register_bootstrap( string $plugin_slug, string $bootstrap_type, string $status ): void {
		$this->bootstrap[ $plugin_slug ][ $bootstrap_type ] = $status;
	}

	public function set_status( string $status ): void {
		$this->status = $status;
	}

	public function get_env_info(): E2EEnvInfo {
		return $this->env_info;
	}
}
