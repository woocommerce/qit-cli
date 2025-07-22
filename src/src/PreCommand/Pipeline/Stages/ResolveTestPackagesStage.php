<?php
namespace QIT_CLI\PreCommand\Pipeline\Stages;

use QIT_CLI\PreCommand\Pipeline\PipelineContext;
use QIT_CLI\PreCommand\Pipeline\PipelineStage;
use QIT_CLI\PreCommand\TestPackageResolver;

class ResolveTestPackagesStage implements PipelineStage {
	protected TestPackageResolver $resolver;

	public function __construct( TestPackageResolver $resolver ) {
		$this->resolver = $resolver;
	}

	public function process( PipelineContext $context ): PipelineContext {
		$cmd          = $context->command;
		$resolved_cfg = $context->get_resolved_config();
		$input        = $context->input;

		// -------- From profile (qit.json) ----------
		$test_type = method_exists( $cmd, 'get_test_type' ) ? $cmd->get_test_type() : 'e2e';
		$profile   = method_exists( $cmd, 'get_test_profile' ) ? $cmd->get_test_profile() : 'default';

		// -------------------------------------------------------------
		// 1. Let TestPackageResolver handle downloading & resolving
		// -------------------------------------------------------------
		$resolved_packages = $this->resolver->resolve( $resolved_cfg, $test_type, $profile );

		// Persist manifests & metadata on ResolvedConfiguration
		$resolved_cfg->test_packages         = array_merge( $resolved_cfg->test_packages, $resolved_packages );
		$resolved_cfg->test_package_metadata = array_merge( $resolved_cfg->test_package_metadata, $this->resolver->getMetadata() );

		// -------------------------------------------------------------
		// 2. CLI-level include/exclude filters
		// -------------------------------------------------------------
		$cli_include = $input->getOption( 'test-package' ) ?? [];
		if ( ! empty( $cli_include ) ) {
			$resolved_packages = array_filter( $resolved_packages, static function ( $ref ) use ( $cli_include ) {
				foreach ( $cli_include as $pattern ) {
					if ( fnmatch( $pattern, $ref ) ) {
						return true;
					}
				}
				return false;
			}, ARRAY_FILTER_USE_KEY );
		}

		// -------------------------------------------------------------
		// 3. Store for later stages / results
		// -------------------------------------------------------------
		$context->set( 'test_packages', $resolved_packages );

		return $context;
	}
}
