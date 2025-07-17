<?php
// src/PreCommand/Pipeline/Stages/ExtractInputStage.php
namespace QIT_CLI\PreCommand\Pipeline\Stages;

use QIT_CLI\PreCommand\Pipeline\PipelineContext;
use QIT_CLI\PreCommand\Pipeline\PipelineStage;

class ExtractInputStage implements PipelineStage {

	public function process( PipelineContext $context ): PipelineContext {
		$in = $context->input;

		$sutSlug = $in->hasArgument( 'sut' ) ? $in->getArgument( 'sut' ) : null;
		$sutType = $in->hasOption( 'type' ) ? $in->getOption( 'type' ) : null;
		$cfgFile = $in->hasOption( 'config' ) ? $in->getOption( 'config' ) : null;

		$context->set( 'sut_slug', $sutSlug )
				->set( 'sut_type', $sutType )
				->set( 'config_file', $cfgFile );

		return $context;
	}
}
