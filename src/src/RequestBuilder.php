<?php

namespace QIT_CLI;

use Composer\CaBundle\CaBundle;
use QIT_CLI\Exceptions\DoingAutocompleteException;
use QIT_CLI\Exceptions\NetworkErrorException;
use QIT_CLI\IO\Output;
use Symfony\Component\Console\Output\OutputInterface;

class RequestBuilder {
	/** @var string $url */
	protected $url;

	/** @var string $method */
	protected $method = 'GET';

	/** @var array<scalar> $post_body */
	protected $post_body = [];

	/** @var array<int, mixed> $curl_opts */
	protected $curl_opts = [];

	/** @var bool $onboarding */
	protected $onboarding = false;

	/** @var bool $skip_auth */
	protected $skip_auth = false;

	/** @var array<int> */
	protected $expected_status_codes = [ 200 ];

	/** @var int */
	protected $retry = 0;

	/** @var int */
	protected $retry_429 = 5;

	/** @var int */
	protected $timeout_in_seconds = 30;

	/** @var array<string> */
	protected $additional_headers = [];

	/** @var array<string,mixed> */
	protected $files = [];

	public function __construct( string $url = '' ) {
		$this->url = $url;
	}

	/**
	 * @param string $url The URL to send the request to.
	 *
	 * @return $this
	 */
	public function with_url( string $url ): self {
		$this->url = $url;

		return $this;
	}

	/**
	 * @param string $method The HTTP method. Defaults to "GET".
	 *
	 * @return $this
	 */
	public function with_method( string $method ): self {
		$this->method = $method;

		return $this;
	}

	/**
	 * @param array<scalar|array<mixed>> $post_body Optionally set curl's post_body.
	 *
	 * @return $this
	 */
	public function with_post_body( array $post_body ): self {
		$this->post_body = $post_body;

		return $this;
	}

	/**
	 * @param array<int, mixed> $curl_opts Optionally set curl's curl_opts.
	 *
	 * @return $this
	 */
	public function with_curl_opts( array $curl_opts ): self {
		$this->curl_opts = $curl_opts;

		return $this;
	}

	/**
	 * @param array<int> $expected_status_codes Optionally set expected response status code.
	 *
	 * @return $this
	 */
	public function with_expected_status_codes( array $expected_status_codes ): self {
		$this->expected_status_codes = $expected_status_codes;

		return $this;
	}

	/**
	 * @param bool $onboarding
	 *
	 * @return $this
	 */
	public function with_onboarding( bool $onboarding ): self {
		$this->onboarding = $onboarding;

		return $this;
	}

	/**
	 * Skip automatic credential injection for this request.
	 *
	 * @return $this
	 */
	public function without_auth(): self {
		$this->skip_auth = true;

		return $this;
	}

	/**
	 * @param int $retry
	 *
	 * @return RequestBuilder
	 */
	public function with_retry( int $retry ): RequestBuilder {
		$this->retry = $retry;

		return $this;
	}

	/**
	 * @param int $timeout_in_seconds
	 *
	 * @return RequestBuilder
	 */
	public function with_timeout_in_seconds( int $timeout_in_seconds ): RequestBuilder {
		$this->timeout_in_seconds = $timeout_in_seconds;

		return $this;
	}

	/**
	 * Allows adding your own headers (like "Header-Name: value").
	 *
	 * @param string[] $headers
	 *
	 * @return $this
	 */
	public function with_additional_headers( array $headers ): self {
		// Merge them into our $additional_headers property.
		$this->additional_headers = array_merge( $this->additional_headers, $headers );

		return $this;
	}

	/**
	 * @param string $field_name
	 * @param string $file_path
	 *
	 * @return $this
	 */
	public function with_file( string $field_name, string $file_path ): self {
		$this->files[ $field_name ] = $file_path;

		return $this;
	}

