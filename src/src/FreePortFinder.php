<?php

namespace QIT_CLI;

class FreePortFinder {
	/**
	 * Finds a free port by letting the OS assign one.
	 *
	 * @return int The free port number.
	 * @throws \Exception If unable to create or retrieve socket information.
	 */
	public function find_free_port() {
		if ( ! function_exists( 'socket_create' ) ) {
			throw new \Exception( 'The socket extension is required to find a free port.' );
		}

		// Create a listening socket on port 0, which tells the OS to assign an available port
		$sock = @socket_create( AF_INET, SOCK_STREAM, SOL_TCP );
		if ( $sock === false ) {
			throw new \Exception( "Failed to create socket: " . socket_strerror( socket_last_error() ) );
		}

		// Bind to port 0 to let the OS choose a free port
		if ( @socket_bind( $sock, '127.0.0.1', 0 ) === false ) {
			$error = socket_strerror( socket_last_error( $sock ) );
			socket_close( $sock );
			throw new \Exception( "Failed to bind socket: " . $error );
		}

		// Retrieve the assigned port number
		if ( @socket_getsockname( $sock, $addr, $port ) === false ) {
			$error = socket_strerror( socket_last_error( $sock ) );
			socket_close( $sock );
			throw new \Exception( "Failed to get socket name: " . $error );
		}

		// Close the socket as it's no longer needed
		socket_close( $sock );

		return $port;
	}
}
