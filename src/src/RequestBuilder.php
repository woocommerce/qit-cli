<?php

namespace QIT_CLI;

use QIT_CLI\Exceptions\DoingAutocompleteException;
use QIT_CLI\Exceptions\NetworkErrorException;
use QIT_CLI\IO\Input;
use QIT_CLI\IO\Output;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ConfirmationQuestion;

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

	/** @var array<int> */
	protected $expected_status_codes = [ 200 ];

	/** @var int */
	protected $retry = 0;

	/** @var int */
	protected $retry_429 = 5;

	/** @var int */
	protected $timeout_in_seconds = 15;

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
	 * @param array<scalar> $post_body Optionally set curl's post_body.
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

	public function request(): string {
		retry_request: // phpcs:ignore Generic.PHP.DiscourageGoto.Found
		if ( defined( 'UNIT_TESTS' ) ) {
			$mocked = App::getVar( 'mock_' . $this->url );
			if ( is_null( $mocked ) ) {
				throw new \LogicException( 'No mock found for ' . $this->url );
			}

			App::setVar( 'mocked_request', $this->to_array() );

			return $mocked;
		}

		if ( empty( $this->url ) ) {
			throw new \LogicException( 'URL cannot be empty.' );
		}

		// Early bail: Do not make remote requests when doing completion.
		if ( App::getVar( 'doing_autocompletion' ) ) {
			throw new DoingAutocompleteException();
		}

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

		if ( App::make( Output::class )->isVeryVerbose() ) {
			$curl_parameters[ CURLOPT_VERBOSE ] = true;
		}

		$this->post_body['client'] = 'qit_cli';

		$proxied = false;

		if ( $this->onboarding ) {
			// When onboarding, proxy the request to test.
			$proxied                              = true;
			$curl_parameters[ CURLOPT_PROXY ]     = Config::get_proxy_url();
			$curl_parameters[ CURLOPT_PROXYTYPE ] = CURLPROXY_SOCKS5;
		} else {
			if ( ! is_null( App::make( Auth::class )->get_manager_secret() ) ) {
				$this->post_body['manager_secret'] = App::make( Auth::class )->get_manager_secret();
				// Connections using the MANAGER_SECRET that are not local must go through Automattic Proxy.
				if ( strpos( $this->url, '.test' ) === false && strpos( $this->url, 'stagingcompatibilitydashboard' ) === false ) {
					$proxied                              = true;
					$curl_parameters[ CURLOPT_PROXY ]     = Config::get_proxy_url();
					$curl_parameters[ CURLOPT_PROXYTYPE ] = CURLPROXY_SOCKS5;
				}
			} elseif ( ! is_null( App::make( Auth::class )->get_partner_auth() ) ) {
				$this->post_body['partner_app_pass'] = App::make( Auth::class )->get_partner_auth();
			}
		}

		switch ( $this->method ) {
			case 'GET':
				// no-op.
				break;
			case 'POST':
				$json_data                             = json_encode( $this->post_body );
				$curl_parameters[ CURLOPT_POST ]       = true;
				$curl_parameters[ CURLOPT_POSTFIELDS ] = $json_data;
				$curl_parameters[ CURLOPT_HTTPHEADER ] = [
					'Content-Type: application/json',
					'Content-Length: ' . strlen( $json_data ),
				];
				break;
			default:
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

			App::make( Output::class )->writeln( sprintf( '[QIT DEBUG] Running external request: %s', json_encode( $request_in_logs, JSON_PRETTY_PRINT ) ) );
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
			if ( $proxied && $body === false ) {
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
					$this->retry_429 --;
					App::make( Output::class )->writeln( '<comment>Request failed... Retrying (429 Too many Requests)</comment>' );

					sleep( $this->wait_after_429( $headers ) );
					goto retry_request; // phpcs:ignore Generic.PHP.DiscourageGoto.Found
				}
			} else {
				if ( $this->retry > 0 ) {
					$this->retry --;
					App::make( Output::class )->writeln( sprintf( '<comment>Request failed... Retrying (HTTP Status Code %s)</comment>', $response_status_code ) );

					$this->maybe_set_certificate_authority_file( $curl_parameters );

					// Between 1 and 5s.
					sleep( rand( 1, 5 ) );
					goto retry_request; // phpcs:ignore Generic.PHP.DiscourageGoto.Found
				}
			}

			throw new NetworkErrorException(
				sprintf(
					'Expected return status code(s): %s. Got return status code: %s. Error message: %s',
					implode( ', ', $this->expected_status_codes ),
					$response_status_code,
					$error_message
				),
				$response_status_code
			);
		}

		return $body;
	}

	/**
	 * @param array<int,scalar> $curl_parameters
	 *
	 * @return void
	 */
	protected function maybe_set_certificate_authority_file( array &$curl_parameters ) {
		$output = App::make( Output::class );
		// Early bail: We only do this for Windows.
		if ( ! is_windows() ) {
			if ( $output->isVerbose() ) {
				$output->writeln( 'Skipping certificate authority file check. Not running on Windows.' );
			}

			return;
		}
		if ( $output->isVerbose() ) {
			$output->writeln( 'Checking if we need to download the certificate authority file...' );
		}

		$cached_ca_filepath = App::make( Cache::class )->get( 'ca_filepath' );

		// Cache hit.
		if ( $cached_ca_filepath !== null ) {
			if ( $output->isVerbose() ) {
				$output->writeln( 'Using cached certificate authority file.' );
			}
			$curl_parameters[ CURLOPT_CAINFO ] = $cached_ca_filepath;

			return;
		}

		if ( $output->isVerbose() ) {
			$output->writeln( 'No cached certificate authority file found.' );
		}

		// Ask the user if he wants us to solve it for them.
		$input = App::make( Input::class );

		$helper   = App::make( QuestionHelper::class );
		$question = new ConfirmationQuestion( '', false );

		if ( getenv( 'QIT_WINDOWS_DOWNLOAD_CA' ) !== 'yes' && ! $input->isInteractive() || ! $helper->ask( $input, $output, $question ) ) {
			if ( $output->isVerbose() ) {
				$output->writeln( 'Skipping certificate authority file download.' );
			}

			var_dump( getenv( 'QIT_WINDOWS_DOWNLOAD_CA' ) );

			return;
		}

		if ( $output->isVerbose() ) {
			$output->writeln( 'Downloading certificate authority file...' );
		}

		// Download it to QIT Config Dir and save it in the cache.
		$local_ca_file = Config::get_qit_dir() . 'cacert.pem';

		if ( ! file_exists( $local_ca_file ) ) {
			$remote_ca_file_contents = @file_get_contents( 'http://curl.se/ca/cacert.pem' );

			if ( empty( $remote_ca_file_contents ) ) {
				$output->writeln( "<error>Could not download the certificate authority file. Please download it manually from http://curl.se/ca/cacert.pem and place it in $local_ca_file</error>" );

				return;
			}

			if ( ! file_put_contents( $local_ca_file, $remote_ca_file_contents ) ) {
				$output->writeln( "<error>Could not write the certificate authority file. Please download it manually from http://curl.se/ca/cacert.pem and place it in $local_ca_file<error>" );

				return;
			}
		}

		if ( $output->isVerbose() ) {
			$output->writeln( 'Certificate authority file downloaded and saved.' );
		}

		$year_in_seconds = 60 * 60 * 24 * 365;

		App::make( Cache::class )->set( 'ca_filepath', $local_ca_file, $year_in_seconds );

		$curl_parameters[ CURLOPT_CAINFO ] = $local_ca_file;
	}

	protected function wait_after_429( string $headers, int $max_wait = 60 ): int {
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

		// And no longer than 60 seconds.
		$retry_after = min( $max_wait, $retry_after );

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
}
