<?php
// src/PreCommand/Pipeline/Stages/ResolveEnvironmentStage.php
namespace QIT_CLI\PreCommand\Pipeline\Stages;

use QIT_CLI\PreCommand\Pipeline\PipelineContext;
use QIT_CLI\PreCommand\Pipeline\PipelineStage;
use QIT_CLI\PreCommand\EnvironmentResolver;
use QIT_CLI\App;
use QIT_CLI\PreCommand\Interfaces\EnvironmentCommand;
use QIT_CLI\PreCommand\SmartOptionExtraction;

class ResolveEnvironmentStage implements PipelineStage {

	use SmartOptionExtraction;

	private EnvironmentResolver $resolver;

	public function __construct( ?EnvironmentResolver $resolver = null ) {
		$this->resolver = $resolver ?: App::make( EnvironmentResolver::class );
	}

	public function process( PipelineContext $context ): PipelineContext {
		$cmd = $context->command;
		if ( ! $cmd instanceof EnvironmentCommand ) {
			return $context; // not applicable
		}

		$resolved_config = $context->get( 'resolved_config' );
		$env_name        = $cmd->get_environment_name();

		$env_overrides = $this->extract_explicit_options(
			$cmd,
			$context->input,
			// same mapping used before
			[
				'plugin'        => 'plugins',
				'theme'         => 'themes',
				'volume'        => 'volumes',
				'php_extension' => 'php_extensions',
				'env'           => 'env_vars',
				'env_file'      => 'env_files',
				'php'           => 'php',
				'wp'            => 'wp',
				'woo'           => 'woo',
				'object_cache'  => 'object_cache',
			]
		);

		$env_result = $this->resolver->resolve(
			$resolved_config,
			$env_name,
			$cmd->should_prepare_environment(),
			$env_overrides,
			$context->input
		);

		$context->set( 'env_result', $env_result );
		return $context;
	}
}
