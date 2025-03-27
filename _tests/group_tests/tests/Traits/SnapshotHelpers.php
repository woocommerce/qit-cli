<?php

namespace QIT\SelfTests\GroupTests\Traits;

trait SnapshotHelpers {
    
    protected function normalize_untriggered_show_output( string $test_output_string ): string {
        // Replace hash values with a standard placeholder "12345"
        $test_output_string = preg_replace_callback(
            '/"hash"\s*:\s*"([^"]+)"|\'hash\'\s*:\s*\'([^\']+)\'/i',
            function($matches) {
                // Replace the hash with "12345" while keeping the original quote style
                if (!empty($matches[1])) {
                    return '"hash":"12345"';
                } else {
                    return "'hash':'12345'";
                }
            },
            $test_output_string
        );
        
        // Replace woo_id values with a standard placeholder "12345"
        $test_output_string = preg_replace_callback(
            '/"woo_id"\s*:\s*"?(\d+)"?|\'woo_id\'\s*:\s*\'?(\d+)\'?/i',
            function($matches) {
                // Replace the woo_id with "12345" while maintaining the original format
                return '"woo_id":12345';
            },
            $test_output_string
        );
        
        return $test_output_string;
    }

    protected function normalized_registered_group_output( string $test_output_string ): string {
        // Replace hash values with a standard placeholder
        $test_output_string = preg_replace_callback(
            '/"hash"\s*:\s*"([^"]+)"|\'hash\'\s*:\s*\'([^\']+)\'/i',
            function($matches) {
                // Replace the hash with "12345" while keeping the original quote style
                if (!empty($matches[1])) {
                    return '"hash":"12345"';
                } else {
                    return "'hash':'12345'";
                }
            },
            $test_output_string
        );
        
        // Replace woo_id values with a standard placeholder
        $test_output_string = preg_replace_callback(
            '/"woo_id"\s*:\s*"?(\d+)"?|\'woo_id\'\s*:\s*\'?(\d+)\'?/i',
            function($matches) {
                // Replace the woo_id with "12345" while maintaining the original format
                return '"woo_id":12345';
            },
            $test_output_string
        );
        
        // Normalize Group ID
        $test_output_string = preg_replace('/Group ID: \d+/', 'Group ID: 12345', $test_output_string);
        
        // Normalize Group Identifier
        $test_output_string = preg_replace('/Group Identifier: [a-f0-9-]+_\d+/', 'Group Identifier: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx_0000000000', $test_output_string);
        
        // Normalize Test Run ID
        $test_output_string = preg_replace('/Test Run ID: \d+/', 'Test Run ID: 12345', $test_output_string);
        
        // Normalize Woo ID in text format
        $test_output_string = preg_replace('/Woo ID: \d+/', 'Woo ID: 12345', $test_output_string);
        
        // Normalize Test Results Manager URL
        $test_output_string = preg_replace(
            '/https:\/\/[^\/]+\?qit_results=\d+\.[a-zA-Z0-9]+/', 
            'https://example.com?qit_results=12345.normalized_hash',
            $test_output_string
        );
        
        return $test_output_string;
    }

    protected function normalize_remote_group_run_output( string $test_output_string ): string {
        // Replace hash values with a standard placeholder
        $test_output_string = preg_replace_callback(
            '/"hash"\s*:\s*"([^"]+)"|\'hash\'\s*:\s*\'([^\']+)\'/i',
            function($matches) {
                // Replace the hash with "12345" while keeping the original quote style
                if (!empty($matches[1])) {
                    return '"hash":"12345"';
                } else {
                    return "'hash':'12345'";
                }
            },
            $test_output_string
        );
        
        // Replace woo_id values with a standard placeholder
        $test_output_string = preg_replace_callback(
            '/"woo_id"\s*:\s*"?(\d+)"?|\'woo_id\'\s*:\s*\'?(\d+)\'?/i',
            function($matches) {
                // Replace the woo_id with "12345" while maintaining the original format
                return '"woo_id":12345';
            },
            $test_output_string
        );
        
        // Normalize Group ID
        $test_output_string = preg_replace('/Group ID: \d+/', 'Group ID: 12345', $test_output_string);
        
        // Normalize Group Identifier
        $test_output_string = preg_replace('/Group Identifier: [a-f0-9-]+_\d+/', 'Group Identifier: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx_0000000000', $test_output_string);
        
        // Normalize Test Run ID
        $test_output_string = preg_replace('/Test Run ID: \d+/', 'Test Run ID: 12345', $test_output_string);
        
        // Normalize Woo ID in text format
        $test_output_string = preg_replace('/Woo ID: \d+/', 'Woo ID: 12345', $test_output_string);
        
        // Normalize Test Results Manager URL
        $test_output_string = preg_replace(
            '/https:\/\/[^\/]+\?qit_results=\d+\.[a-zA-Z0-9]+/', 
            'https://example.com?qit_results=12345.normalized_hash',
            $test_output_string
        );
        
        return $test_output_string;
    }

    protected function normalize_complete_group_run_output( string $test_output_string ): string {
        // Replace hash values with a standard placeholder
        $test_output_string = preg_replace_callback(
            '/"hash"\s*:\s*"([^"]+)"|\'hash\'\s*:\s*\'([^\']+)\'/i',
            function($matches) {
                // Replace the hash with "12345" while keeping the original quote style
                if (!empty($matches[1])) {
                    return '"hash":"12345"';
                } else {
                    return "'hash':'12345'";
                }
            },
            $test_output_string
        );
        
        // Replace woo_id values with a standard placeholder
        $test_output_string = preg_replace_callback(
            '/"woo_id"\s*:\s*"?(\d+)"?|\'woo_id\'\s*:\s*\'?(\d+)\'?/i',
            function($matches) {
                // Replace the woo_id with "12345" while maintaining the original format
                return '"woo_id":12345';
            },
            $test_output_string
        );
        
        // Normalize Group ID
        $test_output_string = preg_replace('/Group ID: \d+/', 'Group ID: 12345', $test_output_string);
        
        // Normalize Group Identifier
        $test_output_string = preg_replace('/Group Identifier: [a-f0-9-]+_\d+/', 'Group Identifier: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx_0000000000', $test_output_string);
        
        // Normalize Test Run ID
        $test_output_string = preg_replace('/Test Run ID: \d+/', 'Test Run ID: 12345', $test_output_string);
        
        // Normalize Woo ID in text format
        $test_output_string = preg_replace('/Woo ID: \d+/', 'Woo ID: 12345', $test_output_string);
        
        // Normalize Status values
        $test_output_string = preg_replace('/Status: [a-zA-Z0-9_-]+/', 'Status: normalized', $test_output_string);
        
        // Normalize Test Results Manager URL
        $test_output_string = preg_replace(
            '/https:\/\/[^\/\s]+\?qit_results=\d+\.[a-zA-Z0-9]+/', 
            'https://example.com?qit_results=12345.normalized_hash',
            $test_output_string
        );
        
        return $test_output_string;
    }
    
}