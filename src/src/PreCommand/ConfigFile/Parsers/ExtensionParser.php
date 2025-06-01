<?php

namespace QIT_CLI\PreCommand\ConfigFile\Parsers;

use QIT_CLI;

// Import the QIT_CLI namespace for normalize_path

class ExtensionParser {
	protected SutParser $sut_parser;

	public function __construct( SutParser $sut_parser ) {
		$this->sut_parser = $sut_parser;
	}

	/**
	 * Parse plugins or themes from environment configuration into a normalized format.
	 *
	 * @param array $items The plugins or themes configuration array.
	 * @param string $type 'plugins' or 'themes'.
	 * @param array $context Parsing context (e.g., root_path).
	 * @param array|null $sut_config SUT configuration for validation.
	 * @param string $env_name Environment name for error messages.
	 *
	 * @return array Array of normalized extension configurations.
	 * @throws \RuntimeException If parsing fails.
	 */
	public function parse( array $items, string $type, array $context, ?array $sut_config = null, string $env_name = 'unknown' ): array {
		$extensions    = [];
		$type_singular = $type === 'plugins' ? 'plugin' : 'theme';

		foreach ( $items as $index => $item ) {
			$extension = [];

			if ( is_string( $item ) ) {
				// Simple string slug (e.g., "wccom-plugin-1")
				$extension = [
					'slug'   => $item,
					'type'   => $type_singular,
					'source' => [ 'type' => 'wporg' ], // Default, resolved later
				];
			} elseif ( is_array( $item ) && isset( $item['slug'] ) ) {
				// Object configuration (e.g., ['slug' => 'wccom-plugin-1', 'source' => [...]])
				$extension = [
					'slug'   => $item['slug'],
					'type'   => $type_singular,
					'source' => $this->parse_extension_source( $item, $context, $env_name ),
				];
			} else {
				throw new \RuntimeException( "Invalid $type_singular at index $index in environment '$env_name'" );
			}

			// Validate against SUT if present
			if ( $sut_config && $extension['slug'] === $sut_config['slug'] ) {
				$this->validate_sut_consistency( $extension, $sut_config, $type_singular, $env_name );
			}

			$extensions[] = $extension;
		}

		return $extensions;
	}

	/**
	 * Parse source information for an extension.
	 */
	protected function parse_extension_source(array $item, array $context, string $env_name): array {
		if (!isset($item['source'])) {
			return ['type' => 'wporg'];
		}

		$source = $item['source'];
		$source_type = $source['type'] ?? 'wporg';
		$parsed_source = ['type' => $source_type];

		switch ($source_type) {
			case 'directory':
				if (!isset($source['path'])) {
					throw new \RuntimeException("Extension '{$item['slug']}' has no directory path in environment '$env_name'");
				}
				if (!is_dir($source['path'])) {
					throw new \RuntimeException("Directory does not exist: {$source['path']} in environment '$env_name'");
				}
				$parsed_source['path'] = $source['path'];
				break;
			case 'zip':
				if (!isset($source['path'])) {
					throw new \RuntimeException("Extension '{$item['slug']}' has no zip path in environment '$env_name'");
				}
				if (!file_exists($source['path'])) {
					throw new \RuntimeException("Zip file does not exist: {$source['path']} in environment '$env_name'");
				}
				if (!preg_match('/\.zip$/', $source['path'])) {
					throw new \RuntimeException("Zip source path must be a .zip file for '{$item['slug']}' in environment '$env_name'");
				}
				$parsed_source['path'] = $source['path'];
				break;
			case 'url':
				if (!isset($source['url'])) {
					throw new \RuntimeException("Extension '{$item['slug']}' has no URL in environment '$env_name'");
				}
				$parsed_source['url'] = $source['url'];
				break;
			case 'wporg':
			case 'wccom':
				$parsed_source['version'] = $source['version'] ?? 'stable';
				break;
			default:
				throw new \RuntimeException("Invalid source type '$source_type' for extension '{$item['slug']}' in environment '$env_name'");
		}

		return $parsed_source;
	}

	/**
	 * Validate extension consistency with SUT configuration.
	 */
	protected function validate_sut_consistency( array $extension, array $sut_config, string $type, string $env_name ): void {
		if ( $extension['source']['type'] !== $sut_config['source']['type'] ) {
			throw new \RuntimeException( "SUT configuration mismatch for $type '{$sut_config['slug']}' in environment '$env_name'" );
		}

		if ( $sut_config['source']['type'] === 'directory' && ( ! isset( $extension['source']['path'] ) || $extension['source']['path'] !== $sut_config['source']['path'] ) ) {
			throw new \RuntimeException( "SUT path mismatch for $type '{$sut_config['slug']}' in environment '$env_name'" );
		}

		if ( $sut_config['source']['type'] === 'zip' && ( ! isset( $extension['source']['path'] ) || $extension['source']['path'] !== $sut_config['source']['path'] ) ) {
			throw new \RuntimeException( "SUT path mismatch for $type '{$sut_config['slug']}' in environment '$env_name'" );
		}
	}
}