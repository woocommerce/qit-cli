<?php

namespace QIT_CLI\Environment\Environments\E2E;

use QIT_CLI\App;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\Environment\EnvUpChecker;
use QIT_CLI\Environment\PluginActivationReportRenderer;
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
	protected $env_info;

	/** @var bool */
	protected $skip_activating_plugins = false;

	public function get_name(): string {
		return 'e2e';
	}

	public function set_skip_activating_plugins( bool $skip_activating_plugins ): void {
		$this->skip_activating_plugins = $skip_activating_plugins;
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

		$this->environment_monitor->environment_added_or_updated( $this->env_info );

		/**
		 * @phpstan-ignore-next-line
		 */
		if ( ! empty( $this->env_info->php_extensions ) ) {
			$this->output->writeln( '<info>Installing PHP extensions...</info>' );
			// Install PHP extensions, if needed.
			$this->docker->run_inside_docker( $this->env_info, [ '/bin/bash', 'bash /qit/bin/php-extensions.sh' ], [
				'PHP_EXTENSIONS' => implode( ' ', $this->env_info->php_extensions ), // Space-separated list of PHP extensions.
			], '0:0' );
		}

		// Copy mu-plugins.
		$this->docker->run_inside_docker( $this->env_info, [ '/bin/bash', '-c', 'mkdir -p /var/www/html/wp-content/mu-plugins && cp /qit/mu-plugins/* /var/www/html/wp-content/mu-plugins 2>&1' ] );

		// Setup WordPress.
		$this->output->writeln( '<info>Setting up WordPress...</info>' );
		$this->docker->run_inside_docker( $this->env_info, [ '/bin/bash', '-c', 'bash /qit/bin/wordpress-setup.sh 2>&1' ], [
			'WORDPRESS_VERSION' => $this->env_info->wp,
			'SITE_URL'          => $this->env_info->site_url,
			'QIT_DOCKER_REDIS'  => $this->env_info->object_cache ? 'yes' : 'no',
		] );

		// Activate plugins.
		if ( ! $this->skip_activating_plugins ) {
			$this->output->writeln( '<info>Activating plugins...</info>' );
			$this->docker->run_inside_docker( $this->env_info, [ 'php', '/qit/bin/plugins-activate.php' ] );
			App::make( PluginActivationReportRenderer::class )->render_php_activation_report( $this->env_info );
		}
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
			'mkdir -p /var/www/html/wp-content/plugins && mkdir -p /var/www/html/wp-content/themes && chown -R 82:82 /var/www/html',
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
}
