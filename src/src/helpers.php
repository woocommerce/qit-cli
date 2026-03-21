<?php

namespace QIT_CLI;

use QIT_CLI\IO\Output;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

function is_windows(): bool {
	if ( defined( 'UNIT_TESTS' ) && UNIT_TESTS ) {
		if ( App::getVar( 'MIMICK_WINDOWS' ) ) {
			return true;
		}
	}

	return defined( 'PHP_WINDOWS_VERSION_BUILD' );
}

function is_mac(): bool {
	return stripos( PHP_OS, 'Darwin' ) !== false;
}

function is_wsl(): bool {
	return getenv( 'WSL_DISTRO_NAME' ) !== false;
}

/**
 * @return string Converts Windows-style directory separator to Unix-style. Makes sure it ends with a trailing slash.
 */
function normalize_path( string $path, bool $trailingslashit = true ): string {
	$path = str_replace( '\\', '/', $path );

	if ( $trailingslashit ) {
		$path = rtrim( $path, '/' ) . '/';
	}

	return $path;
}

function use_tty(): bool {
	return ! is_ci() && ! is_windows();
}

function validate_authentication( string $username, string $qit_token ): void {
	$is_ci = getenv( 'CI' );

	try {
		( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/partner-auth' ) )
			->with_method( 'POST' )
			->with_retry( $is_ci ? 8 : 2 ) // Retry many times due to parallel test runs in CI which might cause 429.
			->with_post_body( [
				'app_pass' => base64_encode( sprintf( '%s:%s', $username, $qit_token ) ),
			] )
			->with_timeout_in_seconds( 300 ) // Depending on how many extensions the user has, this can take a while.
			->request();
	} catch ( \Exception $e ) {
		throw new \Exception( sprintf( 'Could not authenticate to %s using the provided username and QIT Token. Questions? https://qit.woo.com/docs/support/authenticating', get_wccom_url() ) );
	}
}

/**
 * Tries to open a given URL using the default browser.
 *
 * @param string $url The URL to open in the browser.
 *
 * @return void
 * @throws \InvalidArgumentException When the URL is invalid.
 */
function open_in_browser( string $url ): void {
	$url = htmlspecialchars_decode( $url );
	$url = filter_var( $url, FILTER_SANITIZE_URL );

	// We only open URLs.
	// We can also optionally allow FILTER_VALIDATE_IP as well.
	if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
		throw new \InvalidArgumentException( 'Invalid URL provided.' );
	}

	// We only accept HTTP(s) protocol.
	if ( ! preg_match( '#^https?://#i', $url ) ) {
		throw new \InvalidArgumentException( 'Invalid URL provided. Missing HTTP(s) protocol.' );
	}

	switch ( PHP_OS ) {
		case 'Darwin':
			// Mac.
			$command         = 'open';
			$redirect_output = '2>/dev/null';
			break;
		case 'WINNT':
			// Windows. The double quotes are required, as the first parameter of "start" is the title, which we leave empty.
			$command         = 'start ""';
			$redirect_output = '2> nul';
			break;
		default:
			// Portable command across most Linux distros.
			$command         = 'xdg-open';
			$redirect_output = '2>/dev/null';
	}

	/*
	 * The operating system will try to "execute" the URL.
	 * Since it has the HTTP(s) protocol, the default browser
	 * will be assigned to handle it and will open the URL.
	 */
	@exec( sprintf( '%s %s %s', $command, escapeshellarg( $url ), $redirect_output ) );
}

/**
 * @return string The URL of the WCCOM Marketplace to use.
 */
function get_wccom_url(): string {
	return App::make( Cache::class )->get_manager_sync_data( 'wccom_url' );
}

/**
 * @return string The URL to the CD Manager instance to use.
 */
function get_manager_url(): string {
	$override = App::make( Cache::class )->get( 'manager_url' );

	if ( ! is_null( $override ) ) {
		// If it's not staging.
		if ( strpos( $override, 'staging' ) === false ) {
			// And it's contains the old domain.
			if ( strpos( $override, 'compatibilitydashboard.wpcomstaging' ) !== false ) {
				// Update it to the new domain.
				App::make( Cache::class )->set( 'manager_url', 'https://qit.woo.com', - 1 );

				return 'https://qit.woo.com';
			}
		}

		return (string) $override;
	}

	// Low-level alternative to override the Manager URL.
	$env = getenv( 'MANAGER_URL' );

	if ( ! empty( $env ) ) {
		return $env;
	}

	return 'https://qit.woo.com';
}

/**
 * This is a port of the wp_generate_uuid4 function from WordPress Core.
 *
 * @return string A UUID4 string.
 */
