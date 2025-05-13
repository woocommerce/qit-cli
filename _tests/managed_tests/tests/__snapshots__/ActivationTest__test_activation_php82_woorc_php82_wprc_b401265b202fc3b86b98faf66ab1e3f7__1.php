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
                "numPassedTests": 5,
                "numPendingTests": 6,
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
                                    "status": "failed"
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
                "summary": "Test Suites: 0 skipped, 1 failed, 1 passed, 2 total | Tests: 6 skipped, 1 failed, 5 passed, 12 total."
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
                        "passed": 5,
                        "failed": 1,
                        "pending": 0,
                        "skipped": 6,
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
                                "Success: Updated \'woocommerce_coming_soon\' option.\\n",
                                "Success: Updated \'woocommerce_store_pages_only\' option.\\n",
                                "Coming soon mode disabled in beforeAll.\\n",
                                "dependenciesSatisfied: true for Query Monitor\\n",
                                "dependenciesSatisfied: true for WooCommerce\\n",
                                "[INFO] Final sorted plugin list:\\n",
                                " 1. \\"Query Monitor\\" (Dependencies: [])\\n",
                                " 2. \\"WooCommerce\\" (Dependencies: [])\\n",
                                " 3. \\"Activation - Plugin A\\" (Dependencies: [])\\n",
                                "Activated \\"Activation - Plugin A\\" successfully.\\n"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Visit wp-admin pages added by the plugin",
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
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Activate Theme",
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
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Setup Local Pickup",
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
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Set up Cash On Delivery Payment Method",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.check: Timeout 20000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for getByLabel(\'Enable\\/Disable\')\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.check: Timeout 20000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for getByLabel(\'Enable\\/Disable\')\\u001b[22m\\n\\n    at \\/qit\\/tests\\/e2e\\/woocommerce\\/activation\\/activation.spec.js:562:45",
                            "rawStatus": "failed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/activation.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "[test] Woocommerce (Run) > woocommerce\\/activation\\/activation.spec.js",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/qit\\/results\\/playwright\\/activation-Set-up-Cash-On-Delivery-Payment-Method--test-Woocommerce-Run-\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/qit\\/results\\/playwright\\/activation-Set-up-Cash-On-Delivery-Payment-Method--test-Woocommerce-Run-\\/video.webm"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/qit\\/results\\/playwright\\/activation-Set-up-Cash-On-Delivery-Payment-Method--test-Woocommerce-Run-\\/trace.zip"
                                }
                            ],
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
                        "message": "Creation of dynamic property SUT\\\\BarUser::$bar is deprecated",
                        "type": "deprecated",
                        "file_line": "wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php:28",
                        "count": 22
                    },
                    {
                        "message": "Hook setted_site_transient is deprecated since version 6.8.0! Use set_site_transient instead. ",
                        "type": "other",
                        "file_line": ":",
                        "count": 6
                    },
                    {
                        "message": "Hook setted_transient is deprecated since version 6.8.0! Use set_transient instead. ",
                        "type": "other",
                        "file_line": ":",
                        "count": 5
                    }
                ],
                "debug_log": [
                    {
                        "count": "22",
                        "message": "PHP Deprecated: Creation of dynamic property SUT\\\\BarUser::$bar is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php on line 28"
                    }
                ]
            }
        }
    ]
]';
