<?php

require_once __DIR__ . '/bootstrap.php';

// Test the qit_precommand function directly
$config = [
    'environments' => [
        'default' => [
            'php' => '7.4',
            'wp'  => '5.9'
        ]
    ]
];
$configPath = tempnam( sys_get_temp_dir(), 'qit_test_' );
file_put_contents( $configPath, json_encode( $config ) );

echo "Testing qit_precommand with env:up...\n";

try {
    $output = qit_precommand( [
        'env:up',
        '--php', '8.1',
        '--wp', '6.0',
        '--config', $configPath,
        '--json'
    ] );
    
    echo "Raw output:\n";
    echo $output . "\n";
    
    $payload = json_decode( $output, true );
    if ( $payload === null ) {
        echo "Failed to decode JSON. JSON error: " . json_last_error_msg() . "\n";
    } else {
        echo "Decoded payload:\n";
        print_r( $payload );
    }
} catch ( Exception $e ) {
    echo "Exception: " . $e->getMessage() . "\n";
}

unlink( $configPath );