	public function request(): string {
		retry_request: // phpcs:ignore Generic.PHP.DiscourageGoto.Found

		// Apply rate limiting before making the request
		self::apply_rate_limit( $this->url );

		// Add client and version early so they're included in mocks.
		$this->post_body['client']      = 'qit_cli';
		$this->post_body['cli_version'] = App::getVar( 'CLI_VERSION' );

		// Integration test mocking - check this first to allow override of unit tests
		if ( getenv( 'QIT_MOCK_DIR' ) ) {
			return $this->handle_file_mock();
		}

		if ( defined( 'UNIT_TESTS' ) ) {
			$mocked = App::getVar( 'mock_' . $this->url );
			if ( is_null( $mocked ) ) {
				throw new \LogicException( 'No mock found for ' . $this->url );
			}

			// Convert error strings to exceptions
			if ( is_string( $mocked ) && strpos( $mocked, 'exception: ' ) === 0 ) {
				$error_message = substr( $mocked, strlen( 'exception: ' ) );
				throw new \RuntimeException( $error_message );
			}

			App::setVar( 'mocked_request', $this->to_array() );

			// Accumulate all requests with builder state for tests that inspect multiple requests.
			$all   = App::getVar( 'mocked_requests' ) ?? [];
			$all[] = array_merge( $this->to_array(), [ 'skip_auth' => $this->skip_auth ] );
			App::setVar( 'mocked_requests', $all );

			return $mocked;
		}

		// Integration test fixtures — serve cached API responses.
		$fixture_dir = Config::get_fixture_dir();
		if ( $fixture_dir !== null ) {
			$fixture_response = self::resolve_api_fixture( $fixture_dir, $this->url );
			if ( $fixture_response !== null ) {
				self::log_request( $this->url, 'request:fixture', $this->method );

				return $fixture_response;
			}
			// URL didn't match any fixture pattern — let it through (e.g., Manager API calls).
		}

		if ( empty( $this->url ) ) {
			throw new \LogicException( 'URL cannot be empty.' );
		}

		// Early bail: Do not make remote requests when doing completion.
		if ( App::getVar( 'doing_autocompletion' ) ) {
			throw new DoingAutocompleteException();
		}

		self::log_request( $this->url, 'request', $this->method );

		$curl = curl_init();

		$curl_parameters = [
			CURLOPT_URL            => $this->url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_POSTREDIR      => CURL_REDIR_POST_ALL,
			CURLOPT_CONNECTTIMEOUT => $this->timeout_in_seconds,
			CURLOPT_TIMEOUT        => $this->timeout_in_seconds,
			CURLOPT_HEADER         => 1,
		];

		try {
			$ca_path_or_file = CaBundle::getSystemCaRootBundlePath();

			if ( is_dir( $ca_path_or_file ) ) {
				$curl_parameters[ CURLOPT_CAPATH ] = $ca_path_or_file;
			} else {
				$curl_parameters[ CURLOPT_CAINFO ] = $ca_path_or_file;
			}
		} catch ( \Exception $e ) {
			if ( App::make( Output::class )->isVerbose() ) {
				App::make( Output::class )->writeln( '<error>Could not set CAINFO for cURL: ' . $e->getMessage() . '</error>' );
			}
		}

		if ( getenv( 'QIT_DEBUG_REQUESTS' ) ) {
			$curl_parameters[ CURLOPT_VERBOSE ] = true;
		}

		if ( ! empty( getenv( 'QIT_CUSTOM_HEADERS' ) ) ) {
			// Comma-separated list of headers.
			$parsed_env_headers = array_map( 'trim', explode( ',', getenv( 'QIT_CUSTOM_HEADERS' ) ) );

			$this->additional_headers = array_merge( $this->additional_headers, $parsed_env_headers );
		}

		$proxied = false;

		if ( $this->onboarding ) {
			// When onboarding, proxy the request to test.
			$proxied                              = true;
			$curl_parameters[ CURLOPT_PROXY ]     = Config::get_proxy_url();
			$curl_parameters[ CURLOPT_PROXYTYPE ] = CURLPROXY_SOCKS5;
		} elseif ( ! $this->skip_auth ) {
			if ( ! is_null( App::make( Auth::class )->get_manager_secret() ) ) {
				$this->post_body['manager_secret'] = App::make( Auth::class )->get_manager_secret();
				// Connections using the MANAGER_SECRET that are not local must go through Automattic Proxy.
				if ( strpos( $this->url, 'qit.woo.com' ) || strpos( $this->url, 'compatibilitydashboard' ) ) {
					if ( strpos( $this->url, '.test' ) === false && strpos( $this->url, 'stagingcompatibilitydashboard' ) === false ) {
						$proxied                              = true;
						$curl_parameters[ CURLOPT_PROXY ]     = Config::get_proxy_url();
						$curl_parameters[ CURLOPT_PROXYTYPE ] = CURLPROXY_SOCKS5;
					}
				}
			} elseif ( ! is_null( App::make( Auth::class )->get_partner_auth() ) ) {
				$this->post_body['partner_app_pass'] = App::make( Auth::class )->get_partner_auth();
			}
		}

		switch ( $this->method ) {
			case 'GET':
				// no-op.
				$curl_parameters[ CURLOPT_HTTPHEADER ] = $this->additional_headers;
				break;
			case 'POST':
				$curl_parameters[ CURLOPT_POST ] = true;

				if ( ! empty( $this->files ) ) {
					// Handle multipart/form-data for file uploads
					$post_fields = $this->post_body;
					foreach ( $this->files as $field_name => $file_path ) {
						$post_fields[ $field_name ] = new \CURLFile( $file_path );
					}
					$curl_parameters[ CURLOPT_POSTFIELDS ] = $post_fields;
					$curl_parameters[ CURLOPT_HTTPHEADER ] = $this->additional_headers;
				} else {
					// Handle JSON for regular requests
					$json_data                             = json_encode( $this->post_body );
					$curl_parameters[ CURLOPT_POSTFIELDS ] = $json_data;
					$curl_parameters[ CURLOPT_HTTPHEADER ] = array_merge(
						[
							'Content-Type: application/json',
							'Content-Length: ' . strlen( $json_data ),
						],
						$this->additional_headers
					);
				}
				break;
			default:
				$curl_parameters[ CURLOPT_HTTPHEADER ]    = $this->additional_headers;
				$curl_parameters[ CURLOPT_CUSTOMREQUEST ] = $this->method;
				break;
		}

		if ( ! empty( $this->curl_opts ) ) {
			$curl_parameters = array_replace( $curl_parameters, $this->curl_opts );
		}

		curl_setopt_array( $curl, $curl_parameters );

		if ( App::make( Output::class )->isVeryVerbose() ) {
			$request_in_logs = $this->to_array();

			/*
			 * Remove some sensitive data from external request logs just to protect the user from itself
			 * in case it's running on verbose mode in CI.
			 */
			foreach ( [ 'app_pass', 'partner_app_pass', 'manager_secret' ] as $protected_key ) {
				if ( ! empty( $request_in_logs['post_body'][ $protected_key ] ) ) {
					$request_in_logs['post_body'][ $protected_key ] = '***';
				}
			}

			App::make( Output::class )->writeln( sprintf( '[QIT DEBUG] Running external request (%s): %s', gmdate( 'y-m-d H:i:s' ), json_encode( $request_in_logs, JSON_PRETTY_PRINT ) ) );
		}

		$result     = curl_exec( $curl );
		$curl_error = curl_error( $curl );

		// Extract header size and separate headers from body.
		$header_size = curl_getinfo( $curl, CURLINFO_HEADER_SIZE );
		$headers     = substr( $result, 0, $header_size );
		$body        = substr( $result, $header_size );

		$response_status_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
		curl_close( $curl );

		if ( ! in_array( $response_status_code, $this->expected_status_codes, true ) ) {
			if ( $proxied && $result === false ) {
				$body = sprintf( 'Is the Automattic Proxy running and accessible through %s?', Config::get_proxy_url() );
			}

			if ( ! empty( $curl_error ) ) {
				// Network error, such as a timeout, etc.
				$error_message = $curl_error;
			} else {
				// Application error, such as invalid parameters, etc.
				$error_message = $body;
				$json_response = json_decode( $error_message, true );

				if ( is_array( $json_response ) && array_key_exists( 'message', $json_response ) ) {
					$error_message = $json_response['message'];
				}
			}

			if ( $response_status_code === 429 ) {
				if ( $this->retry_429 > 0 ) {
					--$this->retry_429;
					$sleep_seconds = $this->wait_after_429( $headers );
					App::make( Output::class )->writeln( sprintf( '<comment>Request failed... Waiting %d seconds and retrying (429 Too many Requests)</comment>', $sleep_seconds ) );

					sleep( $sleep_seconds );
					goto retry_request; // phpcs:ignore Generic.PHP.DiscourageGoto.Found
				}
			} else {
				if ( $this->retry > 0 ) {
					--$this->retry;
					App::make( Output::class )->writeln( sprintf( '<comment>Request failed... Retrying (HTTP Status Code %s) %s</comment>', $response_status_code, $error_message ) );

					// Between 1 and 5s.
					sleep( rand( 1, 5 ) );
					goto retry_request; // phpcs:ignore Generic.PHP.DiscourageGoto.Found
				}
			}

			if ( App::make( OutputInterface::class )->isVerbose() ) {
				throw new NetworkErrorException(
					sprintf( '%s (Status code: %s, Expected: %s, Request URL: %s)',
						$error_message,
						$response_status_code,
						implode( ', ', $this->expected_status_codes ),
						$this->url
					),
					$response_status_code
				);
			} else {
				$json_decoded = json_decode( $body, true );

				/**
				 * If the errors is a rest_invalid_group_param, it must be parsed and printed.
				 */
				if ( isset( $json_decoded['code'] ) &&
					$json_decoded['code'] === 'rest_invalid_group_param'
				) {
					return $body;
				}
				throw new NetworkErrorException( $error_message );
			}
		}

		return $body;
	}

