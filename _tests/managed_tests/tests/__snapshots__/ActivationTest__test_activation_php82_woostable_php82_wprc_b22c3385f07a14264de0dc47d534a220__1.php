<?php return '[
    [
        {
            "test_run_id": 123456,
            "run_id": 123456,
            "test_type": "e2e",
            "test_type_display": "E2E",
            "wordpress_version": "6.0.0-normalized",
            "woocommerce_version": "6.0.0-normalized",
            "php_version": "8.2",
            "max_php_version": "",
            "min_php_version": "",
            "additional_woo_plugins": [],
            "additional_wp_plugins": [
                "woocommerce"
            ],
            "test_log": "",
            "status": "warning",
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
                "numFailedTestSuites": 0,
                "numPassedTestSuites": 1,
                "numPendingTestSuites": 0,
                "numTotalTestSuites": 1,
                "numFailedTests": 0,
                "numPassedTests": 10,
                "numPendingTests": 0,
                "numTotalTests": 10,
                "testResults": [
                    {
                        "file": "woocommerce\\/activation\\/activation.spec.js",
                        "status": "passed",
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
                                    "status": "passed"
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
                "summary": "Test Suites: 0 skipped, 0 failed, 1 passed, 1 total | Tests: 0 skipped, 0 failed, 10 passed, 10 total."
            }
        },
        {
            "debug_log": {
                "qm_logs": [
                    {
                        "message": "This is test notice!",
                        "type": "notice",
                        "file_line": "wp-content\\/mu-plugins\\/qit-mu-woocommerce.php:105",
                        "count": 100
                    },
                    {
                        "message": "Creation of dynamic property SUT\\\\BarUser::$bar is deprecated",
                        "type": "deprecated",
                        "file_line": "wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php:28",
                        "count": 80
                    }
                ],
                "debug_log": [
                    {
                        "count": "110",
                        "message": "PHP Deprecated: Creation of dynamic property SUT\\\\BarUser::$bar is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php on line 28"
                    },
                    {
                        "count": "13",
                        "message": "PHP Deprecated: Function utf8_encode() is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php on line 37"
                    }
                ]
            }
        }
    ]
]';
