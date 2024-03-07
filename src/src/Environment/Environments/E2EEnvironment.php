<?php

namespace QIT_CLI\Environment\Environments;

use QIT_CLI\App;
use QIT_CLI\Environment\EnvInfo;
use QIT_CLI\Environment\Environment;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function QIT_CLI\is_windows;
use function QIT_CLI\is_wsl;

class E2EEnvironment extends Environment {
	/** @var string */
	protected $description = 'E2E Environment';

	/** @var string */
	protected $wordpress_version;

	/** @var string */
	protected $woocommerce_version;

	/** @var string */
	protected $php_version;

	/** @var array<string> */
	protected $php_extensions;

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

	/**
	 * @param array<string> $php_extensions
	 *
	 * @return void
	 */
	public function set_php_extensions( array $php_extensions ): void {
		$this->php_extensions = $php_extensions;
	}

	protected function post_generate_docker_compose( EnvInfo $env_info ): void {
		$qit_conf = $env_info->temporary_env . '/docker/nginx/conf.d/qit.conf';

		if ( ! file_exists( $qit_conf ) ) {
			throw new \RuntimeException( 'Could not find qit.conf' );
		}

		// Replace "##QIT_PHP_CONTAINER_PLACEHOLDER##" with the PHP Container.
		$qit_conf_contents = file_get_contents( $qit_conf );
		$qit_conf_contents = str_replace( '##QIT_PHP_CONTAINER_PLACEHOLDER##', sprintf( 'qit_env_php_%s', $env_info->env_id ), $qit_conf_contents );
		$qit_conf_contents = str_replace( '##QIT_DOMAIN##', $env_info->domain, $qit_conf_contents );
		file_put_contents( $qit_conf, $qit_conf_contents );
	}

	protected function post_up( EnvInfo $env_info ): void {
		$env_info->site_url = 'http://qit.test:' . $this->get_nginx_port( $env_info );
		$this->environment_monitor->environment_added_or_updated( $env_info );

		/**
		 * @phpstan-ignore-next-line
		 */
		if ( ! empty( $this->php_extensions ) ) {
			$this->docker->run_inside_docker( $env_info, [ '/bin/bash', '/qit/bin/php-extensions.sh' ], [
				'PHP_EXTENSIONS' => implode( ' ', $this->php_extensions ), // Space-separated list of PHP extensions.
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

		// Try to connect to the website.
		if ( ! $this->check_site( $env_info->site_url ) ) {
			$site_url_domain = parse_url( $env_info->site_url, PHP_URL_HOST );
			$io->section( 'Test connection failed' );

			if ( $env_info->domain !== 'localhost' ) {
				$io->writeln( 'We couldn\'t access the website. To fix this, please check if the following line is present in your hosts file:' );
				$io->writeln( sprintf( "\n<info>127.0.0.1 %s</info>\n", $site_url_domain ) );
				if ( is_wsl() ) {
					// Inside Windows WSL.
					$io->writeln( 'It appears you are using Windows Subsystem for Linux (WSL). To update the hosts file automatically, you can run the following command in PowerShell with administrative privileges, outside of WSL:' );
					$io->writeln( sprintf( 'Add-Content -Path $env:windir\System32\drivers\etc\hosts -Value "`n127.0.0.1`t%s" -Force', $site_url_domain ) );
				} elseif ( is_windows() ) {
					// In native Windows.
					$io->writeln( 'If it\'s not, you can add it using this PowerShell command with Administration privileges:' );
					$io->writeln( sprintf( 'Add-Content -Path $env:windir\System32\drivers\etc\hosts -Value "`n127.0.0.1`t%s" -Force', $site_url_domain ) );
				} else {
					$io->writeln( 'If it\'s not, you can add it using this command:' );
					$io->writeln( sprintf( "\n<info>echo \"127.0.0.1 %s\" | sudo tee -a /etc/hosts</info>", $site_url_domain ) );
				}
			}
		}
		$io->writeln( '' );
	}

	protected function check_site( string $site_url ): bool {
		if ( $this->output->isVerbose() ) {
			$this->output->writeln( sprintf( 'Checking if %s is accessible...', $site_url ) );
		}

		$ch = curl_init( $site_url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
		curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3' );
		curl_exec( $ch );
		$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		curl_close( $ch );

		if ( $this->output->isVerbose() ) {
			$this->output->writeln( sprintf( 'HTTP Code: %d', $http_code ) );
		}

		return $http_code === 200;
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