	/**
	 * Downloads a file from the specified URL and writes it to the specified path.
	 *
	 * @param string $url The URL to download the file from.
	 * @param string $file_path The path of the file to write to.
	 *
	 * @throws \RuntimeException If an error occurs during downloading or file handling.
	 * @throws \LogicException If no mock is found for the URL during unit tests.
	 */
	public static function download_file( string $url, string $file_path ): void {
		$output = App::make( Output::class );

		if ( $output->isVeryVerbose() ) {
			$output->writeln( "Downloading $url into $file_path..." );
		}

		// Apply rate limiting for downloads
		self::apply_rate_limit( $url );

		// Check for mock response in unit tests
		if ( defined( 'UNIT_TESTS' ) ) {
			$mocked = App::getVar( 'mock_' . $url );
			if ( is_null( $mocked ) ) {
				throw new \LogicException( 'No mock found for ' . $url );
			}

			// Convert error strings to exceptions
			if ( is_string( $mocked ) && strpos( $mocked, 'exception: ' ) === 0 ) {
				$error_message = substr( $mocked, strlen( 'exception: ' ) );
				throw new \RuntimeException( $error_message );
			}

			if ( is_array( $mocked ) ) {
				$mock_body = (string) ( $mocked['body'] ?? '' );
				if ( file_put_contents( $file_path, $mock_body ) === false ) {
					throw new \RuntimeException( 'Could not write mock response to file: ' . $file_path );
				}

				$mock_status = isset( $mocked['status'] ) ? (int) $mocked['status'] : 200;
				self::assert_download_succeeded( $mock_status, $url, $file_path, $mocked['effective_url'] ?? null );

				if ( $output->isVerbose() ) {
					$output->writeln( "Used mock response for $url, written to $file_path" );
				}

				return;
			}

			// Write mock response to file
			if ( file_put_contents( $file_path, $mocked ) === false ) {
				throw new \RuntimeException( 'Could not write mock response to file: ' . $file_path );
			}
			self::assert_download_succeeded( 200, $url, $file_path );

			if ( $output->isVerbose() ) {
				$output->writeln( "Used mock response for $url, written to $file_path" );
			}

			return;
		}

		// Integration test fixtures — serve local zips instead of downloading.
		$fixture_dir = Config::get_fixture_dir();
		if ( $fixture_dir ) {
			$fixture_file = null;

			// WordPress.org download URL pattern:
			// https://downloads.wordpress.org/plugin/{slug}.{version}.zip
			// https://downloads.wordpress.org/theme/{slug}.{version}.zip
			if ( preg_match( '#downloads\.WordPress\.org/(?:plugin|theme)/([^.]+)\.#i', $url, $matches ) ) {
				$slug         = $matches[1];
				$type         = strpos( $url, '/plugin/' ) !== false ? 'plugins' : 'themes';
				$fixture_file = $fixture_dir . '/' . $type . '/' . $slug . '.zip';
			}

			// GitHub release URL pattern (e.g., woocommerce RC/nightly):
			// https://github.com/{owner}/{repo}/releases/download/{tag}/{slug}.zip
			if ( $fixture_file === null && preg_match( '#github\.com/.+/releases/download/.+/([^/]+)\.zip$#i', $url, $matches ) ) {
				$slug         = $matches[1];
				$fixture_file = $fixture_dir . '/plugins/' . $slug . '.zip';
			}

			if ( $fixture_file !== null && file_exists( $fixture_file ) ) {
				copy( $fixture_file, $file_path );
				self::log_request( $url, 'download:fixture' );

				return;
			}

			// No fixture found — fail loudly so we know a test needs a new fixture.
			throw new \RuntimeException(
				"Integration test attempted to download '$url' but no fixture exists. " .
				'Add a fixture zip or mock this URL.'
			);
		}

		// Open file for writing, create it if it doesn't exist.
		$fp = fopen( $file_path, 'w' );
		if ( $fp === false ) {
			throw new \RuntimeException( 'Could not open file for writing: ' . $file_path );
		}

		$curl = curl_init();

		$curl_parameters = [
			CURLOPT_URL            => $url,
			CURLOPT_RETURNTRANSFER => false, // Directly write the output.
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_FILE           => $fp,   // Write the output to the file.
		];

		try {
			$ca_path_or_file = CaBundle::getSystemCaRootBundlePath();

			if ( is_dir( $ca_path_or_file ) ) {
				$curl_parameters[ CURLOPT_CAPATH ] = $ca_path_or_file;
			} else {
				$curl_parameters[ CURLOPT_CAINFO ] = $ca_path_or_file;
			}
		} catch ( \Exception $e ) {
			if ( App::make( Output::class )->isVerbose() ) {
				App::make( Output::class )->writeln( '<error>Could not set CAINFO for cURL: ' . $e->getMessage() . '</error>' );
			}
		}

		curl_setopt_array( $curl, $curl_parameters );

		self::log_request( $url, 'download:network' );

		$start = microtime( true );
		curl_exec( $curl );
		if ( $output->isVerbose() && ! is_ci() ) {
			$output->writeln( sprintf( 'Downloaded %s in %f seconds.', $url, microtime( true ) - $start ) );
		}
		$curl_error    = curl_error( $curl );
		$http_code     = (int) curl_getinfo( $curl, CURLINFO_HTTP_CODE );
		$effective_url = curl_getinfo( $curl, CURLINFO_EFFECTIVE_URL );
		curl_close( $curl );
		fclose( $fp );

		if ( $curl_error ) {
			// Delete the potentially partially written file.
			self::delete_download_file( $file_path );
			throw new \RuntimeException( 'Curl ' . $curl_error );
		}

		self::assert_download_succeeded( $http_code, $url, $file_path, $effective_url );
	}

