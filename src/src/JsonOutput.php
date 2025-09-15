<?php

namespace QIT_CLI;

use lucatume\DI52\Container;
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
	public static function init( Container $container ): void {
		set_exception_handler( [ self::class, 'handle_exception' ] );
		set_error_handler( [ self::class, 'handle_error' ] ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler
		register_shutdown_function( [ self::class, 'handle_shutdown' ] );

		$container->setVar( 'QIT_JSON_MODE', true );

		/* @phan-suppress-next-line PhanUndeclaredMethod */
		if ( ! stream_filter_append( App::make( Output::class )->getStream(), 'qit_json' ) ) { // @phpstan-ignore-line method.notFound
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
	public static function handle_error( int $severity, string $message, string $file, int $line ): bool {
		if ( ! ( error_reporting() & $severity ) ) { // phpcs:ignore
			// This error is suppressed by current error_reporting settings.
			return false;
		}
		self::output_throwable_as_json( new \ErrorException( $message, 0, $severity, $file, $line ) );

		return true; // We handled it.
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
		if ( ! App::make( Output::class )->isVerbose() ) {
			// If not verbose, just output the message.
			echo json_encode( [ 'error' => $throwable->getMessage() ], JSON_UNESCAPED_SLASHES ), "\n";
			exit( 1 );
		}

		$error_data = [
			'error' => [
				'type'    => get_class( $throwable ),
				'message' => $throwable->getMessage(),
				'file'    => $throwable->getFile(),
				'line'    => $throwable->getLine(),
				'trace'   => $throwable->getTraceAsString(),
			],
		];

		// Echo valid JSON.
		echo json_encode( $error_data, JSON_UNESCAPED_SLASHES ), "\n";
		exit( 1 );
	}
}
