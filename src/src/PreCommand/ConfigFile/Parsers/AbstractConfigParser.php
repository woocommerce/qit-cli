<?php

namespace QIT_CLI\PreCommand\ConfigFile\Parsers;

abstract class AbstractConfigParser {
	protected function resolve_extends( array $section, string $section_name ): array {
		$resolved = [];
		$pending  = $section;

		while ( ! empty( $pending ) ) {
			$resolved_something = false;

			foreach ( $pending as $name => $config ) {
				if ( ! isset( $config['extends'] ) ) {
					$resolved[ $name ] = $config;
					unset( $pending[ $name ] );
					$resolved_something = true;
					continue;
				}

				$base_name = $config['extends'];
				if ( ! isset( $section[ $base_name ] ) ) {
					throw new \RuntimeException( "Extended configuration '$base_name' not found in $section_name '$name'." );
				}

				if ( isset( $resolved[ $base_name ] ) ) {
					$base_config  = $resolved[ $base_name ];
					$child_config = $config;
					unset( $child_config['extends'] );
					$merged_config     = array_merge( $base_config, $child_config );
					$resolved[ $name ] = $merged_config;
					unset( $pending[ $name ] );
					$resolved_something = true;
				}
			}

			if ( ! $resolved_something && ! empty( $pending ) ) {
				throw new \RuntimeException( "Circular dependency detected in $section_name configurations." );
			}
		}

		return $resolved;
	}
}
