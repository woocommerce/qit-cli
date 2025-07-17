<?php
// src/PreCommand/Pipeline/Stages/ExtractInputStage.php
namespace QIT_CLI\PreCommand\Pipeline\Stages;

use QIT_CLI\PreCommand\Pipeline\PipelineContext;
use QIT_CLI\PreCommand\Pipeline\PipelineStage;

class ExtractInputStage implements PipelineStage {

	public function process( PipelineContext $context ): PipelineContext {
		$in = $context->input;

		$sut_slug = $in->hasArgument( 'sut' ) ? $in->getArgument( 'sut' ) : null;
		$sut_type = $in->hasOption( 'type' ) ? $in->getOption( 'type' ) : null;
		$cfg_file = $in->hasOption( 'config' ) ? $in->getOption( 'config' ) : null;

		$context->set( 'sut_slug', $sut_slug )
				->set( 'sut_type', $sut_type )
				->set( 'config_file', $cfg_file );

		return $context;
	}
}
