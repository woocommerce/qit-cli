<?php
// src/PreCommand/Pipeline/Stages/ResolveTestPackagesStage.php
namespace QIT_CLI\PreCommand\Pipeline\Stages;

use QIT_CLI\PreCommand\Pipeline\PipelineContext;
use QIT_CLI\PreCommand\Pipeline\PipelineStage;
use QIT_CLI\PreCommand\Interfaces\LocalTestCommand;
use QIT_CLI\PreCommand\TestPackageResolver;
use QIT_CLI\App;

class ResolveTestPackagesStage implements PipelineStage {

	private TestPackageResolver $resolver;

	public function __construct( ?TestPackageResolver $resolver = null ) {
		$this->resolver = $resolver ?: App::make( TestPackageResolver::class );
	}

	public function process( PipelineContext $context ): PipelineContext {
		$cmd = $context->command;
		if ( ! $cmd instanceof LocalTestCommand ) {
			return $context;
		}

		$resolved_config = $context->get( 'resolved_config' );
		$test_type       = $cmd->get_test_type();
		$profile         = $cmd->get_test_profile();

		try {
			$packages = $this->resolver->resolve( $resolved_config, $test_type, $profile );
		} catch ( \RuntimeException $e ) {
			$packages = []; // no test config ⇒ no packages
		}
		$context->set( 'test_packages', $packages );

		return $context;
	}
}
