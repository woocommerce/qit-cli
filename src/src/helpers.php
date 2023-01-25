<?php

namespace QIT_CLI;

function is_windows(): bool {
	return strtoupper( substr( PHP_OS, 0, 3 ) ) === 'WIN';
}

function validate_authentication( string $username, string $application_password ): void {
	try {
		( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/partner-auth' ) )
			->with_method( 'POST' )
			->with_post_body( [
				'app_pass' => base64_encode( sprintf( '%s:%s', $username, $application_password ) ),
			] )
			->request();
	} catch ( \Exception $e ) {
		throw new \Exception( sprintf( 'Could not authenticate to %s using the provided username and application password.', get_wccom_url() ) );
	}
}

/**
 * Tries to open a given URL using the default browser.
 *
 * @param string $url The URL to open in the browser.
 *
 * @throws \InvalidArgumentException When the URL is invalid.
 *
 * @return void
 */
function open_in_browser( string $url ): void {
	$url = htmlspecialchars_decode( $url );
	$url = filter_var( $url, FILTER_SANITIZE_URL );

	// We only open URLs.
	// We can also optionally allow FILTER_VALIDATE_IP as well.
	// Todo: Remove str_replace once we rename cd_manager.loc to cd-manager.loc.
	if ( ! filter_var( str_replace( '_', '-', $url ), FILTER_VALIDATE_URL ) ) {
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
	return App::make( Config::class )->get_manager_sync_data( 'wccom_url' );
}

/**
 * @return string The URL to the CD Manager instance to use.
 */
function get_manager_url(): string {
	$override = App::make( Config::class )->get_cache( 'manager_url' );

	if ( ! is_null( $override ) ) {
		return (string) $override;
	}

	return 'https://compatibilitydashboard.wpcomstaging.com';
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
