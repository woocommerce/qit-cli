<?php

namespace QIT_CLI\Environment\Environments;

use QIT_CLI\App;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use QIT_CLI\Tunnel\TunnelRunner;
use QIT_CLI\Environment\Environments\QITEnvInfo;

/**
 * Base class for QIT test environments (E2E, Performance, etc.)
 * Contains common functionality shared across different test environment types.
 *
 * @property QITEnvInfo $env_info
 */
abstract class QITEnvironment extends Environment {
	/** @var bool */
	protected $skip_activating_plugins = false;

	/** @var bool */
	protected $skip_activating_themes = false;

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

		// Replace placeholders.
		$qit_conf_contents = file_get_contents( $qit_conf );
		$qit_conf_contents = str_replace(
			[ '##QIT_PHP_CONTAINER_PLACEHOLDER##', '##QIT_DOMAIN_PLACEHOLDER##' ],
			[ sprintf( 'qit_env_php_%s', $this->env_info->env_id ), $this->env_info->domain ],
			$qit_conf_contents
		);

		// Allow subclasses to add custom nginx configuration.
		$custom_nginx_config = $this->get_custom_nginx_config();
		if ( ! empty( $custom_nginx_config ) ) {
			$qit_conf_contents .= $custom_nginx_config;
		}

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
	 * Get custom nginx configuration for this environment type.
	 * Override in subclasses to add environment-specific nginx settings.
	 */
	protected function get_custom_nginx_config(): string {
		return '';
	}

	protected function post_up(): void {
		$this->setup_site_url_and_tunnel();
		$this->environment_monitor->environment_added_or_updated( $this->env_info );
		$this->install_php_extensions();
		$this->copy_mu_plugins();
		$this->setup_wordpress();
		$this->before_plugin_activation();
		$this->activate_plugins_and_themes();
		$this->after_plugin_activation();
	}

	/**
	 * Setup site URL and tunnel configuration.
	 */
	protected function setup_site_url_and_tunnel(): void {
		if ( $this->env_info->tunnel ) {
			$this->env_info->nginx_port = (string) $this->get_nginx_port();

			$site_url = App::make( TunnelRunner::class )->start_tunnel( "http://localhost:{$this->env_info->nginx_port}/", $this->env_info->env_id );

			$this->env_info->domain     = parse_url( $site_url, PHP_URL_HOST );
			$this->env_info->nginx_port = (string) parse_url( $site_url, PHP_URL_PORT );
			$this->env_info->site_url   = sprintf( $site_url );
		} else {
			if ( getenv( 'QIT_EXPOSE_ENVIRONMENT_TO' ) === 'DOCKER' ) {
				// Inside docker, the port is always 80 (that's what Nginx is listening to).
				$this->env_info->nginx_port = '80';
				$this->env_info->site_url   = sprintf( 'http://%s', $this->env_info->domain );
			} else {
				$this->env_info->nginx_port = (string) $this->get_nginx_port();
				$this->env_info->site_url   = sprintf( 'http://%s:%s', $this->env_info->domain, $this->env_info->nginx_port );
			}
		}
	}

	/**
	 * Install PHP extensions if needed.
	 */
	protected function install_php_extensions(): void {
		if ( empty( $this->env_info->php_extensions ) ) {
			return;
		}

		$this->output->writeln( '<info>Installing PHP extensions...</info>' );
		$this->docker->run_inside_docker( $this->env_info, [ '/bin/bash', '-c', 'bash /qit/bin/php-extensions.sh' ], [
			'PHP_EXTENSIONS' => implode( ' ', $this->env_info->php_extensions ),
		], '0:0' );
	}

	/**
	 * Copy mu-plugins to the environment.
	 * Override in subclasses to customize mu-plugin handling.
	 */
	protected function copy_mu_plugins(): void {
		$this->docker->run_inside_docker( $this->env_info, [ '/bin/bash', '-c', 'cp /qit/mu-plugins/* /var/www/html/wp-content/mu-plugins 2>&1' ] );
	}

