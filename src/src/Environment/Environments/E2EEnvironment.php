<?php

namespace QIT_CLI\Environment\Environments;

use QIT_CLI\App;
use QIT_CLI\Environment\EnvInfo;
use QIT_CLI\Environment\Environment;
use Symfony\Component\Console\Helper\Table;
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
		$qit_conf_contents = str_replace( '##QIT_DOMAIN_PLACEHOLDER##', $env_info->domain, $qit_conf_contents );
		file_put_contents( $qit_conf, $qit_conf_contents );
	}

	protected function post_up( EnvInfo $env_info ): void {
		$env_info->site_url = sprintf( 'http://%s:%s', $env_info->domain, $this->get_nginx_port( $env_info ) );
		$this->environment_monitor->environment_added_or_updated( $env_info );

		/**
		 * @phpstan-ignore-next-line
		 */
		if ( ! empty( $this->php_extensions ) ) {
			$this->output->writeln( '<info>Installing PHP extensions...</info>' );
			// Install PHP extensions, if needed.
			$this->docker->run_inside_docker( $env_info, [ '/bin/bash', '/qit/bin/php-extensions.sh' ], [
				'PHP_EXTENSIONS' => implode( ' ', $this->php_extensions ), // Space-separated list of PHP extensions.
			], '0:0' );
		}

		// Setup WordPress.
		$this->output->writeln( '<info>Setting up WordPress...</info>' );
		$this->docker->run_inside_docker( $env_info, [ '/bin/bash', '/qit/bin/wordpress-setup.sh' ], [
			'WORDPRESS_VERSION'   => $this->wordpress_version,
			'WOOCOMMERCE_VERSION' => $this->woocommerce_version,
			'SUT_SLUG'            => 'automatewoo',
			'SITE_URL'            => $env_info->site_url,
			'QIT_DOCKER_REDIS'    => $this->enable_object_cache ? 'yes' : 'no',
		] );

		// Activate plugins.
		$this->output->writeln( '<info>Activating plugins...</info>' );
		$this->docker->run_inside_docker( $env_info, [ 'php', '/qit/bin/plugins-activate.php' ] );
		$this->parse_php_activation_report( $env_info );

		$env_info->php_version       = $this->php_version;
		$env_info->wordpress_version = $this->wordpress_version;
		$env_info->redis             = $this->enable_object_cache;
	}

	protected function parse_php_activation_report( EnvInfo $env_info ): void {
		$activation_report_file = $env_info->temporary_env . 'bin/plugin-activation-report.json';

		if ( ! file_exists( $activation_report_file ) ) {
			// Probably no plugins to activate?
			$this->output->writeln( '<info>No plugins to activate.</info>' );
			return;
		}

		$activation_report = json_decode( file_get_contents( $activation_report_file ), true );

		if ( ! is_array( $activation_report ) ) {
			$this->output->writeln( '<error>Invalid plugin activation report generated.</error>' );

			return;
		}

		$has_big_debug_log = false;

		foreach ( $activation_report as $r ) {
			/**
			 * @var array{
			 *     plugin: string,
			 *     activated: bool,
			 *     debug_log: array<string>,
			 * } $r
			 */
			$expected_schema = [
				'plugin'    => 'string',
				'activated' => 'boolean',
				'debug_log' => 'array',
			];

			foreach ( $expected_schema as $key => $type ) {
				if ( ! array_key_exists( $key, $r ) || gettype( $r[ $key ] ) !== $type ) {
					$this->output->writeln( '<error>Invalid plugin activation report generated.</error>' );

					return;
				}
			}

			if ( ! $r['activated'] ) {
				$this->output->writeln( sprintf( '<error>Plugin %s failed to activate.</error>', $r['plugin'] ) );
			}

			if ( ! empty( $r['debug_log'] ) ) {
				$this->output->writeln(
					sprintf(
						'<error>New debug log entries were generated while activating plugin "%s"%s:</error>',
						$r['plugin'], // @phan-suppress-current-line PhanTypeMismatchArgumentInternal
						count( $r['debug_log'] ) > 10 ? sprintf( ' (%d lines total, showing last 10)', count( $r['debug_log'] ) ) : ''
					)
				);
				if ( count( $r['debug_log'] ) > 10 ) {
					$has_big_debug_log = true;
					$r['debug_log']    = array_slice( $r['debug_log'], - 10 );
				}
				$table = new Table( $this->output );
				foreach ( $r['debug_log'] as $line ) {
					$table->addRow( [ $line ] );
				}
				$table->render();
			}
		}

		if ( $has_big_debug_log ) {
			$this->output->writeln( sprintf( '<info>Some debug logs were too big to show. Full logs: %s</info>', $activation_report_file ) );
		}
	}

	protected function additional_output( EnvInfo $env_info ): void {
		global $argv;
		$io = new SymfonyStyle( App::make( InputInterface::class ), $this->output );

		if ( $this->output->isVerbose() ) {
			// Output a table of volume mappings.
			$io->section( 'Additional Volume Mappings' );

			if ( empty( $this->volumes ) ) {
				$this->output->writeln( 'No additional volume mappings.' );
			} else {
				$volumes = [];

				foreach ( $this->volumes as $k => $v ) {
					$volumes[] = [ $v['local'], $v['in_container'] ];
				}

				$table = new Table( $this->output );
				$table
					->setHeaders( [ 'Host Path', 'Container Path' ] )
					->setRows( $volumes )
					->setStyle( 'box' )
					->render();
			}

			$io->newLine();

			$io->section( 'Plugins and Themes' );
			$this->docker->run_inside_docker( $env_info, [ 'bash', '-c', 'wp plugin list --skip-plugins --skip-themes' ] );
			$this->docker->run_inside_docker( $env_info, [ 'bash', '-c', 'wp theme list --skip-plugins --skip-themes' ] );
		}

		$io->success( 'Temporary test environment created. (' . $env_info->env_id . ')' );

		$listing = [
			sprintf( 'URL: %s', $env_info->site_url ),
			sprintf( 'Admin URL: %s/wp-admin', $env_info->site_url ),
			'Admin Credentials: admin/password',
			sprintf( 'PHP Version: %s', $env_info->php_version ),
			sprintf( 'WordPress Version: %s', $env_info->wordpress_version ),
			sprintf( 'Redis Object Cache? %s', $env_info->redis ? 'Yes' : 'No' ),
			sprintf( 'Path: %s', $env_info->temporary_env ),
		];

		$io->listing( $listing );

		if ( ! $this->output->isVerbose() ) {
			$io->writeln( sprintf( 'To see additional info, run with the "--verbose" flag.' ) );
		}

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
			$this->output->write( sprintf( 'Checking if %s is accessible...', $site_url ) );
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
			$this->output->write( sprintf( " HTTP Code: %d\n", $http_code ) );
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
