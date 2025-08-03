<?php

namespace QIT_CLI\PreCommand\Extensions;

use QIT_CLI\PreCommand\Objects\Extension;

/**
 * Parses extension input from CLI parameters and determines type and slug.
 * 
 * Follows this resolution order:
 * 1. Local path (absolute or relative) that exists → LOCAL
 * 2. Well-formed URI → URL
 * 3. Pure slug (alphanumeric + dash/underscore) → SLUG
 * 4. Otherwise → ERROR
 */
class ExtensionInputParser {
	
	/**
	 * Parse a single extension input value from CLI.
	 * 
	 * @param string $input The raw input value from --plugin or --theme
	 * @param string $type Either 'plugin' or 'theme'
	 * @return Extension The parsed extension object
	 * @throws \InvalidArgumentException If input cannot be parsed
	 */
	public static function parse( string $input, string $type ): Extension {
		$input = trim( $input );
		
		// Check for empty input
		if ( empty( $input ) ) {
			throw new \InvalidArgumentException( 
				"Empty {$type} identifier provided. " .
				"Expected a slug (e.g., 'woocommerce'), " .
				"a local path (e.g., './my-plugin' or '/path/to/plugin.zip'), " .
				"or a URL (e.g., 'https://example.com/plugin.zip')"
			);
		}
		
		// Check for explicit slug@source format
		// Only treat @ as separator if what comes before it is a valid slug
		$explicit_slug = null;
		if ( strpos( $input, '@' ) !== false ) {
			// Split on first @ only (in case URL contains @ in auth)
			$at_pos = strpos( $input, '@' );
			$potential_slug = substr( $input, 0, $at_pos );
			
			// Only treat as slug@source if the part before @ is a valid slug
			if ( self::is_valid_slug( $potential_slug ) ) {
				$explicit_slug = $potential_slug;
				$input = substr( $input, $at_pos + 1 );
				
				// Validate that we have a source after @
				if ( empty( $input ) ) {
					throw new \InvalidArgumentException(
						"Missing source after @ in {$type} specification '{$explicit_slug}@'. " .
						"Expected format: slug@path or slug@url"
					);
				}
			}
			// Otherwise, treat the whole thing as a path/URL that happens to contain @
		}
		
		// 1. Check if it's a path that exists on disk
		if ( self::is_existing_path( $input ) ) {
			return self::parse_local_path( $input, $type, $explicit_slug );
		}
		
		// 2. Check if it's a well-formed URI
		if ( self::is_valid_uri( $input ) ) {
			return self::parse_url( $input, $type, $explicit_slug );
		}
		
		// 3. Check if it's a pure slug (only if no @ was used)
		if ( $explicit_slug === null && self::is_valid_slug( $input ) ) {
			return self::parse_slug( $input, $type );
		}
		
		// 4. Otherwise, error
		$error_msg = "Unrecognized {$type} identifier: '{$input}'. ";
		if ( $explicit_slug !== null ) {
			$error_msg = "Unrecognized source in {$type} specification '{$explicit_slug}@{$input}'. ";
		}
		$error_msg .= "Expected a slug (e.g., 'woocommerce'), " .
			"a local path (e.g., './my-plugin' or '/path/to/plugin.zip'), " .
			"a URL (e.g., 'https://example.com/plugin.zip'), " .
			"or an explicit format (e.g., 'my-plugin@/path/to/plugin.zip')";
		
		throw new \InvalidArgumentException( $error_msg );
	}
	
	/**
	 * Check if input is an existing path on disk.
	 */
	private static function is_existing_path( string $input ): bool {
		// Reject any input that looks like a URL (has a scheme)
		if ( preg_match( '/^[a-zA-Z][a-zA-Z0-9+.-]*:\/\//', $input ) ) {
			return false;
		}
		
		// Check both as-is and with realpath resolution
		return @file_exists( $input ) || ( @realpath( $input ) !== false );
	}
	
	/**
	 * Check if input is a valid URI.
	 */
	private static function is_valid_uri( string $input ): bool {
		// Must have a scheme to be considered a URI
		$parts = parse_url( $input );
		if ( ! isset( $parts['scheme'] ) ) {
			return false;
		}
		
		// Only support HTTP/HTTPS for remote URLs
		$allowed_schemes = [ 'http', 'https' ];
		return in_array( strtolower( $parts['scheme'] ), $allowed_schemes, true );
	}
	
	/**
	 * Check if input is a valid slug.
	 */
	private static function is_valid_slug( string $input ): bool {
		// Don't treat underscores at start/end as valid slugs
		// This helps catch special cases like ___test___
		if ( preg_match( '/^_+|_+$/', $input ) ) {
			return false;
		}
		return (bool) preg_match( '/^[a-zA-Z0-9_-]+$/', $input );
	}
	
