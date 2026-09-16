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
            "test_summary": "Tests: 18 total, 14 passed, 1 failed, 3 skipped",
            "version": "undefined",
            "update_complete": true,
            "malware_whitelist_paths": [],
            "workflow_id": "",
            "runner": "",
            "test_media": [],
            "extension_set": "",
            "phpstan_level": null,
            "test_variation": "",
            "test_packages": [
                "woocommerce\\/activation:11.1"
            ],
            "test_package_checksums": {
                "woocommerce\\/activation:11.1": "b2d8fb32127d915840e0cedede19e13c7f44c43101246e50418596cccf550f58"
            },
            "test_group_id": "",
            "created_at": "2025-01-01 00:00:00",
            "validation_policy_version": "",
            "extension_specs": [
                {
                    "slug": "woocommerce",
                    "woo_product_id": null,
                    "type": "plugin",
                    "source": "url",
                    "requested_version": "rc",
                    "resolved_version": "normalized",
                    "artifact_ref": [],
                    "role": "integration",
                    "reason": "local environment additional plugin"
                }
            ],
            "ctrf_json_extracted": "{EXTRACTED}",
            "debug_log_extracted": "{EXTRACTED}"
        },
        {
            "ctrf_json": {
                "reportFormat": "CTRF",
                "specVersion": "0.1.0",
                "results": {
                    "tool": {
                        "name": "qit-orchestrator",
                        "extra": {
                            "orchestrationType": "test-packages"
                        }
                    },
                    "summary": {
                        "tests": 18,
                        "passed": 14,
                        "failed": 1,
                        "skipped": 3,
                        "pending": 0,
                        "other": 0,
                        "start": 1111111111,
                        "stop": 2222222222
                    },
                    "tests": [
                        {
                            "name": "wp plugin activate woocommerce",
                            "id": "woocommerce\\/activation:11.1-globalSetup-0",
                            "status": "passed",
                            "duration": 999,
                            "extra": {
                                "type": "lifecycle",
                                "phase": "globalSetup",
                                "package": "woocommerce\\/activation:11.1",
                                "packageSlug": "woocommerce\\/activation:11.1",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/activation:11.1",
                                "testType": "e2e",
                                "exitCode": 0,
                                "output": "Plugin \'woocommerce\' activated.\\nSuccess: Activated 1 of 1 plugins.",
                                "isLifecycle": true,
                                "countsTowardTotals": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "wp option update woocommerce_onboarding_profile_completed yes",
                            "id": "woocommerce\\/activation:11.1-globalSetup-1",
                            "status": "passed",
                            "duration": 999,
                            "extra": {
                                "type": "lifecycle",
                                "phase": "globalSetup",
                                "package": "woocommerce\\/activation:11.1",
                                "packageSlug": "woocommerce\\/activation:11.1",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/activation:11.1",
                                "testType": "e2e",
                                "exitCode": 0,
                                "output": "Success: Updated \'woocommerce_onboarding_profile_completed\' option.",
                                "isLifecycle": true,
                                "countsTowardTotals": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "wp option update woocommerce_redirect_to_setup no",
                            "id": "woocommerce\\/activation:11.1-globalSetup-2",
                            "status": "passed",
                            "duration": 999,
                            "extra": {
                                "type": "lifecycle",
                                "phase": "globalSetup",
                                "package": "woocommerce\\/activation:11.1",
                                "packageSlug": "woocommerce\\/activation:11.1",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/activation:11.1",
                                "testType": "e2e",
                                "exitCode": 0,
                                "output": "Success: Updated \'woocommerce_redirect_to_setup\' option.",
                                "isLifecycle": true,
                                "countsTowardTotals": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "wp option update woocommerce_onboarding_profile \'{\\"completed\\":true,\\"skipped\\":true}\' --format=json",
                            "id": "woocommerce\\/activation:11.1-globalSetup-3",
                            "status": "passed",
                            "duration": 999,
                            "extra": {
                                "type": "lifecycle",
                                "phase": "globalSetup",
                                "package": "woocommerce\\/activation:11.1",
                                "packageSlug": "woocommerce\\/activation:11.1",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/activation:11.1",
                                "testType": "e2e",
                                "exitCode": 0,
                                "output": "Success: Updated \'woocommerce_onboarding_profile\' option.",
                                "isLifecycle": true,
                                "countsTowardTotals": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "wp option update woocommerce_coming_soon no",
                            "id": "woocommerce\\/activation:11.1-globalSetup-4",
                            "status": "passed",
                            "duration": 999,
                            "extra": {
                                "type": "lifecycle",
                                "phase": "globalSetup",
                                "package": "woocommerce\\/activation:11.1",
                                "packageSlug": "woocommerce\\/activation:11.1",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/activation:11.1",
                                "testType": "e2e",
                                "exitCode": 0,
                                "output": "Success: Updated \'woocommerce_coming_soon\' option.",
                                "isLifecycle": true,
                                "countsTowardTotals": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "wp option update woocommerce_store_pages_only no",
                            "id": "woocommerce\\/activation:11.1-globalSetup-5",
                            "status": "passed",
                            "duration": 999,
                            "extra": {
                                "type": "lifecycle",
                                "phase": "globalSetup",
                                "package": "woocommerce\\/activation:11.1",
                                "packageSlug": "woocommerce\\/activation:11.1",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/activation:11.1",
                                "testType": "e2e",
                                "exitCode": 0,
                                "output": "Success: Updated \'woocommerce_store_pages_only\' option.",
                                "isLifecycle": true,
                                "countsTowardTotals": false,
                                "packageType": "test",
                                "packageOrder": 1
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
                                    "name": "Capture pre-activation global request baseline",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect \\"The plugin \\"Activation - Plugin A\\" never appeared active in the UI.\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Probe global requests after activating the SUT",
                                    "status": "passed"
                                }
                            ],
                            "suite": "chromium > activation.spec.js",
                            "attachments": [],
                            "stdout": [
                                "[TIMING NORMALIZED] Starting plugin activation test\\n",
                                "[TIMING NORMALIZED] Extracted plugins data:\\n",
                                "  1. \\"Activation - Plugin A\\"\\n",
                                "     - Slug: activation-plugin-a\\n",
                                "     - Entry Point: woocommerce-product-feeds\\/woocommerce-product-feeds.php\\n",
                                "     - Active: false\\n",
                                "     - Can Activate: true\\n",
                                "     - Dependencies: []\\n",
                                "     - Activation Link: http:\\/\\/localhost:PORT\\/wp-admin\\/plugins.php?action=activate&plugin=woocommerce-product-feeds%2Fwoocommerce-product-feeds.php&plugin_status=all&paged=1&s&_wpnonce=NORMALIZED\\n",
                                "\\n",
                                "  2. \\"WooCommerce\\"\\n",
                                "     - Slug: woocommerce\\n",
                                "     - Entry Point: woocommerce\\/woocommerce.php\\n",
                                "     - Active: true\\n",
                                "     - Can Activate: false\\n",
                                "     - Dependencies: []\\n",
                                "\\n",
                                "dependenciesSatisfied: true for WooCommerce\\n",
                                "[INFO] Final sorted plugin list:\\n",
                                " 1. \\"WooCommerce\\" (Dependencies: [])\\n",
                                " 2. \\"Activation - Plugin A\\" (Dependencies: [])\\n",
                                "[TIMING NORMALIZED] Found 2 plugins to process\\n",
                                "[TIMING NORMALIZED] Starting activation loop\\n",
                                "[TIMING NORMALIZED] Navigating to the activation link for \\"Activation - Plugin A\\".\\n",
                                "[TIMING NORMALIZED] Activated \\"Activation - Plugin A\\" successfully.\\n",
                                "QIT_ACTIVATION_SMOKE_PASSED: all baseline and post-activation probes were healthy.\\n",
                                "[TIMING NORMALIZED] Plugin activation test completed. Total activated: 1\\n"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/activation:11.1",
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
                            "name": "Verify global request resilience",
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
                                "QIT_ACTIVATION_SMOKE_PASSED: all baseline and post-activation probes were healthy.\\n"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/activation:11.1",
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
                                "packageSlug": "woocommerce\\/activation:11.1",
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
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/activation:11.1",
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
                                "packageSlug": "woocommerce\\/activation:11.1",
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
                                "packageSlug": "woocommerce\\/activation:11.1",
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
                                "packageSlug": "woocommerce\\/activation:11.1",
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
                                            "file": "\\/private\\/var\\/folders\\/5w\\/k525n7ss29l4c0nh9hfyrhy40000gn\\/T\\/qit-cache\\/packages\\/a8512e16002e059223ecf288532ac3b5\\/tests\\/activation.spec.js",
                                            "line": 863,
                                            "column": 10
                                        }
                                    }
                                ],
                                "packageSlug": "woocommerce\\/activation:11.1",
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
                            "trace": "Error: \\u001b[31mTimed out 5000ms waiting for \\u001b[39m\\u001b[2mexpect(\\u001b[22m\\u001b[31mlocator\\u001b[39m\\u001b[2m).\\u001b[22mtoContainText\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m)\\u001b[22m\\n\\nLocator: locator(\'.wc-block-components-product-name\')\\nExpected string: \\u001b[32m\\"Test Product\\"\\u001b[39m\\nReceived: <element(s) not found>\\nCall log:\\n\\u001b[2m  - Expect \\"toContainText\\" with timeout 5000ms\\u001b[22m\\n\\u001b[2m  - waiting for locator(\'.wc-block-components-product-name\')\\u001b[22m\\n\\n    at \\/private\\/var\\/folders\\/5w\\/k525n7ss29l4c0nh9hfyrhy40000gn\\/T\\/qit-cache\\/packages\\/a8512e16002e059223ecf288532ac3b5\\/tests\\/activation.spec.js:980:69",
                            "snippet": "  978 |\\n  979 |     await page.goto(\'\\/cart\');\\n> 980 |     await expect(page.locator(\'.wc-block-components-product-name\')).toContainText(\'Test Product\');\\n      |                                                                     ^\\n  981 |     await expect(page.locator(\'td.wc-block-cart-item__total .wc-block-formatted-money-amount\')).toContainText(\'$10.00\');\\n  982 |     await expect(page.locator(\'.wc-block-components-totals-item__value > span\')).toContainText(\'$10.00\');\\n  983 | });",
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
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "normalized.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "normalized.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "normalized.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "normalized.zip"
                                }
                            ],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/activation:11.1",
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
                                "packageSlug": "woocommerce\\/activation:11.1",
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
                                "packageSlug": "woocommerce\\/activation:11.1",
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
                                "packageSlug": "woocommerce\\/activation:11.1",
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
                                    "packageId": "woocommerce\\/activation:11.1",
                                    "namespace": "woocommerce",
                                    "testType": "e2e",
                                    "hasRunPhase": true,
                                    "testCount": 12,
                                    "packageType": "test",
                                    "executionOrder": 1,
                                    "firstSeen": 0,
                                    "duration": 999,
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
                        "message": "PHP Fatal error: Uncaught Error: Call to undefined function call_to_undefined_function() in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php:9\\nStack trace:\\n#0 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php({LINE}): {closure}(Object(WP))\\n#1 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php({LINE}): WP_Hook->apply_filters(\'\', Array)\\n#2 \\/var\\/www\\/html\\/wp-includes\\/plugin.php({LINE}): WP_Hook->do_action(Array)\\n#3 \\/var\\/www\\/html\\/wp-includes\\/class-wp.php({LINE}): do_action_ref_array(\'wp\', Array)\\n#4 \\/var\\/www\\/html\\/wp-includes\\/functions.php({LINE}): WP->main(\'\')\\n#5 \\/var\\/www\\/html\\/wp-blog-header.php(16): wp()\\n#6 \\/var\\/www\\/html\\/index.php(17): require(\'\\/var\\/www\\/html\\/w...\')\\n#7 {main}\\n thrown in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php on line 9\\n"
                    },
                    {
                        "count": "Between 10 and 149, normalized to 75",
                        "message": "PHP Notice: Notice on all requests in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php on line 16"
                    },
                    {
                        "count": "7",
                        "message": "PHP Warning: Warning on all requests in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php on line 12"
                    }
                ]
            }
        }
    ]
]';