	/**
	 * Validate a completed file download and remove unusable files before throwing.
	 */
	protected static function assert_download_succeeded( int $http_code, string $url, string $file_path, ?string $effective_url = null ): void {
		// A transport-level success can still be an HTTP error (404, 403, 5xx). With
		// CURLOPT_FILE the error body is streamed to $file_path, so without this guard
		// a 404 "Not found" page (e.g. a WordPress.org download URL for a version that
		// does not exist) masquerades as the downloaded zip and only fails later as a
		// misleading "invalid zip" error. Fail loudly here, naming the real cause.
		$http_error = self::download_http_error_message( $http_code, $url, $file_path, $effective_url );
		if ( $http_error !== null ) {
			self::delete_download_file( $file_path );
			throw new \RuntimeException( $http_error );
		}

		clearstatcache( true, $file_path );
		$file_size = file_exists( $file_path ) ? filesize( $file_path ) : false;
		if ( $file_size === false || $file_size <= 0 ) {
			self::delete_download_file( $file_path );
			throw new \RuntimeException( 'Download failed: empty response for ' . self::sanitize_download_url_for_logs( $effective_url ?: $url ) );
		}
	}

	/**
	 * Build an error message when a download returned an HTTP error status, or null
	 * when the status is acceptable. Extracted so the branch is unit-testable
	 * without performing a real network download.
	 */
	protected static function download_http_error_message( int $http_code, string $url, string $file_path, ?string $effective_url = null ): ?string {
		if ( $http_code >= 200 && $http_code < 300 ) {
			return null;
		}

		$snippet = '';
		if ( is_readable( $file_path ) ) {
			$body    = (string) file_get_contents( $file_path, false, null, 0, 200 );
			$snippet = trim( (string) preg_replace( '/\s+/', ' ', $body ) );
		}

		$url_for_logs           = self::sanitize_download_url_for_logs( $url );
		$effective_url_for_logs = $effective_url !== null ? self::sanitize_download_url_for_logs( $effective_url ) : null;
		$effective_url_suffix   = $effective_url_for_logs !== null && $effective_url_for_logs !== $url_for_logs
			? sprintf( ' (effective URL: %s)', $effective_url_for_logs )
			: '';

		return sprintf(
			'Download failed: HTTP %d for %s%s',
			$http_code,
			$url_for_logs,
			$effective_url_suffix . ( $snippet !== '' ? ' - response body starts with: ' . $snippet : '' )
		);
	}

