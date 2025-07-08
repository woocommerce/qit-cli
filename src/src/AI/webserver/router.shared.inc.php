<?php
// ---------- Common bootstrap for listener & worker -------------

header('Content-Type: application/json');
ini_set('log_errors', 1);
ini_set('error_log', '{{LOG_FILE}}');
ini_set('display_errors', 0);

define('QIT_AI_DIR', '{{AI_DIR}}');
define('QIT_DB_PATH', QIT_AI_DIR . 'node.db');

// =================================================================
// the rest is literally the same code you already had in router.php
// * token validation
// * rate‑limit
// * helper log_xxx() functions
// * LLPhant bootstrap
// * $method / $uri / $input variables
// =================================================================
include __DIR__ . '/router.bootstrap.core.php';   // <-- extracted from your big router.php