<?php

namespace QIT_CLI\LocalTests\Performance\Environment;

use QIT_CLI\App;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\Environment\Environments\ThemeActivation;
use QIT_CLI\Environment\PluginActivationReportRenderer;
use QIT_CLI\Tunnel\TunnelRunner;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

class PerformanceEnvironment extends Environment {
	/** @var string */
	protected $description = 'Performance Test Environment';

	/**
	 * @var PerformanceEnvInfo
	 */
	protected $env_info;

	/** @var bool */
	protected $skip_activating_plugins = false;

	/** @var bool */
	protected $skip_activating_themes = false;

	public function get_name(): string {
		return 'performance';
	}

	public function set_skip_activating_plugins( bool $skip_activating_plugins ): void {
		$this->skip_activating_plugins = $skip_activating_plugins;
	}

	public function set_skip_activating_themes( bool $skip_activating_themes ): void {
		$this->skip_activating_themes = $skip_activating_themes;
	}

	protected function post_generate_docker_compose(): void {
		$qit_conf = $this->env_info->temporary_env . '/docker/nginx/conf.d/qit.conf';

		if ( ! file_exists( $qit_conf ) ) {
			throw new \RuntimeException( 'Could not find qit.conf' );
		}

		// Replace placeholders and add performance optimizations.
		$qit_conf_contents = file_get_contents( $qit_conf );
		$qit_conf_contents = str_replace(
			[ '##QIT_PHP_CONTAINER_PLACEHOLDER##', '##QIT_DOMAIN_PLACEHOLDER##' ],
			[ sprintf( 'qit_env_php_%s', $this->env_info->env_id ), $this->env_info->domain ],
			$qit_conf_contents
		);

		// Add performance-specific nginx optimizations.
		$qit_conf_contents .= $this->get_performance_nginx_config();
		file_put_contents( $qit_conf, $qit_conf_contents );

		// Update PHP configuration with environment ID.
		$qit_ini = $this->env_info->temporary_env . '/docker/php-fpm/qit.ini';
		if ( file_exists( $qit_ini ) ) {
			$qit_ini_contents = file_get_contents( $qit_ini );
			$qit_ini_contents = str_replace( '##QIT_ENV_ID##', $this->env_info->env_id, $qit_ini_contents );
			file_put_contents( $qit_ini, $qit_ini_contents );
		}
	}

	/**
	 * Get performance-specific nginx configuration.
	 */
	private function get_performance_nginx_config(): string {
		return "\n\n# Performance test optimizations\n" .
			"keepalive_requests 1000;\n";
	}

	public function up( string $type = 'up' ): void {
		if ( ! in_array( $type, [ 'up', 'up_and_test' ], true ) ) {
			throw new \InvalidArgumentException( 'Invalid type: ' . $type );
		}

		try {
			App::make( \QIT_CLI\Environment\Docker::class )->find_docker();
		} catch ( \Exception $e ) {
			throw new \RuntimeException( 'QIT needs Docker to be able to process this command.' );
		}

		// Start the benchmark.
		$start = microtime( true );

		// Download the performance environment.
		$this->environment_downloader->maybe_download( 'performance' );
		$this->maybe_create_cache_dir();
		$this->copy_environment();
		$this->environment_monitor->environment_added_or_updated( $this->env_info );

		if ( ! empty( $this->env_info->plugins ) || ! empty( $this->env_info->themes ) ) {
			$this->output->writeln( '<info>Downloading plugins and themes...</info>' );
		}

		$this->extension_downloader->download( $this->env_info, $this->cache_dir, $this->env_info->plugins, $this->env_info->themes );

		if ( $type === 'up_and_test' ) {
			$this->custom_tests_downloader->download( $this->env_info, $this->cache_dir, $this->env_info->plugins, $this->env_info->themes, 'performance' );
		}

		$this->output->writeln( '<info>Starting Docker Environment...</info>' );
		$this->generate_docker_compose();
		$this->post_generate_docker_compose();
		$this->up_docker_compose();
		$this->post_up();

		if ( $this->output->isVerbose() ) {
			$this->output->writeln( 'Server started in ' . round( microtime( true ) - $start, 2 ) . ' seconds' );
		}

		$this->additional_output();
	}

