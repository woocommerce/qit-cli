<?php
// src/PreCommand/Pipeline/Stages/ValidateSUTStage.php
namespace QIT_CLI\PreCommand\Pipeline\Stages;

use QIT_CLI\PreCommand\Pipeline\PipelineContext;
use QIT_CLI\PreCommand\Pipeline\PipelineStage;

/**
 * Ensures a System‑Under‑Test is present for test commands.
 */
class ValidateSUTStage implements PipelineStage {

	public function process( PipelineContext $context ): PipelineContext {
		$resolved = $context->get( 'resolved_config' );
		if ( ! $resolved || ! $resolved->sut ) {
			throw new \RuntimeException(
				'System Under Test (SUT) is required for test commands. ' .
				'Specify it via CLI argument or qit.json.'
			);
		}
		return $context;
	}
}
