<?php
namespace QIT_AI_Webserver\Lib;

class HeartbeatSender {
	private string $node_id;
	private string $node_token;
	private string $heartbeat_url;
	private int $interval;
	private int $last_sent = 0;

	public function __construct( string $node_id, string $node_token, string $heartbeat_url, int $interval = 60 ) {
		$this->node_id       = $node_id;
		$this->node_token    = $node_token;
		$this->heartbeat_url = rtrim( $heartbeat_url, '/' );
		$this->interval      = $interval;
	}

	/** Call on every poll‑loop iteration */
	public function maybe_send(): void {
		if ( time() - $this->last_sent < $this->interval ) {
			return;
		}
		$this->last_sent = time();

		$data = [
			'node_token'  => $this->node_token,
			'busy'        => file_exists( getenv( 'QIT_NODE_DIR' ) . '/busy.lock' ) ? 1 : 0,
			'last_error'  => null, // Will be populated if there's an error file
			'system_info' => [
				'memory_usage' => memory_get_usage( true ),
				'cpu_load'     => sys_getloadavg()[0] ?? null,
			],
		];

		OutboundRequest::heartbeat( $this->heartbeat_url, $data ); // Fire-and-forget, don't check result
	}
}