	protected function post_up(): void {
		if ( $this->env_info->tunnel ) {
			// Host port.
			$this->env_info->nginx_port = (string) $this->get_nginx_port();

			$site_url = App::make( TunnelRunner::class )->start_tunnel( "http://localhost:{$this->env_info->nginx_port}/", $this->env_info->env_id );

			$this->env_info->domain     = parse_url( $site_url, PHP_URL_HOST );
			$this->env_info->nginx_port = (string) parse_url( $site_url, PHP_URL_PORT );

			// Site URL with explicit port.
			$this->env_info->site_url = sprintf( $site_url );
		} else {
			if ( getenv( 'QIT_EXPOSE_ENVIRONMENT_TO' ) === 'DOCKER' ) {
				// Inside docker, the port is always 80 (that's what Nginx is listening to).
				$this->env_info->nginx_port = '80';

				// Site URL without explicit port.
				$this->env_info->site_url = sprintf( 'http://%s', $this->env_info->domain );
			} else {
				// Host port.
				$this->env_info->nginx_port = (string) $this->get_nginx_port();

				// Site URL with explicit port.
				$this->env_info->site_url = sprintf( 'http://%s:%s', $this->env_info->domain, $this->env_info->nginx_port );
			}
		}

		$this->environment_monitor->environment_added_or_updated( $this->env_info );

		$this->install_php_extensions();
		$this->setup_wordpress();
		$this->activate_required_plugins();
		$this->activate_plugins_and_themes();
		$this->enable_payment_method();
	}

	/**
	 * Install PHP extensions if needed.
	 */
	private function install_php_extensions(): void {
		if ( empty( $this->env_info->php_extensions ) ) {
			return;
		}

		$this->output->writeln( '<info>Installing PHP extensions...</info>' );
		$this->docker->run_inside_docker( $this->env_info, [ '/bin/bash', '-c', 'bash /qit/bin/php-extensions.sh' ], [
			'PHP_EXTENSIONS' => implode( ' ', $this->env_info->php_extensions ),
		], '0:0' );
	}

	/**
	 * Setup WordPress installation.
	 */
	private function setup_wordpress(): void {
		// Copy mu-plugins.
		$this->docker->run_inside_docker( $this->env_info, [ '/bin/bash', '-c', 'cp /qit/mu-plugins/* /var/www/html/wp-content/mu-plugins 2>&1' ] );

		// Install WordPress.
		$this->output->writeln( '<info>Installing WordPress...</info>' );
		$this->docker->run_inside_docker( $this->env_info, [ '/bin/bash', '-c', 'bash /qit/bin/wordpress-setup.sh 2>&1' ], [
			'TUNNEL'            => $this->env_info->tunnel ? 'yes' : 'no',
			'WORDPRESS_VERSION' => $this->env_info->wp,
			'SITE_URL'          => $this->env_info->site_url,
			'QIT_DOCKER_REDIS'  => $this->env_info->object_cache ? 'yes' : 'no',
		] );

		// Generate base data for performance testing.
		$this->generate_base_data();
	}

	/**
	 * Activate required plugins.
	 * This method installs and activates plugins that are required for the performance environment.
	 */
	private function activate_required_plugins(): void {
		$this->output->writeln( '<info>Activating required plugins...</info>' );
		$this->docker->run_inside_docker( $this->env_info, [ 'bash', '-c', 'wp plugin install https://github.com/WP-API/Basic-Auth/archive/master.zip --force --activate' ] );
	}

	/**
	 * Activate plugins and themes.
	 */
	private function activate_plugins_and_themes(): void {
		// Activate plugins.
		if ( ! $this->skip_activating_plugins ) {
			$this->output->writeln( '<info>Activating plugins...</info>' );
			$activation_output = $this->docker->run_inside_docker( $this->env_info, [ 'php', '/qit/bin/plugins-activate.php' ] );
			App::make( PluginActivationReportRenderer::class )->render_php_activation_report( $this->env_info, $activation_output );
		}

		// Handle themes.
		$theme_activation = new ThemeActivation( $this->env_info, $this->docker, $this->output );

		if ( ! $this->skip_activating_themes ) {
			$theme_activation->auto_activate_themes();
		}

		$theme_activation->maybe_activate_theme_that_is_dependency_of_sut();
	}

	/**
	 * Enable payment methods.
	 */
	private function enable_payment_method(): void {
		$this->output->writeln( '<info>Enabling Cash-on-delivery payment method...</info>' );
		$this->docker->run_inside_docker( $this->env_info, [ 'bash', '-c', 'wp wc payment_gateway update cod --enabled=true --user=admin' ] );
	}


	protected function get_generate_docker_compose_envs(): array {
		return [
			'PHP_VERSION'      => $this->env_info->php_version,
			'QIT_DOCKER_REDIS' => $this->env_info->object_cache ? 'yes' : 'no',
			'DOMAIN'           => $this->env_info->domain,
		];
	}

