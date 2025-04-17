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
                "numFailedTestSuites": 0,
                "numPassedTestSuites": 2,
                "numPendingTestSuites": 0,
                "numTotalTestSuites": 2,
                "numFailedTests": 0,
                "numPassedTests": 12,
                "numPendingTests": 0,
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
                "summary": "Test Suites: 0 skipped, 0 failed, 2 passed, 2 total | Tests: 0 skipped, 0 failed, 12 passed, 12 total."
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
                        "passed": 12,
                        "failed": 0,
                        "pending": 0,
                        "skipped": 0,
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
                                "Notice: Function _load_textdomain_just_in_time was called <strong>incorrectly<\\/strong>. Translation loading for the <code>woocommerce<\\/code> domain was triggered too early. This is usually an indicator for some code in the plugin or theme running too early. Translations should be loaded at the <code>init<\\/code> action or later. Please see <a href=\\"https:\\/\\/developer.wordpress.org\\/advanced-administration\\/debug\\/debug-wordpress\\/\\">Debugging in WordPress<\\/a> for more information. (This message was added in version 6.7.0.) in \\/var\\/www\\/html\\/wp-includes\\/functions.php on line 6121\\nSuccess: Updated \'woocommerce_coming_soon\' option.\\n",
                                "Notice: Function _load_textdomain_just_in_time was called <strong>incorrectly<\\/strong>. Translation loading for the <code>woocommerce<\\/code> domain was triggered too early. This is usually an indicator for some code in the plugin or theme running too early. Translations should be loaded at the <code>init<\\/code> action or later. Please see <a href=\\"https:\\/\\/developer.wordpress.org\\/advanced-administration\\/debug\\/debug-wordpress\\/\\">Debugging in WordPress<\\/a> for more information. (This message was added in version 6.7.0.) in \\/var\\/www\\/html\\/wp-includes\\/functions.php on line 6121\\nSuccess: Updated \'woocommerce_store_pages_only\' option.\\n",
                                "Coming soon mode disabled in beforeAll.\\n",
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
                            "name": "Create a Product",
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
                            "name": "Create a Simple Order",
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
                                "annotations": [
                                    {
                                        "type": "slow"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "Add Product Cart",
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
                            "name": "Can Place Order",
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
                            "name": "Deactivate Plugin",
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
                            "name": "Activate Other Theme",
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
                        "count": 100
                    },
                    {
                        "message": "Function utf8_encode() is deprecated",
                        "type": "deprecated",
                        "file_line": "wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php:37",
                        "count": 14
                    }
                ],
                "debug_log": [
                    {
                        "count": "100",
                        "message": "PHP Deprecated: Creation of dynamic property SUT\\\\BarUser::$bar is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php on line 28"
                    },
                    {
                        "count": "14",
                        "message": "PHP Deprecated: Function utf8_encode() is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php on line 37"
                    },
                    {
                        "count": "1",
                        "message": "PHP Deprecated: Hook setted_transient is deprecated since version 6.8.0! Use set_transient instead. in \\/var\\/www\\/html\\/wp-includes\\/functions.php on line 6121"
                    },
                    {
                        "count": "2",
                        "message": "PHP Notice: Function _load_textdomain_just_in_time was called incorrectly. Translation loading for the query-monitor domain was triggered too early. This is usually an indicator for some code in the plugin or theme running too early. Translations should be loaded at the init action or later. Please see Debugging in WordPress for more information. (This message was added in version 6.7.0.) in \\/var\\/www\\/html\\/wp-includes\\/functions.php on line 6121"
                    },
                    {
                        "count": "3",
                        "message": "PHP Notice: Function _load_textdomain_just_in_time was called incorrectly. Translation loading for the woocommerce domain was triggered too early. This is usually an indicator for some code in the plugin or theme running too early. Translations should be loaded at the init action or later. Please see Debugging in WordPress for more information. (This message was added in version 6.7.0.) in \\/var\\/www\\/html\\/wp-includes\\/functions.php on line 6121"
                    }
                ]
            }
        }
    ]
]';
