<?php

namespace QIT_CLI\PreCommand\Pipeline\Stages;

use QIT_CLI\PreCommand\Pipeline\PipelineContext;
use QIT_CLI\PreCommand\Pipeline\PipelineStage;

class ConsolidateWooCommerceStage implements PipelineStage {

	public function process( PipelineContext $context ): PipelineContext {

		$cfg        = $context->get( 'resolved_config' );   // ResolvedConfiguration
		$env        = $context->get( 'env_result' );        // EnvironmentResult (built by resolver)

		if ( ! $env ) {
			return $context; // not an env‑related command
		}

		/** @var array<string,mixed> $plugins */
		$plugins     = $env->env_info['plugins'] ?? [];
		$wooChannel  = $env->env_info['woo']    // already filled by resolver
		             ?? $cfg->get_environment()['woo']
		             ?? null;

		if ( $wooChannel === null ) {
			// Nothing to consolidate.
			return $context;
		}

		$foundIndex = null;

		foreach ( $plugins as $i => $p ) {
			if ( strtolower( $p['slug'] ?? '' ) === 'woocommerce' ) {
				$foundIndex = $i;
				break;
			}
		}

		if ( $foundIndex !== null ) {
			// The user also added  --plugin woocommerce ...
			// Don't overwrite an explicit semantic version.
			if ( empty( $plugins[ $foundIndex ]['version'] ) ) {
				$plugins[ $foundIndex ]['version'] = $wooChannel;
			}
		} else {
			$plugins[] = [
				'slug'      => 'woocommerce',
				'type'      => 'plugin',
				'from'      => 'wporg',
				'version'   => $wooChannel,
				'priority'  => 50,
				// source / entrypoint will be filled later by PluginResolver as usual
			];
		}

		// Push back
		$env->env_info['plugins'] = $plugins;
		$context->set( 'env_result', $env );

		return $context;
	}
}