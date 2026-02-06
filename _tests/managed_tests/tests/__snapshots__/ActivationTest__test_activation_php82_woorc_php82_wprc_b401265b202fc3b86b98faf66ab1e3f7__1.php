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
            "test_result_json": "",
            "performance_results": "",
            "status": "success",
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
            "test_summary": "No errors detected.",
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
                        "passed": 17,
                        "failed": 0,
                        "skipped": 0,
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
                                            "line": 716,
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
                                "SUT \\"Activation - Plugin A\\" has 0 dependencies (requires): []\\n",
                                "Found 0 active plugins that depend on the SUT (must deactivate first)\\n",
                                "Step 3: Deactivating SUT \\"Activation - Plugin A\\"\\n",
                                "\\u2713 Successfully deactivated SUT \\"Activation - Plugin A\\"\\n"
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
                        "count": "Between 10 and 149, normalized to 75",
                        "message": "PHP Deprecated: Creation of dynamic property SUT\\\\BarUser::$bar is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php on line 28"
                    },
                    {
                        "count": "Between 10 and 149, normalized to 75",
                        "message": "PHP Deprecated: Function utf8_encode() is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php on line 37"
                    }
                ]
            }
        }
    ]
]';
