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

        $data = [
            'node_token' => $this->nodeToken,
            'busy' => file_exists(getenv('QIT_NODE_DIR') . '/busy.lock') ? 1 : 0,
            'last_error' => null, // Will be populated if there's an error file
            'system_info' => [
                'memory_usage' => memory_get_usage(true),
                'cpu_load' => sys_getloadavg()[0] ?? null,
            ]
        ];

        $request = OutboundRequest::heartbeat($this->heartbeatUrl, $data, 'node-heartbeat');
        $request->send(); // Fire-and-forget, don't check result
    }
}
