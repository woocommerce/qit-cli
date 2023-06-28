<?php

namespace QIT_CLI\Fixer;

use QIT_CLI\Fixer\Exceptions\SecurityFixerException;

class SecurityFixer {
	public function fix( string $local_plugin_dir, string $test_result_json ): void {
		// Normalize Windows directory separators.
		$local_plugin_dir = str_replace( '\\', '/', $local_plugin_dir );

		if ( ! file_exists( $local_plugin_dir ) ) {
			throw SecurityFixerException::plugin_dir_not_found( $local_plugin_dir );
		}

		$test_result = json_decode( $test_result_json, true );

		if ( ! is_array( $test_result ) ) {
			throw SecurityFixerException::test_result_json_invalid();
		}

		foreach ( $test_result['tool'] as &$tool ) {
			foreach ( $tool['files'] as $file_path => &$file ) {
				$file_locally = $this->find_file_correspondence( $file_path, $local_plugin_dir );
				foreach ( $file['messages'] as &$message ) {
					if ( ! empty( $message['ai'] ) && is_array( $message['ai'] ) ) {
						$code_to_fix = $message['codeFragment'];
						$fix         = array_shift( $message['ai'] );

						// Replace the faulty code with the fix and preserve surrounding whitespaces and line breaks
						$fix_with_whitespaces = preg_replace(
							'/^([\n\r\s]*)(.*)[\n\r\s]*$/s',
							'${1}' . $fix . "\n",
							$code_to_fix
						);

						$content = file_get_contents( $file_locally );

						// Replace the faulty code with the fixed content
						$fixed_content = str_replace( $code_to_fix, $fix_with_whitespaces, $content );

						// Write the fixed content back to the file
						file_put_contents( $file_locally, $fixed_content );
					}
				}
			}
		}
	}

	protected function find_file_correspondence( string $file_in_json, string $local_plugin_dir ): string {
		if ( stripos( $file_in_json, '/ci/plugins/' ) ) {
			/*
			 * PHPCS sends a path like this:
			 * /home/runner/work/foo-bar-baz/ci/plugins/woocommerce-example-plugin/woocommerce-example-plugin.php
			 * woocommerce-example-plugin/woocommerce-example-plugin.php
			 */
			$plugin_in_json = trim( trim( substr( $file_in_json, stripos( $file_in_json, '/ci/plugins/' ) + strlen( '/ci/plugins/' ) ), '/' ) );
		} else {
			/*
			 * SemGrep sends a path like this:
			 * /woocommerce-example-plugin/woocommerce-example-plugin.php
			 * woocommerce-example-plugin/woocommerce-example-plugin.php
			 */
			$plugin_in_json = trim( trim( $file_in_json, '/' ) );
		}

		// ['woocommerce-example-plugin', 'woocommerce-example-plugin.php']
		$parts = explode( '/', $plugin_in_json );

		// 'woocommerce-example-plugin'
		$top_level_directory = array_shift( $parts );

		$local_plugin_basename = basename( $local_plugin_dir );

		if ( $local_plugin_basename !== $top_level_directory ) {
			throw SecurityFixerException::plugin_path_does_not_match( $local_plugin_basename, $top_level_directory );
		}

		// Get relative path from JSON file path.
		$relative_path = substr( $plugin_in_json, strlen( $top_level_directory ) );

		// Replace the prefix of JSON file path with local plugin dir.
		$local_file_path = $local_plugin_dir . $relative_path;

		if ( ! file_exists( $local_file_path ) ) {
			throw SecurityFixerException::file_does_not_exist_locally( $local_file_path );
		}

		// Return the local file path.
		return $local_file_path;
	}
}