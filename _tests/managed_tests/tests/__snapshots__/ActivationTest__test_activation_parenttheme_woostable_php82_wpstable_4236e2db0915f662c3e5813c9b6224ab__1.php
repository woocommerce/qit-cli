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
            "test_result_json": "",
            "performance_results": "",
            "status": "failed",
            "test_result_aws_url": "https:\\/\\/test-results-aws.com",
            "test_result_aws_expiration": 1234567890,
            "is_development": false,
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
            "test_summary": "",
            "version": "",
            "update_complete": true,
            "malware_whitelist_paths": [],
            "workflow_id": "",
            "runner": "",
            "test_media": [],
            "extension_set": "",
            "phpstan_level": null,
            "test_variation": "",
            "test_packages": [],
            "iterations": 3,
            "test_group_id": "",
            "ctrf_json_extracted": "{EXTRACTED}",
            "debug_log_extracted": "{EXTRACTED}"
        },
        {
            "ctrf_json": {
                "results": {
                    "tool": {
                        "name": "playwright",
                        "extra": {
                            "orchestrationType": "test-packages"
                        }
                    },
                    "summary": {
                        "tests": 11,
                        "passed": 7,
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
                                    "name": "Expect \\"The plugin \\"Akismet Anti-spam: Spam Protection\\" never appeared active in the UI.\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect \\"The plugin \\"Hello Dolly\\" never appeared active in the UI.\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect \\"The plugin \\"WooCommerce\\" never appeared active in the UI.\\"",
                                    "status": "passed"
                                }
                            ],
                            "suite": "chromium > activation.spec.js",
                            "attachments": [],
                            "stdout": [
                                "Coming soon mode disabled in beforeAll.\\n",
                                "[TIMING NORMALIZED] Starting plugin activation test\\n",
                                "[TIMING NORMALIZED] Extracted plugins data:\\n",
                                "  1. \\"Akismet Anti-spam: Spam Protection\\"\\n",
                                "     - Slug: akismet-anti-spam-spam-protection\\n",
                                "     - Entry Point: akismet\\/akismet.php\\n",
                                "     - Active: false\\n",
                                "     - Can Activate: true\\n",
                                "     - Dependencies: []\\n",
                                "     - Activation Link: http:\\/\\/localhost:32831\\/wp-admin\\/plugins.php?action=activate&plugin=akismet%2Fakismet.php&plugin_status=all&paged=1&s&_wpnonce=NORMALIZED\\n",
                                "\\n",
                                "  2. \\"Hello Dolly\\"\\n",
                                "     - Slug: hello-dolly\\n",
                                "     - Entry Point: hello.php\\n",
                                "     - Active: false\\n",
                                "     - Can Activate: true\\n",
                                "     - Dependencies: []\\n",
                                "     - Activation Link: http:\\/\\/localhost:32831\\/wp-admin\\/plugins.php?action=activate&plugin=hello.php&plugin_status=all&paged=1&s&_wpnonce=NORMALIZED\\n",
                                "\\n",
                                "  3. \\"WooCommerce\\"\\n",
                                "     - Slug: woocommerce\\n",
                                "     - Entry Point: woocommerce\\/woocommerce.php\\n",
                                "     - Active: false\\n",
                                "     - Can Activate: true\\n",
                                "     - Dependencies: []\\n",
                                "     - Activation Link: http:\\/\\/localhost:32831\\/wp-admin\\/plugins.php?action=activate&plugin=woocommerce%2Fwoocommerce.php&plugin_status=all&paged=1&s&_wpnonce=NORMALIZED\\n",
                                "\\n",
                                "dependenciesSatisfied: true for Akismet Anti-spam: Spam Protection\\n",
                                "dependenciesSatisfied: true for Hello Dolly\\n",
                                "dependenciesSatisfied: true for WooCommerce\\n",
                                "[INFO] Final sorted plugin list:\\n",
                                " 1. \\"Akismet Anti-spam: Spam Protection\\" (Dependencies: [])\\n",
                                " 2. \\"Hello Dolly\\" (Dependencies: [])\\n",
                                " 3. \\"WooCommerce\\" (Dependencies: [])\\n",
                                "[TIMING NORMALIZED] Found 3 plugins to process\\n",
                                "[TIMING NORMALIZED] Starting activation loop\\n",
                                "[TIMING NORMALIZED] Navigating to the activation link for \\"Akismet Anti-spam: Spam Protection\\".\\n",
                                "[TIMING NORMALIZED] Activated \\"Akismet Anti-spam: Spam Protection\\" successfully.\\n",
                                "[TIMING NORMALIZED] Navigating to the activation link for \\"Hello Dolly\\".\\n",
                                "[TIMING NORMALIZED] Activated \\"Hello Dolly\\" successfully.\\n",
                                "[TIMING NORMALIZED] Navigating to the activation link for \\"WooCommerce\\".\\n",
                                "[TIMING NORMALIZED] Activated \\"WooCommerce\\" successfully.\\n",
                                "[TIMING NORMALIZED] Plugin activation test completed. Total activated: 3\\n"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/activation:latest",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/activation",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
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
                            "suite": "chromium > activation.spec.js",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/activation:latest",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/activation",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
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
                            "suite": "chromium > activation.spec.js",
                            "attachments": [],
                            "stdout": [
                                "Activated the theme: bistro\\n",
                                "Confirmation: bistro is now the active theme.\\n"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/activation:latest",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/activation",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
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
                            "suite": "chromium > activation.spec.js",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/activation:latest",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/activation",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
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
                            "suite": "chromium > activation.spec.js",
                            "attachments": [],
                            "stdout": [
                                "isAlreadyChecked: false\\n",
                                "Setting up Cash on Delivery\\n",
                                "Cash on Delivery setup complete\\n"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/activation:latest",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/activation",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
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
                            "suite": "chromium > activation.spec.js",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/activation:latest",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/activation",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
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
                            "suite": "chromium > activation.spec.js",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "slow",
                                        "location": {
                                            "file": "\\/tmp\\/qit-cache\\/packages\\/a2e9cee1612f8a15d851484d861bc9bc\\/tests\\/activation.spec.js",
                                            "line": 715,
                                            "column": 10
                                        }
                                    }
                                ],
                                "packageSlug": "woocommerce\\/activation:latest",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/activation",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Add Product Cart",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "Error: \\u001b[31mTimed out 5000ms waiting for \\u001b[39m\\u001b[2mexpect(\\u001b[22m\\u001b[31mlocator\\u001b[39m\\u001b[2m).\\u001b[22mtoContainText\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m)\\u001b[22m\\n\\nLocator: locator(\'.wc-block-components-product-name\')\\nExpected string: \\u001b[32m\\"Test Product\\"\\u001b[39m\\nReceived: <element(s) not found>\\nCall log:\\n\\u001b[2m  - Expect \\"toContainText\\" with timeout 5000ms\\u001b[22m\\n\\u001b[2m  - waiting for locator(\'.wc-block-components-product-name\')\\u001b[22m\\n",
                            "trace": "Error: \\u001b[31mTimed out 5000ms waiting for \\u001b[39m\\u001b[2mexpect(\\u001b[22m\\u001b[31mlocator\\u001b[39m\\u001b[2m).\\u001b[22mtoContainText\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m)\\u001b[22m\\n\\nLocator: locator(\'.wc-block-components-product-name\')\\nExpected string: \\u001b[32m\\"Test Product\\"\\u001b[39m\\nReceived: <element(s) not found>\\nCall log:\\n\\u001b[2m  - Expect \\"toContainText\\" with timeout 5000ms\\u001b[22m\\n\\u001b[2m  - waiting for locator(\'.wc-block-components-product-name\')\\u001b[22m\\n\\n    at \\/tmp\\/qit-cache\\/packages\\/a2e9cee1612f8a15d851484d861bc9bc\\/tests\\/activation.spec.js:825:69",
                            "snippet": "  823 |\\n  824 |     await page.goto(\'\\/cart\');\\n> 825 |     await expect(page.locator(\'.wc-block-components-product-name\')).toContainText(\'Test Product\');\\n      |                                                                     ^\\n  826 |     await expect(page.locator(\'td.wc-block-cart-item__total .wc-block-formatted-money-amount\')).toContainText(\'$10.00\');\\n  827 |     await expect(page.locator(\'.wc-block-components-totals-item__value > span\')).toContainText(\'$10.00\');\\n  828 | });",
                            "rawStatus": "failed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/activation.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "chromium > activation.spec.js",
                            "attachments": [
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/tmp\\/qit-cache\\/packages\\/a2e9cee1612f8a15d851484d861bc9bc\\/test-results\\/activation-Add-Product-Cart-chromium\\/error-context.md"
                                }
                            ],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/activation:latest",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/activation",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
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
                            "suite": "chromium > activation.spec.js",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/activation:latest",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/activation",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
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
                            "suite": "chromium > activation.spec.js",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/activation:latest",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/activation",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
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
                            "suite": "chromium > activation.spec.js",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/activation:latest",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/activation",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        }
                    ],
                    "extra": {
                        "qitPackageMetadata": {
                            "version": "1.0.0",
                            "packages": [
                                {
                                    "packageId": "woocommerce\\/activation",
                                    "namespace": "woocommerce",
                                    "testType": "e2e",
                                    "hasRunPhase": true,
                                    "testCount": 11,
                                    "packageType": "test",
                                    "executionOrder": 1,
                                    "firstSeen": 0,
                                    "duration": 36730,
                                    "isLocal": false,
                                    "hasBlobReport": true,
                                    "hasAllureReport": true
                                }
                            ],
                            "summary": {
                                "totalPackages": 1,
                                "packagesWithTests": 1,
                                "utilityPackages": 0
                            },
                            "reportCompleteness": {
                                "blob": {
                                    "complete": true,
                                    "packagesWithBlob": 1,
                                    "totalPackagesWithTests": 1,
                                    "missingFrom": []
                                },
                                "allure": {
                                    "complete": true,
                                    "packagesWithAllure": 1,
                                    "totalPackagesWithTests": 1,
                                    "missingFrom": []
                                }
                            }
                        }
                    }
                }
            }
        },
        {
            "debug_log": {
                "qm_logs": [],
                "debug_log": [
                    {
                        "count": "1",
                        "message": "PHP Fatal error: Uncaught Error: Call to undefined function call_to_undefined_function() in \\/var\\/www\\/html\\/wp-content\\/themes\\/bistro\\/functions.php:10\\nStack trace:\\n#0 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php(324): {closure}(Object(WP))\\n#1 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php(348): WP_Hook->apply_filters(NULL, Array)\\n#2 \\/var\\/www\\/html\\/wp-includes\\/plugin.php(565): WP_Hook->do_action(Array)\\n#3 \\/var\\/www\\/html\\/wp-includes\\/class-wp.php(835): do_action_ref_array(\'wp\', Array)\\n#4 \\/var\\/www\\/html\\/wp-includes\\/functions.php(1342): WP->main(\'\')\\n#5 \\/var\\/www\\/html\\/wp-blog-header.php(16): wp()\\n#6 \\/var\\/www\\/html\\/index.php(17): require(\'\\/var\\/www\\/html\\/w...\')\\n#7 {main}\\n thrown in \\/var\\/www\\/html\\/wp-content\\/themes\\/bistro\\/functions.php on line 10\\n"
                    },
                    {
                        "count": "Between 10 and 149, normalized to 75",
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
