<?php return '[
    [
        {
            "test_run_id": 123456,
            "run_id": 123456,
            "test_type": "e2e",
            "test_type_display": "E2E",
            "wordpress_version": "6.0.0-normalized",
            "woocommerce_version": "6.0.0-normalized",
            "php_version": "7.4",
            "max_php_version": "",
            "min_php_version": "",
            "additional_woo_plugins": [],
            "additional_wp_plugins": [
                "woocommerce"
            ],
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
            "event": "e2e_local_run",
            "optional_features": {
                "hpos": false,
                "new_product_editor": false
            },
            "test_results_manager_url": "https:\\/\\/test-results-manager.com",
            "test_results_manager_expiration": 1234567890,
            "test_summary": "",
            "version": "",
            "update_complete": false,
            "ai_suggestion_status": "none",
            "malware_whitelist_paths": [],
            "workflow_id": "",
            "runner": "",
            "test_result_json_extracted": "{EXTRACTED}",
            "debug_log_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "numFailedTestSuites": 1,
                "numPassedTestSuites": 0,
                "numPendingTestSuites": 0,
                "numTotalTestSuites": 1,
                "numFailedTests": 1,
                "numPassedTests": 9,
                "numPendingTests": 0,
                "numTotalTests": 10,
                "testResults": [
                    {
                        "file": "woocommerce\\/activation\\/activation.spec.js",
                        "status": "failed",
                        "has_pending": false,
                        "tests": {
                            "woocommerce\\/activation\\/activation.spec.js": [
                                {
                                    "title": "Activate Plugins",
                                    "status": "passed"
                                },
                                {
                                    "title": "Activate Theme",
                                    "status": "passed"
                                },
                                {
                                    "title": "Setup Local Pickup",
                                    "status": "passed"
                                },
                                {
                                    "title": "Set up Cash On Delivery Payment Method",
                                    "status": "passed"
                                },
                                {
                                    "title": "Create a Product",
                                    "status": "passed"
                                },
                                {
                                    "title": "Create a Simple Order",
                                    "status": "passed"
                                },
                                {
                                    "title": "Add Product Cart",
                                    "status": "failed"
                                },
                                {
                                    "title": "Can Place Order",
                                    "status": "passed"
                                },
                                {
                                    "title": "Deactivate Plugin",
                                    "status": "passed"
                                },
                                {
                                    "title": "Activate Other Theme",
                                    "status": "passed"
                                }
                            ]
                        }
                    }
                ],
                "summary": "Test Suites: 0 skipped, 1 failed, 0 passed, 1 total | Tests: 0 skipped, 1 failed, 9 passed, 10 total."
            }
        },
        {
            "debug_log": [
                {
                    "message": "This is test notice!",
                    "type": "notice",
                    "file_line": "wp-content\\/mu-plugins\\/qit-mu-woocommerce.php:105",
                    "count": 95
                },
                {
                    "message": "Notice on all requests",
                    "type": "notice",
                    "file_line": "wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php:16",
                    "count": 80
                },
                {
                    "message": " Uncaught Error: Call to undefined function call_to_undefined_function()",
                    "type": "PHP Fatal",
                    "file_line": "wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php:9",
                    "count": 2
                }
            ]
        }
    ]
]';