	/**
	 * Setup WordPress installation.
	 */
	protected function setup_wordpress(): void {
		$this->output->writeln( '<info>Installing WordPress...</info>' );
		$this->docker->run_inside_docker( $this->env_info, [ '/bin/bash', '-c', 'bash /qit/bin/wordpress-setup.sh 2>&1' ], [
			'TUNNEL'                  => $this->env_info->tunnel ? 'yes' : 'no',
			'WORDPRESS_VERSION'       => $this->env_info->wordpress_version === 'stable' ? 'latest' : $this->env_info->wordpress_version,
			'SITE_URL'                => $this->env_info->site_url,
			'QIT_DOCKER_REDIS'        => $this->env_info->object_cache ? 'yes' : 'no',
			'QIT_NETWORK_RESTRICTION' => $this->env_info->network_restriction ? 'true' : 'false',
		] );
	}

	/**
	 * Hook to run operations before plugin activation.
	 * Override in subclasses to add custom logic.
	 */
	protected function before_plugin_activation(): void {
		// Default: do nothing. Override in subclasses if needed.
	}

	/**
	 * Activate plugins and themes.
	 */
	protected function activate_plugins_and_themes(): void {
		if ( ! $this->env_info->skip_activating_plugins ) {
			$this->output->writeln( '<info>Activating plugins...</info>' );
			$activation_output = $this->docker->run_inside_docker( $this->env_info, [ 'php', '/qit/bin/plugins-activate.php' ] );
			App::make( \QIT_CLI\Environment\PluginActivationReportRenderer::class )->render_php_activation_report( $this->env_info, $activation_output );
		}

		$theme_activation = new ThemeActivation( $this->env_info, $this->docker, $this->output );

		// Always install parent themes if needed (even when skip_activating_themes is true)
		// This ensures child themes have their required parent themes available
		$theme_activation->install_parent_themes_if_needed();

		if ( ! $this->env_info->skip_activating_themes ) {
			$theme_activation->auto_activate_themes();
			$theme_activation->maybe_activate_theme_that_is_dependency_of_sut();
		}
	}

	/**
	 * Hook to run operations after plugin activation.
	 * Override in subclasses to add custom logic.
	 */
	protected function after_plugin_activation(): void {
		// Default: do nothing. Override in subclasses if needed.
	}

	/**
	 * @return array<string,string>
	 */
	protected function get_generate_docker_compose_envs(): array {
		return [
			'PHP_VERSION'             => $this->env_info->php_version,
			'QIT_DOCKER_REDIS'        => $this->env_info->object_cache ? 'yes' : 'no',
			'DOMAIN'                  => $this->env_info->domain,
			'QIT_NETWORK_RESTRICTION' => $this->env_info->network_restriction ? 'true' : 'false',
		];
	}

	/**
	 * @param array<string,string> $default_volumes
	 *
	 * @return array<string,string>
	 */
	protected function additional_default_volumes( array $default_volumes ): array {
		$default_volumes['/var/www/html'] = sprintf( 'qit_env_volume_%s', $this->env_info->env_id );

		return $default_volumes;
	}

	protected function additional_output(): void {
		$io = new SymfonyStyle( App::make( InputInterface::class ), $this->output );

		if ( $this->output->isVerbose() ) {
			$io->section( 'Additional Volume Mappings' );

			if ( empty( $this->volumes ) ) {
				$this->output->writeln( 'No additional volume mappings.' );
			} else {
				$volumes = [];

				foreach ( $this->volumes as $v ) {
					// Volumes are in the format: array{local: string, in_container: string}.
					// Type is guaranteed by PHPDoc, so we can directly access the keys.
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

		$this->render_environment_info( $io );

		$io->writeln( '' );
	}

	/**
	 * Render environment information output.
	 * Override in subclasses to customize output.
	 */
	abstract protected function render_environment_info( SymfonyStyle $io ): void;
}
