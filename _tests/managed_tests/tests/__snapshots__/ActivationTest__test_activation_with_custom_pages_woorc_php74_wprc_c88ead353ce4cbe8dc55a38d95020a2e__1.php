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
            "test_media": [
                {
                    "type": "jpg",
                    "path": "normalized.jpg",
                    "data": {
                        "Title": [
                            "Plugin A"
                        ],
                        "URL": [
                            "\\/wp-admin\\/admin.php?page=plugin-a"
                        ],
                        "Timings": [
                            "Time to page load: NORMALIZED",
                            "Time to network idle: NORMALIZED"
                        ],
                        "PHP Debug Log": [
                            "[Notice] Notice in custom page. (on file wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php line 11)",
                            "[Notice] Undefined index: bar (on file wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php line 16)",
                            "[Warning] Warning in custom page. (on file wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php line 13)",
                            ""
                        ],
                        "JavaScript Console Log": [
                            "Console warning: Console Warning in custom page.",
                            "Console error: Console Error in custom page.",
                            "Uncaught exception: \\"Error - Uncaught Error in custom page. - Error: Uncaught Error in custom page.\\n    at http:\\/\\/normalized\\/wp-admin\\/admin.php?page=plugin-a:200:223\\""
                        ]
                    }
                },
                {
                    "type": "jpg",
                    "path": "normalized.jpg",
                    "data": {
                        "Title": [
                            "Plugin B"
                        ],
                        "URL": [
                            "\\/wp-admin\\/admin.php?page=plugin-b"
                        ],
                        "Timings": [
                            "Time to page load: NORMALIZED",
                            "Time to network idle: NORMALIZED"
                        ],
                        "PHP Debug Log": [
                            "[TIMESTAMP] PHP Fatal error:  Uncaught Error: Call to undefined function call_to_an_undefined_function() in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php:29",
                            "Stack trace:",
                            "#0 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php(324): {closure}(\'\')",
                            "#1 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php(348): WP_Hook->apply_filters(\'\', Array)",
                            "#2 \\/var\\/www\\/html\\/wp-includes\\/plugin.php(517): WP_Hook->do_action(Array)",
                            "#3 \\/var\\/www\\/html\\/wp-admin\\/admin.php(259): do_action(\'toplevel_page_p...\')",
                            "#4 {main}",
                            "  thrown in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php on line 29",
                            ""
                        ],
                        "JavaScript Console Log": [
                            "Console error: Failed to load resource: the server responded with a status of 500 (Internal Server Error)",
                            "Console error: PHP Fatal Error: Uncaught Error: Call to undefined function call_to_an_undefined_function()"
                        ]
                    }
                }
            ],
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
                "numPassedTests": 11,
                "numPendingTests": 0,
                "numTotalTests": 11,
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
                "summary": "Test Suites: 0 skipped, 0 failed, 1 passed, 1 total | Tests: 0 skipped, 0 failed, 11 passed, 11 total."
            }
        },
        {
            "debug_log": {
                "qm_logs": [
                    {
                        "message": "Notice in custom page.",
                        "type": "notice",
                        "file_line": "wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php:11",
                        "count": 1
                    },
                    {
                        "message": "Undefined index: bar",
                        "type": "notice",
                        "file_line": "wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php:16",
                        "count": 1
                    },
                    {
                        "message": "Warning in custom page.",
                        "type": "warning",
                        "file_line": "wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php:13",
                        "count": 1
                    },
                    {
                        "message": " Uncaught Error: Call to undefined function call_to_an_undefined_function()",
                        "type": "PHP Fatal",
                        "file_line": "wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php:29",
                        "count": 1
                    }
                ],
                "debug_log": [
                    {
                        "count": "1",
                        "message": "PHP Fatal error: Uncaught Error: Call to undefined function call_to_an_undefined_function() in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php:29\\nStack trace:\\n#0 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php(324): {closure}(\'\')\\n#1 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php(348): WP_Hook->apply_filters(\'\', Array)\\n#2 \\/var\\/www\\/html\\/wp-includes\\/plugin.php(517): WP_Hook->do_action(Array)\\n#3 \\/var\\/www\\/html\\/wp-admin\\/admin.php(259): do_action(\'toplevel_page_p...\')\\n#4 {main}\\n thrown in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php on line 29\\n"
                    },
                    {
                        "count": "1",
                        "message": "PHP Notice: Notice in custom page. in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php on line 11"
                    },
                    {
                        "count": "1",
                        "message": "PHP Notice: Undefined index: bar in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php on line 16"
                    },
                    {
                        "count": "1",
                        "message": "PHP Warning: Warning in custom page. in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php on line 13"
                    }
                ]
            }
        }
    ]
]';
