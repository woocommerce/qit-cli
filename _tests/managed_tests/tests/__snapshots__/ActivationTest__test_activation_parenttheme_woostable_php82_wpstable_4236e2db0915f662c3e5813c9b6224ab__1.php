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
                "id": 1822936,
                "host": "wccom",
                "name": "Bistro",
                "type": "theme"
            },
            "client": "qit_cli",
            "event": "local_run",
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
            "test_media": [],
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
                "numPassedTests": 7,
                "numPendingTests": 3,
                "numTotalTests": 11,
                "testResults": [
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
                "summary": "Test Suites: 0 skipped, 1 failed, 0 passed, 1 total | Tests: 3 skipped, 1 failed, 7 passed, 11 total."
            }
        },
        {
            "debug_log": {
                "qm_logs": [
                    {
                        "message": "Notice on all requests - Parent Theme",
                        "type": "notice",
                        "file_line": "wp-content\\/themes\\/bistro\\/functions.php:17",
                        "count": 90
                    },
                    {
                        "message": "Warning on all requests - Parent Theme",
                        "type": "warning",
                        "file_line": "wp-content\\/themes\\/bistro\\/functions.php:13",
                        "count": 6
                    },
                    {
                        "message": " Uncaught Error: Call to undefined function call_to_undefined_function()",
                        "type": "PHP Fatal",
                        "file_line": "wp-content\\/themes\\/bistro\\/functions.php:10",
                        "count": 1
                    }
                ],
                "debug_log": [
                    {
                        "count": "1",
                        "message": "PHP Fatal error: Uncaught Error: Call to undefined function call_to_undefined_function() in \\/var\\/www\\/html\\/wp-content\\/themes\\/bistro\\/functions.php:10\\nStack trace:\\n#0 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php(324): {closure}(Object(WP))\\n#1 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php(348): WP_Hook->apply_filters(NULL, Array)\\n#2 \\/var\\/www\\/html\\/wp-includes\\/plugin.php(565): WP_Hook->do_action(Array)\\n#3 \\/var\\/www\\/html\\/wp-includes\\/class-wp.php(830): do_action_ref_array(\'wp\', Array)\\n#4 \\/var\\/www\\/html\\/wp-includes\\/functions.php(1336): WP->main(\'\')\\n#5 \\/var\\/www\\/html\\/wp-blog-header.php(16): wp()\\n#6 \\/var\\/www\\/html\\/index.php(17): require(\'\\/var\\/www\\/html\\/w...\')\\n#7 {main}\\n thrown in \\/var\\/www\\/html\\/wp-content\\/themes\\/bistro\\/functions.php on line 10\\n"
                    },
                    {
                        "count": "90",
                        "message": "PHP Notice: Notice on all requests - Parent Theme in \\/var\\/www\\/html\\/wp-content\\/themes\\/bistro\\/functions.php on line 17"
                    },
                    {
                        "count": "6",
                        "message": "PHP Warning: Warning on all requests - Parent Theme in \\/var\\/www\\/html\\/wp-content\\/themes\\/bistro\\/functions.php on line 13"
                    }
                ]
            }
        }
    ]
]';
