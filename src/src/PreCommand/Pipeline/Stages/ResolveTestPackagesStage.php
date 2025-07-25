<?php
namespace QIT_CLI\PreCommand\Pipeline\Stages;

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
			// 2‑a) Local test package(s) from environment->setup_only
			$env_name = $cmd->get_environment_name();                  // always set on Local* cmds
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
		--------------------------------------------------------------------
		 * 4.  Download / extract
		 * ------------------------------------------------------------------
		 */
		$verify = $input->getOption( 'verify' ) !== false;

		$download_urls = $this->downloader->fetch_download_urls( $needed_packages );

		$resolved_paths = [];
		foreach ( $needed_packages as $identifier ) {
			if ( ! isset( $download_urls[ $identifier ] ) ) {
				throw new RuntimeException( "Package not found or access denied: {$identifier}" );
			}

			$url_info = $download_urls[ $identifier ];
			$zip      = sys_get_temp_dir() . '/qit-' . $this->downloader->generate_filename( $identifier );
			$extract  = sys_get_temp_dir() . '/qit-' . str_replace( [ '/', ':' ], '-', $identifier );

			$this->downloader->download_file( $url_info['url'], $zip );

			if ( $verify && ! empty( $url_info['checksum'] ) ) {
				if ( ! $this->downloader->verify_checksum( $zip, $url_info['checksum'] ) ) {
					unlink( $zip );
					throw new RuntimeException( "Checksum verification failed for: {$identifier}" );
				}
			}

			$this->downloader->extract_package( $zip, $extract, true );
			unlink( $zip );

			$resolved_paths[ $identifier ] = [
				'path'     => $extract,
				'type'     => 'static',
				'resolved' => true,
			];
		}

		/*
		--------------------------------------------------------------------
		 * 5.  Persist in context
		 * ------------------------------------------------------------------
		 */
		$context->set_test_packages( $resolved_paths );

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
}
