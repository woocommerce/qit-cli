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
                        "passed": 1,
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
                                },
                                {
                                    "name": "Expect \\"The plugin \\"Activation - Plugin A\\" never appeared active in the UI.\\"",
                                    "status": "passed"
                                }
                            ],
                            "suite": "chromium > activation.spec.js",
                            "attachments": [],
                            "stdout": [
                                "Coming soon mode disabled in beforeAll.\\n",
                                "[TIMING NORMALIZED] Starting plugin activation test\\n",
                                "[TIMING NORMALIZED] Extracted plugins data:\\n",
                                "  1. \\"Activation - Plugin A\\"\\n",
                                "     - Slug: activation-plugin-a\\n",
                                "     - Entry Point: woocommerce-product-feeds\\/woocommerce-product-feeds.php\\n",
                                "     - Active: false\\n",
                                "     - Can Activate: true\\n",
                                "     - Dependencies: []\\n",
                                "     - Activation Link: http:\\/\\/localhost:32833\\/wp-admin\\/plugins.php?action=activate&plugin=woocommerce-product-feeds%2Fwoocommerce-product-feeds.php&plugin_status=all&paged=1&s&_wpnonce=NORMALIZED\\n",
                                "\\n",
                                "  2. \\"Akismet Anti-spam: Spam Protection\\"\\n",
                                "     - Slug: akismet-anti-spam-spam-protection\\n",
                                "     - Entry Point: akismet\\/akismet.php\\n",
                                "     - Active: false\\n",
                                "     - Can Activate: true\\n",
                                "     - Dependencies: []\\n",
                                "     - Activation Link: http:\\/\\/localhost:32833\\/wp-admin\\/plugins.php?action=activate&plugin=akismet%2Fakismet.php&plugin_status=all&paged=1&s&_wpnonce=NORMALIZED\\n",
                                "\\n",
                                "  3. \\"Hello Dolly\\"\\n",
                                "     - Slug: hello-dolly\\n",
                                "     - Entry Point: hello.php\\n",
                                "     - Active: false\\n",
                                "     - Can Activate: true\\n",
                                "     - Dependencies: []\\n",
                                "     - Activation Link: http:\\/\\/localhost:32833\\/wp-admin\\/plugins.php?action=activate&plugin=hello.php&plugin_status=all&paged=1&s&_wpnonce=NORMALIZED\\n",
                                "\\n",
                                "  4. \\"WooCommerce\\"\\n",
                                "     - Slug: woocommerce\\n",
                                "     - Entry Point: woocommerce\\/woocommerce.php\\n",
                                "     - Active: false\\n",
                                "     - Can Activate: true\\n",
                                "     - Dependencies: []\\n",
                                "     - Activation Link: http:\\/\\/localhost:32833\\/wp-admin\\/plugins.php?action=activate&plugin=woocommerce%2Fwoocommerce.php&plugin_status=all&paged=1&s&_wpnonce=NORMALIZED\\n",
                                "\\n",
                                "dependenciesSatisfied: true for Akismet Anti-spam: Spam Protection\\n",
                                "dependenciesSatisfied: true for Hello Dolly\\n",
                                "dependenciesSatisfied: true for WooCommerce\\n",
                                "[INFO] Final sorted plugin list:\\n",
                                " 1. \\"Akismet Anti-spam: Spam Protection\\" (Dependencies: [])\\n",
                                " 2. \\"Hello Dolly\\" (Dependencies: [])\\n",
                                " 3. \\"WooCommerce\\" (Dependencies: [])\\n",
                                " 4. \\"Activation - Plugin A\\" (Dependencies: [])\\n",
                                "[TIMING NORMALIZED] Found 4 plugins to process\\n",
                                "[TIMING NORMALIZED] Starting activation loop\\n",
                                "[TIMING NORMALIZED] Navigating to the activation link for \\"Akismet Anti-spam: Spam Protection\\".\\n",
                                "[TIMING NORMALIZED] Activated \\"Akismet Anti-spam: Spam Protection\\" successfully.\\n",
                                "[TIMING NORMALIZED] Navigating to the activation link for \\"Hello Dolly\\".\\n",
                                "[TIMING NORMALIZED] Activated \\"Hello Dolly\\" successfully.\\n",
                                "[TIMING NORMALIZED] Navigating to the activation link for \\"WooCommerce\\".\\n",
                                "[TIMING NORMALIZED] Activated \\"WooCommerce\\" successfully.\\n",
                                "[TIMING NORMALIZED] Navigating to the activation link for \\"Activation - Plugin A\\".\\n",
                                "[TIMING NORMALIZED] Activated \\"Activation - Plugin A\\" successfully.\\n",
                                "[TIMING NORMALIZED] Plugin activation test completed. Total activated: 4\\n"
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
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "Error: There was a fatal error in the debug log\\n\\n\\u001b[2mexpect(\\u001b[22m\\u001b[31mreceived\\u001b[39m\\u001b[2m).\\u001b[22mnot\\u001b[2m.\\u001b[22mtoContain\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m) \\/\\/ indexOf\\u001b[22m\\n\\nExpected substring: not \\u001b[32m\\"Fatal error\\"\\u001b[39m\\nReceived string:        \\u001b[31m\\"[TIMESTAMP] PHP \\u001b[7mFatal error\\u001b[27m:  Uncaught Error: Call to undefined function call_to_an_undefined_function() in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php:29\\u001b[39m\\n\\u001b[31mStack trace:\\u001b[39m\\n\\u001b[31m#0 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php(332): {closure}(\'\')\\u001b[39m\\n\\u001b[31m#1 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php(356): WP_Hook->apply_filters(\'\', Array)\\u001b[39m\\n\\u001b[31m#2 \\/var\\/www\\/html\\/wp-includes\\/plugin.php(517): WP_Hook->do_action(Array)\\u001b[39m\\n\\u001b[31m#3 \\/var\\/www\\/html\\/wp-admin\\/admin.php(264): do_action(\'toplevel_page_p...\')\\u001b[39m\\n\\u001b[31m#4 {main}\\u001b[39m\\n\\u001b[31m  thrown in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php on line 29\\"\\u001b[39m",
                            "trace": "Error: There was a fatal error in the debug log\\n\\n\\u001b[2mexpect(\\u001b[22m\\u001b[31mreceived\\u001b[39m\\u001b[2m).\\u001b[22mnot\\u001b[2m.\\u001b[22mtoContain\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m) \\/\\/ indexOf\\u001b[22m\\n\\nExpected substring: not \\u001b[32m\\"Fatal error\\"\\u001b[39m\\nReceived string:        \\u001b[31m\\"[TIMESTAMP] PHP \\u001b[7mFatal error\\u001b[27m:  Uncaught Error: Call to undefined function call_to_an_undefined_function() in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php:29\\u001b[39m\\n\\u001b[31mStack trace:\\u001b[39m\\n\\u001b[31m#0 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php(332): {closure}(\'\')\\u001b[39m\\n\\u001b[31m#1 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php(356): WP_Hook->apply_filters(\'\', Array)\\u001b[39m\\n\\u001b[31m#2 \\/var\\/www\\/html\\/wp-includes\\/plugin.php(517): WP_Hook->do_action(Array)\\u001b[39m\\n\\u001b[31m#3 \\/var\\/www\\/html\\/wp-admin\\/admin.php(264): do_action(\'toplevel_page_p...\')\\u001b[39m\\n\\u001b[31m#4 {main}\\u001b[39m\\n\\u001b[31m  thrown in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php on line 29\\"\\u001b[39m\\n    at \\/tmp\\/qit-cache\\/packages\\/a2e9cee1612f8a15d851484d861bc9bc\\/tests\\/activation.spec.js:538:89\\n    at \\/tmp\\/qit-cache\\/packages\\/a2e9cee1612f8a15d851484d861bc9bc\\/tests\\/activation.spec.js:474:9",
                            "snippet": "  536 |\\n  537 |             \\/\\/ There should be no \\"Fatal Error\\" in the debug log.\\n> 538 |             expect(debugLog.join(\'\\\\n\'), \'There was a fatal error in the debug log\').not.toContain(\'Fatal error\');\\n      |                                                                                         ^\\n  539 |\\n  540 |             visitedPages.push(addedMenuItem.url);\\n  541 |",
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
                            "suite": "chromium > activation.spec.js",
                            "attachments": [
                                {
                                    "name": "00_Plugin_A",
                                    "contentType": "image\\/jpeg",
                                    "path": "\\/tmp\\/qit-cache\\/packages\\/a2e9cee1612f8a15d851484d861bc9bc\\/test-results\\/activation-Visit-wp-admin-pages-added-by-the-plugin-chromium\\/attachments\\/00-Plugin-A-HASHNORMALIZED.jpg"
                                },
                                {
                                    "name": "01_Plugin_B",
                                    "contentType": "image\\/jpeg",
                                    "path": "\\/tmp\\/qit-cache\\/packages\\/a2e9cee1612f8a15d851484d861bc9bc\\/test-results\\/activation-Visit-wp-admin-pages-added-by-the-plugin-chromium\\/attachments\\/01-Plugin-B-HASHNORMALIZED.jpg"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/tmp\\/qit-cache\\/packages\\/a2e9cee1612f8a15d851484d861bc9bc\\/test-results\\/activation-Visit-wp-admin-pages-added-by-the-plugin-chromium\\/error-context.md"
                                }
                            ],
                            "stdout": [
                                "Navigating to http:\\/\\/localhost:32833\\/wp-admin\\/admin.php?page=plugin-a\\n",
                                "Uncaught exception: \\"Error - Uncaught Error in custom page. - Error: Uncaught Error in custom page.\\n    at http:\\/\\/localhost:32833\\/wp-admin\\/admin.php?page=plugin-a:425:385\\"\\n",
                                "Navigating to http:\\/\\/localhost:32833\\/wp-admin\\/admin.php?page=plugin-b\\n"
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
                                    "duration": 23103,
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
                        "message": "PHP Fatal error: Uncaught Error: Call to undefined function call_to_an_undefined_function() in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php:29\\nStack trace:\\n#0 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php(332): {closure}(\'\')\\n#1 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php(356): WP_Hook->apply_filters(\'\', Array)\\n#2 \\/var\\/www\\/html\\/wp-includes\\/plugin.php(517): WP_Hook->do_action(Array)\\n#3 \\/var\\/www\\/html\\/wp-admin\\/admin.php(264): do_action(\'toplevel_page_p...\')\\n#4 {main}\\n thrown in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php on line 29\\n"
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
