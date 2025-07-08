<?php
namespace QIT_AI_Webserver;

final class WorkerPoller
{
    /** Run an infinite poll‑loop until SIGTERM */
    public static function run(string $workerUrl, string $nodeToken, int $sleep = 1): void
    {
	    // ── make signal handling optional ───────────────────────────────
	    if (function_exists('pcntl_async_signals')) {
		    pcntl_async_signals(true);
		    pcntl_signal(SIGTERM, static fn () => exit(0));
	    }

        while (true) {
            $ch = curl_init(rtrim($workerUrl, '/') . '/run-one');
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    "X-Node-Token: $nodeToken",
                ],
                CURLOPT_POSTFIELDS     => '{}',
                CURLOPT_TIMEOUT        => 30,
            ]);
            curl_exec($ch);   // Ignore body – the worker logs everything
            curl_close($ch);

            sleep($sleep);
        }
    }
}