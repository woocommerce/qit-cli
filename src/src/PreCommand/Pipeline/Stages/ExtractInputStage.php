<?php
// src/PreCommand/Pipeline/Stages/ExtractInputStage.php
namespace QIT_CLI\PreCommand\Pipeline\Stages;

use QIT_CLI\PreCommand\Pipeline\PipelineContext;
use QIT_CLI\PreCommand\Pipeline\PipelineStage;
use QIT_CLI\PreCommand\Interfaces\LocalTestCommand;

class ExtractInputStage implements PipelineStage {

	public function process( PipelineContext $context ): PipelineContext {
		$in  = $context->input;
		$cmd = $context->command;

		// existing extraction …
		$sut_slug = $in->hasArgument( 'sut' ) ? $in->getArgument( 'sut' ) : null;
		$sut_type = $in->hasOption( 'type' ) ? $in->getOption( 'type' ) : null;

		// NEW: fall back to 'woo_extension' positional for LocalTestCommand
		if ( $cmd instanceof LocalTestCommand && ! $sut_slug ) {
			if ( $in->hasArgument( 'woo_extension' ) ) {
				$sut_slug = $in->getArgument( 'woo_extension' );
				$sut_type = $sut_type ?: 'plugin';
			}
		}

		$context->set( 'sut_slug', $sut_slug )
				->set( 'sut_type', $sut_type )
				->set( 'config_file', $in->getOption( 'config' ) );

		return $context;
	}
}
