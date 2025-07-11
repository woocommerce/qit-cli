<?php
namespace QIT_AI_Webserver\Lib;

class HeartbeatSender {
    private string $nodeId;
    private string $nodeToken;
    private string $heartbeatUrl;
    private int    $interval;
    private int    $lastSent = 0;

    public function __construct( string $nodeId, string $nodeToken, string $heartbeatUrl, int $interval = 60 ) {
        $this->nodeId       = $nodeId;
        $this->nodeToken    = $nodeToken;
        $this->heartbeatUrl = rtrim( $heartbeatUrl, '/' );
        $this->interval     = $interval;
    }

    /** Call on every poll‑loop iteration */
    public function maybeSend(): void {
        if ( time() - $this->lastSent < $this->interval ) {
            return;
        }
        $this->lastSent = time();

        $payload = json_encode( [
            'node_id'    => $this->nodeId,
            'node_token' => $this->nodeToken,
        ] );

		register_shutdown_function( function () use ( $payload ) {
			$error = error_get_last();
		});

		try {
			// Log outbound heartbeat request to the manager
			log_info('Outbound heartbeat request to manager', [
				'endpoint' => $this->heartbeatUrl,
				'method' => 'POST',
				'node_id' => $this->nodeId
			]);
		} catch (\Exception $e) {
			throw $e;
		}


        $ch = curl_init( $this->heartbeatUrl );
        curl_setopt_array( $ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [ 'Content-Type: application/json' ],
            CURLOPT_TIMEOUT        => 5,
        ] );

        $response = curl_exec( $ch );
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close( $ch );

        // Log the response from the manager
        log_info('Heartbeat response from manager', [
            'endpoint' => $this->heartbeatUrl,
            'status_code' => $status_code,
            'response_size' => strlen($response ?? ''),
            'error' => $error ?: null
        ]);
    }
}
