<?php
/**
 * Network connectivity check script that appends results to a file.
 * This allows multiple packages to write their results without overwriting.
 */

$package_name = getenv('QIT_TEST_PACKAGE_NAME') ?: 'unknown';
$timestamp = microtime(true);

$response = wp_remote_get('https://www.google.com', array('timeout' => 5));
$is_online = !is_wp_error($response);
$status = $is_online ? 'ONLINE' : 'OFFLINE';

$result = json_encode([
    'package' => $package_name,
    'status' => $status,
    'timestamp' => $timestamp
]) . "\n";

// Append to file so multiple packages can write
file_put_contents('/tmp/network-status.txt', $result, FILE_APPEND | LOCK_EX);

// Also log for debugging
error_log("[NETWORK-CHECK] Package: $package_name, Status: $status");