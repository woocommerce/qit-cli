<?php
namespace QIT_CLI\PreCommand\Pipeline;

/**
 * Every stage receives a PipelineContext and must return it (possibly mutated).
 */
interface PipelineStage {

	public function process( PipelineContext $context ): PipelineContext;
}
