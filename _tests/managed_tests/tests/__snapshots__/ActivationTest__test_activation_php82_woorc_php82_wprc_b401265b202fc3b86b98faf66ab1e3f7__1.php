<?php return '[
    [
        {
            "test_run_id": 123456,
            "run_id": 123456,
            "test_type": "activation",
            "test_type_display": "Activation",
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
            "event": "local_or_ci_run_normalized",
            "optional_features": {
                "hpos": false,
                "new_product_editor": false
            },
            "test_results_manager_url": "https:\\/\\/test-results-manager.com",
            "test_results_manager_expiration": 1234567890,
            "test_summary": "",
            "version": "",
            "update_complete": true,
            "ai_suggestion_status": "none",
            "malware_whitelist_paths": [],
            "workflow_id": "",
            "runner": "",
            "test_media": [],
            "test_result_json_extracted": "{EXTRACTED}",
            "debug_log_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "numFailedTestSuites": 1,
                "numPassedTestSuites": 1,
                "numPendingTestSuites": 0,
                "numTotalTestSuites": 2,
                "numFailedTests": 1,
                "numPassedTests": 8,
                "numPendingTests": 3,
                "numTotalTests": 12,
                "testResults": [
                    {
                        "file": "scripts\\/bash.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "scripts\\/bash.js": [
                                {
                                    "title": "Bash Script",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "woocommerce\\/activation\\/activation.spec.js",
                        "status": "failed",
                        "has_pending": true,
                        "tests": {
                            "woocommerce\\/activation\\/activation.spec.js": [
                                {
                                    "title": "Activate Plugins",
                                    "status": "passed"
                                },
                                {
                                    "title": "Visit wp-admin pages added by the plugin",
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
                                    "status": "pending"
                                },
                                {
                                    "title": "Deactivate Plugin",
                                    "status": "pending"
                                },
                                {
                                    "title": "Activate Other Theme",
                                    "status": "pending"
                                }
                            ]
                        }
                    }
                ],
                "summary": "Test Suites: 0 skipped, 1 failed, 1 passed, 2 total | Tests: 3 skipped, 1 failed, 8 passed, 12 total."
            }
        },
        {
            "debug_log": {
                "qm_logs": [
                    {
                        "message": "Creation of dynamic property SUT\\\\BarUser::$bar is deprecated",
                        "type": "deprecated",
                        "file_line": "wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php:28",
                        "count": 100
                    },
                    {
                        "message": "Function utf8_encode() is deprecated",
                        "type": "deprecated",
                        "file_line": "wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php:37",
                        "count": 2
                    }
                ],
                "debug_log": [
                    {
                        "count": "100",
                        "message": "PHP Deprecated: Creation of dynamic property SUT\\\\BarUser::$bar is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php on line 28"
                    },
                    {
                        "count": "2",
                        "message": "PHP Deprecated: Function utf8_encode() is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php on line 37"
                    }
                ]
            }
        }
    ]
]';
