<?php
/* bootstrap-node.php -------------------------------------------------- */

require_once __DIR__ . '/helpers.php';                    // log_*()

// ───────────────────────────────────────────────────────────────────────
// 1. RUNTIME – validate env‑vars, set INI, register autoloader
// ───────────────────────────────────────────────────────────────────────
/**
 * Runtime initialization
 */
function qit_runtime_init(): void {
	static $once = false;
	if ( $once ) {
		return;
	} $once = true;

	foreach ( [ 'QIT_NODE_TOKEN', 'QIT_NODE_DIR', 'QIT_AI_DIR', 'QIT_PROVIDER', 'QIT_PROVIDER_CFG' ]
			as $var ) {
		if ( getenv( $var ) === false || getenv( $var ) === '' ) {
			throw new RuntimeException( "Env‑var $var is missing" );
		}
	}

	$node_dir = rtrim( getenv( 'QIT_NODE_DIR' ), '/' ) . '/';
	// Configure error logging
	error_reporting( E_ALL ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting
	// Note: Using ini_set for node configuration - WordPress norms don't apply here
	ini_set( 'log_errors', '1' );
	ini_set( 'error_log', $node_dir . 'php-errors.log' );
	ini_set( 'display_errors', '1' );

	// PSR‑4 autoloader for QIT_AI_Webserver\*
	spl_autoload_register(function ( $class_to_load ) {
		$prefix = 'QIT_AI_Webserver\\';
		if ( strncmp( $class_to_load, $prefix, strlen( $prefix ) ) !== 0 ) {
			return;
		}
		$path = __DIR__ . '/' . str_replace( '\\', '/', substr( $class_to_load, strlen( $prefix ) ) ) . '.php';
		if ( is_file( $path ) ) {
			require $path;
		}
	});
}

/**
 * 2. HTTP – parse request & (optionally) enforce token / rate limit
 * @return array<string, mixed>
 */
function qit_http_request( bool $check_token = true ): array {
	qit_runtime_init();

	$method      = $_SERVER['REQUEST_METHOD'] ?? 'GET';
	$raw_uri     = $_SERVER['REQUEST_URI'] ?? '/';
	$uri         = parse_url( $raw_uri, PHP_URL_PATH ) ?? '/';
	$headers     = getallheaders();
	$remote_addr = $_SERVER['REMOTE_ADDR'] ?? '';

	// ---- token guard --------------------------------------------------
	if ( $check_token ) {
		$provided_token = $headers['X-Node-Token'] ?? '';
		if ( $provided_token !== getenv( 'QIT_NODE_TOKEN' ) ) {
			// Log **before** responding so we can correlate in Manager logs.
			if ( function_exists( 'log_error' ) ) {
				\log_error('Request rejected – node token mismatch', [
					'expected_prefix' => substr( getenv( 'QIT_NODE_TOKEN' ), 0, 8 ) . '...',
					'provided_prefix' => substr( $provided_token, 0, 8 ) . '...',
					'method'          => $method,
					'uri'             => $uri,
					'remote_addr'     => $remote_addr,
				]);
			}
			http_response_code( 403 );
			echo json_encode( [ 'error' => 'Unauthorized – token mismatch' ] );
			exit;
		}
	} else {
		// For worker router, check that it's being requested from localhost (by the poller)
		if ( $remote_addr !== '127.0.0.1' && $remote_addr !== 'localhost' ) {
			if ( function_exists( 'log_warning' ) ) {
				\log_warning('Request rejected – worker router not local', [
					'remote_addr' => $remote_addr,
					'method'      => $method,
					'uri'         => $uri,
				]);
			}
			http_response_code( 403 );
			echo json_encode( [ 'error' => 'Worker can only be accessed from localhost' ] );
			exit;
		}
	}

	// ---- rate limit ---------------------------------------------------
	$key  = strtolower( $method ) . '_' . trim( $uri, '/' ) . '_' . md5( $headers['X-Node-Token'] ?? '' );
	$file = getenv( 'QIT_NODE_DIR' ) . "/rate-limit/$key";
	if ( ! is_dir( dirname( $file ) ) ) {
		mkdir( dirname( $file ), 0700, true );
	}
	if ( file_exists( $file ) && microtime( true ) - filemtime( $file ) < 0.005 ) {
		if ( function_exists( 'log_warning' ) ) {
			\log_warning('Request rate-limited', [
				'key'         => $key,
				'remote_addr' => $remote_addr,
				'method'      => $method,
				'uri'         => $uri,
			]);
		}
		http_response_code( 429 );
		echo json_encode( [ 'error' => 'Rate limited' ] );
		exit;
	}
	touch( $file );

	// ---- body ---------------------------------------------------------
	$body  = file_get_contents( 'php://input' ) ?: '';
	$input = $body === '' ? [] : json_decode( $body, true );
	if ( $body !== '' && json_last_error() !== JSON_ERROR_NONE ) {
		if ( function_exists( 'log_error' ) ) {
			\log_error('Malformed JSON received', [
				'json_error'     => json_last_error_msg(),
				'method'         => $method,
				'uri'            => $uri,
				'remote_addr'    => $remote_addr,
				'truncated_body' => substr( $body, 0, 200 ) . ( strlen( $body ) > 200 ? '…' : '' ),
			]);
		}
		http_response_code( 400 );
		echo json_encode( [ 'error' => 'Malformed JSON: ' . json_last_error_msg() ] );
		exit;
	}

	return compact( 'method', 'uri', 'headers', 'input' );
}

/**
 * 3. LLM – boot LLPhant once per request / CLI invocation
 * @param array<string, mixed> $overrides
 */
function qit_llm_boot( array $overrides = [] ): void {
	qit_runtime_init();

	$provider = getenv( 'QIT_PROVIDER' );
	$cfg      = json_decode( getenv( 'QIT_PROVIDER_CFG' ), true ) + $overrides;
	$cfg['provider'] = $provider;

	$bootstrap = new \QIT_AI_Webserver\Lib\LLPhantBootstrap($cfg);
	$bootstrap->boot( $cfg );
}
