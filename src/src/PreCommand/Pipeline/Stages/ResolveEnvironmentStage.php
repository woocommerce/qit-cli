<?php
// src/PreCommand/Pipeline/Stages/ResolveEnvironmentStage.php
namespace QIT_CLI\PreCommand\Pipeline\Stages;

use QIT_CLI\PreCommand\Pipeline\PipelineContext;
use QIT_CLI\PreCommand\Pipeline\PipelineStage;
use QIT_CLI\PreCommand\EnvironmentResolver;
use QIT_CLI\PreCommand\Configuration\ConfigMerger;
use QIT_CLI\App;
use QIT_CLI\PreCommand\Interfaces\EnvironmentCommand;
use QIT_CLI\PreCommand\SmartOptionExtraction;

class ResolveEnvironmentStage implements PipelineStage {

	use SmartOptionExtraction;

	private EnvironmentResolver $resolver;
	private ConfigMerger $merger;

	public function __construct( EnvironmentResolver $resolver, ConfigMerger $merger ) {
		$this->resolver = $resolver;
		$this->merger = $merger;
	}

	public function process( PipelineContext $context ): PipelineContext {
		$cmd = $context->command;
		if ( ! $cmd instanceof EnvironmentCommand ) {
			return $context; // not applicable
		}

		$resolved_config = $context->get( 'resolved_config' );
		$env_name        = $cmd->get_environment_name();

		// Extract CLI overrides
		$cli_overrides = $this->extract_explicit_options(
			$cmd,
			$context->input
		);

		// Get config values from qit.json
		$config_values = $resolved_config->get_environment( $env_name );

		// Get command defaults
		$command_defaults = $this->extract_option_defaults( $cmd );

		// Use ConfigMerger to apply proper precedence
		$merged_config = $this->merger->merge( $cli_overrides, $config_values, $command_defaults );

		$env_result = $this->resolver->resolve(
			$resolved_config,
			$env_name,
			$context->input,
			$cmd->should_prepare_environment(),
			$merged_config
		);

		$context->set( 'env_result', $env_result );
		return $context;
	}
}
