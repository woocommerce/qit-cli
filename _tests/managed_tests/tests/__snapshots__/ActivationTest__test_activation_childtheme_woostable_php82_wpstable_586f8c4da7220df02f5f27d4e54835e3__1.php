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
            "performance_results": "",
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
            "event": "local_or_ci_run_normalized",
            "optional_features": {
                "hpos": false,
                "new_product_editor": false
            },
            "test_results_manager_url": "https:\\/\\/test-results-manager.com",
            "test_results_manager_expiration": 1234567890,
            "test_summary": "Test Suites: 0 skipped, 1 failed, 1 passed, 2 total | Tests: 3 skipped, 1 failed, 8 passed, 12 total.",
            "version": "",
            "update_complete": true,
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
            "ctrf_json": {
                "results": {
                    "tool": {
                        "name": "playwright"
                    },
                    "summary": {
                        "tests": 12,
                        "passed": 8,
                        "failed": 1,
                        "pending": 0,
                        "skipped": 3,
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
                            },
                            "retryAttempts": []
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
                            "steps": [
                                {
                                    "name": "Expect \\"The plugin \\"WooCommerce\\" never appeared active in the UI.\\"",
                                    "status": "passed"
                                }
                            ],
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
                                "[TIMING NORMALIZED] Found 2 plugins to process\\n",
                                "[TIMING NORMALIZED] Starting activation loop\\n",
                                "[TIMING NORMALIZED] Navigating to the activation link for \\"WooCommerce\\".\\n",
                                "[TIMING NORMALIZED] Activated \\"WooCommerce\\" successfully.\\n",
                                "[TIMING NORMALIZED] Plugin activation test completed. Total activated: 1\\n"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            },
                            "retryAttempts": []
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
                            "stdout": [
                                "Parent theme installation required. Installing now.\\n",
                                "Activated the theme: bistro\\n",
                                "Confirmation: bistro is now the active theme.\\n"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            },
                            "retryAttempts": []
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
                            "stdout": [
                                "isAlreadyChecked: false\\n",
                                "Setting up Cash on Delivery\\n",
                                "Cash on Delivery setup complete\\n"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            },
                            "retryAttempts": []
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
                                        "type": "slow",
                                        "location": {
                                            "file": "\\/qit\\/tests\\/e2e\\/woocommerce\\/activation\\/activation.spec.js",
                                            "line": 674,
                                            "column": 10
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Add Product Cart",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "Error: \\u001b[31mTimed out 20000ms waiting for \\u001b[39m\\u001b[2mexpect(\\u001b[22m\\u001b[31mlocator\\u001b[39m\\u001b[2m).\\u001b[22mtoContainText\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m)\\u001b[22m\\n\\nLocator: locator(\'.wc-block-components-product-name\')\\nExpected string: \\u001b[32m\\"Test Product\\"\\u001b[39m\\nReceived: <element(s) not found>\\nCall log:\\n\\u001b[2m  - Expect \\"toContainText\\" with timeout 20000ms\\u001b[22m\\n\\u001b[2m  - waiting for locator(\'.wc-block-components-product-name\')\\u001b[22m\\n",
                            "trace": "Error: \\u001b[31mTimed out 20000ms waiting for \\u001b[39m\\u001b[2mexpect(\\u001b[22m\\u001b[31mlocator\\u001b[39m\\u001b[2m).\\u001b[22mtoContainText\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m)\\u001b[22m\\n\\nLocator: locator(\'.wc-block-components-product-name\')\\nExpected string: \\u001b[32m\\"Test Product\\"\\u001b[39m\\nReceived: <element(s) not found>\\nCall log:\\n\\u001b[2m  - Expect \\"toContainText\\" with timeout 20000ms\\u001b[22m\\n\\u001b[2m  - waiting for locator(\'.wc-block-components-product-name\')\\u001b[22m\\n\\n    at \\/qit\\/tests\\/e2e\\/woocommerce\\/activation\\/activation.spec.js:784:69",
                            "snippet": "  782 |\\n  783 |     await page.goto(\'\\/cart\');\\n> 784 |     await expect(page.locator(\'.wc-block-components-product-name\')).toContainText(\'Test Product\');\\n      |                                                                     ^\\n  785 |     await expect(page.locator(\'td.wc-block-cart-item__total .wc-block-formatted-money-amount\')).toContainText(\'$10.00\');\\n  786 |     await expect(page.locator(\'.wc-block-components-totals-item__value > span\')).toContainText(\'$10.00\');\\n  787 | });",
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
                                    "path": "\\/qit\\/results\\/playwright\\/activation-Add-Product-Cart--test-Woocommerce-Run-\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/qit\\/results\\/playwright\\/activation-Add-Product-Cart--test-Woocommerce-Run-\\/video.webm"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/qit\\/results\\/playwright\\/activation-Add-Product-Cart--test-Woocommerce-Run-\\/trace.zip"
                                }
                            ],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            },
                            "retryAttempts": []
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
                            },
                            "retryAttempts": []
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
                            },
                            "retryAttempts": []
                        }
                    ]
                }
            }
        },
        {
            "debug_log": {
                "qm_logs": [
                    {
                        "message": " Uncaught Error: Call to undefined function call_to_undefined_function()",
                        "type": "PHP Fatal",
                        "file_line": "wp-content\\/themes\\/bistro\\/functions.php:10",
                        "count": "1"
                    },
                    {
                        "message": "Notice on all requests - Child theme",
                        "type": "notice",
                        "file_line": "wp-content\\/themes\\/bistro\\/functions.php:17",
                        "count": "Between 10 and 149, normalized to 75"
                    },
                    {
                        "message": "Warning on all requests - Child theme",
                        "type": "warning",
                        "file_line": "wp-content\\/themes\\/bistro\\/functions.php:13",
                        "count": "6"
                    }
                ],
                "debug_log": [
                    {
                        "count": "1",
                        "message": "PHP Fatal error: Uncaught Error: Call to undefined function call_to_undefined_function() in \\/var\\/www\\/html\\/wp-content\\/themes\\/bistro\\/functions.php:10\\nStack trace:\\n#0 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php(324): {closure}(Object(WP))\\n#1 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php(348): WP_Hook->apply_filters(NULL, Array)\\n#2 \\/var\\/www\\/html\\/wp-includes\\/plugin.php(565): WP_Hook->do_action(Array)\\n#3 \\/var\\/www\\/html\\/wp-includes\\/class-wp.php(835): do_action_ref_array(\'wp\', Array)\\n#4 \\/var\\/www\\/html\\/wp-includes\\/functions.php(1342): WP->main(\'\')\\n#5 \\/var\\/www\\/html\\/wp-blog-header.php(16): wp()\\n#6 \\/var\\/www\\/html\\/index.php(17): require(\'\\/var\\/www\\/html\\/w...\')\\n#7 {main}\\n thrown in \\/var\\/www\\/html\\/wp-content\\/themes\\/bistro\\/functions.php on line 10\\n"
                    },
                    {
                        "count": "Between 10 and 149, normalized to 75",
                        "message": "PHP Notice: Notice on all requests - Child theme in \\/var\\/www\\/html\\/wp-content\\/themes\\/bistro\\/functions.php on line 17"
                    },
                    {
                        "count": "6",
                        "message": "PHP Warning: Warning on all requests - Child theme in \\/var\\/www\\/html\\/wp-content\\/themes\\/bistro\\/functions.php on line 13"
                    }
                ]
            }
        }
    ]
]';
