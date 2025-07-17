<?php
// src/PreCommand/Pipeline/Stages/ValidateSUTStage.php
namespace QIT_CLI\PreCommand\Pipeline\Stages;

use QIT_CLI\PreCommand\Interfaces\LocalTestCommand;
use QIT_CLI\PreCommand\Pipeline\PipelineContext;
use QIT_CLI\PreCommand\Pipeline\PipelineStage;

/**
 * Ensures a System‑Under‑Test is present when it will actually be executed locally.
 */
class ValidateSUTStage implements PipelineStage {

	public function process( PipelineContext $context ): PipelineContext {
		$cmd = $context->command;

		// Only local-test style commands truly need a SUT in qit.json
		if ( ! $cmd instanceof LocalTestCommand ) {
			return $context;
		}

		$resolved = $context->get( 'resolved_config' );
		if ( ! $resolved || ! $resolved->sut ) {
			throw new \RuntimeException(
				'System Under Test (SUT) is required for local test commands. ' .
				'Specify it via CLI argument or qit.json.'
			);
		}

		return $context;
	}
}