	protected static function sanitize_download_url_for_logs( string $url ): string {
		$parts = parse_url( $url );
		if ( $parts === false || ! isset( $parts['scheme'], $parts['host'] ) ) {
			return (string) preg_replace( '/[?#].*$/', '', $url );
		}

		$sanitized = $parts['scheme'] . '://' . $parts['host'];
		if ( isset( $parts['port'] ) ) {
			$sanitized .= ':' . $parts['port'];
		}
		$sanitized .= $parts['path'] ?? '';

		return $sanitized;
	}

	protected static function delete_download_file( string $file_path ): void {
		if ( file_exists( $file_path ) ) {
			unlink( $file_path );
		}
	}

	protected function wait_after_429( string $headers, int $max_wait = 180 ): int {
		$retry_after = null;

		// HTTP dates are always expressed in GMT, never in local time. (RFC 9110 5.6.7).
		$gmt_timezone = new \DateTimeZone( 'GMT' );

		// HTTP headers are case-insensitive according to RFC 7230.
		$headers = strtolower( $headers );

		foreach ( explode( "\r\n", $headers ) as $header ) {
			/**
			 * Retry-After header is specified by RFC 9110 10.2.3
			 *
			 * It can be formatted as http-date, or int (seconds).
			 *
			 * Retry-After: Fri, 31 Dec 1999 23:59:59 GMT
			 * Retry-After: 120
			 *
			 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-10.2.3
			 */
			if ( strpos( $header, 'retry-after:' ) !== false ) {
				$retry_after_header = trim( substr( $header, strpos( $header, ':' ) + 1 ) );

				// seconds.
				if ( is_numeric( $retry_after_header ) ) {
					$retry_after = intval( $retry_after_header );
				} else {
					// Parse as HTTP-date in GMT timezone.
					try {
						$retry_after = ( new \DateTime( $retry_after_header, $gmt_timezone ) )->getTimestamp() - ( new \DateTime( 'now', $gmt_timezone ) )->getTimestamp();
					} catch ( \Exception $e ) {
						$retry_after = null;
					}
					// http-date.
					$retry_after_time = strtotime( $retry_after_header );
					if ( $retry_after_time !== false ) {
						$retry_after = $retry_after_time - time();
					}
				}

				if ( ! defined( 'UNIT_TESTS' ) ) {
					App::make( Output::class )->writeln( sprintf( 'Got 429. Retrying after %d seconds...', $retry_after ) );
				}
			}
		}

		// If no retry-after is specified, do a back-off.
		if ( is_null( $retry_after ) ) {
			$retry_after = 5 * pow( 2, abs( $this->retry_429 - 5 ) );
		}

		// Ensure we wait at least 1 second.
		$retry_after = max( 1, $retry_after );

		// And no longer than 180 seconds.
		$retry_after = min( $max_wait, $retry_after );

		$retry_after += rand( 0, 5 ); // Add a random number of seconds to avoid all clients retrying at the same time.

		return $retry_after;
	}

