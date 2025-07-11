<?php
/* bootstrap-node.php -------------------------------------------------- */

require_once __DIR__ . '/helpers.php';                    // log_*()

// ───────────────────────────────────────────────────────────────────────
// 1. RUNTIME – validate env‑vars, set INI, register autoloader
// ───────────────────────────────────────────────────────────────────────
function qit_runtime_init(): void {
    static $once = false; if ($once) return; $once = true;

    foreach (['QIT_NODE_TOKEN','QIT_NODE_DIR','QIT_AI_DIR','QIT_PROVIDER','QIT_PROVIDER_CFG']
             as $var) {
        if (getenv($var) === false || getenv($var) === '') {
            throw new RuntimeException("Env‑var $var is missing");
        }
    }

    $nodeDir = rtrim(getenv('QIT_NODE_DIR'), '/').'/';
    ini_set('log_errors', 1);
    ini_set('error_log', $nodeDir.'php-errors.log');
    ini_set('display_errors', 0);

    // PSR‑4 autoloader for QIT_AI_Webserver\*
    spl_autoload_register(function ($class) {
        $prefix = 'QIT_AI_Webserver\\';
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) return;
        $path = __DIR__.'/'.str_replace('\\','/', substr($class, strlen($prefix))).'.php';
        if (is_file($path)) require $path;
    });
}

/* -------------------------------------------------------------------- *
 * 2. HTTP – parse request & (optionally) enforce token / rate limit    *
 * -------------------------------------------------------------------- */
function qit_http_request(bool $checkToken = true): array {
    qit_runtime_init();

    $method  = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $rawUri  = $_SERVER['REQUEST_URI']    ?? '/';
    $uri     = parse_url($rawUri,  PHP_URL_PATH) ?? '/';
    $headers = getallheaders();
    $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';

    // ---- token guard --------------------------------------------------
    if ($checkToken) {
        if (($headers['X-Node-Token'] ?? '') !== getenv('QIT_NODE_TOKEN')) {
            http_response_code(403);
            echo json_encode(['error'=>'Unauthorized']); exit;
        }
    } else {
        // For worker router, check that it's being requested from localhost (by the poller)
        if ($remoteAddr !== '127.0.0.1' && $remoteAddr !== 'localhost') {
            http_response_code(403);
            echo json_encode(['error'=>'Worker can only be accessed from localhost']); exit;
        }
    }

    // ---- rate limit ---------------------------------------------------
    $key  = strtolower($method).'_'.trim($uri,'/').'_'.md5($headers['X-Node-Token'] ?? '');
    $file = getenv('QIT_NODE_DIR')."/rate-limit/$key";
    if (!is_dir(dirname($file))) mkdir(dirname($file), 0700, true);
    if (file_exists($file) && microtime(true)-filemtime($file) < 0.005) {
        http_response_code(429); echo json_encode(['error'=>'Rate limited']); exit;
    }
    touch($file);

    // ---- body ---------------------------------------------------------
    $body  = file_get_contents('php://input') ?: '';
    $input = $body === '' ? [] : json_decode($body, true);
    if ($body !== '' && json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error'=>'Malformed JSON: '.json_last_error_msg()]); exit;
    }

    return compact('method','uri','headers','input');
}

/* -------------------------------------------------------------------- *
 * 3. LLM – boot LLPhant once per request / CLI invocation              *
 * -------------------------------------------------------------------- */
function qit_llm_boot(array $overrides = []): void {
    qit_runtime_init();

    $provider = getenv('QIT_PROVIDER');
    $cfg      = json_decode(getenv('QIT_PROVIDER_CFG'), true) + $overrides;

    \QIT_AI_Webserver\Lib\LLPhantBootstrap::boot($provider, $cfg);
}
