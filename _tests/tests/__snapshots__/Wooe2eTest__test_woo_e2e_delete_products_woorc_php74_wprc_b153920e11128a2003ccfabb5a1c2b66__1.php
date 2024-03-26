<?php return '[
    [
        {
            "test_run_id": 123456,
            "run_id": 123456,
            "test_type": "woo-e2e",
            "wordpress_version": "6.0.0-normalized",
            "woocommerce_version": "6.0.0-normalized",
            "php_version": "7.4",
            "max_php_version": "",
            "min_php_version": "",
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
                "name": "Google Product Feed",
                "type": "plugin"
            },
            "client": "qit_cli",
            "event": "cli_development_extension_test",
            "optional_features": {
                "hpos": false,
                "new_product_editor": false
            },
            "test_results_manager_url": "https:\\/\\/test-results-manager.com",
            "test_results_manager_expiration": 1234567890,
            "test_summary": "Test Suites: 0 skipped, 1 failed, 0 passed, 1 total | Tests: 0 skipped, 1 failed, 0 passed, 1 total.",
            "debug_log": "",
            "version": "Undefined",
            "update_complete": true,
            "ai_suggestion_status": "none",
            "malware_whitelist_paths": [],
            "test_result_json_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "numFailedTestSuites": 1,
                "numPassedTestSuites": 0,
                "numPendingTestSuites": 0,
                "numTotalTestSuites": 1,
                "numFailedTests": 1,
                "numPassedTests": 0,
                "numPendingTests": 0,
                "numTotalTests": 1,
                "testResults": [
                    {
                        "file": "merchant\\/products\\/block-editor\\/create-variable-product-block-editor.spec.js",
                        "status": "failed",
                        "has_pending": false,
                        "tests": {
                            "Variations tab": [],
                            "Variations tab > Create variable product": [
                                {
                                    "title": "can create a variation option and publish the product",
                                    "status": "failed"
                                }
                            ]
                        }
                    }
                ],
                "summary": "Test Suites: 0 skipped, 1 failed, 0 passed, 1 total | Tests: 0 skipped, 1 failed, 0 passed, 1 total."
            }
        }
    ]
]';
