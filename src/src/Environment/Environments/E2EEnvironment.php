<?php

namespace QIT_CLI\Environment\Environments;

use QIT_CLI\App;
use QIT_CLI\Environment\EnvInfo;
use QIT_CLI\Environment\Environment;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
		$env_info->nginx_port = $this->get_nginx_port( $env_info );
		$this->environment_monitor->environment_added_or_updated( $env_info );

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
			'SITE_URL'            => 'http://qit.test:' . $env_info->nginx_port,
		] );
	}

	protected function additional_output( EnvInfo $env_info ): void {
		$io = new SymfonyStyle( App::make( InputInterface::class ), $this->output );
		$io->section( 'Disposable Test Environment Created - ' . $env_info->env_id );

		$io->writeln( sprintf( 'URL: %s', 'http://qit.test:' . $env_info->nginx_port ) );
		$io->writeln( sprintf( 'Admin: %s/wp-admin', 'http://qit.test:' . $env_info->nginx_port ) );
		$io->writeln( 'Admin Credentials: admin/password' );
		$io->writeln( 'Customer Credentials: customer/password' );
		$io->writeln( sprintf( 'Path: %s', $env_info->temporary_env ) );
	}
}