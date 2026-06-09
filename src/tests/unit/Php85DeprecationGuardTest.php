<?php

/**
 * Guards against re-introducing calls that are deprecated on PHP 8.5.
 *
 * These deprecations only surface at runtime on PHP 8.5+, so CI running on
 * older PHP versions would not catch them. This static scan does.
 *
 * - curl_close() is a no-op since PHP 8.0 and deprecated since 8.5. Handles
 *   are freed when they go out of scope; use unset() if eager release is needed.
 * - ReflectionProperty/ReflectionMethod::setAccessible() is a no-op since
 *   PHP 8.1 and deprecated since 8.5. Prefer public accessors over reflection.
 */
class Php85DeprecationGuardTest extends \PHPUnit\Framework\TestCase {
	public function test_src_has_no_php85_deprecated_calls() {
		$src_dir = realpath( __DIR__ . '/../../src' );
		$this->assertNotFalse( $src_dir, 'Could not resolve src directory.' );

		$deprecated_patterns = [
			'curl_close'    => '/\bcurl_close\s*\(/',
			'setAccessible' => '/->\s*setAccessible\s*\(/',
		];

		$violations = [];

		$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $src_dir, FilesystemIterator::SKIP_DOTS ) );
		foreach ( $iterator as $file ) {
			if ( $file->getExtension() !== 'php' ) {
				continue;
			}

			$contents = file_get_contents( $file->getPathname() );
			foreach ( $deprecated_patterns as $name => $pattern ) {
				if ( preg_match( $pattern, $contents ) ) {
					$violations[] = sprintf( '%s in %s', $name, $file->getPathname() );
				}
			}
		}

		$this->assertSame( [], $violations, "Found calls that are deprecated on PHP 8.5:\n" . implode( "\n", $violations ) );
	}
}
