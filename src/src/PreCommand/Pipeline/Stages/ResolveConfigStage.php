<?php
// src/PreCommand/Pipeline/Stages/ResolveConfigStage.php
namespace QIT_CLI\PreCommand\Pipeline\Stages;

use QIT_CLI\PreCommand\Configuration\ConfigurationResolver;
use QIT_CLI\PreCommand\Pipeline\PipelineContext;
use QIT_CLI\PreCommand\Pipeline\PipelineStage;
use QIT_CLI\App;

/**
 * Merge qit.json + CLI defaults into a ResolvedConfiguration.
 */
class ResolveConfigStage implements PipelineStage {

	private ConfigurationResolver $resolver;

	public function __construct( ?ConfigurationResolver $resolver = null ) {
		// allow simple new ResolveConfigStage() in tests
		$this->resolver = $resolver ?: App::make( ConfigurationResolver::class );
	}

	public function process( PipelineContext $context ): PipelineContext {
		$cfg_file = $context->get( 'config_file' );
		$slug     = $context->get( 'sut_slug' );
		$type     = $context->get( 'sut_type' );

		$resolved = $this->resolver->resolve( $cfg_file, $slug, $type );

		$context->set( 'resolved_config', $resolved );
		return $context;
	}
}
