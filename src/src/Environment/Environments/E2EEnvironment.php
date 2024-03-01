<?php

namespace QIT_CLI\Environment\Environments;

use QIT_CLI\App;
use QIT_CLI\Environment\EnvInfo;
use QIT_CLI\Environment\Environment;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class E2EEnvironment extends Environment {
	/** @var string */
	protected $description = 'E2E Environment';

	/** @var string */
	protected $wordpress_version;

	/** @var string */
	protected $woocommerce_version;

	/** @var string */
	protected $php_version;

	/** @var bool */
	protected $enable_object_cache = false;

	public function get_name(): string {
		return 'e2e';
	}

	public function set_wordpress_version( string $wordpress_version ): void {
		if ( in_array( $wordpress_version, [ 'stable', 'rc' ], true ) ) {
			$this->wordpress_version = $this->cache->get_manager_sync_data( 'versions' )['wordpress'][ $wordpress_version ];
		} else {
			$this->wordpress_version = $wordpress_version;
		}
	}

	public function set_woocommerce_version( string $woocommerce_version ): void {
		if ( in_array( $woocommerce_version, [ 'stable', 'rc' ], true ) ) {
			$this->woocommerce_version = $this->cache->get_manager_sync_data( 'versions' )['woocommerce'][ $woocommerce_version ];
		} else {
			$this->woocommerce_version = $woocommerce_version;
		}
	}

	public function set_php_version( string $php_version ): void {
		$this->php_version = $php_version;
	}

	public function set_enable_object_cache( bool $enable_object_cache ): void {
		$this->enable_object_cache = $enable_object_cache;
	}

	protected function post_generate_docker_compose( EnvInfo $env_info ): void {
		$qit_conf = $env_info->temporary_env . '/docker/nginx/conf.d/qit.conf';

		if ( ! file_exists( $qit_conf ) ) {
			throw new \RuntimeException( 'Could not find qit.conf' );
		}

		// Replace "##QIT_PHP_CONTAINER_PLACEHOLDER##" with the PHP Container.
		$qit_conf_contents = file_get_contents( $qit_conf );
		$qit_conf_contents = str_replace( '##QIT_PHP_CONTAINER_PLACEHOLDER##', sprintf( 'qit_env_php_%s', $env_info->env_id ), $qit_conf_contents );
		file_put_contents( $qit_conf, $qit_conf_contents );
	}

	protected function post_up( EnvInfo $env_info ): void {
		$env_info->site_url = 'http://qit.test:' . $this->get_nginx_port( $env_info );
		$this->environment_monitor->environment_added_or_updated( $env_info );

		$php_extensions = [];

		/**
		 * @todo Add support for PHP extensions in the temp environments.
		 * @phpstan-ignore-next-line
		 */
		if ( ! empty( $php_extensions ) ) {
			$this->docker->run_inside_docker( $env_info, [ '/bin/bash', '/qit/bin/php-extensions.sh' ], [
				'PHP_EXTENSIONS' => '', // Space-separated list of PHP extensions.
			], '0:0' );
		}

		$this->docker->run_inside_docker( $env_info, [ '/bin/bash', '/qit/bin/wordpress-setup.sh' ], [
			'WORDPRESS_VERSION'   => $this->wordpress_version,
			'WOOCOMMERCE_VERSION' => $this->woocommerce_version,
			'SUT_SLUG'            => 'automatewoo',
			'SITE_URL'            => $env_info->site_url,
		] );
	}

	protected function additional_output( EnvInfo $env_info ): void {
		$io = new SymfonyStyle( App::make( InputInterface::class ), $this->output );
		$io->section( 'Disposable Test Environment Created - ' . $env_info->env_id );

		$io->writeln( sprintf( 'URL: %s', $env_info->site_url ) );
		$io->writeln( sprintf( 'Admin: %s/wp-admin', $env_info->site_url ) );
		$io->writeln( 'Admin Credentials: admin/password' );
		$io->writeln( 'Customer Credentials: customer/password' );
		$io->writeln( sprintf( 'Path: %s', $env_info->temporary_env ) );
	}

	/**
	 * @return array<string,string>
	 */
	protected function get_generate_docker_compose_envs( EnvInfo $env_info ): array {
		return [
			'PHP_VERSION'      => $this->php_version,
			'QIT_DOCKER_REDIS' => $this->enable_object_cache ? 'yes' : 'no',
		];
	}
}
