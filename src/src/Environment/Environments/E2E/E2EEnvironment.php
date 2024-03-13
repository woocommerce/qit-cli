<?php

namespace QIT_CLI\Environment\Environments\E2E;

use QIT_CLI\App;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\Environment\EnvUpChecker;
use QIT_CLI\Environment\PluginActivationReportRenderer;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
		$this->env_info->site_url = sprintf( 'http://%s:%s', $this->env_info->domain, $this->get_nginx_port() );
		$this->environment_monitor->environment_added_or_updated( $this->env_info );

		/**
		 * @phpstan-ignore-next-line
		 */
		if ( ! empty( $this->env_info->php_extensions ) ) {
			$this->output->writeln( '<info>Installing PHP extensions...</info>' );
			// Install PHP extensions, if needed.
			$this->docker->run_inside_docker( $this->env_info, [ '/bin/bash', '/qit/bin/php-extensions.sh' ], [
				'PHP_EXTENSIONS' => implode( ' ', $this->env_info->php_extensions ), // Space-separated list of PHP extensions.
			], '0:0' );
		}

		// Setup WordPress.
		$this->output->writeln( '<info>Setting up WordPress...</info>' );
		$this->docker->run_inside_docker( $this->env_info, [ '/bin/bash', '/qit/bin/wordpress-setup.sh' ], [
			'WORDPRESS_VERSION'   => $this->env_info->wordpress_version,
			'WOOCOMMERCE_VERSION' => $this->env_info->woocommerce_version,
			'PLUGINS_TO_INSTALL'  => json_encode( $this->env_info->plugins ),
			'THEMES_TO_INSTALL'   => json_encode( $this->env_info->themes ),
			'SUT_SLUG'            => 'automatewoo', // @todo: Set this.
			'SITE_URL'            => $this->env_info->site_url,
			'QIT_DOCKER_REDIS'    => $this->env_info->object_cache ? 'yes' : 'no',
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

		$io->success( 'Temporary test environment created. (' . $this->env_info->env_id . ')' );

		$listing = [
			sprintf( 'URL: %s', $this->env_info->site_url ),
			sprintf( 'Admin URL: %s/wp-admin', $this->env_info->site_url ),
			'Admin Credentials: admin/password',
			sprintf( 'PHP Version: %s', $this->env_info->php_version ),
			sprintf( 'WordPress Version: %s', $this->env_info->wordpress_version ),
			sprintf( 'Redis Object Cache? %s', $this->env_info->object_cache ? 'Yes' : 'No' ),
			sprintf( 'Path: %s', $this->env_info->temporary_env ),
		];

		$io->listing( $listing );

		if ( ! $this->output->isVerbose() ) {
			$io->writeln( sprintf( 'To see additional info, run with the "--verbose" flag.' ) );
		}

		// Try to connect to the website.
		App::make( EnvUpChecker::class )->check_and_render( $this->env_info );

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
		$default_volumes['/var/www/html'] = "{$this->env_info->temporary_env}/html";

		return $default_volumes;
	}
}
