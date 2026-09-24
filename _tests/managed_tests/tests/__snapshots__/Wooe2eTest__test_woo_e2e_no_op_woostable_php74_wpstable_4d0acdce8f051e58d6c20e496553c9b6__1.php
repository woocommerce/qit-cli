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
            "test_summary": "Tests: 126 total, 123 passed, 0 failed, 3 skipped",
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
                "woocommerce\\/core-e2e-tests:11.1"
            ],
            "test_package_checksums": {
                "woocommerce\\/core-e2e-tests:11.1": "12e13909de8c7e838552ea28eafdb0b167092a1ef406717b483e9ac8098ab351"
            },
            "test_group_id": "",
            "created_at": "2025-01-01 00:00:00",
            "validation_policy_version": "",
            "extension_specs": [
                {
                    "slug": "woocommerce",
                    "woo_product_id": null,
                    "type": "plugin",
                    "source": "wporg",
                    "requested_version": "stable",
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
                        "tests": 126,
                        "passed": 123,
                        "failed": 0,
                        "skipped": 3,
                        "pending": 0,
                        "other": 0,
                        "start": 1111111111,
                        "stop": 2222222222
                    },
                    "tests": [
                        {
                            "name": "wp plugin activate woocommerce",
                            "id": "woocommerce\\/core-e2e-tests:11.1-globalSetup-0",
                            "status": "passed",
                            "duration": 999,
                            "extra": {
                                "type": "lifecycle",
                                "phase": "globalSetup",
                                "package": "woocommerce\\/core-e2e-tests:11.1",
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests:11.1",
                                "testType": "e2e",
                                "exitCode": 0,
                                "output": "Success: Plugin already activated.\\nWarning: Plugin \'woocommerce\' is already active.",
                                "isLifecycle": true,
                                "countsTowardTotals": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "wp theme install twentytwentythree --activate",
                            "id": "woocommerce\\/core-e2e-tests:11.1-globalSetup-1",
                            "status": "passed",
                            "duration": 999,
                            "extra": {
                                "type": "lifecycle",
                                "phase": "globalSetup",
                                "package": "woocommerce\\/core-e2e-tests:11.1",
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests:11.1",
                                "testType": "e2e",
                                "exitCode": 0,
                                "output": "Installing Twenty Twenty-Three (1.7)\\nDownloading installation package from https:\\/\\/downloads.wordpress.org\\/theme\\/twentytwentythree.1.7.zip...\\nUsing cached file \'\\/qit\\/cache\\/wp-cli\\/theme\\/twentytwentythree-1.7.zip\'...\\nUnpacking the package...\\nInstalling the theme...\\nTheme installed successfully.\\nActivating \'twentytwentythree\'...\\nSuccess: Switched to \'Twenty Twenty-Three\' theme.\\nSuccess: Installed 1 of 1 themes.",
                                "isLifecycle": true,
                                "countsTowardTotals": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "wp rewrite structure \'\\/%postname%\\/\' --hard",
                            "id": "woocommerce\\/core-e2e-tests:11.1-globalSetup-2",
                            "status": "passed",
                            "duration": 999,
                            "extra": {
                                "type": "lifecycle",
                                "phase": "globalSetup",
                                "package": "woocommerce\\/core-e2e-tests:11.1",
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests:11.1",
                                "testType": "e2e",
                                "exitCode": 0,
                                "output": "Success: Rewrite structure set.\\nWarning: Regenerating a .htaccess file requires special configuration. See usage docs.\\nSuccess: Rewrite rules flushed.\\nWarning: Regenerating a .htaccess file requires special configuration. See usage docs.",
                                "isLifecycle": true,
                                "countsTowardTotals": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "wp user create customer customer@woocommercecoree2etestsuite.com --user_pass=password --role=customer --first_name=Jane --last_name=Smith",
                            "id": "woocommerce\\/core-e2e-tests:11.1-globalSetup-3",
                            "status": "passed",
                            "duration": 999,
                            "extra": {
                                "type": "lifecycle",
                                "phase": "globalSetup",
                                "package": "woocommerce\\/core-e2e-tests:11.1",
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests:11.1",
                                "testType": "e2e",
                                "exitCode": 0,
                                "output": "Success: Created user 2.",
                                "isLifecycle": true,
                                "countsTowardTotals": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "wp option update woocommerce_onboarding_profile_completed yes",
                            "id": "woocommerce\\/core-e2e-tests:11.1-globalSetup-4",
                            "status": "passed",
                            "duration": 999,
                            "extra": {
                                "type": "lifecycle",
                                "phase": "globalSetup",
                                "package": "woocommerce\\/core-e2e-tests:11.1",
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests:11.1",
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
                            "id": "woocommerce\\/core-e2e-tests:11.1-globalSetup-5",
                            "status": "passed",
                            "duration": 999,
                            "extra": {
                                "type": "lifecycle",
                                "phase": "globalSetup",
                                "package": "woocommerce\\/core-e2e-tests:11.1",
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests:11.1",
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
                            "id": "woocommerce\\/core-e2e-tests:11.1-globalSetup-6",
                            "status": "passed",
                            "duration": 999,
                            "extra": {
                                "type": "lifecycle",
                                "phase": "globalSetup",
                                "package": "woocommerce\\/core-e2e-tests:11.1",
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests:11.1",
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
                            "id": "woocommerce\\/core-e2e-tests:11.1-globalSetup-7",
                            "status": "passed",
                            "duration": 999,
                            "extra": {
                                "type": "lifecycle",
                                "phase": "globalSetup",
                                "package": "woocommerce\\/core-e2e-tests:11.1",
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests:11.1",
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
                            "id": "woocommerce\\/core-e2e-tests:11.1-globalSetup-8",
                            "status": "passed",
                            "duration": 999,
                            "extra": {
                                "type": "lifecycle",
                                "phase": "globalSetup",
                                "package": "woocommerce\\/core-e2e-tests:11.1",
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests:11.1",
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
                            "name": "wp option update blogname \'WooCommerce Core E2E Test Suite\'",
                            "id": "woocommerce\\/core-e2e-tests:11.1-globalSetup-9",
                            "status": "passed",
                            "duration": 999,
                            "extra": {
                                "type": "lifecycle",
                                "phase": "globalSetup",
                                "package": "woocommerce\\/core-e2e-tests:11.1",
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests:11.1",
                                "testType": "e2e",
                                "exitCode": 0,
                                "output": "Success: Updated \'blogname\' option.",
                                "isLifecycle": true,
                                "countsTowardTotals": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "wp option update woocommerce_enable_ajax_add_to_cart yes",
                            "id": "woocommerce\\/core-e2e-tests:11.1-globalSetup-10",
                            "status": "passed",
                            "duration": 999,
                            "extra": {
                                "type": "lifecycle",
                                "phase": "globalSetup",
                                "package": "woocommerce\\/core-e2e-tests:11.1",
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests:11.1",
                                "testType": "e2e",
                                "exitCode": 0,
                                "output": "Success: Value passed for \'woocommerce_enable_ajax_add_to_cart\' option is unchanged.",
                                "isLifecycle": true,
                                "countsTowardTotals": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Install WC using WC Beta Tester",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/install-wc.setup.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "install wc > ..\\/fixtures\\/install-wc.setup.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "description": "Skipping installing WC using WC Beta Tester; INSTALL_WC not found.",
                                        "location": {
                                            "file": "\\/private\\/var\\/folders\\/5w\\/k525n7ss29l4c0nh9hfyrhy40000gn\\/T\\/qit-cache\\/packages\\/01817ecfff68e84a9b0ea8db7aab95fa\\/fixtures\\/install-wc.setup.ts",
                                            "line": 23,
                                            "column": 8
                                        }
                                    }
                                ],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "authenticate users",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/auth.setup.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "authenticate admin",
                                    "status": "passed"
                                },
                                {
                                    "name": "authenticate customer",
                                    "status": "passed"
                                }
                            ],
                            "suite": "global authentication > ..\\/fixtures\\/auth.setup.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "setup site",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/site.setup.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "configure HPOS",
                                    "status": "passed"
                                },
                                {
                                    "name": "enable product object caching",
                                    "status": "passed"
                                },
                                {
                                    "name": "disable coming soon",
                                    "status": "passed"
                                },
                                {
                                    "name": "disable onboarding wizard",
                                    "status": "passed"
                                },
                                {
                                    "name": "determine if multisite",
                                    "status": "passed"
                                },
                                {
                                    "name": "general settings",
                                    "status": "passed"
                                },
                                {
                                    "name": "tax display settings",
                                    "status": "passed"
                                },
                                {
                                    "name": "clear tax rates",
                                    "status": "passed"
                                },
                                {
                                    "name": "clear orphaned tax classes",
                                    "status": "passed"
                                },
                                {
                                    "name": "enable offline payment gateways",
                                    "status": "passed"
                                },
                                {
                                    "name": "enable baseline free shipping",
                                    "status": "passed"
                                }
                            ],
                            "suite": "site setup > ..\\/fixtures\\/site.setup.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Failed to update onboarding profile: \\u001b[90mundefined\\u001b[39m\\n"
                            ],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should add only one product to the cart with AJAX add to cart buttons disabled and \\"Geolocate (with page caching support)\\" as the default customer location",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/add-to-cart.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > cart\\/add-to-cart.spec.ts > Add to Cart behavior",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should be able to navigate and remove item from mini cart using keyboard",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/add-to-cart.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Add product to cart and open mini cart",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify and interact with remove button",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify cart is empty",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > cart\\/add-to-cart.spec.ts > Add to Cart behavior",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can undo product removal in classic cart",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "add product to cart",
                                    "status": "passed"
                                },
                                {
                                    "name": "remove product and verify undo link appears",
                                    "status": "passed"
                                },
                                {
                                    "name": "click undo to restore product",
                                    "status": "passed"
                                },
                                {
                                    "name": "remove product again after undo",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify undo link disappears after navigation",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > cart\\/cart.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can add and remove products, increase quantity and proceed to checkout - blocks cart",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "empty cart is displayed",
                                    "status": "passed"
                                },
                                {
                                    "name": "one product in cart is displayed",
                                    "status": "passed"
                                },
                                {
                                    "name": "can increase quantity",
                                    "status": "passed"
                                },
                                {
                                    "name": "can add another product to cart",
                                    "status": "passed"
                                },
                                {
                                    "name": "can proceed to checkout and return",
                                    "status": "passed"
                                },
                                {
                                    "name": "can remove the first product",
                                    "status": "passed"
                                },
                                {
                                    "name": "can remove the last product",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > cart\\/cart.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can add and remove products, increase quantity and proceed to checkout - classic cart",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "empty cart is displayed",
                                    "status": "passed"
                                },
                                {
                                    "name": "one product in cart is displayed",
                                    "status": "passed"
                                },
                                {
                                    "name": "can increase quantity",
                                    "status": "passed"
                                },
                                {
                                    "name": "can add another product to cart",
                                    "status": "passed"
                                },
                                {
                                    "name": "can proceed to checkout and return",
                                    "status": "passed"
                                },
                                {
                                    "name": "can remove the first product",
                                    "status": "passed"
                                },
                                {
                                    "name": "can remove the last product",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > cart\\/cart.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "guest can checkout paying with cash on delivery on blocks checkout",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "guest can checkout paying with cash on delivery on classic checkout",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "guest can create an account at checkout on blocks checkout",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "guest can create an account at checkout on classic checkout",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "logged in customer can checkout with default addresses and direct bank transfer on blocks checkout",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "logged in customer can checkout with default addresses and direct bank transfer on classic checkout",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "customer can login at checkout and place the order with a different shipping address blocks checkout",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "customer can login at checkout and place the order with a different shipping address classic checkout",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "existing customer can update the billing address and place the order with direct bank transfer on blocks checkout",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "existing customer can update the billing address and place the order with direct bank transfer on classic checkout",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "applies a coupon via the cart block form",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-block-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > coupons\\/cart-block-coupons.spec.ts > Cart Block Applying Coupons",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "prevents cart block applying coupon with usage limit",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-block-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > coupons\\/cart-block-coupons.spec.ts > Cart Block Applying Coupons",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "applies a coupon via the classic cart form",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > coupons\\/cart-checkout-coupons.spec.ts > Cart applying coupons",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "rejected coupon surfaces its error in cart and checkout",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-restricted-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "cart",
                                    "status": "passed"
                                },
                                {
                                    "name": "checkout",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/cart-checkout-restricted-coupons.spec.ts > Cart & Checkout Restricted Coupons",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "accepted coupon surfaces success in cart and checkout",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-restricted-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "cart",
                                    "status": "passed"
                                },
                                {
                                    "name": "checkout",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/cart-checkout-restricted-coupons.spec.ts > Cart & Checkout Restricted Coupons",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "email-restricted coupon can be used by the right customer but only once",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-restricted-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > coupons\\/cart-checkout-restricted-coupons.spec.ts > Cart & Checkout Restricted Coupons",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create a product-restricted coupon through the admin form",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "fill the rendered coupon form",
                                    "status": "passed"
                                },
                                {
                                    "name": "publish the coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify persisted product restriction",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/coupons.spec.ts > Coupon management",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Merchant can view a list of all customers, filter and download",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/customer-list.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Go to the customers reports page",
                                    "status": "passed"
                                },
                                {
                                    "name": "Check that the customers are displayed in the list",
                                    "status": "passed"
                                },
                                {
                                    "name": "Check that the customer list can be filtered by first name",
                                    "status": "passed"
                                },
                                {
                                    "name": "Hide and display columns",
                                    "status": "passed"
                                },
                                {
                                    "name": "Download the customer list",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > customer\\/customer-list.spec.ts > Merchant > Customer List",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Merchant can view a single customer",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/customer-list.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Switch to single customer view",
                                    "status": "passed"
                                },
                                {
                                    "name": "Check that the customer is displayed",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > customer\\/customer-list.spec.ts > Merchant > Customer List",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Merchant can use advanced filters",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/customer-list.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Switch to advanced filters",
                                    "status": "passed"
                                },
                                {
                                    "name": "Add a filter for email",
                                    "status": "passed"
                                },
                                {
                                    "name": "Add a filter for country",
                                    "status": "passed"
                                },
                                {
                                    "name": "Apply the filters",
                                    "status": "passed"
                                },
                                {
                                    "name": "Check that the filter is applied",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > customer\\/customer-list.spec.ts > Merchant > Customer List",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can add billing address from my account",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/my-account-addresses.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > my-account\\/my-account-addresses.spec.ts > Customer can manage addresses in My Account > Addresses page",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can add shipping address from my account",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/my-account-addresses.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > my-account\\/my-account-addresses.spec.ts > Customer can manage addresses in My Account > Addresses page",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create a new account via my account",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/my-account-create-account.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > my-account\\/my-account-create-account.spec.ts > Shopper My Account Create Account",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can see downloadable file and click to download it",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/my-account-downloads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > my-account\\/my-account-downloads.spec.ts > Customer can manage downloadable file in My Account > Downloads page",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "allows customer to pay for their order in My Account",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/my-account-pay-order.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > my-account\\/my-account-pay-order.spec.ts > Customer can pay for their order through My Account",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create a simple guest order",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-order.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/create-order.spec.ts > WooCommerce Orders > Add new order",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can add a product using the keyboard without a rogue search box",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-order.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/create-order.spec.ts > WooCommerce Orders > Add new order",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/private\\/var\\/folders\\/5w\\/k525n7ss29l4c0nh9hfyrhy40000gn\\/T\\/qit-cache\\/packages\\/01817ecfff68e84a9b0ea8db7aab95fa\\/tests\\/order\\/create-order.spec.ts",
                                            "line": 428,
                                            "column": 8
                                        }
                                    }
                                ],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create an order for an existing customer",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-order.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/create-order.spec.ts > WooCommerce Orders > Add new order",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create new order",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-order.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/create-order.spec.ts > WooCommerce Orders > Add new order",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create new complex order with multiple product types & tax classes",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-order.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/create-order.spec.ts > WooCommerce Orders > Add new order",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can bulk update order status",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-bulk-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-bulk-edit.spec.ts > Bulk edit orders",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can apply a coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-coupon.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-coupon.spec.ts > WooCommerce Orders > Apply Coupon",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can remove a coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-coupon.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-coupon.spec.ts > WooCommerce Orders > Apply Coupon",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can view single order",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-edit.spec.ts > Edit order",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can update order status",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-edit.spec.ts > Edit order",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can update order status to cancelled",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-edit.spec.ts > Edit order",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can update order details",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-edit.spec.ts > Edit order",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "saving an order does not trigger a false unsaved-changes warning",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-edit.spec.ts > Edit order",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can add and delete order notes",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-edit.spec.ts > Edit order",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can load billing and shipping details",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Open our test order and select the customer we just created.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load the billing and shipping addresses",
                                    "status": "passed"
                                },
                                {
                                    "name": "Save the order and confirm addresses saved",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > order\\/order-edit.spec.ts > Edit order",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can copy billing address to shipping address",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Open our second test order and select the customer we just created.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Update the shipping method name",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load the billing address and then copy it to the shipping address",
                                    "status": "passed"
                                },
                                {
                                    "name": "Save the order and confirm addresses saved",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > order\\/order-edit.spec.ts > Edit order",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "shows the lost connection notice when the heartbeat request fails",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-edit.spec.ts > Edit order",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can add downloadable product permissions to order without product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-edit.spec.ts > Edit order > Downloadable product permissions",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can add downloadable product permissions to order with product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-edit.spec.ts > Edit order > Downloadable product permissions",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can edit downloadable product permissions",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-edit.spec.ts > Edit order > Downloadable product permissions",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can revoke downloadable product permissions",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-edit.spec.ts > Edit order > Downloadable product permissions",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should not allow downloading a product if download attempts are exceeded",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-edit.spec.ts > Edit order > Downloadable product permissions",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should not allow downloading a product if expiration date has passed",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-edit.spec.ts > Edit order > Downloadable product permissions",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can issue a refund by quantity",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-refund.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-refund.spec.ts > WooCommerce Orders > Refund an order",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can delete an issued refund",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-refund.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-refund.spec.ts > WooCommerce Orders > Refund an order",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can update order after refunding item without automatic stock adjustment",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-refund.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-refund.spec.ts > WooCommerce Orders > Refund and restock an order item",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should filter by All",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-status-filter.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-status-filter.spec.ts > WooCommerce Orders > Filter Order by Status",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should filter by Pending payment",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-status-filter.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-status-filter.spec.ts > WooCommerce Orders > Filter Order by Status",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should filter by Processing",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-status-filter.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-status-filter.spec.ts > WooCommerce Orders > Filter Order by Status",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should filter by On hold",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-status-filter.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-status-filter.spec.ts > WooCommerce Orders > Filter Order by Status",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should filter by Completed",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-status-filter.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-status-filter.spec.ts > WooCommerce Orders > Filter Order by Status",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should filter by Cancelled",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-status-filter.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-status-filter.spec.ts > WooCommerce Orders > Filter Order by Status",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should filter by Refunded",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-status-filter.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-status-filter.spec.ts > WooCommerce Orders > Filter Order by Status",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should filter by Failed",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-status-filter.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-status-filter.spec.ts > WooCommerce Orders > Filter Order by Status",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create a variable product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-variable-product.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Go to the \\"Add new product\\" page",
                                    "status": "passed"
                                },
                                {
                                    "name": "Type \\"<PRODUCT_NAME>\\" into the \\"Product name\\" input field.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Select the \\"Variable product\\" product type.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Scroll into the \\"Attributes\\" tab and click it.",
                                    "status": "passed"
                                },
                                {
                                    "name": "See if the tour was displayed.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Tour was displayed, so dismiss it.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Wait for the tour\'s dismissal to be saved",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the \\"Variations\\" tab to appear",
                                    "status": "passed"
                                },
                                {
                                    "name": "Save draft.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the \\"Product draft updated.\\" notice to appear.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the product type to be \\"Variable product\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Save product ID for clean up.",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/create-variable-product.spec.ts > Add variable product",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create a simple virtual product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-create-simple.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "add new product",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product name and description",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product price and inventory information",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product attributes",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product advanced information",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product categories",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product tags",
                                    "status": "passed"
                                },
                                {
                                    "name": "add virtual product details",
                                    "status": "passed"
                                },
                                {
                                    "name": "publish the product",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the saved product in frontend",
                                    "status": "passed"
                                },
                                {
                                    "name": "shopper can add the product to cart",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-create-simple.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create a simple non virtual product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-create-simple.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "add new product",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product name and description",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product price and inventory information",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product attributes",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product advanced information",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product categories",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product tags",
                                    "status": "passed"
                                },
                                {
                                    "name": "add shipping details",
                                    "status": "passed"
                                },
                                {
                                    "name": "publish the product",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the saved product in frontend",
                                    "status": "passed"
                                },
                                {
                                    "name": "shopper can add the product to cart",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-create-simple.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create a simple downloadable product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-create-simple.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "add new product",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product name and description",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product price and inventory information",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product attributes",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product advanced information",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product categories",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product tags",
                                    "status": "passed"
                                },
                                {
                                    "name": "publish the product",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the saved product in frontend",
                                    "status": "passed"
                                },
                                {
                                    "name": "shopper can add the product to cart",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-create-simple.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can delete a product from edit view",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-delete.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Navigate to product edit page",
                                    "status": "passed"
                                },
                                {
                                    "name": "Move product to trash",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify product was trashed",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-delete.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can quick delete a product from product list",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-delete.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Navigate to products list page",
                                    "status": "passed"
                                },
                                {
                                    "name": "Move product to trash",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify product was trashed",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-delete.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can permanently delete a product from trash list",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-delete.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Navigate to products trash list page",
                                    "status": "passed"
                                },
                                {
                                    "name": "Permanently delete the product",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify product was permanently deleted",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-delete.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can edit a product and save the changes",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "edit the product name",
                                    "status": "passed"
                                },
                                {
                                    "name": "edit the product description",
                                    "status": "passed"
                                },
                                {
                                    "name": "edit the product price",
                                    "status": "passed"
                                },
                                {
                                    "name": "publish the updated product",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the changes",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-edit.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can bulk edit products",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "select and bulk edit the products",
                                    "status": "passed"
                                },
                                {
                                    "name": "update the regular price",
                                    "status": "passed"
                                },
                                {
                                    "name": "update the sale price",
                                    "status": "passed"
                                },
                                {
                                    "name": "update the stock quantity",
                                    "status": "passed"
                                },
                                {
                                    "name": "save the updates",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the changes",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-edit.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can restore regular price when bulk editing products",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "select and bulk edit the products",
                                    "status": "passed"
                                },
                                {
                                    "name": "update the sale price",
                                    "status": "passed"
                                },
                                {
                                    "name": "save the updates",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the changes",
                                    "status": "passed"
                                },
                                {
                                    "name": "Update products leaving the \\"Sale > Change to\\" empty",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify products have their regular price again",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-edit.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can decrease the sale price if the product was not previously in sale when bulk editing products",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Update products with the \\"Sale > Decrease existing sale price\\" option",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify products have a sale price",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-edit.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "increasing the sale price from 0 does not change the sale price when bulk editing products",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Update products with the \\"Sale > Increase existing sale price\\" option",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify products have a sale price",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-edit.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should be able to add grouped products to the cart",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-grouped.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-grouped.spec.ts > Grouped Product Page",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should be able to remove grouped products from the cart",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-grouped.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-grouped.spec.ts > Grouped Product Page",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can view products reviews list",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-reviews.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-reviews.spec.ts > Product Reviews > Merchant manages reviews",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can filter the reviews by product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-reviews.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-reviews.spec.ts > Product Reviews > Merchant manages reviews",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can quick edit a product review",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-reviews.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-reviews.spec.ts > Product Reviews > Merchant manages reviews",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can edit a product review",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-reviews.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-reviews.spec.ts > Product Reviews > Merchant manages reviews",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can approve a product review",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-reviews.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-reviews.spec.ts > Product Reviews > Merchant manages reviews",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can mark a product review as spam",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-reviews.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-reviews.spec.ts > Product Reviews > Merchant manages reviews",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can reply to a product review",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-reviews.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-reviews.spec.ts > Product Reviews > Merchant manages reviews",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can delete a product review",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-reviews.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-reviews.spec.ts > Product Reviews > Merchant manages reviews",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "shopper can post a review",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-reviews.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-reviews.spec.ts > Product Reviews > Shopper adds reviews",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can do a partial search for a product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-search.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-search.spec.ts > Products > Search and View a product",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can view a product\'s details after search",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-search.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-search.spec.ts > Products > Search and View a product",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "returns no results for non-existent product search",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-search.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-search.spec.ts > Products > Search and View a product",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "admin can manage consumer keys",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/consumer-token.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "navigate to rest api settings page",
                                    "status": "passed"
                                },
                                {
                                    "name": "can generate a consumer key",
                                    "status": "passed"
                                },
                                {
                                    "name": "can use the consumer key",
                                    "status": "passed"
                                },
                                {
                                    "name": "can revoke the consumer key",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > settings\\/consumer-token.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Save Changes button is disabled by default and enabled only after changes.",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@non-critical",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-general.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > settings\\/settings-general.spec.ts > WooCommerce General Settings",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can update settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-general.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > settings\\/settings-general.spec.ts > WooCommerce General Settings",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can enable tax calculation",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-tax.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > settings\\/settings-tax.spec.ts > WooCommerce Tax Settings > enable",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can set tax options",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-tax.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > settings\\/settings-tax.spec.ts > WooCommerce Tax Settings",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can add tax classes",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-tax.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > settings\\/settings-tax.spec.ts > WooCommerce Tax Settings",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can set rate settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-tax.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > settings\\/settings-tax.spec.ts > WooCommerce Tax Settings",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can remove tax classes",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-tax.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > settings\\/settings-tax.spec.ts > WooCommerce Tax Settings",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can enable analytics tracking",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@skip-on-wpcom"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-woo-com.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > settings\\/settings-woo-com.spec.ts > WooCommerce woo.com Settings",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can enable marketplace suggestions",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@skip-on-wpcom"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-woo-com.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > settings\\/settings-woo-com.spec.ts > WooCommerce woo.com Settings",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Webhook cannot be bulk deleted without nonce",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/webhooks.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > settings\\/webhooks.spec.ts > Manage webhooks",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can add a shipping class with an unique slug",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shipping-classes.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > shipping\\/shipping-classes.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can add a shipping class with an auto-generated slug",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shipping-classes.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > shipping\\/shipping-classes.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can delete the shipping zone region",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shipping-zones.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > shipping\\/shipping-zones.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can delete the shipping zone method",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shipping-zones.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > shipping\\/shipping-zones.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "saves an unsaved shipping zone when adding a method",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shipping-zones.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > shipping\\/shipping-zones.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/private\\/var\\/folders\\/5w\\/k525n7ss29l4c0nh9hfyrhy40000gn\\/T\\/qit-cache\\/packages\\/01817ecfff68e84a9b0ea8db7aab95fa\\/tests\\/shipping\\/shipping-zones.spec.ts",
                                            "line": 128,
                                            "column": 6
                                        }
                                    }
                                ],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can redirect user to cart from shop page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@not-e2e",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-redirection.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > shop\\/cart-redirection.spec.ts > Cart > Redirect to cart from shop",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can redirect user to cart from detail page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@not-e2e",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-redirection.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > shop\\/cart-redirection.spec.ts > Cart > Redirect to cart from shop",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should let user search the store",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shop-search-browse-sort.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Go to the shop and perform the search",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > shop\\/shop-search-browse-sort.spec.ts > Search, browse by categories and sort items in the shop",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should let user browse products by categories",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shop-search-browse-sort.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Open a product and browse to its category via the breadcrumb",
                                    "status": "passed"
                                },
                                {
                                    "name": "Ensure the category page contains all the relevant products",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > shop\\/shop-search-browse-sort.spec.ts > Search, browse by categories and sort items in the shop",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should let user sort the products in the shop",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shop-search-browse-sort.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Sort by price high to low",
                                    "status": "passed"
                                },
                                {
                                    "name": "Sort by price low to high",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > shop\\/shop-search-browse-sort.spec.ts > Search, browse by categories and sort items in the shop",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
                                "isLocal": false,
                                "packageType": "test",
                                "packageOrder": 1
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Check the title of the shop page after the page has been deleted",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shop-title-after-deletion.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > shop\\/shop-title-after-deletion.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [],
                                "packageSlug": "woocommerce\\/core-e2e-tests:11.1",
                                "phase": "run",
                                "testType": "e2e",
                                "namespace": "woocommerce",
                                "packageId": "woocommerce\\/core-e2e-tests",
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
                                    "packageId": "woocommerce\\/core-e2e-tests:11.1",
                                    "namespace": "woocommerce",
                                    "testType": "e2e",
                                    "hasRunPhase": true,
                                    "testCount": 115,
                                    "packageType": "test",
                                    "executionOrder": 1,
                                    "firstSeen": 0,
                                    "duration": 999,
                                    "isLocal": false,
                                    "hasBlobReport": false,
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
                                    "complete": false,
                                    "packagesWithBlob": 0,
                                    "totalPackagesWithTests": 1,
                                    "missingFrom": [
                                        "core-e2e-tests:11.1"
                                    ]
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
                        "message": "PHP Notice: Undefined property: stdClass::$version_checked in \\/var\\/www\\/html\\/wp-includes\\/update.php on line {LINE}"
                    }
                ]
            }
        }
    ]
]';