	/**
	 * @param array<string,string> $default_volumes
	 *
	 * @return array<string,string>
	 */
	protected function additional_default_volumes( array $default_volumes ): array {
		// Create a named docker volume.
		$named_volume = sprintf( 'qit_env_volume_%s', $this->env_info->env_id );
		$process      = new Process( [
			App::make( Docker::class )->find_docker(),
			'volume',
			'create',
			'--driver',
			'local',
			$named_volume,
		] );
		if ( $this->output->isVerbose() ) {
			$this->output->writeln( $process->getCommandLine() );
		}
		$process->mustRun( function ( $type, $buffer ) {
			if ( $this->output->isVerbose() ) {
				$this->output->write( $buffer );
			}
		} );

		$args = [
			App::make( Docker::class )->find_docker(),
			'run',
			'--rm',
			'--mount',
			'src=' . $named_volume . ',dst=/var/www/html',
			'busybox',
			'sh',
			'-c',
			'mkdir -p /var/www/html/wp-content/plugins && mkdir -p /var/www/html/wp-content/themes && mkdir -p /var/www/html/wp-content/mu-plugins && chown -R 82:82 /var/www/html',
		];

		/*
		 * Create "wp-content/plugins" and "wp-content/themes" directories mount binds have correct parent directory permissions.
		 * We make them owned by 82:82, which is the UID of "www-data" in our alpine PHP images.
		 * Once the container starts and the entrypoint is triggered, FixUID will map these to the runtime UID.
		 */
		$dirs_process = new Process( $args );
		$dirs_process->mustRun( function ( $type, $buffer ) {
			if ( $this->output->isVerbose() ) {
				$this->output->write( $buffer );
			}
		} );

		$default_volumes['/var/www/html'] = $named_volume;

		return $default_volumes;
	}

	protected function additional_output(): void {
		$io = new SymfonyStyle( App::make( InputInterface::class ), $this->output );

		if ( $this->output->isVerbose() ) {
			// Output a table of volume mappings.
			$io->section( 'Additional Volume Mappings' );

			if ( empty( $this->volumes ) ) {
				$this->output->writeln( 'No additional volume mappings.' );
			} else {
				$volumes = [];

				foreach ( $this->volumes as $v ) {
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
			$this->docker->run_inside_docker( $this->env_info, [ 'bash', '-c', 'wp plugin list --skip-plugins --skip-themes' ] );
			$this->docker->run_inside_docker( $this->env_info, [ 'bash', '-c', 'wp theme list --skip-plugins --skip-themes' ] );
		}

		if ( $this->output->isVerbose() || ! getenv( 'QIT_HIDE_SITE_INFO' ) ) {
			if ( ! getenv( 'QIT_CODEGEN' ) ) {
				$io->success( 'Temporary test environment created. (' . $this->env_info->env_id . ')' );
			}

			$listing = [
				sprintf( 'URL: %s', $this->env_info->site_url ),
				sprintf( 'Admin URL: %s/wp-admin', $this->env_info->site_url ),
				'Admin Credentials: admin/password',
				sprintf( 'PHP Version: %s', $this->env_info->php_version ),
				sprintf( 'WordPress Version: %s', $this->env_info->wp ),
				sprintf( 'Redis Object Cache? %s', $this->env_info->object_cache ? 'Yes' : 'No' ),
				sprintf( 'Path: %s', $this->env_info->temporary_env ),
			];

			$io->listing( $listing );

			if ( ! $this->output->isVerbose() ) {
				$io->writeln( sprintf( 'To see additional info, run with the "--verbose" flag.' ) );
			}
		} else {
			$this->output->writeln( '<info>Environment ready.</info>' );
		}

		$io->writeln( '' );
	}

	/*
	 * Download and import the performance database dump.
	 */
	private function generate_base_data(): void {
		$this->output->writeln( '<info>Generating test products and orders...</info>' );

		// Download and import in one command to avoid storing large files
		$import_command = implode( ' && ', [
			'cd /tmp',
			'echo "Downloading performance database dump..."',
			'curl -L -o woocommerce_dump.sql.zip https://qit.woo.com/wp-content/uploads/qit-env-db-dumps/woocommerce_dump.sql.zip',
			'echo "Importing database..."',
			"unzip -p woocommerce_dump.sql.zip | mysql -h qit_env_db_{$this->env_info->env_id} -u \$MYSQL_USER -p\$MYSQL_PASSWORD \$MYSQL_DATABASE --binary-mode=1",
			'rm -f woocommerce_dump.sql.zip',
			'echo "Database import completed"'
		] );

		try {
			$this->docker->run_inside_docker(
				$this->env_info,
				[ '/bin/bash', '-c', $import_command ],
				[],
				null,
				600 // 10 minute timeout for download and import
			);
		} catch ( \Exception $e ) {
			$this->output->writeln( '<comment>Warning: Could not import performance database. Continuing with fresh installation.</comment>' );
			if ( $this->output->isVerbose() ) {
				$this->output->writeln( '<comment>Error: ' . $e->getMessage() . '</comment>' );
			}
		}
	}
}