function generate_uuid4() {
	// Return a predictable UUID4 for unit tests.
	if ( defined( 'UNIT_TESTS' ) && UNIT_TESTS ) {
		return '123e4567-e89b-12d3-a456-426614174000';
	}

	return sprintf(
		'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0x0fff ) | 0x4000,
		mt_rand( 0, 0x3fff ) | 0x8000,
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0xffff )
	);
}

function is_ci(): bool {
	return (bool) getenv( 'CI' );
}

/**
 * @param int $seconds The number of seconds to format.
 *
 * @return string A human-readable string representing the elapsed time.
 */
function format_elapsed_time( int $seconds ): string {
	$periods       = [ 'second', 'minute', 'hour', 'day', 'week', 'month', 'year' ];
	$lengths       = [ 60, 60, 24, 7, 4.35, 12 ];
	$count_lengths = count( $lengths );

	for ( $i = 0; $seconds >= $lengths[ $i ] && $i < $count_lengths - 1; $i++ ) {
		$seconds /= $lengths[ $i ];
	}

	$seconds = round( $seconds );
	if ( $seconds != 1 ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison,Universal.Operators.StrictComparisons.LooseNotEqual
		$periods[ $i ] .= 's';
	}

	return "$seconds $periods[$i] ago";
}

function banner( SymfonyStyle $io, string $label, bool $line_before = true, bool $line_after = true, string $icon = '' ): void {
	$line = str_repeat( '─', max( 0, 60 - mb_strlen( $label ) ) );

	if ( $line_before ) {
		$io->writeln( '' );
	}
	$io->writeln( "<fg=bright-cyan;options=bold>── $icon {$label} {$line}</>" );
	if ( $line_after ) {
		$io->writeln( '' );
	}
}

/**
 * Checks if an option was explicitly provided in the input.
 *
 * @param InputInterface $input The input interface.
 * @param string         $option_name The name of the option to check (e.g., 'profile').
 *
 * @return bool True if the option was explicitly provided, false otherwise.
 */
function is_option_explicitly_provided( InputInterface $input, string $option_name ): bool {
	// Handle QITInput wrapper
	if ( $input instanceof \QIT_CLI\QITInput ) {
		$input = $input->get_symfony_input();
	}

	if ( $input instanceof ArgvInput ) {
		// For ArgvInput, check if the option appears in the raw argv array
		$argv = $_SERVER['argv'] ?? [];
		foreach ( $argv as $arg ) {
			if ( preg_match( "/^--{$option_name}(=.*)?$/", $arg ) || preg_match( '/^-p(=.*)?$/', $arg ) ) {
				return true;
			}
		}

		return false;
	} elseif ( $input instanceof ArrayInput ) {
		// For ArrayInput, check if the option is present using hasParameterOption
		return $input->hasParameterOption( [ "--{$option_name}", '-p' ], true );
	}

	// Default to false for other input types
	return false;
}

/**
 * Write debug output only when verbose mode is enabled
 *
 * @param string|array<string> $messages The message(s) to output.
 * @param string               $type The type of message (info, comment, error, etc.).
 * @return void
 */
function debug_log( $messages, string $type = 'comment' ): void {
	$output = App::make( Output::class );

	if ( ! $output->isVerbose() ) {
		return;
	}

	if ( ! is_array( $messages ) ) {
		$messages = [ $messages ];
	}

	foreach ( $messages as $message ) {
		switch ( $type ) {
			case 'info':
				$output->writeln( "<info>[DEBUG] $message</info>" );
				break;
			case 'error':
				$output->writeln( "<e>[DEBUG] $message</e>" );
				break;
			case 'comment':
			default:
				$output->writeln( "<comment>[DEBUG] $message</comment>" );
				break;
		}
	}
}

/**
 * Write very verbose debug output (requires -vv or -vvv)
 *
 * @param string|array<string> $messages The message(s) to output.
 * @param string               $type The type of message.
 * @return void
 */
function debug_log_verbose( $messages, string $type = 'comment' ): void {
	$output = App::make( Output::class );

	if ( ! $output->isVeryVerbose() ) {
		return;
	}

	debug_log( $messages, $type );
}

/**
 * Dump a variable for debugging (only in verbose mode)
 *
 * @param mixed  $variable The variable to dump.
 * @param string $label Optional label for the dump.
 * @return void
 */
function debug_dump( $variable, string $label = '' ): void {
	$output = App::make( Output::class );

	if ( ! $output->isVerbose() ) {
		return;
	}

	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export -- Debug function in CLI tool
	$dump = var_export( $variable, true );
	if ( $label ) {
		$output->writeln( "<comment>[DEBUG] $label:</comment>" );
	}
	$output->writeln( "<comment>$dump</comment>" );
}
