<?php
// src/PreCommand/Pipeline/Stages/BuildApiPayloadStage.php
namespace QIT_CLI\PreCommand\Pipeline\Stages;

use QIT_CLI\PreCommand\Pipeline\PipelineContext;
use QIT_CLI\PreCommand\Pipeline\PipelineStage;
use QIT_CLI\PreCommand\Interfaces\ConfigurableTestCommand;
use QIT_CLI\PreCommand\Results\ConfigurationResult;
use QIT_CLI\PreCommand\SmartOptionExtraction;

/**
 * Produces the ConfigurationResult (remote tests) and stores it in context.
 */
class BuildApiPayloadStage implements PipelineStage {

	use SmartOptionExtraction;

	/** Map CLI option names → test_config keys */
	private function option_mapping(): array {
		return [ 'phpstan_level' => 'phpstan_level' ];
	}

	public function process( PipelineContext $context ): PipelineContext {
		$cmd = $context->command;
		if ( ! $cmd instanceof ConfigurableTestCommand ) {
			return $context; // not applicable
		}

		$resolved  = $context->get( 'resolved_config' );
		$test_type = $cmd->get_test_type();
		$profile   = $cmd->get_test_profile();

		$test_config = $resolved->get_test_config( $test_type, $profile );

		// merge CLI overrides
		$overrides   = $this->extract_explicit_options( $cmd, $context->input, $this->option_mapping() );
		$test_config = array_merge( $test_config, $overrides );

		$result = new ConfigurationResult( $resolved, $test_config );
		$context->set_result( $result );

		return $context;
	}
}