	/**
	 * @return array<mixed> The array version of this class.
	 */
	public function to_array(): array {
		return [
			'url'                   => $this->url,
			'method'                => $this->method,
			'post_body'             => $this->post_body,
			'curl_opts'             => $this->curl_opts,
			'expected_status_codes' => $this->expected_status_codes,
		];
	}

	private function handle_file_mock(): string {
		$mock_dir = getenv( 'QIT_MOCK_DIR' );

		// Record the request
		$this->record_request( $mock_dir );

		// Return mock response
		$url_hash  = sha1( $this->url );
		$mock_file = $mock_dir . '/' . $url_hash . '.json';
		if ( ! file_exists( $mock_file ) ) {
			throw new \LogicException( 'No mock for: ' . $this->url );
		}

		return file_get_contents( $mock_file );
	}

	/**
	 * Check if a URL matches a fixture file for wporg API responses.
	 *
	 * @param string $fixture_dir The fixture directory path.
	 * @param string $url         The request URL.
	 *
	 * @return string|null The fixture file contents, or null if URL doesn't match a fixture pattern.
	 * @throws \RuntimeException If URL matches a fixture pattern but no fixture file exists.
	 */
	private static function resolve_api_fixture( string $fixture_dir, string $url ): ?string {
		// WordPress.org plugin API.
		if ( preg_match( '#api\.WordPress\.org/plugins/info.+request.slug.=([a-z0-9_-]+)#i', $url, $m ) ) {
			$file = $fixture_dir . '/api/plugins/' . $m[1] . '.json';
			if ( file_exists( $file ) ) {
				return file_get_contents( $file );
			}
			throw new \RuntimeException( "No API fixture for plugin '{$m[1]}'. Add: fixtures/api/plugins/{$m[1]}.json" );
		}

		// WordPress.org theme API.
		if ( preg_match( '#api\.WordPress\.org/themes/info.+request.slug.=([a-z0-9_-]+)#i', $url, $m ) ) {
			$file = $fixture_dir . '/api/themes/' . $m[1] . '.json';
			if ( file_exists( $file ) ) {
				return file_get_contents( $file );
			}
			throw new \RuntimeException( "No API fixture for theme '{$m[1]}'. Add: fixtures/api/themes/{$m[1]}.json" );
		}

		// Not a wporg URL — let it through.
		return null;
	}

