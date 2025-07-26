<?php

namespace QIT_CLI\PreCommand\Pipeline\Stages;

use QIT_CLI\PreCommand\Interfaces\EnvironmentCommand;
use QIT_CLI\PreCommand\Pipeline\PipelineContext;
use QIT_CLI\PreCommand\Pipeline\PipelineStage;
use QIT_CLI\PreCommand\Interfaces\ConfigurableTestCommand;
use QIT_CLI\PreCommand\Interfaces\LocalTestCommand;
use QIT_CLI\TestPackageDownloader;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Single, generic resolver for *all* test‑package scenarios.
 *
 *  – Works for LocalTestCommand    (run locally against a WP env)
 *  – Works for ConfigurableTestCommand (remote run via Manager)
 *  – Honours explicit --test-package / <package> CLI input
 *
 * The stage decides what packages are *really* needed by looking at:
 *   • the Command type
 *   • the concrete test‑type/profile/environment chosen
 *   • CLI overrides
 * Everything else that might be present in qit.json is ignored.
 */
class ResolveTestPackagesStage implements PipelineStage {
	private TestPackageDownloader $downloader;

	public function __construct( TestPackageDownloader $downloader ) {
		$this->downloader = $downloader;
	}

	public function process( PipelineContext $context ): PipelineContext {
		$cmd      = $context->command;
		$input    = $context->input;
		$resolved = $context->get_resolved_config();   // already filtered/extended config

		$needed_packages = [];

		/*
		--------------------------------------------------------------------
		 * 1.  CLI‑level explicit packages (highest priority)
		 * ------------------------------------------------------------------
		 */
		$cli_packages    = $this->cliPackageList( $input );
		$needed_packages = array_merge( $needed_packages, $cli_packages );

		/*
		--------------------------------------------------------------------
		 * 2.  Packages implied by the current command
		 * ------------------------------------------------------------------
		 */
		if ( $cmd instanceof LocalTestCommand ) {
			/* ---------------------------------------------------------
			 * LOCAL test commands (run:e2e, etc.)
			 * -------------------------------------------------------*/
			$env_name = $cmd->get_environment_name();
			if ( isset( $resolved->environments[ $env_name ]['setup_only'] ) ) {
				$needed_packages = array_merge(
					$needed_packages,
					$resolved->environments[ $env_name ]['setup_only']
				);
			}

			// 2‑b) Packages declared in the active test profile (if any)
			if ( $cmd->get_test_type() && $cmd->get_test_profile() ) {
				$pkg             = $resolved->test_types[ $cmd->get_test_type() ]
				                   [ $cmd->get_test_profile() ]['test_packages'] ?? [];
				$needed_packages = array_merge( $needed_packages, $pkg );
			}
		} elseif ( $cmd instanceof EnvironmentCommand ) {
			/* ---------------------------------------------------------
			 * Pure env commands (env:up, env:enter, …)
			 * -------------------------------------------------------*/
			$env_name = $cmd->get_environment_name();     // exists on EnvironmentCommand

			if ( isset( $resolved->environments[ $env_name ]['setup_only'] ) ) {
				$needed_packages = array_merge(
					$needed_packages,
					$resolved->environments[ $env_name ]['setup_only']
				);
			}
		} elseif ( $cmd instanceof ConfigurableTestCommand ) {
			// Remote runs only need the active profile's packages
			$pkg             = $resolved->test_types[ $cmd->get_test_type() ]
			                   [ $cmd->get_test_profile() ]['test_packages'] ?? [];
			$needed_packages = array_merge( $needed_packages, $pkg );
		}

		// De‑dupe + normalise
		$needed_packages = array_values( array_unique( array_filter( $needed_packages ) ) );

		/*
		--------------------------------------------------------------------
		 * 3.  Nothing to do?  Bail early.
		 * ------------------------------------------------------------------
		 */
		if ( empty( $needed_packages ) ) {
			$context->set_test_packages( [] );

			return $context;
		}

		/*
		 * 4.  Split local ⬄ remote
		 * -------------------------------------------------------------*/
		$local_packages  = [];
		$remote_packages = [];

		foreach ($needed_packages as $id) {
			$meta = $resolved->test_package_metadata[$id] ?? null;
			if ($meta && !empty($meta['local'])) {
				$local_packages[] = $id;
			} else {
				$remote_packages[] = $id;
			}
		}

		$resolved_paths = [];

		/*--------------------------------------------------------------
		 * 5‑a  Handle LOCAL packages
		 *-------------------------------------------------------------*/
		foreach ($local_packages as $identifier) {
			$configDir = dirname($context->get('config_file') ?? getcwd());
			$path      = $this->resolve_local_package_path($identifier, $configDir);

			$resolved_paths[$identifier] = [
				'path'     => $path,
				'type'     => 'local',
				'resolved' => true,
			];
		}

		/*--------------------------------------------------------------
		 * 5‑b  Handle REMOTE packages
		 *-------------------------------------------------------------*/
		if ($remote_packages) {
			$verify        = $input->hasOption('verify')
				? $input->getOption('verify') !== false
				: true;
			$download_urls = $this->downloader->fetch_download_urls($remote_packages);

			foreach ($remote_packages as $identifier) {
				if (!isset($download_urls[$identifier])) {
					throw new RuntimeException("Package not found or access denied: {$identifier}");
				}

				$url_info = $download_urls[$identifier];
				$zip      = sys_get_temp_dir() . '/qit-' . $this->downloader->generate_filename($identifier);
				$extract  = sys_get_temp_dir() . '/qit-' . str_replace(['/', ':'], '-', $identifier);

				$this->downloader->download_file($url_info['url'], $zip);

				if ($verify && !empty($url_info['checksum'])) {
					if (!$this->downloader->verify_checksum($zip, $url_info['checksum'])) {
						unlink($zip);
						throw new RuntimeException("Checksum verification failed for: {$identifier}");
					}
				}

				$this->downloader->extract_package($zip, $extract, true);
				unlink($zip);

				$resolved_paths[$identifier] = [
					'path'     => $extract,
					'type'     => 'remote',
					'resolved' => true,
				];
			}
		}

		/*
		--------------------------------------------------------------------
		 * 5.  Persist in context
		 * ------------------------------------------------------------------
		 */
		$context->set_test_packages( $resolved_paths );

		// NEW: expose "setup‑only" packages for the environment result
		$context->set( 'setup_only_packages', $resolved_paths );

		return $context;
	}

