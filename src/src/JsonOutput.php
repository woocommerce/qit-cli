<?php

namespace QIT_CLI;

use QIT_CLI\IO\Output;

/**
 * A helper class that:
 * 1) Registers error/exception handlers so all fatal errors are output as JSON
 * 2) Registers a stream filter that only allows valid JSON if `--json` is passed
 */
class JsonOutput {
	/**
	 * Call this once in your bootstrap to set up JSON error handlers and filters.
	 */
	public static function init( $container ): void {
		set_exception_handler( [ self::class, 'handle_exception' ] );
		set_error_handler( [ self::class, 'handle_error' ] );
		register_shutdown_function( [ self::class, 'handle_shutdown' ] );

		// Register our 'qit_json' filter.
		if ( ! stream_filter_register( 'qit_json', QIT_JSON_Filter::class ) ) {
			exit( 151 );
		}

		$container->setVar( 'QIT_JSON_MODE', true );

		/** @var \QIT_CLI\IO\Output $output */
		$output = App::make( Output::class );

		if ( ! stream_filter_append( $output->getStream(), 'qit_json' ) ) {
			exit( 152 );
		}
	}

	/**
	 * Exception handler: convert the exception to JSON and exit(1).
	 */
	public static function handle_exception( \Throwable $throwable ): void {
		self::output_throwable_as_json( $throwable );
	}

	/**
	 * Error handler: convert a PHP error into an ErrorException, then JSON.
	 */
	public static function handle_error( $severity, $message, $file, $line ): bool {
		if ( ! ( error_reporting() & $severity ) ) {
			// This error is suppressed by current error_reporting settings
			return false;
		}
		self::output_throwable_as_json( new \ErrorException( $message, 0, $severity, $file, $line ) );

		return true; // We handled it
	}

	/**
	 * Shutdown handler: catch any fatal errors (e.g., E_ERROR, E_PARSE).
	 */
	public static function handle_shutdown(): void {
		$error = error_get_last();
		if ( $error && in_array( $error['type'], [ E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR ], true ) ) {
			self::output_throwable_as_json(
				new \ErrorException(
					$error['message'],
					0,
					$error['type'],
					$error['file'],
					$error['line']
				)
			);
		}
	}

	/**
	 * Output a Throwable in JSON format and exit(1).
	 */
	public static function output_throwable_as_json( \Throwable $throwable ): void {
		$error_data = [
			'error' => [
				'type'    => get_class( $throwable ),
				'message' => $throwable->getMessage(),
				'file'    => $throwable->getFile(),
				'line'    => $throwable->getLine(),
				'trace'   => $throwable->getTraceAsString(),
			],
		];

		// Echo valid JSON
		echo json_encode( $error_data ), "\n";
		exit( 1 );
	}
}

/**
 * Stream filter that only passes valid JSON, discarding or logging anything else.
 */
class QIT_JSON_Filter extends \php_user_filter {
	public function filter( $in, $out, &$consumed, $closing ): int {
		while ( $bucket = stream_bucket_make_writeable( $in ) ) {
			if ( null !== json_decode( $bucket->data ) ) {
				// Data is valid JSON, pass it on
				$consumed += $bucket->datalen;
				stream_bucket_append( $out, $bucket );
			} else {
				// Not valid JSON, optionally log to file
				if ( ! empty( getenv( 'QIT_NON_JSON_OUTPUT' ) ) ) {
					file_put_contents( getenv( 'QIT_NON_JSON_OUTPUT' ), $bucket->data, FILE_APPEND );
				}
			}
		}

		return PSFS_PASS_ON;
	}
}
