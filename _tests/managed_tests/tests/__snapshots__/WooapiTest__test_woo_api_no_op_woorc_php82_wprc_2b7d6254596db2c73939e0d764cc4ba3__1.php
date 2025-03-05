<?php return '[
    [
        {
            "test_run_id": 123456,
            "run_id": 123456,
            "test_type": "woo-api",
            "test_type_display": "Woo API",
            "wordpress_version": "6.0.0-normalized",
            "woocommerce_version": "6.0.0-normalized",
            "php_version": "8.2",
            "max_php_version": "",
            "min_php_version": "",
            "additional_woo_plugins": [],
            "additional_wp_plugins": [],
            "test_log": "",
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
            "event": "cli_development_extension_test",
            "optional_features": {
                "hpos": false,
                "new_product_editor": false
            },
            "test_results_manager_url": "https:\\/\\/test-results-manager.com",
            "test_results_manager_expiration": 1234567890,
            "test_summary": "Test failed before it was executed.",
            "version": "Undefined",
            "update_complete": true,
            "ai_suggestion_status": "none",
            "malware_whitelist_paths": [],
            "workflow_id": "1234567890",
            "runner": "normalized",
            "test_media": [],
            "extension_set": "",
            "phpstan_level": null,
            "test_variation": "",
            "test_result_json_extracted": "{EXTRACTED}",
            "ctrf_json_extracted": "{EXTRACTED}",
            "debug_log_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "numFailedTestSuites": 0,
                "numPassedTestSuites": 0,
                "numPendingTestSuites": 0,
                "numTotalTestSuites": 0,
                "numFailedTests": 0,
                "numPassedTests": 0,
                "numPendingTests": 0,
                "numTotalTests": 0,
                "testResults": [],
                "summary": "Test failed before it was executed."
            }
        },
        {
            "ctrf_json": {
                "results": {
                    "tool": {
                        "name": "playwright"
                    },
                    "summary": {
                        "tests": 270,
                        "passed": 266,
                        "failed": 0,
                        "pending": 0,
                        "skipped": 4,
                        "other": 0,
                        "start": 1111111111,
                        "stop": 2222222222,
                        "suites": 0
                    },
                    "tests": [
                        {
                            "name": "remove consumer key",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/token.teardown.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "consumer token teardown > ..\\/fixtures\\/token.teardown.js",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/install-wc.setup.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "install wc > ..\\/fixtures\\/install-wc.setup.js",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "description": "Skipping installing WC using WC Beta Tester; INSTALL_WC not found."
                                    }
                                ]
                            }
                        },
                        {
                            "name": "authenticate admin",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/auth.setup.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "global authentication > ..\\/fixtures\\/auth.setup.js",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "authenticate customer",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/auth.setup.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "global authentication > ..\\/fixtures\\/auth.setup.js",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "generate consumer key",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/token.setup.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "consumer token setup > ..\\/fixtures\\/token.setup.js",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "configure HPOS",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/site.setup.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "site setup > ..\\/fixtures\\/site.setup.js",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "convert Cart and Checkout pages to shortcode",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/site.setup.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "site setup > ..\\/fixtures\\/site.setup.js",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "disable coming soon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/site.setup.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "site setup > ..\\/fixtures\\/site.setup.js",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "disable onboarding wizard",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/site.setup.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "site setup > ..\\/fixtures\\/site.setup.js",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "determine if multisite",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/site.setup.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "site setup > ..\\/fixtures\\/site.setup.js",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "general settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/site.setup.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "site setup > ..\\/fixtures\\/site.setup.js",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create a coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/coupons.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/coupons\\/coupons.test.js > Coupons API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve a coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/coupons.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/coupons\\/coupons.test.js > Coupons API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update a coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/coupons.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/coupons\\/coupons.test.js > Coupons API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can permanently delete a coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/coupons.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/coupons\\/coupons.test.js > Coupons API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can batch create coupons",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/coupons.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/coupons\\/coupons.test.js > Batch update coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can batch update coupons",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/coupons.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/coupons\\/coupons.test.js > Batch update coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can batch delete coupons",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/coupons.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/coupons\\/coupons.test.js > Batch update coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can list all coupons by default",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/coupons.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/coupons\\/coupons.test.js > List coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can limit result set to matching code",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/coupons.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/coupons\\/coupons.test.js > List coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can paginate results",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/coupons.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/coupons\\/coupons.test.js > List coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can limit results to matching string",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/coupons.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/coupons\\/coupons.test.js > List coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add coupon to an order",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/coupons.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/coupons\\/coupons.test.js > Add coupon to order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve admin user",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.js > Customers API tests: CRUD > Retrieve after env setup",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve subscriber user",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.js > Customers API tests: CRUD > Retrieve after env setup",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "retrieve user with id 0 is invalid",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.js > Customers API tests: CRUD > Retrieve after env setup",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve customers",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.js > Customers API tests: CRUD > Retrieve after env setup",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all customers",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.js > Customers API tests: CRUD > Retrieve after env setup",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create a customer",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.js > Customers API tests: CRUD > Create a customer",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve a customer",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.js > Customers API tests: CRUD > Retrieve after create",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all customers after create",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.js > Customers API tests: CRUD > Retrieve after create",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update the admin user\\/customer",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.js > Customers API tests: CRUD > Update a customer",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "retrieve after update admin",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.js > Customers API tests: CRUD > Update a customer",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update the subscriber user\\/customer",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.js > Customers API tests: CRUD > Update a customer",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "retrieve after update subscriber",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.js > Customers API tests: CRUD > Update a customer",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update a customer",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.js > Customers API tests: CRUD > Update a customer",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "retrieve after update customer",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.js > Customers API tests: CRUD > Update a customer",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can permanently delete an customer",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.js > Customers API tests: CRUD > Delete a customer",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can batch create customers",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.js > Customers API tests: CRUD > Batch update customers",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can batch update customers",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.js > Customers API tests: CRUD > Batch update customers",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can batch delete customers",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.js > Customers API tests: CRUD > Batch update customers",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can list all data",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/data-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/data\\/data-crud.test.js > Data API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can view country data",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/data-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/data\\/data-crud.test.js > Data API tests",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "can view currency data",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/data-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/data\\/data-crud.test.js > Data API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can view current currency",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/data-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/data\\/data-crud.test.js > Data API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can access a non-authenticated endpoint",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/hello.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/hello\\/hello.test.js > Test API connectivity",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can access an authenticated endpoint",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/hello.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/hello\\/hello.test.js > Test API connectivity",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add complex order",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-complex.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-complex.test.js > Orders API test",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search by billing first name",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.js > Order Search API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search by billing company name",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.js > Order Search API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search by billing address 2",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.js > Order Search API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search by billing city name",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.js > Order Search API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search by billing post code",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.js > Order Search API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search by billing phone",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.js > Order Search API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search by billing state",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.js > Order Search API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search by shipping first name",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.js > Order Search API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search by shipping last name",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.js > Order Search API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search by shipping address 2",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.js > Order Search API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search by shipping city",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.js > Order Search API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search by shipping post code",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.js > Order Search API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search by shipping state",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.js > Order Search API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search by orderId",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.js > Order Search API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can return an empty result set when no matches were found",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.js > Order Search API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create a pending order by default",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Create an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create an order with status pending",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Create an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create an order with status processing",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Create an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create an order with status on-hold",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Create an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create an order with status completed",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Create an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create an order with status cancelled",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Create an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create an order with status refunded",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Create an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create an order with status failed",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Create an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create a order note",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Create an order > Order Notes tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve an order note",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Create an order > Order Notes tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all order notes",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Create an order > Order Notes tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "cannot update an order note",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Create an order > Order Notes tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can permanently delete an order note",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Create an order > Order Notes tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve an order",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Retrieve an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update status of an order to pending",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Update an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update status of an order to processing",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Update an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update status of an order to on-hold",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Update an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update status of an order to completed",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Update an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update status of an order to cancelled",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Update an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update status of an order to refunded",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Update an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update status of an order to failed",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Update an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add shipping and billing contacts to an order",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Update an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add a product to an order",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Update an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can pay for an order",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Update an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can permanently delete an order",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Delete an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create an order",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve an order",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add shipping and billing contacts to an order",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can permanently delete an order",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "pagination",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests > List all orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "inclusion \\/ exclusion",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests > List all orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "parent",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests > List all orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "status",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests > List all orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "customer",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests > List all orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests > List all orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "dp (precision)",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests > List all orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "search",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests > List all orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "default",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests > orderby",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "date",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests > orderby",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "id",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests > orderby",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "include",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/orders.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests > orderby",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can view all payment gateways",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/payment-gateways-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/payment-gateways\\/payment-gateways-crud.test.js > Payment Gateways API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can view a payment gateway",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/payment-gateways-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/payment-gateways\\/payment-gateways-crud.test.js > Payment Gateways API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update a payment gateway",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/payment-gateways-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/payment-gateways\\/payment-gateways-crud.test.js > Payment Gateways API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "defaults",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-list.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "pagination",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-list.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "search",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-list.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "inclusion \\/ exclusion",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-list.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "slug",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-list.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "sku",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-list.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "type",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-list.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "featured",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-list.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "categories",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-list.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "on sale",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-list.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "price",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-list.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "before \\/ after",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-list.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "attributes",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-list.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "status",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-list.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "shipping class",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-list.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "tax class",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-list.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "stock status",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-list.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "tags",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-list.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "parent",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-list.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "default",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-list.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products > orderby",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "date",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-list.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products > orderby",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "id",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-list.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products > orderby",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "title",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-list.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products > orderby",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "slug orderby",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-list.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products > orderby",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "price orderby",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-list.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products > orderby",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "include",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-list.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products > orderby",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "rating (desc)",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-list.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products > orderby",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "rating (asc)",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-list.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products > orderby",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "popularity (asc)",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-list.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products > orderby",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "popularity (desc)",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-list.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products > orderby",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add a simple product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add a virtual product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can view a single product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update a single product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can delete a product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add a product attribute",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product attributes tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve a product attribute",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product attributes tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all product attribute",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product attributes tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update a product attribute",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product attributes tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can permanently delete a product attribute",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product attributes tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can batch update product attributes",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product attributes tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add a product attribute term",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product attributes tests: CRUD > Product attribute terms tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve a product attribute term",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product attributes tests: CRUD > Product attribute terms tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all product attribute terms",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product attributes tests: CRUD > Product attribute terms tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update a product attribute term",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product attributes tests: CRUD > Product attribute terms tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can permanently delete a product attribute term",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product attributes tests: CRUD > Product attribute terms tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can batch update product attribute terms",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product attributes tests: CRUD > Product attribute terms tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add a product category",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product categories tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve a product category",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product categories tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all product categories",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product categories tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update a product category",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product categories tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can permanently delete a product category",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product categories tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can batch update product categories",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product categories tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add a product review",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product review tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "cannot add a product review with invalid product_id",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product review tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "cannot add a duplicate product review",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product review tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve a product review",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product review tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all product reviews",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product review tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update a product review",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product review tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can permanently delete a product review",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product review tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can batch update product reviews",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product review tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add a product shipping class",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product shipping classes tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve a product shipping class",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product shipping classes tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all product shipping classes",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product shipping classes tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update a product shipping class",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product shipping classes tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can permanently delete a product shipping class",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product shipping classes tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can batch update product shipping classes",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product shipping classes tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add a product tag",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product tags tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve a product tag",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product tags tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all product tags",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product tags tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update a product tag",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product tags tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can permanently delete a product tag",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product tags tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can batch update product tags",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product tags tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add a variable product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product variation tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add a product variation",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product variation tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve a product variation",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product variation tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all product variations",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product variation tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update a product variation",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product variation tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can permanently delete a product variation",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product variation tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can batch update product variations",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product variation tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can batch create products",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Batch update products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can batch update products",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Batch update products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can batch delete products",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/products-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Batch update products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create a refund",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/refunds.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/refunds\\/refunds.test.js > Refunds API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve a refund",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/refunds.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/refunds\\/refunds.test.js > Refunds API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve refund info from refund endpoint",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/refunds.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/refunds\\/refunds.test.js > Refunds API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can list all refunds",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/refunds.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/refunds\\/refunds.test.js > Refunds API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can delete a refund",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/refunds.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/refunds\\/refunds.test.js > Refunds API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can view all reports",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/reports-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/reports\\/reports-crud.test.js > Reports API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can view sales reports",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/reports-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/reports\\/reports-crud.test.js > Reports API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can view top sellers reports",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/reports-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/reports\\/reports-crud.test.js > Reports API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can view coupons totals",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/reports-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/reports\\/reports-crud.test.js > Reports API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can view customers totals",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/reports-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/reports\\/reports-crud.test.js > Reports API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can view orders totals",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/reports-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/reports\\/reports-crud.test.js > Reports API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can view products totals",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/reports-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/reports\\/reports-crud.test.js > Reports API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can view reviews totals",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/reports-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/reports\\/reports-crud.test.js > Reports API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all settings groups",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.js > Settings API tests: CRUD > List all settings groups",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all general settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.js > Settings API tests: CRUD > List all settings options",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve a settings option",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.js > Settings API tests: CRUD > Retrieve a settings option",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update a settings option",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.js > Settings API tests: CRUD > Update a settings option",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can batch update settings options",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.js > Settings API tests: CRUD > Batch Update a settings option",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all products settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.js > Settings API tests: CRUD > List all Products settings options",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all tax settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.js > Settings API tests: CRUD > List all Tax settings options",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all shipping settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.js > Settings API tests: CRUD > List all Shipping settings options",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all checkout settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.js > Settings API tests: CRUD > List all Checkout settings options",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all account settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.js > Settings API tests: CRUD > List all Account settings options",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all email settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.js > Settings API tests: CRUD > List all Email settings options",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all email settings with Email Improvements feature enabled",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.js > Settings API tests: CRUD > List all Email settings options with Email Improvements feature enabled",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all advanced settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.js > Settings API tests: CRUD > List all Advanced settings options",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all email new order settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.js > Settings API tests: CRUD > List all Email New Order settings",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all email failed order settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.js > Settings API tests: CRUD > List all Email Failed Order settings",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all email customer on hold order settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.js > Settings API tests: CRUD > List all Email Customer On Hold Order settings",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all email customer processing order settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.js > Settings API tests: CRUD > List all Email Customer Processing Order settings",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all email customer completed order settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.js > Settings API tests: CRUD > List all Email Customer Completed Order settings",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all email customer refunded order settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.js > Settings API tests: CRUD > List all Email Customer Refunded Order settings",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all email customer invoice settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.js > Settings API tests: CRUD > List all Email Customer Invoice settings",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all email customer note settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.js > Settings API tests: CRUD > List all Email Customer Note settings",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all email customer reset password settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.js > Settings API tests: CRUD > List all Email Customer Reset Password settings",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all email customer new account settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.js > Settings API tests: CRUD > List all Email Customer New Account settings",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "cannot create a shipping method",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shipping-method.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-method.test.js > Shipping methods API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all shipping methods",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shipping-method.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-method.test.js > Shipping methods API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve a shipping method",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shipping-method.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-method.test.js > Shipping methods API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "cannot update a shipping method",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shipping-method.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-method.test.js > Shipping methods API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "cannot delete a shipping method",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shipping-method.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-method.test.js > Shipping methods API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add a Flat rate shipping method",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shipping-method.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-method.test.js > Shipping methods API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add a Free shipping shipping method",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shipping-method.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-method.test.js > Shipping methods API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add a Local pickup shipping method",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shipping-method.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-method.test.js > Shipping methods API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "cannot delete the default shipping zone \\"Locations not covered by your other zones\\"",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shipping-zones.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-zones.test.js > Shipping zones API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "cannot update the default shipping zone",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shipping-zones.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-zones.test.js > Shipping zones API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create a shipping zone",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shipping-zones.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-zones.test.js > Shipping zones API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve a shipping zone",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shipping-zones.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-zones.test.js > Shipping zones API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can list all shipping zones",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shipping-zones.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-zones.test.js > Shipping zones API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update a shipping zone",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shipping-zones.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-zones.test.js > Shipping zones API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add a shipping region to a shipping zone",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shipping-zones.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-zones.test.js > Shipping zones API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update a shipping region on a shipping zone",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shipping-zones.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-zones.test.js > Shipping zones API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can clear\\/delete a shipping region on a shipping zone",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shipping-zones.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-zones.test.js > Shipping zones API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can delete a shipping zone",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shipping-zones.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-zones.test.js > Shipping zones API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can view all system status tools",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/system-status-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/system-status\\/system-status-crud.test.js > System Status API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve a system status tool",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/system-status-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/system-status\\/system-status-crud.test.js > System Status API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can run a tool from system status",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/system-status-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/system-status\\/system-status-crud.test.js > System Status API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can enable tax calculations",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/tax-classes-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/taxes\\/tax-classes-crud.test.js > Tax Classes API tests: CRUD > Create a tax class",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create a tax class",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/tax-classes-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/taxes\\/tax-classes-crud.test.js > Tax Classes API tests: CRUD > Create a tax class",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve a tax class",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/tax-classes-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/taxes\\/tax-classes-crud.test.js > Tax Classes API tests: CRUD > Retrieve after create",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all tax classes",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/tax-classes-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/taxes\\/tax-classes-crud.test.js > Tax Classes API tests: CRUD > Retrieve after create",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "cannot update a tax class",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/tax-classes-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/taxes\\/tax-classes-crud.test.js > Tax Classes API tests: CRUD > Update a tax class",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can permanently delete a tax class",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/tax-classes-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/taxes\\/tax-classes-crud.test.js > Tax Classes API tests: CRUD > Delete a tax class",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create a tax rate",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/tax-rates-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/taxes\\/tax-rates-crud.test.js > Tax Rates API tests: CRUD > Create a tax rate",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve a tax rate",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/tax-rates-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/taxes\\/tax-rates-crud.test.js > Tax Rates API tests: CRUD > Retrieve after create",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all tax rates",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/tax-rates-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/taxes\\/tax-rates-crud.test.js > Tax Rates API tests: CRUD > Retrieve after create",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update a tax rate",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/tax-rates-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/taxes\\/tax-rates-crud.test.js > Tax Rates API tests: CRUD > Update a tax rate",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "retrieve after update tax rate",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/tax-rates-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/taxes\\/tax-rates-crud.test.js > Tax Rates API tests: CRUD > Update a tax rate",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can permanently delete a tax rate",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/tax-rates-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/taxes\\/tax-rates-crud.test.js > Tax Rates API tests: CRUD > Delete a tax rate",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can batch create tax rates",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/tax-rates-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/taxes\\/tax-rates-crud.test.js > Tax Rates API tests: CRUD > Batch tax rate operations",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can batch update tax rates",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/tax-rates-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/taxes\\/tax-rates-crud.test.js > Tax Rates API tests: CRUD > Batch tax rate operations",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can batch delete tax rates",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/tax-rates-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/taxes\\/tax-rates-crud.test.js > Tax Rates API tests: CRUD > Batch tax rate operations",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create a webhook",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/webhooks-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/webhooks\\/webhooks-crud.test.js > Webhooks API tests > Create a webhook",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve a webhook",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/webhooks-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/webhooks\\/webhooks-crud.test.js > Webhooks API tests > Retrieve after create",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all webhooks",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/webhooks-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/webhooks\\/webhooks-crud.test.js > Webhooks API tests > Retrieve after create",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update a web hook",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/webhooks-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/webhooks\\/webhooks-crud.test.js > Webhooks API tests > Update a webhook",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can permanently delete a webhook",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/webhooks-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/webhooks\\/webhooks-crud.test.js > Webhooks API tests > Delete a webhook",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can batch create webhooks",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/webhooks-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/webhooks\\/webhooks-crud.test.js > Webhooks API tests > Batch webhook operations",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can batch update webhooks",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/webhooks-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/webhooks\\/webhooks-crud.test.js > Webhooks API tests > Batch webhook operations",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can batch delete webhooks",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/webhooks-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/webhooks\\/webhooks-crud.test.js > Webhooks API tests > Batch webhook operations",
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
                "generic": [
                    {
                        "count": "1",
                        "message": "PHP Notice: Function wpdb::prepare was called incorrectly. The query argument of wpdb::prepare() must have a placeholder. Please see Debugging in WordPress for more information. (This message was added in version 3.9.0.) in \\/var\\/www\\/html\\/wp-includes\\/functions.php on line 6121"
                    },
                    {
                        "count": "9",
                        "message": "WordPress database error You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near \')\' at line 1 for query DELETE FROM wp_options WHERE option_name IN ( ) made by do_action(\'wp_ajax_as_async_request_queue_runner\'), WP_Hook->do_action, WP_Hook->apply_filters, WP_Async_Request->maybe_handle, ActionScheduler_AsyncRequest_QueueRunner->handle, do_action(\'action_scheduler_run_queue\'), WP_Hook->do_action, WP_Hook->apply_filters, ActionScheduler_QueueRunner->run, ActionScheduler_QueueRunner->do_batch, ActionScheduler_Abstract_QueueRunner->process_action, ActionScheduler_Action->execute, do_action_ref_array(\'wc_delete_related_product_transients_async\'), WP_Hook->do_action, WP_Hook->apply_filters, wc_delete_related_product_transients, _wc_delete_transients"
                    }
                ]
            }
        }
    ]
]';