	/*
	----------------------------------------------------------------------
	 * Helpers
	 * --------------------------------------------------------------------
	 */
	/**
	 * Extract test packages from CLI input
	 *
	 * @param InputInterface $input CLI input object.
	 *
	 * @return string[] Array of package identifiers
	 */
	private function cliPackageList( InputInterface $input ): array {
		$out = [];

		// Positional <package>
		if ( $input->hasArgument( 'package' ) && $input->getArgument( 'package' ) ) {
			$out[] = $input->getArgument( 'package' );
		}

		// --test-package can be string or array
		if ( $input->hasOption( 'test-package' ) ) {
			$opt = $input->getOption( 'test-package' );
			if ( is_string( $opt ) && $opt !== '' ) {
				$out[] = $opt;
			} elseif ( is_array( $opt ) ) {
				$out = array_merge( $out, $opt );
			}
		}

		return $out;
	}

	/* ---------------------------------------------------------------
	 * Resolve local path helper
	 * -------------------------------------------------------------*/
	/** Resolve a local test‑package reference to its directory */
	private function resolve_local_package_path(
		string $identifier,
		string $configDir // ← pass dirname($context->get('config_file'))
	): string {
		// Absolute or relative → make absolute first
		$abs = str_starts_with($identifier, '/')
			? $identifier
			: $configDir . '/' . ltrim($identifier, '/');

		// If a manifest file is given → drop filename
		if (substr($abs, -13) === '/manifest.json') {
			$abs = dirname($abs);
		}

		// Resolve symlinks & "../" parts (don't care if it returns false)
		$real = realpath($abs) ?: $abs;

		// Final sanity check
		if (!is_dir($real)) {
			throw new RuntimeException("Local package directory not found: {$identifier}");
		}
		if (!file_exists($real . '/manifest.json')) {
			throw new RuntimeException("No manifest.json found in local package: {$real}");
		}
		return $real;
	}
}
