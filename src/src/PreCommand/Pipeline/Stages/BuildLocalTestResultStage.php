<?php
// src/PreCommand/Pipeline/Stages/BuildLocalTestResultStage.php
namespace QIT_CLI\PreCommand\Pipeline\Stages;

use QIT_CLI\PreCommand\Pipeline\PipelineContext;
use QIT_CLI\PreCommand\Pipeline\PipelineStage;
use QIT_CLI\PreCommand\Interfaces\LocalTestCommand;
use QIT_CLI\PreCommand\Results\LocalTestResult;
use QIT_CLI\PreCommand\SmartOptionExtraction;
use QIT_CLI\PreCommand\Configuration\ConfigMerger;
use QIT_CLI\App;

class BuildLocalTestResultStage implements PipelineStage {

	use SmartOptionExtraction;

	private ConfigMerger $merger;

	public function __construct( ConfigMerger $merger ) {
		$this->merger = $merger;
	}

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

		// Extract CLI overrides
		$cli_overrides = $this->extract_explicit_options(
			$cmd,
			$context->input
		);

		// Get config values from qit.json
		try {
			$config_values = $resolved_config->get_test_config( $test_type, $profile );
		} catch ( \RuntimeException $e ) {
			$config_values = []; // run with defaults
		}

		// Get command defaults
		$command_defaults = $this->extract_option_defaults( $cmd );

		// Use ConfigMerger to apply proper precedence
		$test_cfg = $this->merger->merge( $cli_overrides, $config_values, $command_defaults );

		$result = new LocalTestResult( $resolved_config, $env_result->env_info, $test_packages, $test_cfg );
		$context->set_result( $result );

		return $context;
	}
}
