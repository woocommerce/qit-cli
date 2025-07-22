<?php
namespace QIT_CLI\PreCommand\Pipeline\Stages;

use QIT_CLI\PreCommand\Pipeline\PipelineContext;
use QIT_CLI\PreCommand\Pipeline\PipelineStage;
use QIT_CLI\PreCommand\Objects\TestPackage;

class ResolveTestPackagesStage implements PipelineStage {

	public function process( PipelineContext $context ): PipelineContext {
		$cmd            = $context->command;
		$resolved_cfg    = $context->get_resolved_config();
		$input          = $context->input;

		// -------- From profile (qit.json) ----------
		$test_type  = method_exists( $cmd, 'get_test_type' ) ? $cmd->get_test_type() : 'e2e';
		$profile    = method_exists( $cmd, 'get_test_profile' ) ? $cmd->get_test_profile() : 'default';

		try {
			$profile_cfg = $resolved_cfg->get_test_config( $test_type, $profile );
			$packages_cfg = $profile_cfg['test_packages'] ?? [];
		} catch ( \RuntimeException $e ) {
			$packages_cfg = [];
		}

		// -------- From CLI -------------------------
		$cli_specs = $input->getOption( 'test-package' ) ?? [];

		// -------- Merge (CLI wins; de‑dupe on slug) --
		$all_specs = array_merge( $packages_cfg, $cli_specs );

		$seen  = [];
		$final = [];
		foreach ( $all_specs as $spec ) {
			$tp = TestPackage::fromString( $spec );
			if ( isset( $seen[ $tp->slug ] ) ) {
				// if slug already present, overwrite only if coming from CLI
				if ( in_array( $spec, $cli_specs, true ) ) {
					$idx             = $seen[ $tp->slug ];
					$final[ $idx ]   = $tp;
				}
				continue;
			}
			$seen[ $tp->slug ] = count( $final );
			$final[]           = $tp;
		}

		$context->set( 'test_packages', $final );

		return $context;
	}
}
