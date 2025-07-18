<?php

namespace QIT_CLI\PreCommand\Pipeline\Stages;

use QIT_CLI\Environment\Extension;
use QIT_CLI\PreCommand\Pipeline\PipelineContext;
use QIT_CLI\PreCommand\Pipeline\PipelineStage;

class ConsolidateWooCommerceStage implements PipelineStage {

	public function process( PipelineContext $context ): PipelineContext {
		$cfg = $context->get_resolved_config();   // ResolvedConfiguration
		$env = $context->get_env_result();        // EnvironmentResult (built by resolver)

		if ( ! $env ) {
			return $context; // not an env‑related command
		}

		$env_info = $env->env_info; // EnvInfo (E2EEnvInfo, etc.)

		$plugins     = $env_info->plugins ?? [];
		$woo_channel = $env_info->woo    // already filled by resolver
						?? $cfg->get_environment( 'default' )['woo']
							?? null;

		if ( $woo_channel === null || $woo_channel === '' ) {
			// Nothing to consolidate.
			return $context;
		}

		$found_index = null;

		/**
		 * @var int $i
		 * @var Extension $p
		 */
		foreach ( $plugins as $i => $p ) {
			if ( strtolower( $p->slug ) === 'woocommerce' ) {
				$found_index = $i;
				break;
			}
		}

		if ( $found_index !== null ) {
			// --plugin woocommerce was present.  Woo flag wins.
			$plugins[ $found_index ]->version = $woo_channel;
		} else {
			/** @phpstan-ignore-next-line */
			$plugins[] = Extension::fromArray( [
				'slug'     => 'woocommerce',
				'type'     => 'plugin',
				'from'     => 'wporg',
				'version'  => $woo_channel,
				'priority' => 50,
			] );
		}

		// Push back
		$env->env_info->plugins = $plugins;
		$context->set_env_result( $env );

		return $context;
	}
}
