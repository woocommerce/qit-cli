<?php
// src/PreCommand/Pipeline/Stages/BuildEnvironmentResultStage.php
namespace QIT_CLI\PreCommand\Pipeline\Stages;

use QIT_CLI\PreCommand\Pipeline\PipelineContext;
use QIT_CLI\PreCommand\Pipeline\PipelineStage;
use QIT_CLI\PreCommand\Results\EnvironmentResult;

class BuildEnvironmentResultStage implements PipelineStage {

	public function process( PipelineContext $context ): PipelineContext {
		// env_result already holds an EnvironmentResult from resolver
		$result = $context->get( 'env_result' );
		if ( $result instanceof EnvironmentResult ) {
			$context->set_result( $result );
		}
		return $context;
	}
}
