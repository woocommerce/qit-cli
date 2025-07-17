<?php
// src/PreCommand/Pipeline/Stages/BuildLocalTestResultStage.php
namespace QIT_CLI\PreCommand\Pipeline\Stages;

use QIT_CLI\PreCommand\Pipeline\PipelineContext;
use QIT_CLI\PreCommand\Pipeline\PipelineStage;
use QIT_CLI\PreCommand\Interfaces\LocalTestCommand;
use QIT_CLI\PreCommand\Results\LocalTestResult;
use QIT_CLI\PreCommand\SmartOptionExtraction;

class BuildLocalTestResultStage implements PipelineStage {

	use SmartOptionExtraction;

	public function process( PipelineContext $context ): PipelineContext {
		$cmd = $context->command;
		if ( ! $cmd instanceof LocalTestCommand ) {
			return $context;
		}

		$resolved_config = $context->get( 'resolved_config' );
		$env_result      = $context->get( 'env_result' );
		$test_packages   = $context->get( 'test_packages', [] );

		$test_type = $cmd->get_test_type();
		$profile   = $cmd->get_test_profile();

		try {
			$test_cfg = $resolved_config->get_test_config( $test_type, $profile );
		} catch ( \RuntimeException $e ) {
			$test_cfg = []; // run with defaults
		}

		// CLI overrides for test‑specific options
		$overrides = $this->extract_explicit_options(
			$cmd,
			$context->input,
			[ 'phpstan_level' => 'phpstan_level' ]
		);
		$test_cfg  = array_merge( $test_cfg, $overrides );

		$result = new LocalTestResult( $resolved_config, $env_result->env_info, $test_packages, $test_cfg );
		$context->set_result( $result );

		return $context;
	}
}
