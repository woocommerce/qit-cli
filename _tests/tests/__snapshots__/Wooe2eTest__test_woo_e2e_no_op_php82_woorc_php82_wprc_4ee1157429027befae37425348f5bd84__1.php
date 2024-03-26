<?php return '[
    [
        {
            "test_run_id": 123456,
            "run_id": 123456,
            "test_type": "woo-e2e",
            "wordpress_version": "6.0.0-normalized",
            "woocommerce_version": "6.0.0-normalized",
            "php_version": "8.2",
            "max_php_version": "",
            "min_php_version": "",
            "additional_woo_plugins": [],
            "additional_wp_plugins": [],
            "test_log": "",
            "status": "success",
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
            "test_summary": "Test Suites: 0 skipped, 0 failed, 1 passed, 1 total | Tests: 0 skipped, 0 failed, 1 passed, 1 total.",
            "version": "Undefined",
            "update_complete": true,
            "ai_suggestion_status": "none",
            "malware_whitelist_paths": [],
            "test_result_json_extracted": "{EXTRACTED}",
            "debug_log_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "numFailedTestSuites": 0,
                "numPassedTestSuites": 1,
                "numPendingTestSuites": 0,
                "numTotalTestSuites": 1,
                "numFailedTests": 0,
                "numPassedTests": 1,
                "numPendingTests": 0,
                "numTotalTests": 1,
                "testResults": [
                    {
                        "file": "merchant\\/products\\/block-editor\\/create-variable-product-block-editor.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Variations tab": [],
                            "Variations tab > Create variable product": [
                                {
                                    "title": "can create a variation option and publish the product",
                                    "status": "passed"
                                }
                            ]
                        }
                    }
                ],
                "summary": "Test Suites: 0 skipped, 0 failed, 1 passed, 1 total | Tests: 0 skipped, 0 failed, 1 passed, 1 total."
            }
        },
        {
            "debug_log": []
        }
    ]
]';
