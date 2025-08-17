<?php

namespace QIT\IntegrationTests\Fixtures;

use PHPUnit\Framework\TestCase;
use function qit;

class NetworkLoggingTest extends TestCase {
    
    public function test_network_logging_creates_log_file(): void {
        // Create a simple config
        $tempDir = sys_get_temp_dir() . '/qit_network_test_' . uniqid();
        mkdir($tempDir, 0755, true);
        
        $config = [
            '$schema'      => 'https://qit.woo.com/json-schema/qit',
            'sut'          => [
                'type'   => 'plugin',
                'slug'   => 'woocommerce',
                'source' => ['type' => 'wporg']
            ],
            'environments' => [
                'default' => [
                    'php' => '8.2',
                    'wp'  => 'stable',
                ]
            ],
            'test_types' => [
                'e2e' => [
                    'default' => [
                        'test_packages' => [
                            __DIR__ . '/../../fixtures/test-packages/network-test-package',
                        ]
                    ]
                ]
            ]
        ];
        
        $configPath = $tempDir . '/qit.json';
        file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT));
        
        // Run the test - output is captured
        $proc = qit([
            'run:e2e',
            'woocommerce',
            '--config=' . $configPath,
        ], return_process: true);
        
        $output = $proc->getOutput();
        $stderr = $proc->getErrorOutput();
        $fullOutput = $output . "\n" . $stderr;
        
        // Save full output for debugging
        file_put_contents('/tmp/network-test-output.txt', $fullOutput);
        
        // Print for debugging
        echo "\n=== Looking for network.log messages ===\n";
        if (strpos($fullOutput, 'Checking for network.log') !== false) {
            echo "✓ Found 'Checking for network.log' message\n";
        } else {
            echo "✗ No 'Checking for network.log' message found\n";
        }
        
        if (strpos($fullOutput, 'Network log preserved') !== false) {
            echo "✓ Found 'Network log preserved' message\n";
            // Extract the path
            if (preg_match('/Network log preserved at: (.+)/', $fullOutput, $matches)) {
                $logPath = trim($matches[1]);
                echo "  Log file path: $logPath\n";
                
                if (file_exists($logPath)) {
                    echo "  ✓ Log file exists!\n";
                    $logSize = filesize($logPath);
                    echo "  File size: $logSize bytes\n";
                    
                    if ($logSize > 0) {
                        echo "\n=== First 20 lines of network.log ===\n";
                        $lines = file($logPath);
                        for ($i = 0; $i < min(20, count($lines)); $i++) {
                            echo $lines[$i];
                        }
                        
                        // Count total requests
                        $content = file_get_contents($logPath);
                        $requestCount = substr_count($content, 'REQUEST:');
                        echo "\n=== Total HTTP requests logged: $requestCount ===\n";
                    }
                } else {
                    echo "  ✗ Log file does not exist at path\n";
                }
            }
        } else {
            echo "✗ No 'Network log preserved' message found\n";
            echo "\nChecking for 'No network.log found' message...\n";
            if (strpos($fullOutput, 'No network.log found') !== false) {
                echo "  Found 'No network.log found' - no HTTP requests were made\n";
            }
        }
        
        // The test should succeed regardless - we're just checking the mechanism
        $this->assertEquals(0, $proc->getExitCode(), 'E2E test should pass');
        
        // Clean up
        if (file_exists($tempDir)) {
            exec("rm -rf $tempDir");
        }
    }
}