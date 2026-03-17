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
            "test_summary": "Tests: 17 total, 7 passed, 1 failed, 9 skipped",
            "version": "undefined",
            "update_complete": true,
            "malware_whitelist_paths": [],
            "workflow_id": "",
            "runner": "",
            "test_media": [],
            "extension_set": "",
            "phpstan_level": null,
            "test_variation": "",
            "test_packages": [],
            "test_group_id": "",
            "created_at": "2025-01-01 00:00:00",
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
                        "tests": 17,
                        "passed": 7,
                        "failed": 1,
                        "skipped": 9,
                        "pending": 0,
                        "other": 0,
                        "start": 1111111111,
                        "stop": 2222222222
                    },
                    "tests": [
                        {
                            "name": "wp plugin activate woocommerce",
                            "id": "woocommerce\\/activation:latest-globalSetup-0",
                            "status": "passed",
                            "duration": 999,
                            "extra": {
                                "type": "lifecycle",
                                "phase": "globalSetup",
                                "package": "woocommerce\\/activation:latest",
                                "packageSlug": "woocommerce\\/activation:latest",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/activation:latest",
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
                            "id": "woocommerce\\/activation:latest-globalSetup-1",
                            "status": "passed",
                            "duration": 999,
                            "extra": {
                                "type": "lifecycle",
                                "phase": "globalSetup",
                                "package": "woocommerce\\/activation:latest",
                                "packageSlug": "woocommerce\\/activation:latest",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/activation:latest",
                                "testType": "e2e",
                                "exitCode": 0,
                                "output": "Notice: Function _load_textdomain_just_in_time was called <strong>incorrectly<\\/strong>. Translation loading for the <code>woocommerce<\\/code> domain was triggered too early. This is usually an indicator for some code in the plugin or theme running too early. Translations should be loaded at the <code>init<\\/code> action or later. Please see <a href=\\"https:\\/\\/developer.wordpress.org\\/advanced-administration\\/debug\\/debug-wordpress\\/\\">Debugging in WordPress<\\/a> for more information. (This message was added in version 6.7.0.) in \\/var\\/www\\/html\\/wp-includes\\/functions.php on line {LINE}\\nSuccess: Updated \'woocommerce_onboarding_profile_completed\' option.",
                                "isLifecycle": true,
                                "countsTowardTotals": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "wp option update woocommerce_redirect_to_setup no",
                            "id": "woocommerce\\/activation:latest-globalSetup-2",
                            "status": "passed",
                            "duration": 999,
                            "extra": {
                                "type": "lifecycle",
                                "phase": "globalSetup",
                                "package": "woocommerce\\/activation:latest",
                                "packageSlug": "woocommerce\\/activation:latest",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/activation:latest",
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
                            "id": "woocommerce\\/activation:latest-globalSetup-3",
                            "status": "passed",
                            "duration": 999,
                            "extra": {
                                "type": "lifecycle",
                                "phase": "globalSetup",
                                "package": "woocommerce\\/activation:latest",
                                "packageSlug": "woocommerce\\/activation:latest",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/activation:latest",
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
                            "id": "woocommerce\\/activation:latest-globalSetup-4",
                            "status": "passed",
                            "duration": 999,
                            "extra": {
                                "type": "lifecycle",
                                "phase": "globalSetup",
                                "package": "woocommerce\\/activation:latest",
                                "packageSlug": "woocommerce\\/activation:latest",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/activation:latest",
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
                            "id": "woocommerce\\/activation:latest-globalSetup-5",
                            "status": "passed",
                            "duration": 999,
                            "extra": {
                                "type": "lifecycle",
                                "phase": "globalSetup",
                                "package": "woocommerce\\/activation:latest",
                                "packageSlug": "woocommerce\\/activation:latest",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/activation:latest",
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
                                    "name": "Expect \\"The plugin \\"Activation - Plugin A\\" never appeared active in the UI.\\"",
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
                                "[TIMING NORMALIZED] Plugin activation test completed. Total activated: 1\\n"
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
                            "message": "Error: There was a fatal error in the debug log\\n\\n\\u001b[2mexpect(\\u001b[22m\\u001b[31mreceived\\u001b[39m\\u001b[2m).\\u001b[22mnot\\u001b[2m.\\u001b[22mtoContain\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m) \\/\\/ indexOf\\u001b[22m\\n\\nExpected substring: not \\u001b[32m\\"Fatal error\\"\\u001b[39m\\nReceived string:        \\u001b[31m\\"[TIMESTAMP] PHP \\u001b[7mFatal error\\u001b[27m:  Uncaught Error: Call to undefined function call_to_an_undefined_function() in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php:29\\u001b[39m\\n\\u001b[31mStack trace:\\u001b[39m\\n\\u001b[31m#0 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php({LINE}): {closure}(\'\')\\u001b[39m\\n\\u001b[31m#1 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php({LINE}): WP_Hook->apply_filters(\'\', Array)\\u001b[39m\\n\\u001b[31m#2 \\/var\\/www\\/html\\/wp-includes\\/plugin.php({LINE}): WP_Hook->do_action(Array)\\u001b[39m\\n\\u001b[31m#3 \\/var\\/www\\/html\\/wp-admin\\/admin.php({LINE}): do_action(\'toplevel_page_p...\')\\u001b[39m\\n\\u001b[31m#4 {main}\\u001b[39m\\n\\u001b[31m  thrown in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php on line 29\\"\\u001b[39m",
                            "trace": "Error: There was a fatal error in the debug log\\n\\n\\u001b[2mexpect(\\u001b[22m\\u001b[31mreceived\\u001b[39m\\u001b[2m).\\u001b[22mnot\\u001b[2m.\\u001b[22mtoContain\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m) \\/\\/ indexOf\\u001b[22m\\n\\nExpected substring: not \\u001b[32m\\"Fatal error\\"\\u001b[39m\\nReceived string:        \\u001b[31m\\"[TIMESTAMP] PHP \\u001b[7mFatal error\\u001b[27m:  Uncaught Error: Call to undefined function call_to_an_undefined_function() in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php:29\\u001b[39m\\n\\u001b[31mStack trace:\\u001b[39m\\n\\u001b[31m#0 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php({LINE}): {closure}(\'\')\\u001b[39m\\n\\u001b[31m#1 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php({LINE}): WP_Hook->apply_filters(\'\', Array)\\u001b[39m\\n\\u001b[31m#2 \\/var\\/www\\/html\\/wp-includes\\/plugin.php({LINE}): WP_Hook->do_action(Array)\\u001b[39m\\n\\u001b[31m#3 \\/var\\/www\\/html\\/wp-admin\\/admin.php({LINE}): do_action(\'toplevel_page_p...\')\\u001b[39m\\n\\u001b[31m#4 {main}\\u001b[39m\\n\\u001b[31m  thrown in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php on line 29\\"\\u001b[39m\\n    at \\/tmp\\/qit-cache\\/packages\\/a2e9cee1612f8a15d851484d861bc9bc\\/tests\\/activation.spec.js:537:89\\n    at \\/tmp\\/qit-cache\\/packages\\/a2e9cee1612f8a15d851484d861bc9bc\\/tests\\/activation.spec.js:473:9",
                            "snippet": "  535 |\\n  536 |             \\/\\/ There should be no \\"Fatal Error\\" in the debug log.\\n> 537 |             expect(debugLog.join(\'\\\\n\'), \'There was a fatal error in the debug log\').not.toContain(\'Fatal error\');\\n      |                                                                                         ^\\n  538 |\\n  539 |             visitedPages.push(addedMenuItem.url);\\n  540 |",
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
                            "stdout": [
                                "Navigating to http:\\/\\/localhost:PORT\\/wp-admin\\/admin.php?page=plugin-a\\n",
                                "Uncaught exception: \\"Error - Uncaught Error in custom page. - Error: Uncaught Error in custom page.\\n    at http:\\/\\/localhost:PORT\\/wp-admin\\/admin.php?page=plugin-a:208:223\\"\\n",
                                "Navigating to http:\\/\\/localhost:PORT\\/wp-admin\\/admin.php?page=plugin-b\\n"
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
                                    "packageId": "woocommerce\\/activation:latest",
                                    "namespace": "woocommerce",
                                    "testType": "e2e",
                                    "hasRunPhase": true,
                                    "testCount": 11,
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
                        "message": "PHP Fatal error: Uncaught Error: Call to undefined function call_to_an_undefined_function() in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php:29\\nStack trace:\\n#0 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php({LINE}): {closure}(\'\')\\n#1 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php({LINE}): WP_Hook->apply_filters(\'\', Array)\\n#2 \\/var\\/www\\/html\\/wp-includes\\/plugin.php({LINE}): WP_Hook->do_action(Array)\\n#3 \\/var\\/www\\/html\\/wp-admin\\/admin.php({LINE}): do_action(\'toplevel_page_p...\')\\n#4 {main}\\n thrown in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php on line 29\\n"
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
