<?php

namespace QIT_CLI\Environment\Environments\E2E;

use QIT_CLI\App;
use QIT_CLI\Environment\EnvUpChecker;
use QIT_CLI\Environment\Environments\QITEnvironment;
use QIT_CLI\Environment\GlobalSetupOrchestrator;
use QIT_CLI\Environment\PackagePhaseRunner;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @property E2EEnvInfo $env_info
 */
class E2EEnvironment extends QITEnvironment {
	/** @var string */
	protected $description = 'E2E Environment';

	public function get_name(): string {
		return 'e2e';
	}

	protected function setup_site_url_and_tunnel(): void {
		parent::setup_site_url_and_tunnel();

		// Set container names for reference (E2E-specific)
		$this->env_info->php_container = sprintf( 'qit_env_php_%s', $this->env_info->env_id );
		$this->env_info->db_container  = sprintf( 'qit_env_db_%s', $this->env_info->env_id );

		// Try to get the database port (if exposed)
		try {
			$db_container = $this->env_info->get_docker_container( 'db' );
			if ( $db_container ) {
				$docker              = $this->docker->find_docker();
				$get_db_port_process = new \Symfony\Component\Process\Process( [ $docker, 'port', $db_container, '3306' ] );
				$get_db_port_process->run();

				if ( $get_db_port_process->isSuccessful() ) {
					$output = $get_db_port_process->getOutput();
					if ( preg_match( '/0\.0\.0\.0:(\d+)/', $output, $matches ) ) {
						$this->env_info->db_port = (int) $matches[1];
					}
				}
			}
		} catch ( \Exception $e ) {
			// Database port might not be exposed, that's okay
			$this->env_info->db_port = 0;
		}
	}

	protected function copy_mu_plugins(): void {
		parent::copy_mu_plugins();

		// Copy mu-plugins from test packages
		if ( ! empty( $this->env_info->test_packages_for_setup ) ) {
			foreach ( $this->env_info->test_packages_for_setup as $info ) {
				// Check if manifest exists and has mu_plugins
				if ( ! empty( $info['manifest'] ) && ! empty( $info['manifest']['mu_plugins'] ) ) {
					$container_path = $info['container_path'];
					if ( empty( $container_path ) ) {
						continue;
					}

					foreach ( $info['manifest']['mu_plugins'] as $mu_plugin ) {
						// Resolve the mu-plugin path relative to the test package directory
						$mu_plugin_path = $container_path . '/' . ltrim( $mu_plugin, './' );
						$copy_command   = sprintf(
							'if [ -f "%s" ]; then cp "%s" /var/www/html/wp-content/mu-plugins/ 2>&1; fi',
							$mu_plugin_path,
							$mu_plugin_path
						);
						$this->docker->run_inside_docker( $this->env_info, [ '/bin/bash', '-c', $copy_command ] );
					}
				}
			}
		}
	}

	protected function before_plugin_activation(): void {
		// Utility packages need to run AFTER plugin activation
		// (they may depend on plugin post types, taxonomies, etc.)
		// See after_plugin_activation() instead
	}

