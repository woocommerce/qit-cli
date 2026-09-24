<?php

namespace QIT_CLI\Blueprints;

/**
 * Reads a WordPress Playground Blueprint into a plain array.
 *
 * Only local files are accepted. Blueprints can carry arbitrary PHP, SQL and
 * shell payloads, and unlike Playground (which runs them in a WASM sandbox)
 * QIT executes them inside a Docker container with host volumes mounted, so
 * fetching a remote Blueprint by URL is deliberately not supported here.
 *
 * @see https://developer.wordpress.org/playground/blueprints/data-format
 */
class BlueprintParser {

	/**
	 * Parse a Blueprint from a file path.
	 *
	 * @param string $path Path to a .json Blueprint file.
	 *
	 * @return array<string, mixed>
	 */
	public function from_file( string $path ): array {
		if ( preg_match( '#^https?://#i', $path ) === 1 ) {
			throw new BlueprintException(
				'Remote Blueprints are not supported. Blueprints can execute arbitrary PHP, SQL and shell ' .
				'commands inside the environment, so download the file and review it before using it: ' . $path
			);
		}

		if ( ! is_file( $path ) || ! is_readable( $path ) ) {
			throw new BlueprintException( sprintf( 'Blueprint file not found or not readable: %s', $path ) );
		}

		$contents = file_get_contents( $path );

		if ( $contents === false ) {
			throw new BlueprintException( sprintf( 'Could not read Blueprint file: %s', $path ) );
		}

		return $this->from_string( $contents, $path );
	}

	/**
	 * Parse a Blueprint from a JSON string.
	 *
	 * @param string $json   The raw JSON.
	 * @param string $source Human-readable origin, used in error messages.
	 *
	 * @return array<string, mixed>
	 */
	public function from_string( string $json, string $source = 'blueprint' ): array {
		$decoded = json_decode( $json, true );

		if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $decoded ) ) {
			throw new BlueprintException( sprintf( 'Invalid JSON in %s: %s', $source, json_last_error_msg() ) );
		}

		$this->assert_supported_version( $decoded, $source );

		return $decoded;
	}

	/**
	 * Blueprints v2 are declarative and use a different shape. Only v1 (the
	 * `steps` array format) is transpiled for now.
	 *
	 * @param array<string, mixed> $blueprint The decoded Blueprint.
	 * @param string               $source    Human-readable origin.
	 */
	private function assert_supported_version( array $blueprint, string $source ): void {
		$version = $blueprint['version'] ?? null;

		if ( $version !== null && (string) $version !== '1' ) {
			throw new BlueprintException( sprintf(
				'%s declares Blueprint version "%s". Only version 1 (the "steps" format) is supported for now.',
				$source,
				(string) $version
			) );
		}

		if ( isset( $blueprint['steps'] ) && ! is_array( $blueprint['steps'] ) ) {
			throw new BlueprintException( sprintf( '%s has a "steps" key that is not an array.', $source ) );
		}
	}
}