	/**
	 * Parse a local path into an Extension.
	 * 
	 * @param string $path The local path
	 * @param string $type Extension type
	 * @param string|null $explicit_slug Explicitly provided slug, if any
	 */
	private static function parse_local_path( string $path, string $type, ?string $explicit_slug = null ): Extension {
		$realpath = realpath( $path ) ?: $path;
		
		// Determine if it's a zip or directory
		$is_zip = ( pathinfo( $realpath, PATHINFO_EXTENSION ) === 'zip' );
		
		// Use explicit slug if provided, otherwise infer from path
		if ( $explicit_slug !== null ) {
			$slug = $explicit_slug;
			$added_from = 'Added from CLI parameter (local path with explicit slug)';
		} else {
			$slug = self::infer_slug_from_path( $realpath );
			$added_from = 'Added from CLI parameter (local path)';
			
			// Add warning about inferred slug
			if ( class_exists( '\\QIT_CLI\\App' ) && method_exists( '\\QIT_CLI\\App', 'make' ) ) {
				$output = \QIT_CLI\App::make( \QIT_CLI\IO\Output::class );
				if ( $output instanceof \QIT_CLI\IO\Output ) {
					$output->writeln( sprintf(
						'<comment>Warning: Inferred slug "%s" from path "%s". If this is incorrect, use explicit format: %s@%s</comment>',
						$slug,
						$path,
						'<correct-slug>',
						$path
					) );
				}
			}
		}
		
		$extension = new Extension( $slug, $type );
		$extension->from = 'local';
		$extension->source = $realpath;
		
		if ( ! $is_zip && is_dir( $realpath ) ) {
			$extension->directory = $realpath;
		}
		
		$extension->added_automatically = $added_from;
		
		return $extension;
	}
	
	/**
	 * Parse a URL into an Extension.
	 * 
	 * @param string $url The URL
	 * @param string $type Extension type
	 * @param string|null $explicit_slug Explicitly provided slug, if any
	 */
	private static function parse_url( string $url, string $type, ?string $explicit_slug = null ): Extension {
		// Use explicit slug if provided, otherwise infer from URL
		if ( $explicit_slug !== null ) {
			$slug = $explicit_slug;
			$added_from = 'Added from CLI parameter (URL with explicit slug)';
		} else {
			$slug = self::infer_slug_from_url( $url );
			$added_from = 'Added from CLI parameter (URL)';
			
			// Add warning about inferred slug
			if ( class_exists( '\\QIT_CLI\\App' ) && method_exists( '\\QIT_CLI\\App', 'make' ) ) {
				$output = \QIT_CLI\App::make( \QIT_CLI\IO\Output::class );
				if ( $output instanceof \QIT_CLI\IO\Output ) {
					$output->writeln( sprintf(
						'<comment>Warning: Inferred slug "%s" from URL "%s". If this is incorrect, use explicit format: %s@%s</comment>',
						$slug,
						$url,
						'<correct-slug>',
						$url
					) );
				}
			}
		}
		
		$extension = new Extension( $slug, $type );
		$extension->from = 'url';
		$extension->source = $url;
		$extension->added_automatically = $added_from;
		
		return $extension;
	}
	
	/**
	 * Parse a pure slug into an Extension.
	 */
	private static function parse_slug( string $slug, string $type ): Extension {
		$extension = new Extension( $slug, $type );
		// Don't set 'from' - let ExtensionResolver determine the source
		$extension->version = 'stable';
		$extension->added_automatically = 'Added from CLI parameter (slug)';
		
		return $extension;
	}
	
	/**
	 * Infer a slug from a local path.
	 * 
	 * @param string $path Absolute path to file or directory
	 * @return string The inferred slug
	 * @throws \InvalidArgumentException If inferred slug is not valid
	 */
	private static function infer_slug_from_path( string $path ): string {
		// Get the basename (filename or directory name)
		$basename = basename( $path );
		
		// Remove .zip extension if present
		if ( substr( $basename, -4 ) === '.zip' ) {
			$basename = substr( $basename, 0, -4 );
		}
		
		// Validate the basename as a slug - don't modify it
		return self::validate_slug( $basename );
	}
	
	/**
	 * Infer a slug from a URL.
	 * 
	 * @param string $url The URL
	 * @return string The inferred slug
	 * @throws \InvalidArgumentException If inferred slug is not valid
	 */
	private static function infer_slug_from_url( string $url ): string {
		// Get the filename from URL
		$path = parse_url( $url, PHP_URL_PATH );
		if ( empty( $path ) || $path === '/' ) {
			throw new \InvalidArgumentException(
				"Cannot infer slug from URL '{$url}' (no filename in path). " .
				"Please use explicit format: slug@url"
			);
		}
		
		// Get just the filename part
		$basename = basename( $path );
		if ( empty( $basename ) ) {
			throw new \InvalidArgumentException(
				"Cannot infer slug from URL '{$url}' (no filename in path). " .
				"Please use explicit format: slug@url"
			);
		}
		
		// Remove .zip extension if present
		if ( substr( $basename, -4 ) === '.zip' ) {
			$basename = substr( $basename, 0, -4 );
		}
		
		// Validate the basename as a slug - don't modify it
		return self::validate_slug( $basename );
	}
	
	/**
	 * Validate that a string is a valid slug.
	 * Don't modify it - just check if it's valid.
	 * 
	 * @param string $string Raw string
	 * @return string The string if valid
	 * @throws \InvalidArgumentException If string is not a valid slug
	 */
	private static function validate_slug( string $string ): string {
		if ( empty( $string ) ) {
			throw new \InvalidArgumentException( 
				'Cannot infer slug from empty name. Please use explicit format: slug@path'
			);
		}
		
		if ( ! self::is_valid_slug( $string ) ) {
			throw new \InvalidArgumentException( 
				"Inferred slug '{$string}' is not valid. Slugs must contain only letters, numbers, hyphens, and underscores. " .
				"Please use explicit format: slug@path"
			);
		}
		
		return $string;
	}
}