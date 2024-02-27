<?php

namespace QIT_CLI\Environment\Environments;

use QIT_CLI\Environment\EnvInfo;
use QIT_CLI\Environment\Environment;

class E2EEnvironment extends Environment {
	/** @var string */
	protected $description = 'E2E Environment';

	public function get_name(): string {
		return 'e2e';
	}

	protected function post_generate_docker_compose( EnvInfo $env_info ): void {
		$qit_conf = $env_info->temporary_env . '/docker/nginx/conf.d/qit.conf';

		if ( ! file_exists( $qit_conf ) ) {
			throw new \RuntimeException( 'Could not find qit.conf' );
		}

		// Replace "##QIT_PHP_CONTAINER_PLACEHOLDER##" with the PHP Container
		$qit_conf_contents = file_get_contents( $qit_conf );
		$qit_conf_contents = str_replace( '##QIT_PHP_CONTAINER_PLACEHOLDER##', sprintf( 'qit_env_php_%s', $env_info->env_id ), $qit_conf_contents );
		file_put_contents( $qit_conf, $qit_conf_contents );
	}

	protected function post_up( EnvInfo $env_info ): void {
		$php_extensions = [];

		if ( ! empty( $php_extensions ) ) {
			$this->docker->run_inside_docker( $env_info, [ '/bin/bash', '/qit/bin/php-extensions.sh' ], [
				'PHP_EXTENSIONS' => '', // Space-separated list of PHP extensions.
			], '0:0' );
		}

		$this->docker->run_inside_docker( $env_info, [ '/bin/bash', '/qit/bin/wordpress-setup.sh' ], [
			'WORDPRESS_VERSION'   => 'latest',
			'WOOCOMMERCE_VERSION' => '8.6.0',
			'SUT_SLUG'            => 'automatewoo',
			'SITE_URL'            => 'http://qit.test:' . $this->get_nginx_port( $env_info ),
		] );
	}
}