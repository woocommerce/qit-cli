<?php

namespace QIT_CLI\Environment\Environments\Performance;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Environment\Environments\QITEnvironment;
use Symfony\Component\Console\Style\SymfonyStyle;

class PerformanceEnvironment extends QITEnvironment {
	/** @var string */
	protected $description = 'Performance Test Environment';

	/**
	 * @var PerformanceEnvInfo
	 */
	protected \QIT_CLI\Environment\Environments\EnvInfo $env_info;

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
				sprintf( 'PHP Version: %s', $this->env_info->php ),
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
	}

	/**
	 * Download and import the performance database dump.
	 */
	private function generate_base_data(): void {
		$this->output->writeln( '<info>Generating test products and orders...</info>' );

		// Get database dump download URL.
		$cache       = App::make( Cache::class );
		$db_dump_url = $cache->get_manager_sync_data( 'db_dump_file' );

		// Download and import in one command to avoid storing large files.
		$import_command = implode( ' && ', [
			'cd /tmp',
			'echo "Downloading performance database dump..."',
			"curl -L -o woocommerce_dump.sql.zip {$db_dump_url}",
			'echo "Importing database..."',
			"unzip -p woocommerce_dump.sql.zip | mariadb -h qit_env_db_{$this->env_info->env_id} -u \$MYSQL_USER -p\$MYSQL_PASSWORD \$MYSQL_DATABASE --skip-ssl --binary-mode=1",
			'rm -f woocommerce_dump.sql.zip',
			'echo "Database import completed"',
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
}
