<?php

namespace QIT_CLI\Performance\Runner;

use QIT_CLI\App;
use QIT_CLI\Config;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;

class K6DockerConfig {

	/** @var Docker */
	private $docker;

	public function __construct( Docker $docker ) {
		$this->docker = $docker;
	}

	public function build_k6_docker_args( E2EEnvInfo $env_info, string $results_dir, string $container_name ): array {
		$k6_args = $this->get_base_docker_args( $env_info, $results_dir, $container_name );
		$k6_args = array_merge( $k6_args, $this->get_volume_mounts( $env_info, $results_dir ) );
		$k6_args = array_merge( $k6_args, $this->get_environment_variables() );
		$k6_args = array_merge( $k6_args, $this->get_user_args() );
		$k6_args = array_merge( $k6_args, $this->get_k6_image_and_command() );

		return $k6_args;
	}

	private function get_base_docker_args( E2EEnvInfo $env_info, string $results_dir, string $container_name ): array {
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

	private function get_volume_mounts( E2EEnvInfo $env_info, string $results_dir ): array {
		$default_test_file = sys_get_temp_dir() . '/qit-k6-default-test.js';

		return [
			'-v',
			Config::get_qit_dir() . 'cache/k6:/k6-cache',
			'-v',
			$env_info->temporary_env . '/k6:/tests',
			'-v',
			$results_dir . ':/results',
			'-v',
			$env_info->temporary_env . '/k6/qitHelpers.js:/qitHelpers/qitHelpers.js',
			'-v',
			$env_info->temporary_env . '/k6/test-info.json:/qitHelpers/test-info.json',
			'-v',
			"$default_test_file:/tests/default-performance-test.js",
		];
	}

	private function get_environment_variables(): array {
		$env_args = [];

		// Add QIT-specific environment variables
		$qit_vars = [
			'BASE_URL' => App::getVar( 'BASE_URL' ),
			'QIT_DOMAIN' => App::getVar( 'QIT_DOMAIN' ),
			'QIT_INTERNAL_DOMAIN' => App::getVar( 'QIT_INTERNAL_DOMAIN' ),
			'QIT_INTERNAL_NGINX' => App::getVar( 'QIT_INTERNAL_NGINX' ),
		];

		foreach ( $qit_vars as $key => $value ) {
			if ( $value ) {
				$env_args[] = '-e';
				$env_args[] = "$key=$value";
			}
		}

		// Add additional Docker environment variables
		foreach ( App::getVar( 'QIT_DOCKER_ENV_VARS' ) ?? [] as $env_key => $env_value ) {
			$env_args[] = '-e';
			$env_args[] = "$env_key=$env_value";
		}

		return $env_args;
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

	private function get_k6_image_and_command(): array {
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

	public function set_environment_variables( E2EEnvInfo $env_info ): void {
		App::setVar( 'BASE_URL', $env_info->site_url );
		App::setVar( 'QIT_DOMAIN', $env_info->domain );
		App::setVar( 'QIT_INTERNAL_DOMAIN', sprintf( 'host.docker.internal:%s', $env_info->nginx_port ) );
		App::setVar( 'QIT_INTERNAL_NGINX', sprintf( 'qitenvnginx%s', $env_info->env_id ) );
	}
} 