	protected function after_plugin_activation(): void {
		/*
		--------------------------------------------------------------
		 * Execute test package setup phases (unless skipped)
		 * ------------------------------------------------------------
		 * Note: Running AFTER plugin activation so WooCommerce/other plugins
		 * are fully initialized (post types, taxonomies, etc.)
		 */
		if ( ! empty( $this->env_info->test_packages_for_setup ) && ! $this->env_info->skip_test_phases ) {
			// Set up test_packages_metadata for PackagePhaseRunner to find container paths
			// This maps package_id => metadata (needed by PackagePhaseRunner)
			$this->env_info->test_packages_metadata = [];
			foreach ( $this->env_info->test_packages_for_setup as $info ) {
				if ( isset( $info['package_id'] ) ) {
					$this->env_info->test_packages_metadata[ $info['package_id'] ] = $info;
				}
			}

			$runner = new PackagePhaseRunner(
				$this->docker,
				$this->output,
				$this->environment_vars,
				$this->manifest_parser
			);

			$this->output->writeln( '' );
			if ( $this->env_info->global_setup_only ) {
				$this->output->writeln( 'Running Package Setup (--global-setup mode)' );
				$this->output->writeln( str_repeat( '-', 44 ) );
			} else {
				$this->output->writeln( 'Running Test Package Setup' );
				$this->output->writeln( str_repeat( '-', 26 ) );
			}

			// Create a custom orchestrator for setup packages
			$ctrf_validator     = $this->ctrf_validator;
			$setup_orchestrator = App::make( GlobalSetupOrchestrator::class );

			// Get environment manager from App container if available (for secret handling)
			$env_manager = App::getVar( 'environment_manager', null );
			if ( $env_manager !== null ) {
				$setup_orchestrator->set_environment_manager( $env_manager );
			}

			$is_first_package = true;
			foreach ( $this->env_info->test_packages_for_setup as $pkg_path => $info ) {
				// Use the actual package ID from manifest for orchestrator
				$package_id = $info['package_id'] ?? $pkg_path; // Fallback for backwards compatibility
				$setup_orchestrator->start_package( $package_id, $info );

				try {
					$total_commands = 0;

					// Run globalSetup for ALL packages
					$commands_run    = $runner->run_phase(
						$this->env_info,
						'globalSetup',
						$package_id,
						$info['path'],
						null,  // No artifacts_dir for setup phases
						$setup_orchestrator
					);
					$total_commands += $commands_run;

					// Run setup phase ONLY for the first (main) package
					// This is for manual testing - run:e2e handles setup per package with DB restore
					// Run setup for ALL packages when global_setup_only, otherwise only first
					if ( $is_first_package || $this->env_info->global_setup_only ) {
						$setup_commands       = $runner->run_phase(
							$this->env_info,
							'setup',
							$package_id,
							$info['path'],
							null,  // No artifacts_dir for setup phases
							$setup_orchestrator
						);
							$total_commands  += $setup_commands;
							$is_first_package = false;
					}

					$setup_orchestrator->end_package( $package_id, true, $total_commands );
				} catch ( \Exception $e ) {
					$setup_orchestrator->end_package( $package_id, false, 0, $e->getMessage() );
					// Continue with other packages even if one fails
				}
			}
		}
	}

	protected function render_environment_info( SymfonyStyle $io ): void {
		// Only show verbose output if explicitly requested
		if ( $this->output->isVerbose() ) {
			if ( ! getenv( 'QIT_CODEGEN' ) ) {
				$io->success( 'Temporary test environment created. (' . $this->env_info->env_id . ')' );
			}

			$listing = [
				sprintf( 'URL: %s', $this->env_info->site_url ),
				sprintf( 'Admin URL: %s/wp-admin', $this->env_info->site_url ),
				'Admin Credentials: admin/password',
				sprintf( 'PHP Version: %s', $this->env_info->php_version ),
				sprintf( 'WordPress Version: %s', $this->env_info->wordpress_version ),
			];

			if ( ! empty( $this->env_info->woocommerce_version ) ) {
				$listing[] = sprintf( 'WooCommerce: %s', $this->env_info->woocommerce_version );
			}

			$listing[] = sprintf( 'Redis Object Cache? %s', $this->env_info->object_cache ? 'Yes' : 'No' );
			$listing[] = sprintf( 'Path: %s', $this->env_info->temporary_env );

			$io->listing( $listing );
		} elseif ( getenv( 'QIT_HIDE_SITE_INFO' ) ) {
			$this->output->writeln( '<info>Environment ready.</info>' );
		}
		// Otherwise, show nothing here - the compact summary will be shown by UpEnvironmentCommand

		// Try to connect to the website if we are exposing this environment to host.
		if ( getenv( 'QIT_EXPOSE_ENVIRONMENT_TO' ) !== 'DOCKER' ) {
			App::make( EnvUpChecker::class )->check_and_render( $this->env_info );
		}
	}
}
