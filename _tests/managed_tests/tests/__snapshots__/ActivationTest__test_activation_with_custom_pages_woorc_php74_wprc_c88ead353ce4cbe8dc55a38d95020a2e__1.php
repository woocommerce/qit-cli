<?php return '[
    [
        {
            "test_run_id": 123456,
            "run_id": 123456,
            "test_type": "activation",
            "test_type_display": "Activation",
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
            "event": "local_or_ci_run_normalized",
            "optional_features": {
                "hpos": false,
                "new_product_editor": false
            },
            "test_results_manager_url": "https:\\/\\/test-results-manager.com",
            "test_results_manager_expiration": 1234567890,
            "test_summary": "Test Suites: 0 skipped, 1 failed, 1 passed, 2 total | Tests: 9 skipped, 1 failed, 2 passed, 12 total.",
            "version": "",
            "update_complete": true,
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
                            "[Warning] Warning in custom page. (on file wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php line 13)"
                        ],
                        "JavaScript Console Log": [
                            "Console warning: Console Warning in custom page.",
                            "Console error: Console Error in custom page.",
                            "Uncaught exception: \\"Error - Uncaught Error in custom page. - Error: Uncaught Error in custom page.\\n    at http:\\/\\/normalized\\/wp-admin\\/admin.php?page=plugin-a:209:223\\""
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
                            "#3 \\/var\\/www\\/html\\/wp-admin\\/admin.php(260): do_action(\'toplevel_page_p...\')",
                            "#4 {main}",
                            "  thrown in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php on line 29"
                        ],
                        "JavaScript Console Log": [
                            "Console error: Failed to load resource: the server responded with a status of 500 (Internal Server Error)",
                            "Console error: PHP Fatal Error: Uncaught Error: Call to undefined function call_to_an_undefined_function()"
                        ]
                    }
                }
            ],
            "extension_set": "",
            "phpstan_level": null,
            "test_variation": "",
            "test_group_id": "",
            "test_result_json_extracted": "{EXTRACTED}",
            "ctrf_json_extracted": "{EXTRACTED}",
            "debug_log_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "numFailedTestSuites": 1,
                "numPassedTestSuites": 1,
                "numPendingTestSuites": 0,
                "numTotalTestSuites": 2,
                "numFailedTests": 1,
                "numPassedTests": 2,
                "numPendingTests": 9,
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
                                    "status": "failed"
                                },
                                {
                                    "title": "Activate Theme",
                                    "status": "pending"
                                },
                                {
                                    "title": "Setup Local Pickup",
                                    "status": "pending"
                                },
                                {
                                    "title": "Set up Cash On Delivery Payment Method",
                                    "status": "pending"
                                },
                                {
                                    "title": "Create a Product",
                                    "status": "pending"
                                },
                                {
                                    "title": "Create a Simple Order",
                                    "status": "pending"
                                },
                                {
                                    "title": "Add Product Cart",
                                    "status": "pending"
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
                "summary": "Test Suites: 0 skipped, 1 failed, 1 passed, 2 total | Tests: 9 skipped, 1 failed, 2 passed, 12 total."
            }
        },
        {
            "ctrf_json": {
                "results": {
                    "tool": {
                        "name": "playwright"
                    },
                    "summary": {
                        "tests": 12,
                        "passed": 2,
                        "failed": 1,
                        "pending": 0,
                        "skipped": 9,
                        "other": 0,
                        "start": 1111111111,
                        "stop": 2222222222,
                        "suites": 0
                    },
                    "tests": [
                        {
                            "name": "Bash Script",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/bash.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "[setup] Woocommerce (Shell) > scripts\\/bash.js",
                            "attachments": [],
                            "stdout": [
                                "\\u001b[1m\\u001b[34m=== Isolated Setup for woocommerce (Bash) ===\\u001b[0m\\n",
                                "Downloading installation package from https:\\/\\/downloads.wordpress.org\\/plugin\\/query-monitor.3.17.0.zip...\\n",
                                "Unpacking the package...\\n",
                                "Installing the plugin...\\n",
                                "Plugin installed successfully.\\n",
                                "Activating \'query-monitor\'...\\n",
                                "Plugin \'query-monitor\' activated.\\n",
                                "Success: Installed 1 of 1 plugins.\\n",
                                "Installing Twenty Twenty-Four (1.3)\\n",
                                "Downloading installation package from https:\\/\\/downloads.wordpress.org\\/theme\\/twentytwentyfour.1.3.zip...\\n",
                                "Unpacking the package...\\n",
                                "Installing the theme...\\n",
                                "Theme installed successfully.\\n",
                                "Success: Installed 1 of 1 themes.\\n",
                                "Plugin \'woocommerce\' activated.\\n",
                                "Success: Activated 1 of 1 plugins.\\n",
                                "[QIT] Finished bash script. Exit code: 0\\n"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "Isolated Setup for woocommerce (Bash)",
                                        "description": "Running bash script for plugin: woocommerce"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "Activate Plugins",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/activation.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "[test] Woocommerce (Run) > woocommerce\\/activation\\/activation.spec.js",
                            "attachments": [],
                            "stdout": [
                                "Coming soon mode disabled in beforeAll.\\n",
                                "[TIMING NORMALIZED] Starting plugin activation test\\n",
                                "dependenciesSatisfied: true for Query Monitor\\n",
                                "dependenciesSatisfied: true for WooCommerce\\n",
                                "[INFO] Final sorted plugin list:\\n",
                                " 1. \\"Query Monitor\\" (Dependencies: [])\\n",
                                " 2. \\"WooCommerce\\" (Dependencies: [])\\n",
                                " 3. \\"Activation - Plugin A\\" (Dependencies: [])\\n",
                                "[TIMING NORMALIZED] Found 3 plugins to process\\n",
                                "[TIMING NORMALIZED] Starting activation loop\\n",
                                "[TIMING NORMALIZED] Navigating to the activation link for \\"Activation - Plugin A\\".\\n",
                                "[TIMING NORMALIZED] Activated \\"Activation - Plugin A\\" successfully.\\n",
                                "[TIMING NORMALIZED] Plugin activation test completed. Total activated: 1\\n"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Visit wp-admin pages added by the plugin",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "Error: There was a fatal error in the debug log\\n\\n\\u001b[2mexpect(\\u001b[22m\\u001b[31mreceived\\u001b[39m\\u001b[2m).\\u001b[22mnot\\u001b[2m.\\u001b[22mtoContain\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m) \\/\\/ indexOf\\u001b[22m\\n\\nExpected substring: not \\u001b[32m\\"Fatal error\\"\\u001b[39m\\nReceived string:        \\u001b[31m\\"[TIMESTAMP] PHP \\u001b[7mFatal error\\u001b[27m:  Uncaught Error: Call to undefined function call_to_an_undefined_function() in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php:29\\u001b[39m\\n\\u001b[31mStack trace:\\u001b[39m\\n\\u001b[31m#0 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php(324): {closure}(\'\')\\u001b[39m\\n\\u001b[31m#1 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php(348): WP_Hook->apply_filters(\'\', Array)\\u001b[39m\\n\\u001b[31m#2 \\/var\\/www\\/html\\/wp-includes\\/plugin.php(517): WP_Hook->do_action(Array)\\u001b[39m\\n\\u001b[31m#3 \\/var\\/www\\/html\\/wp-admin\\/admin.php(260): do_action(\'toplevel_page_p...\')\\u001b[39m\\n\\u001b[31m#4 {main}\\u001b[39m\\n\\u001b[31m  thrown in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php on line 29\\"\\u001b[39m",
                            "trace": "Error: There was a fatal error in the debug log\\n\\n\\u001b[2mexpect(\\u001b[22m\\u001b[31mreceived\\u001b[39m\\u001b[2m).\\u001b[22mnot\\u001b[2m.\\u001b[22mtoContain\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m) \\/\\/ indexOf\\u001b[22m\\n\\nExpected substring: not \\u001b[32m\\"Fatal error\\"\\u001b[39m\\nReceived string:        \\u001b[31m\\"[TIMESTAMP] PHP \\u001b[7mFatal error\\u001b[27m:  Uncaught Error: Call to undefined function call_to_an_undefined_function() in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php:29\\u001b[39m\\n\\u001b[31mStack trace:\\u001b[39m\\n\\u001b[31m#0 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php(324): {closure}(\'\')\\u001b[39m\\n\\u001b[31m#1 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php(348): WP_Hook->apply_filters(\'\', Array)\\u001b[39m\\n\\u001b[31m#2 \\/var\\/www\\/html\\/wp-includes\\/plugin.php(517): WP_Hook->do_action(Array)\\u001b[39m\\n\\u001b[31m#3 \\/var\\/www\\/html\\/wp-admin\\/admin.php(260): do_action(\'toplevel_page_p...\')\\u001b[39m\\n\\u001b[31m#4 {main}\\u001b[39m\\n\\u001b[31m  thrown in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php on line 29\\"\\u001b[39m\\n    at \\/qit\\/tests\\/e2e\\/woocommerce\\/activation\\/activation.spec.js:503:89\\n    at \\/qit\\/tests\\/e2e\\/woocommerce\\/activation\\/activation.spec.js:439:9",
                            "rawStatus": "failed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/activation.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Visit Plugin A",
                                    "status": "passed"
                                },
                                {
                                    "name": "Visit Plugin B",
                                    "status": "failed"
                                }
                            ],
                            "suite": "[test] Woocommerce (Run) > woocommerce\\/activation\\/activation.spec.js",
                            "attachments": [
                                {
                                    "name": "00_Plugin_A",
                                    "contentType": "image\\/jpeg",
                                    "path": "\\/qit\\/results\\/playwright\\/activation-Visit-wp-admin-pages-added-by-the-plugin--test-Woocommerce-Run-\\/attachments\\/00-Plugin-A-HASHNORMALIZED.jpg"
                                },
                                {
                                    "name": "01_Plugin_B",
                                    "contentType": "image\\/jpeg",
                                    "path": "\\/qit\\/results\\/playwright\\/activation-Visit-wp-admin-pages-added-by-the-plugin--test-Woocommerce-Run-\\/attachments\\/01-Plugin-B-HASHNORMALIZED.jpg"
                                },
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/qit\\/results\\/playwright\\/activation-Visit-wp-admin-pages-added-by-the-plugin--test-Woocommerce-Run-\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/qit\\/results\\/playwright\\/activation-Visit-wp-admin-pages-added-by-the-plugin--test-Woocommerce-Run-\\/video.webm"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/qit\\/results\\/playwright\\/activation-Visit-wp-admin-pages-added-by-the-plugin--test-Woocommerce-Run-\\/trace.zip"
                                }
                            ],
                            "stdout": [
                                "Navigating to http:\\/\\/qitenvnginxNORMALIZED\\/wp-admin\\/admin.php?page=plugin-a\\n",
                                "Uncaught exception: \\"Error - Uncaught Error in custom page. - Error: Uncaught Error in custom page.\\n    at http:\\/\\/qitenvnginxNORMALIZED\\/wp-admin\\/admin.php?page=plugin-a:209:223\\"\\n",
                                "Navigating to http:\\/\\/qitenvnginxNORMALIZED\\/wp-admin\\/admin.php?page=plugin-b\\n"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Activate Theme",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/activation.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "[test] Woocommerce (Run) > woocommerce\\/activation\\/activation.spec.js",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Setup Local Pickup",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/activation.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "[test] Woocommerce (Run) > woocommerce\\/activation\\/activation.spec.js",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Set up Cash On Delivery Payment Method",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/activation.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "[test] Woocommerce (Run) > woocommerce\\/activation\\/activation.spec.js",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Create a Product",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/activation.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "[test] Woocommerce (Run) > woocommerce\\/activation\\/activation.spec.js",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Create a Simple Order",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/activation.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "[test] Woocommerce (Run) > woocommerce\\/activation\\/activation.spec.js",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Add Product Cart",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/activation.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "[test] Woocommerce (Run) > woocommerce\\/activation\\/activation.spec.js",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Can Place Order",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/activation.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "[test] Woocommerce (Run) > woocommerce\\/activation\\/activation.spec.js",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Deactivate Plugin",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/activation.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "[test] Woocommerce (Run) > woocommerce\\/activation\\/activation.spec.js",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Activate Other Theme",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/activation.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "[test] Woocommerce (Run) > woocommerce\\/activation\\/activation.spec.js",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            }
                        }
                    ]
                }
            }
        },
        {
            "debug_log": {
                "qm_logs": [
                    {
                        "message": " Uncaught Error: Call to undefined function call_to_an_undefined_function()",
                        "type": "PHP Fatal",
                        "file_line": "wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php:29",
                        "count": "1"
                    },
                    {
                        "message": "Notice in custom page.",
                        "type": "notice",
                        "file_line": "wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php:11",
                        "count": "1"
                    },
                    {
                        "message": "Undefined index: bar",
                        "type": "notice",
                        "file_line": "wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php:16",
                        "count": "1"
                    },
                    {
                        "message": "Warning in custom page.",
                        "type": "warning",
                        "file_line": "wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php:13",
                        "count": "1"
                    }
                ],
                "debug_log": [
                    {
                        "count": "1",
                        "message": "PHP Fatal error: Uncaught Error: Call to undefined function call_to_an_undefined_function() in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php:29\\nStack trace:\\n#0 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php(324): {closure}(\'\')\\n#1 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php(348): WP_Hook->apply_filters(\'\', Array)\\n#2 \\/var\\/www\\/html\\/wp-includes\\/plugin.php(517): WP_Hook->do_action(Array)\\n#3 \\/var\\/www\\/html\\/wp-admin\\/admin.php(260): do_action(\'toplevel_page_p...\')\\n#4 {main}\\n thrown in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php on line 29\\n"
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
