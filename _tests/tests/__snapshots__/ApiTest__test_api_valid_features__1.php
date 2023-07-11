<?php return '[
    [
        {
            "run_id": 123456,
            "test_type": "api",
            "wordpress_version": "6.0.0-normalized",
            "woocommerce_version": "6.0.0-normalized",
            "php_version": "7.4",
            "additional_woo_plugins": [],
            "additional_wp_plugins": [],
            "test_log": "",
            "status": "failed",
            "test_result_aws_url": "https:\\/\\/test-results-aws.com",
            "test_result_aws_expiration": 1234567890,
            "is_development": true,
            "send_notifications": false,
            "woo_extension": {
                "id": 18619,
                "host": "wccom",
                "name": "Google Product Feed"
            },
            "client": "qit_cli",
            "event": "cli_development_extension_test",
            "optional_features": {
                "hpos": true,
                "new_product_editor": true
            },
            "test_results_manager_url": "https:\\/\\/test-results-manager.com",
            "test_results_manager_expiration": 1234567890,
            "test_summary": "Test failed before it was executed.",
            "version": "Undefined",
            "test_result_json_extracted": "{EXTRACTED}",
            "debug_log_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "numFailedTestSuites": 0,
                "numPassedTestSuites": 0,
                "numPendingTestSuites": 0,
                "numTotalTestSuites": 0,
                "numFailedTests": 0,
                "numPassedTests": 0,
                "numPendingTests": 0,
                "numTotalTests": 0,
                "testResults": [],
                "summary": "Test failed before it was executed."
            }
        },
        {
            "debug_log": [
                {
                    "count": "5",
                    "message": "PHP Notice: New Product Editor is enabled as expected. in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php on line 12"
                }
            ]
        }
    ]
]';
