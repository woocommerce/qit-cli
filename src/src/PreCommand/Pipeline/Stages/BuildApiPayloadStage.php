<?php
// src/PreCommand/Pipeline/Stages/BuildApiPayloadStage.php
namespace QIT_CLI\PreCommand\Pipeline\Stages;

use QIT_CLI\PreCommand\Pipeline\PipelineContext;
use QIT_CLI\PreCommand\Pipeline\PipelineStage;
use QIT_CLI\PreCommand\Interfaces\ConfigurableTestCommand;
use QIT_CLI\PreCommand\Results\ConfigurationResult;
use QIT_CLI\PreCommand\SmartOptionExtraction;
use QIT_CLI\PreCommand\Configuration\ConfigMerger;
use QIT_CLI\App;

/**
 * Produces the ConfigurationResult (remote tests) and stores it in context.
 */
class BuildApiPayloadStage implements PipelineStage {

	use SmartOptionExtraction;

	private ConfigMerger $merger;

	public function __construct( ConfigMerger $merger ) {
		$this->merger = $merger;
	}

	public function process( PipelineContext $context ): PipelineContext {
		$cmd = $context->command;
		if ( ! $cmd instanceof ConfigurableTestCommand ) {
			return $context; // not applicable
		}

		$resolved  = $context->get( 'resolved_config' );
		$test_type = $cmd->get_test_type();
		$profile   = $cmd->get_test_profile();

		// Extract CLI overrides
		$cli_overrides = $this->extract_explicit_options( $cmd, $context->input );

		// Get config values from qit.json
		try {
			$config_values = $resolved->get_test_config( $test_type, $profile );
		} catch ( \RuntimeException $e ) {
			// No config provided in qit.json – default to empty array
			$config_values = [];
		}

		// Get command defaults
		$command_defaults = $this->extract_option_defaults( $cmd );

		// Use ConfigMerger to apply proper precedence
		$test_config = $this->merger->merge( $cli_overrides, $config_values, $command_defaults );

		$result = new ConfigurationResult( $resolved, $test_config );
		$context->set_result( $result );

		return $context;
	}
}
