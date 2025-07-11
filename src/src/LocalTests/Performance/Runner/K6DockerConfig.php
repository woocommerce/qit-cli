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

	public function build_k6_docker_args( PerformanceEnvInfo $env_info, string $results_dir, string $container_name ): array {
		return array_merge(
			$this->get_base_docker_args( $env_info, $container_name ),
			$this->get_volume_mounts( $env_info, $results_dir ),
			$this->get_environment_variables(),
			$this->get_user_args(),
			$this->get_k6_command()
		);
	}

	private function get_base_docker_args( PerformanceEnvInfo $env_info, string $container_name ): array {
		return [
			$this->docker->find_docker(),
			'run',
			"--name=$container_name",
			"--network={$env_info->docker_network}",
			'--rm',
			'--init',
			'--add-host=host.docker.internal:host-gateway',
		];
	}

	private function get_volume_mounts( PerformanceEnvInfo $env_info, string $results_dir ): array {
		$volumes = [
			Config::get_qit_dir() . 'cache/k6'             => '/k6-cache',
			$env_info->temporary_env . '/k6'               => '/tests',
			$results_dir                                   => '/results',
			$env_info->temporary_env . '/k6/qitHelpers.js' => '/qitHelpers/qitHelpers.js',
			$env_info->temporary_env . '/k6/test-info.json' => '/qitHelpers/test-info.json',
			sys_get_temp_dir() . '/qit-k6-default-test.js' => '/tests/default-performance-test.js',
		];

		$args = [];
		foreach ( $volumes as $host_path => $container_path ) {
			$args[] = '-v';
			$args[] = "$host_path:$container_path";
		}

		return $args;
	}

	private function get_environment_variables(): array {
		$env_vars = array_merge(
			[
				'BASE_URL'            => App::getVar( 'BASE_URL' ),
				'QIT_DOMAIN'          => App::getVar( 'QIT_DOMAIN' ),
				'QIT_INTERNAL_DOMAIN' => App::getVar( 'QIT_INTERNAL_DOMAIN' ),
				'QIT_INTERNAL_NGINX'  => App::getVar( 'QIT_INTERNAL_NGINX' ),
			],
			App::getVar( 'QIT_DOCKER_ENV_VARS' ) ?? []
		);

		$args = [];
		foreach ( $env_vars as $key => $value ) {
			if ( $value ) {
				$args[] = '-e';
				$args[] = "$key=$value";
			}
		}

		return $args;
	}

	private function get_user_args(): array {
		if ( Docker::should_set_user() ) {
			return [
				'--user',
				implode( ':', Docker::get_user_and_group() ),
			];
		}

		return [];
	}

	private function get_k6_command(): array {
		return [
			'grafana/k6:latest',
			'run',
			'--duration',
			'30s',
			'--vus',
			'10',
			'--out',
			'json=/results/k6-results.json',
		];
	}

	public function set_environment_variables( PerformanceEnvInfo $env_info ): void {
		$env_vars = [
			'BASE_URL'            => $env_info->site_url,
			'QIT_DOMAIN'          => $env_info->domain,
			'QIT_INTERNAL_DOMAIN' => "host.docker.internal:{$env_info->nginx_port}",
			'QIT_INTERNAL_NGINX'  => "qitenvnginx{$env_info->env_id}",
		];

		foreach ( $env_vars as $key => $value ) {
			App::setVar( $key, $value );
		}
	}
}