	private function record_request( string $mock_dir ): void {
		$entry = [
			'url'  => $this->url,
			'hash' => sha1( $this->url ),
			'body' => $this->to_array(),
		];

		// ➊ keep last‑request semantics
		file_put_contents( $mock_dir . '/last_request.json', json_encode( $entry, JSON_PRETTY_PRINT ) );

		// ➋ append to the chronological log
		$log_file = $mock_dir . '/_requests.json';
		$log      = is_file( $log_file ) ? json_decode( file_get_contents( $log_file ), true ) : [];
		$log[]    = $entry;
		file_put_contents( $log_file, json_encode( $log, JSON_PRETTY_PRINT ) );
	}


	/**
	 * Log an outbound HTTP request when QIT_REQUEST_LOG is set.
	 * Used to audit network activity during tests.
	 *
	 * @param string $url    The request URL.
	 * @param string $type   'request' or 'download'.
	 * @param string $method HTTP method (GET, POST, etc.).
	 */
	private static function log_request( string $url, string $type, string $method = '' ): void {
		$log_dir = Config::get_request_log();
		if ( ! $log_dir ) {
			return;
		}

		$log_file = $log_dir . '/_requests.json';
		$log      = is_file( $log_file ) ? json_decode( file_get_contents( $log_file ), true ) : [];
		$log[]    = [
			'url'    => $url,
			'type'   => $type,
			'method' => $method,
		];
		file_put_contents( $log_file, json_encode( $log, JSON_PRETTY_PRINT ) );
	}

	/**
	 * Apply rate limiting to prevent hitting API rate limits.
	 * Ensures at least 1 second delay between requests to the same domain.
	 *
	 * @param string $url The URL to rate limit.
	 * @return void
	 */
	protected static function apply_rate_limit( string $url ): void {
		// Local static variables to keep state between calls
		static $last_request_time   = [];
		static $rate_limit_delay_us = 1000000; // 1 second in microseconds

		// Skip rate limiting for unit tests and local/mock environments
		if ( defined( 'UNIT_TESTS' ) || getenv( 'QIT_MOCK_DIR' ) ) {
			return;
		}

		// Extract domain from URL
		$parsed_url = parse_url( $url );
		if ( ! isset( $parsed_url['host'] ) ) {
			return;
		}

		$domain = $parsed_url['host'];

		// Check if we've made a request to this domain recently
		if ( isset( $last_request_time[ $domain ] ) ) {
			$time_since_last    = microtime( true ) - $last_request_time[ $domain ];
			$time_since_last_us = (int) ( $time_since_last * 1000000 );

			// If less than the delay threshold, sleep for the remaining time
			if ( $time_since_last_us < $rate_limit_delay_us ) {
				$sleep_time = $rate_limit_delay_us - $time_since_last_us;

				// Log the rate limiting if verbose output is enabled
				try {
					$output = App::make( Output::class );
					if ( $output->isVerbose() ) {
						$output->writeln(
							sprintf( 'Rate limiting: Waiting %dms before request to %s',
								(int) ( $sleep_time / 1000 ),
								$domain
							)
						);
					}
				} catch ( \Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
					// Output might not be available in all contexts - silently continue
				}

				usleep( $sleep_time );
			}
		}

		// Update the last request time for this domain
		$last_request_time[ $domain ] = microtime( true );
	}
}
