<?php

namespace QIT_CLI\LocalTests\Performance\Runner;

use QIT_CLI\App;
use QIT_CLI\Config;
use QIT_CLI\Environment\Docker;
use QIT_CLI\LocalTests\Performance\Environment\PerformanceEnvInfo;

class K6DockerConfig {

	/** @var Docker */
	private $docker;

	public function __construct( Docker $docker ) {
		$this->docker = $docker;
	}

	/**
	 * @param PerformanceEnvInfo $env_info
	 * @param string             $results_dir
	 * @param string             $container_name
	 * @param array<mixed>       $test_infos
	 * @return array<string>
	 */
	public function build_k6_docker_args( PerformanceEnvInfo $env_info, string $results_dir, string $container_name, array $test_infos = [] ): array {
		return array_merge(
			$this->get_base_docker_args( $env_info, $container_name ),
			$this->get_volume_mounts( $env_info, $results_dir, $test_infos ),
			$this->get_environment_variables( $env_info ),
			$this->get_k6_command()
		);
	}

	/**
	 * @return array<string>
	 */
	private function get_base_docker_args( PerformanceEnvInfo $env_info, string $container_name ): array {
		return [
			$this->docker->find_docker(),
			'run',
			"--name=$container_name",
			"--network={$env_info->docker_network}",
			'--rm',
			'--init',
			'--add-host=host.docker.internal:host-gateway',
			'-p',
			'5665:5665'	// Port for k6 live web dashboard.
		];
	}

	/**
	 * @param PerformanceEnvInfo $env_info
	 * @param string             $results_dir
	 * @param array<mixed>       $test_infos
	 * @return array<string>
	 */
	private function get_volume_mounts( PerformanceEnvInfo $env_info, string $results_dir, array $test_infos = [] ): array {
		// Base volumes for k6 operation.
		$volumes = [
			Config::get_qit_dir() . 'cache/k6' => '/k6-cache',
			$results_dir                       => '/results',
		];

		$args = [];
		foreach ( $volumes as $host_path => $container_path ) {
			$args[] = '-v';
			$args[] = "$host_path:$container_path";
		}

		// Mount test directories.
		foreach ( $test_infos as $test_info ) {
			$args[] = '-v';
			$args[] = "{$test_info['path_in_host']}:{$test_info['path_in_php_container']}";
		}

		return $args;
	}

	/**
	 * @return array<string>
	 */
	private function get_environment_variables( PerformanceEnvInfo $env_info ): array {
		// Environment variables for k6 container.
		$internal_nginx_name = "qitenvnginx{$env_info->env_id}";

		$args = [
			'-e',
			sprintf( 'BASE_URL=%s', $env_info->site_url ),
			'-e',
			sprintf( 'QIT_DOMAIN=%s', $env_info->domain ),
			'-e',
			sprintf( 'QIT_INTERNAL_DOMAIN=%s', "http://host.docker.internal:{$env_info->nginx_port}" ),
			'-e',
			sprintf( 'QIT_INTERNAL_NGINX=%s', $internal_nginx_name ),
		];

		// Enable k6 web dashboard and export HTML report.
		$args[] = '-e';
		$args[] = 'K6_WEB_DASHBOARD=true';
		$args[] = '-e';
		$args[] = 'K6_WEB_DASHBOARD_EXPORT=/results/dashboard-report.html';

		// Pass additional env vars to the test environment.
		foreach ( App::getVar( 'QIT_DOCKER_ENV_VARS' ) ?? [] as $env_key => $env_value ) {
			$args[] = '-e';
			$args[] = "$env_key=$env_value";
		}

		return $args;
	}

	/**
	 * @return array<string>
	 */
	private function get_k6_command(): array {
		return [
			'grafana/k6:master-with-browser',
			'run',
			'--out',
			'json=/results/result-extended.json',
			'--summary-export',
			'/results/result.json',
		];
	}
}
