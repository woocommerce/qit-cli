<?php

namespace QIT_CLI\Environment\Environments\Performance;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Environment\Environments\QITEnvironment;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @property PerformanceEnvInfo $env_info
 */
class PerformanceEnvironment extends QITEnvironment {
	/** @var string */
	protected $description = 'Performance Test Environment';

	public function get_name(): string {
		return 'performance';
	}

	protected function get_custom_nginx_config(): string {
		return "\n\n# Performance test optimizations\n" .
			"keepalive_requests 1000;\n";
	}

	protected function before_plugin_activation(): void {
		$this->activate_required_plugins();
	}

	protected function after_plugin_activation(): void {
		$this->enable_payment_method();
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
	 * Enable payment methods.
	 */
	private function enable_payment_method(): void {
		$this->output->writeln( '<info>Enabling Cash-on-delivery payment method...</info>' );
		$this->docker->run_inside_docker( $this->env_info, [ 'bash', '-c', 'wp wc payment_gateway update cod --enabled=true --user=admin' ] );
	}

	protected function render_environment_info( SymfonyStyle $io ): void {
		if ( $this->output->isVerbose() || ! getenv( 'QIT_HIDE_SITE_INFO' ) ) {
			if ( ! getenv( 'QIT_CODEGEN' ) ) {
				$io->success( 'Temporary test environment created. (' . $this->env_info->env_id . ')' );
			}

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
		} else {
			$this->output->writeln( '<info>Environment ready.</info>' );
		}
	}

	/**
	 * Download and import the performance database dump.
	 * The dump file is cached in /tmp for potential database resets.
	 */
	public function generate_base_data(): void {
		$this->output->writeln( '<info>Generating test products and orders...</info>' );

		// Get database dump download URL.
		$cache       = App::make( Cache::class );
		$db_dump_url = $cache->get_manager_sync_data( 'db_dump_file' );

		$import_command = implode( ' && ', [
			'cd /tmp',
			'echo "Downloading performance database dump..."',
			"curl -L -o woocommerce_dump.sql.zip {$db_dump_url}",
			'echo "Importing database..."',
			"unzip -p woocommerce_dump.sql.zip | mariadb -h qit_env_db_{$this->env_info->env_id} -u \$MYSQL_USER -p\$MYSQL_PASSWORD \$MYSQL_DATABASE --skip-ssl --binary-mode=1",
			'echo "Database import completed (dump file cached for potential resets)"',
		] );

		try {
			$this->docker->run_inside_docker(
				$this->env_info,
				[ '/bin/bash', '-c', $import_command ],
				[],
				null,
				600 // 10 minute timeout for download and import.
			);
		} catch ( \Exception $e ) {
			$this->output->writeln( '<comment>Warning: Could not import performance database. Continuing with fresh installation.</comment>' );
			if ( $this->output->isVerbose() ) {
				$this->output->writeln( '<comment>Error: ' . $e->getMessage() . '</comment>' );
			}
		}
	}

	/**
	 * Toggle a plugin's activation state.
	 *
	 * @param string $sut_slug The plugin slug to toggle.
	 * @param string $action Either 'activate' or 'deactivate'.
	 */
	private function toggle_plugin( string $sut_slug, string $action ): void {
		if ( empty( $sut_slug ) ) {
			throw new \InvalidArgumentException( 'Plugin slug cannot be empty.' );
		}

		if ( ! in_array( $action, [ 'activate', 'deactivate' ], true ) ) {
			throw new \InvalidArgumentException(
				sprintf( 'Invalid action: %s. Expected "activate" or "deactivate".', $action )
			);
		}

		$action_present = $action === 'activate' ? 'Activating' : 'Deactivating';
		$this->output->writeln( sprintf( '<info>%s SUT plugin: %s</info>', $action_present, $sut_slug ) );

		$this->docker->run_inside_docker(
			$this->env_info,
			[ 'wp', 'plugin', $action, $sut_slug, '--quiet' ]
		);

		// Flush caches to ensure clean state.
		$this->docker->run_inside_docker(
			$this->env_info,
			[ 'wp', 'cache', 'flush', '--quiet' ]
		);
	}

	/**
	 * Deactivate the SUT plugin to create baseline environment state.
	 *
	 * @param string $sut_slug The SUT plugin slug to deactivate.
	 */
	public function deactivate_sut_plugin( string $sut_slug ): void {
		$this->toggle_plugin( $sut_slug, 'deactivate' );
	}

	/**
	 * Activate the SUT plugin.
	 *
	 * @param string $sut_slug The SUT plugin slug to activate.
	 */
	public function activate_sut_plugin( string $sut_slug ): void {
		$this->toggle_plugin( $sut_slug, 'activate' );
	}
}
