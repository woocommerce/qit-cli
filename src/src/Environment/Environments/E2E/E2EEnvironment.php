<?php

namespace QIT_CLI\Environment\Environments\E2E;

use QIT_CLI\App;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\Environment\Environments\ThemeActivation;
use QIT_CLI\Environment\EnvUpChecker;
use QIT_CLI\Environment\PluginActivationReportRenderer;
use QIT_CLI\Tunnel\TunnelRunner;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

class E2EEnvironment extends Environment {
	/** @var string */
	protected $description = 'E2E Environment';

	/**
	 * @var E2EEnvInfo
	 */
	protected \QIT_CLI\Environment\Environments\EnvInfo $env_info;

	/** @var bool */
	protected $skip_activating_plugins = false;

	/** @var bool */
	protected $skip_activating_themes = false;

	public function get_name(): string {
		return 'e2e';
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

		// Replace "##QIT_PHP_CONTAINER_PLACEHOLDER##" with the PHP Container.
		$qit_conf_contents = file_get_contents( $qit_conf );
		$qit_conf_contents = str_replace( '##QIT_PHP_CONTAINER_PLACEHOLDER##', sprintf( 'qit_env_php_%s', $this->env_info->env_id ), $qit_conf_contents );
		$qit_conf_contents = str_replace( '##QIT_DOMAIN_PLACEHOLDER##', $this->env_info->domain, $qit_conf_contents );
		file_put_contents( $qit_conf, $qit_conf_contents );
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

		/**
		 * @phpstan-ignore-next-line
		 */
		if ( ! empty( $this->env_info->php_extensions ) ) {
			$this->output->writeln( '<info>Installing PHP extensions...</info>' );
			// Install PHP extensions, if needed.
			$this->docker->run_inside_docker( $this->env_info, [ '/bin/bash', '-c', 'bash /qit/bin/php-extensions.sh' ], [
				'PHP_EXTENSIONS' => implode( ' ', $this->env_info->php_extensions ), // Space-separated list of PHP extensions.
			], '0:0' );
		}

		// Copy mu-plugins.
		$this->docker->run_inside_docker( $this->env_info, [ '/bin/bash', '-c', 'cp /qit/mu-plugins/* /var/www/html/wp-content/mu-plugins 2>&1' ] );

		// Setup WordPress.
		$this->output->writeln( '<info>Installing WordPress...</info>' );
		$this->docker->run_inside_docker( $this->env_info, [ '/bin/bash', '-c', 'bash /qit/bin/wordpress-setup.sh 2>&1' ], [
			'TUNNEL'            => $this->env_info->tunnel ? 'yes' : 'no',
			'WORDPRESS_VERSION' => $this->env_info->wp === 'stable' ? 'latest' : $this->env_info->wp,
			'SITE_URL'          => $this->env_info->site_url,
			'QIT_DOCKER_REDIS'  => $this->env_info->object_cache ? 'yes' : 'no',
		] );

		/*
		--------------------------------------------------------------
		 * Execute bootstrap test‑packages
		 * ------------------------------------------------------------
		 */
		if ( ! empty( $this->env_info->bootstrap_packages ) ) {
			$runner     = new \QIT_CLI\Environment\PackagePhaseRunner(
				$this->docker,
				$this->output
			);
			$total_cmds = 0;

			$this->output->writeln( "\n<comment>🔧  Test‑package bootstrap phase</comment>" );
			$this->output->writeln( '<comment>----------------------------</comment>' );

			foreach ( $this->env_info->bootstrap_packages as $pkg_id => $info ) {
				$total_cmds += $runner->run_phase(
					$this->env_info,
					'globalSetup',
					$pkg_id,
					$info['path']
				);
			}

			if ( $total_cmds === 0 ) {
				$this->output->writeln(
					"  <info>• No setup commands found – skipping</info>\n"
				);
			} else {
				$this->output->writeln(
					"\n<info>[OK] 🟩  All {$total_cmds} command(s) executed successfully.</info>\n"
				);
			}
		}

		// Activate plugins.
		if ( ! $this->skip_activating_plugins ) {
			$this->output->writeln( '<info>Activating plugins...</info>' );
			$activation_output = $this->docker->run_inside_docker( $this->env_info, [ 'php', '/qit/bin/plugins-activate.php' ] );
			App::make( PluginActivationReportRenderer::class )->render_php_activation_report( $this->env_info, $activation_output );
		}

		$theme_activation = new ThemeActivation(
			$this->env_info,
			$this->docker,
			$this->output
		);

		// Activate theme.
		if ( ! $this->skip_activating_themes ) {
			$theme_activation->auto_activate_themes();
		}

		$theme_activation->maybe_activate_theme_that_is_dependency_of_sut();
	}

	protected function additional_output(): void {
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
				sprintf( 'PHP Version: %s', $this->env_info->php ),
				sprintf( 'WordPress Version: %s', $this->env_info->wp ),
			];
			
			if ( ! empty( $this->env_info->woo ) ) {
				$listing[] = sprintf( 'WooCommerce: %s', $this->env_info->woo );
			}
			
			$listing[] = sprintf( 'Redis Object Cache? %s', $this->env_info->object_cache ? 'Yes' : 'No' );
			$listing[] = sprintf( 'Path: %s', $this->env_info->temporary_env );

			$io->listing( $listing );

			if ( ! $this->output->isVerbose() ) {
				$io->writeln( sprintf( 'To see additional info, run with the "--verbose" flag.' ) );
			}
		} else {
			$this->output->writeln( '<info>Environment ready.</info>' );
		}

		// Try to connect to the website if we are exposing this environment to host.
		if ( getenv( 'QIT_EXPOSE_ENVIRONMENT_TO' ) !== 'DOCKER' ) {
			App::make( EnvUpChecker::class )->check_and_render( $this->env_info );
		}

		$io->writeln( '' );
	}

	/**
	 * @return array<string,string>
	 */
	protected function get_generate_docker_compose_envs(): array {
		return [
			'PHP_VERSION'      => $this->env_info->php,
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
			'mkdir -p /var/www/html/wp-content/plugins && mkdir -p /var/www/html/wp-content/themes && mkdir -p /var/www/html/wp-content/mu-plugins && chown -R 1000:1000 /var/www/html',
		];

		/*
		 * Create "wp-content/plugins", "wp-content/themes", and "wp-content/mu-plugins" directories with correct permissions.
		 * Owned by 1000:1000 to ensure the cp command in post_up succeeds without root or 777.
		 * Fixuid skips the mounted volume /var/www/html, so we set ownership directly.
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
}
