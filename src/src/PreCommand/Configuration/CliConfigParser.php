<?php
declare( strict_types=1 );

namespace QIT_CLI\PreCommand\Configuration;

use QIT_CLI\QITInput;
use Symfony\Component\Console\Input\InputInterface;
use function QIT_CLI\is_option_explicitly_provided;

/**
 * Parses CLI input into a partial environment config overlay.
 *
 * Returns a partial qit.json environment block containing ONLY
 * options that were explicitly provided on the command line.
 * Symfony defaults are never included — they are handled by
 * the hardcoded defaults in UpEnvironmentCommand after merge.
 */
class CliConfigParser {

	/**
	 * Extract explicitly-provided CLI options as an environment config overlay.
	 *
	 * @param InputInterface $input The input (QITInput or raw InputInterface).
	 *
	 * @return array<string, mixed> Partial environment config.
	 */
	public static function parse( InputInterface $input ): array {
		$config = [];

		/* ─ Scalar version options ─ */
		foreach ( [ 'php_version', 'wordpress_version', 'woocommerce_version' ] as $opt ) {
			if ( self::is_explicit( $input, $opt ) ) {
				$value = $input->getOption( $opt );
				if ( $value !== null ) {
					$config[ $opt ] = $value;
				}
			}
		}

		/* ─ Boolean flag ─ */
		if ( self::is_explicit( $input, 'object_cache' ) ) {
			$config['object_cache'] = (bool) $input->getOption( 'object_cache' );
		}

		/* ─ Extension lists ─ */
		if ( self::is_explicit( $input, 'plugin' ) ) {
			$config['plugins'] = (array) $input->getOption( 'plugin' );
		}
		if ( self::is_explicit( $input, 'theme' ) ) {
			$config['themes'] = (array) $input->getOption( 'theme' );
		}

		/* ─ Simple array options ─ */
		if ( self::is_explicit( $input, 'volume' ) ) {
			$config['volumes'] = (array) $input->getOption( 'volume' );
		}
		if ( self::is_explicit( $input, 'php_extension' ) ) {
			$config['php_extensions'] = (array) $input->getOption( 'php_extension' );
		}

		/* ─ Tunnel ─ */
		if ( self::is_explicit( $input, 'tunnel' ) ) {
			$tunnel_value          = $input->getOption( 'tunnel' );
			$config['tunnel_type'] = $tunnel_value;
			$config['tunnel']      = $tunnel_value !== 'no_tunnel';
		}

		/* ─ Network mode (mutually exclusive flags) ─ */
		if ( self::is_explicit( $input, 'offline' ) ) {
			$config['network_mode'] = 'offline';
		} elseif ( self::is_explicit( $input, 'online' ) ) {
			$config['network_mode'] = 'online';
		}

		return $config;
	}

	/**
	 * Check if an option was explicitly provided on the CLI (not a Symfony default).
	 *
	 * QITInput::hasOption already performs this check.
	 * For raw InputInterface, we fall back to is_option_explicitly_provided().
	 *
	 * @param InputInterface $input The input interface.
	 * @param string         $name  The option name.
	 *
	 * @return bool
	 */
	private static function is_explicit( InputInterface $input, string $name ): bool {
		// QITInput overrides hasOption() to check explicit CLI provision.
		if ( $input instanceof QITInput ) {
			return $input->hasOption( $name );
		}

		if ( function_exists( 'QIT_CLI\is_option_explicitly_provided' ) ) {
			return is_option_explicitly_provided( $input, $name );
		}

		return $input->hasParameterOption( "--$name" );
	}
}
