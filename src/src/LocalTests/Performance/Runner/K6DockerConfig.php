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
	 * @return array<string>
	 */
	public function build_k6_docker_args( PerformanceEnvInfo $env_info, string $results_dir, string $container_name ): array {
		return array_merge(
			$this->get_base_docker_args( $env_info, $container_name ),
			$this->get_volume_mounts( $env_info, $results_dir ),
			$this->get_environment_variables( $env_info ),
			$this->get_k6_command()
		);
	}

	/**
	 * @return array<string>
	 */
	private function get_base_docker_args( PerformanceEnvInfo $env_info, string $container_name ): array {
		$args = [
			$this->docker->find_docker(),
			'run',
			"--name=$container_name",
			"--network={$env_info->docker_network}",
			'--rm',
			'--init',
			'--add-host=host.docker.internal:host-gateway',
			'-p',
			'5665:5665', // Port for k6 live web dashboard.
		];

		// Run k6 as current user to match file ownership.
		if ( Docker::should_set_user() ) {
			$args[] = '--user';
			$args[] = implode( ':', Docker::get_user_and_group() );
		}

		return $args;
	}

	/**
	 * @param PerformanceEnvInfo $env_info
	 * @param string             $results_dir
	 * @return array<string>
	 */
	private function get_volume_mounts( PerformanceEnvInfo $env_info, string $results_dir ): array {
		$volumes = [
			Config::get_qit_dir() . 'cache/k6' => '/k6-cache',
			$results_dir                       => '/results',
		];

		$args = [];
		foreach ( $volumes as $host_path => $container_path ) {
			$args[] = '-v';
			$args[] = "$host_path:$container_path";
		}

		// Mount test packages.
		if ( ! empty( $env_info->test_packages_for_setup ) ) {
			foreach ( $env_info->test_packages_for_setup as $pkg_info ) {
				$args[] = '-v';
				$args[] = "{$pkg_info['path']}:{$pkg_info['container_path']}";
			}
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

		// Set HOME for browser support when running with --user.
		if ( Docker::should_set_user() ) {
			$args[] = '-e';
			$args[] = 'HOME=/tmp';
		}

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
		$args = [
			'grafana/k6:master-with-browser',
			'run',
			'--out',
			'json=/results/result-extended.json',
			'--summary-export',
			'/results/result.json',
		];

		return $args;
	}
}
