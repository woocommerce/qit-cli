<?php return '[
    [
        {
            "test_run_id": 123456,
            "run_id": 123456,
            "test_type": "woo-api",
            "test_type_display": "Woo API",
            "wordpress_version": "6.0.0-normalized",
            "woocommerce_version": "6.0.0-normalized",
            "php_version": "7.4",
            "max_php_version": "",
            "min_php_version": "",
            "additional_woo_plugins": [],
            "additional_wp_plugins": [],
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
            "event": "cli_development_extension_test",
            "optional_features": {
                "hpos": false,
                "new_product_editor": false
            },
            "test_results_manager_url": "https:\\/\\/test-results-manager.com",
            "test_results_manager_expiration": 1234567890,
            "test_summary": "Delete_Products Normalized Summary",
            "version": "Undefined",
            "update_complete": true,
            "ai_suggestion_status": "none",
            "malware_whitelist_paths": [],
            "workflow_id": "1234567890",
            "runner": "normalized",
            "test_media": [],
            "test_result_json_extracted": "{EXTRACTED}",
            "ctrf_json_extracted": "{EXTRACTED}",
            "debug_log_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": []
        },
        {
            "ctrf_json": {
                "results": {
                    "tool": {
                        "name": "playwright"
                    },
                    "summary": {
                        "tests": 258,
                        "passed": 156,
                        "failed": 22,
                        "pending": 0,
                        "skipped": 80,
                        "other": 0,
                        "start": 1737573624785,
                        "stop": 1737573681415,
                        "suites": 0
                    },
                    "tests": [
                        {
                            "name": "can create a coupon",
                            "status": "passed",
                            "duration": 148,
                            "start": 1737573625,
                            "stop": 1737573625,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/coupons\\/coupons.test.js",
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
                            "duration": 84,
                            "start": 1737573625,
                            "stop": 1737573625,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/coupons\\/coupons.test.js",
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
                            "duration": 83,
                            "start": 1737573625,
                            "stop": 1737573625,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/coupons\\/coupons.test.js",
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
                            "duration": 145,
                            "start": 1737573625,
                            "stop": 1737573625,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/coupons\\/coupons.test.js",
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
                            "duration": 109,
                            "start": 1737573625,
                            "stop": 1737573625,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/coupons\\/coupons.test.js",
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
                            "duration": 83,
                            "start": 1737573625,
                            "stop": 1737573625,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/coupons\\/coupons.test.js",
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
                            "duration": 213,
                            "start": 1737573625,
                            "stop": 1737573626,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/coupons\\/coupons.test.js",
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
                            "duration": 74,
                            "start": 1737573626,
                            "stop": 1737573626,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/coupons\\/coupons.test.js",
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
                            "duration": 72,
                            "start": 1737573626,
                            "stop": 1737573626,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/coupons\\/coupons.test.js",
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
                            "duration": 79,
                            "start": 1737573626,
                            "stop": 1737573626,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/coupons\\/coupons.test.js",
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
                            "duration": 80,
                            "start": 1737573626,
                            "stop": 1737573626,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/coupons\\/coupons.test.js",
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
                            "duration": 151,
                            "start": 1737573626,
                            "stop": 1737573626,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/coupons\\/coupons.test.js",
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
                            "duration": 79,
                            "start": 1737573627,
                            "stop": 1737573627,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/customers\\/customers-crud.test.js",
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
                            "duration": 80,
                            "start": 1737573627,
                            "stop": 1737573627,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/customers\\/customers-crud.test.js",
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
                            "duration": 69,
                            "start": 1737573627,
                            "stop": 1737573627,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/customers\\/customers-crud.test.js",
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
                            "duration": 75,
                            "start": 1737573627,
                            "stop": 1737573627,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/customers\\/customers-crud.test.js",
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
                            "duration": 78,
                            "start": 1737573627,
                            "stop": 1737573627,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/customers\\/customers-crud.test.js",
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
                            "duration": 178,
                            "start": 1737573627,
                            "stop": 1737573627,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/customers\\/customers-crud.test.js",
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
                            "duration": 71,
                            "start": 1737573627,
                            "stop": 1737573627,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/customers\\/customers-crud.test.js",
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
                            "duration": 79,
                            "start": 1737573628,
                            "stop": 1737573628,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/customers\\/customers-crud.test.js",
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
                            "duration": 80,
                            "start": 1737573628,
                            "stop": 1737573628,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/customers\\/customers-crud.test.js",
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
                            "duration": 70,
                            "start": 1737573628,
                            "stop": 1737573628,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/customers\\/customers-crud.test.js",
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
                            "duration": 81,
                            "start": 1737573628,
                            "stop": 1737573628,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/customers\\/customers-crud.test.js",
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
                            "duration": 70,
                            "start": 1737573628,
                            "stop": 1737573628,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/customers\\/customers-crud.test.js",
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
                            "duration": 89,
                            "start": 1737573628,
                            "stop": 1737573628,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/customers\\/customers-crud.test.js",
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
                            "duration": 71,
                            "start": 1737573628,
                            "stop": 1737573628,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/customers\\/customers-crud.test.js",
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
                            "duration": 167,
                            "start": 1737573628,
                            "stop": 1737573628,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/customers\\/customers-crud.test.js",
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
                            "duration": 219,
                            "start": 1737573628,
                            "stop": 1737573628,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/customers\\/customers-crud.test.js",
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
                            "duration": 110,
                            "start": 1737573628,
                            "stop": 1737573629,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/customers\\/customers-crud.test.js",
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
                            "duration": 249,
                            "start": 1737573629,
                            "stop": 1737573629,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/customers\\/customers-crud.test.js",
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
                            "duration": 73,
                            "start": 1737573629,
                            "stop": 1737573629,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/data\\/data-crud.test.js",
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
                            "duration": 0,
                            "start": 1737573629,
                            "stop": 1737573629,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/data\\/data-crud.test.js",
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
                            "duration": 68,
                            "start": 1737573629,
                            "stop": 1737573629,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/data\\/data-crud.test.js",
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
                            "duration": 70,
                            "start": 1737573629,
                            "stop": 1737573629,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/data\\/data-crud.test.js",
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
                            "duration": 68,
                            "start": 1737573629,
                            "stop": 1737573629,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/hello\\/hello.test.js",
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
                            "duration": 859,
                            "start": 1737573629,
                            "stop": 1737573630,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/hello\\/hello.test.js",
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
                            "status": "failed",
                            "duration": 1,
                            "start": 1737573631,
                            "stop": 1737573631,
                            "message": "SyntaxError: Unexpected end of JSON input",
                            "trace": "SyntaxError: Unexpected end of JSON input\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-complex.test.js:124:36",
                            "rawStatus": "failed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-complex.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-complex.test.js > Orders API test",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search by billing first name",
                            "status": "failed",
                            "duration": 1,
                            "start": 1737573633,
                            "stop": 1737573633,
                            "message": "SyntaxError: Unexpected end of JSON input",
                            "trace": "SyntaxError: Unexpected end of JSON input\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js:58:31",
                            "rawStatus": "failed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.js > Order Search API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search by billing company name",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573633,
                            "stop": 1737573633,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.js > Order Search API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search by billing address 2",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573633,
                            "stop": 1737573633,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.js > Order Search API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search by billing city name",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573633,
                            "stop": 1737573633,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.js > Order Search API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search by billing post code",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573633,
                            "stop": 1737573633,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.js > Order Search API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search by billing phone",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573633,
                            "stop": 1737573633,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.js > Order Search API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search by billing state",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573633,
                            "stop": 1737573633,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.js > Order Search API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search by shipping first name",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573633,
                            "stop": 1737573633,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.js > Order Search API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search by shipping last name",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573633,
                            "stop": 1737573633,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.js > Order Search API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search by shipping address 2",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573633,
                            "stop": 1737573633,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.js > Order Search API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search by shipping city",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573633,
                            "stop": 1737573633,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.js > Order Search API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search by shipping post code",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573633,
                            "stop": 1737573633,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.js > Order Search API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search by shipping state",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573633,
                            "stop": 1737573633,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.js > Order Search API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search by orderId",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573633,
                            "stop": 1737573633,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.js > Order Search API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can return an empty result set when no matches were found",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573633,
                            "stop": 1737573633,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js",
                            "retries": 1,
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
                            "duration": 169,
                            "start": 1737573637,
                            "stop": 1737573637,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
                            "retries": 1,
                            "flaky": true,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Create an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create an order with status pending",
                            "status": "passed",
                            "duration": 198,
                            "start": 1737573637,
                            "stop": 1737573637,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
                            "retries": 1,
                            "flaky": true,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Create an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create an order with status processing",
                            "status": "passed",
                            "duration": 294,
                            "start": 1737573637,
                            "stop": 1737573638,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
                            "retries": 1,
                            "flaky": true,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Create an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create an order with status on-hold",
                            "status": "passed",
                            "duration": 287,
                            "start": 1737573638,
                            "stop": 1737573638,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
                            "retries": 1,
                            "flaky": true,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Create an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create an order with status completed",
                            "status": "passed",
                            "duration": 276,
                            "start": 1737573638,
                            "stop": 1737573638,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
                            "retries": 1,
                            "flaky": true,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Create an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create an order with status cancelled",
                            "status": "passed",
                            "duration": 197,
                            "start": 1737573638,
                            "stop": 1737573638,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
                            "retries": 1,
                            "flaky": true,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Create an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create an order with status refunded",
                            "status": "passed",
                            "duration": 187,
                            "start": 1737573638,
                            "stop": 1737573639,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
                            "retries": 1,
                            "flaky": true,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Create an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create an order with status failed",
                            "status": "passed",
                            "duration": 236,
                            "start": 1737573639,
                            "stop": 1737573639,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
                            "retries": 1,
                            "flaky": true,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Create an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create a order note",
                            "status": "passed",
                            "duration": 72,
                            "start": 1737573639,
                            "stop": 1737573639,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
                            "retries": 1,
                            "flaky": true,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Create an order > Order Notes tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve an order note",
                            "status": "passed",
                            "duration": 68,
                            "start": 1737573639,
                            "stop": 1737573639,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
                            "retries": 1,
                            "flaky": true,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Create an order > Order Notes tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all order notes",
                            "status": "passed",
                            "duration": 73,
                            "start": 1737573639,
                            "stop": 1737573639,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
                            "retries": 1,
                            "flaky": true,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Create an order > Order Notes tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "cannot update an order note",
                            "status": "passed",
                            "duration": 68,
                            "start": 1737573639,
                            "stop": 1737573639,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
                            "retries": 1,
                            "flaky": true,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Create an order > Order Notes tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can permanently delete an order note",
                            "status": "passed",
                            "duration": 126,
                            "start": 1737573639,
                            "stop": 1737573639,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
                            "retries": 1,
                            "flaky": true,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Create an order > Order Notes tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve an order",
                            "status": "passed",
                            "duration": 69,
                            "start": 1737573639,
                            "stop": 1737573639,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
                            "retries": 1,
                            "flaky": true,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Retrieve an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update status of an order to pending",
                            "status": "failed",
                            "duration": 0,
                            "start": 1737573639,
                            "stop": 1737573639,
                            "message": "SyntaxError: Unexpected end of JSON input",
                            "trace": "SyntaxError: Unexpected end of JSON input\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js:234:25",
                            "rawStatus": "failed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Update an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update status of an order to processing",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573640,
                            "stop": 1737573640,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Update an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update status of an order to on-hold",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573640,
                            "stop": 1737573640,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Update an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update status of an order to completed",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573640,
                            "stop": 1737573640,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Update an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update status of an order to cancelled",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573640,
                            "stop": 1737573640,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Update an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update status of an order to refunded",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573640,
                            "stop": 1737573640,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Update an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update status of an order to failed",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573640,
                            "stop": 1737573640,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Update an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add shipping and billing contacts to an order",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573640,
                            "stop": 1737573640,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Update an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add a product to an order",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573640,
                            "stop": 1737573640,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Update an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can pay for an order",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573640,
                            "stop": 1737573640,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Update an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can permanently delete an order",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573640,
                            "stop": 1737573640,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.js > Orders API tests: CRUD > Delete an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create an order",
                            "status": "failed",
                            "duration": 0,
                            "start": 1737573642,
                            "stop": 1737573642,
                            "message": "SyntaxError: Unexpected end of JSON input",
                            "trace": "SyntaxError: Unexpected end of JSON input\\n    at createSampleSimpleProducts (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js:1273:32)\\n    at productsTestSetupCreateSampleData (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js:2133:28)\\n    at createSampleData (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js:2182:6)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js:2502:17",
                            "rawStatus": "failed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve an order",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573643,
                            "stop": 1737573643,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add shipping and billing contacts to an order",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573643,
                            "stop": 1737573643,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can permanently delete an order",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573643,
                            "stop": 1737573643,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "pagination",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573643,
                            "stop": 1737573643,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests > List all orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "inclusion \\/ exclusion",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573643,
                            "stop": 1737573643,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests > List all orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "parent",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573643,
                            "stop": 1737573643,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests > List all orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "status",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573643,
                            "stop": 1737573643,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests > List all orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "customer",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573643,
                            "stop": 1737573643,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests > List all orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "product",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573643,
                            "stop": 1737573643,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests > List all orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "dp (precision)",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573643,
                            "stop": 1737573643,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests > List all orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "search",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573643,
                            "stop": 1737573643,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests > List all orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "default",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573643,
                            "stop": 1737573643,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests > orderby",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "date",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573643,
                            "stop": 1737573643,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests > orderby",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "id",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573643,
                            "stop": 1737573643,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.js > Orders API tests > orderby",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "include",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573643,
                            "stop": 1737573643,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
                            "retries": 1,
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
                            "duration": 120,
                            "start": 1737573643,
                            "stop": 1737573644,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/payment-gateways\\/payment-gateways-crud.test.js",
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
                            "duration": 83,
                            "start": 1737573644,
                            "stop": 1737573644,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/payment-gateways\\/payment-gateways-crud.test.js",
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
                            "duration": 157,
                            "start": 1737573644,
                            "stop": 1737573644,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/payment-gateways\\/payment-gateways-crud.test.js",
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
                            "status": "failed",
                            "duration": 0,
                            "start": 1737573645,
                            "stop": 1737573645,
                            "message": "SyntaxError: Unexpected end of JSON input",
                            "trace": "SyntaxError: Unexpected end of JSON input\\n    at createSampleSimpleProducts (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js:1233:31)\\n    at createSampleData (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js:2083:27)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js:2122:16",
                            "rawStatus": "failed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "pagination",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573647,
                            "stop": 1737573647,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "search",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573647,
                            "stop": 1737573647,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "inclusion \\/ exclusion",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573647,
                            "stop": 1737573647,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "slug",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573647,
                            "stop": 1737573647,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "sku",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573647,
                            "stop": 1737573647,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "type",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573647,
                            "stop": 1737573647,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "featured",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573647,
                            "stop": 1737573647,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "categories",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573647,
                            "stop": 1737573647,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "on sale",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573647,
                            "stop": 1737573647,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "price",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573647,
                            "stop": 1737573647,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "before \\/ after",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573647,
                            "stop": 1737573647,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "attributes",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573647,
                            "stop": 1737573647,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "status",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573647,
                            "stop": 1737573647,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "shipping class",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573647,
                            "stop": 1737573647,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "tax class",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573647,
                            "stop": 1737573647,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "stock status",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573647,
                            "stop": 1737573647,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "tags",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573647,
                            "stop": 1737573647,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "parent",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573647,
                            "stop": 1737573647,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "default",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573647,
                            "stop": 1737573647,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products > orderby",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "date",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573647,
                            "stop": 1737573647,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products > orderby",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "id",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573647,
                            "stop": 1737573647,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products > orderby",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "title",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573647,
                            "stop": 1737573647,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products > orderby",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "slug orderby",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573647,
                            "stop": 1737573647,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products > orderby",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "price orderby",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573647,
                            "stop": 1737573647,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products > orderby",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "include",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573647,
                            "stop": 1737573647,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products > orderby",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "rating (desc)",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573647,
                            "stop": 1737573647,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
                            "retries": 1,
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
                            "duration": 0,
                            "start": 1737573647,
                            "stop": 1737573647,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
                            "retries": 1,
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
                            "duration": 0,
                            "start": 1737573647,
                            "stop": 1737573647,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
                            "retries": 1,
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
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573647,
                            "stop": 1737573647,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.js > Products API tests: List All Products > List all products > orderby",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add a simple product",
                            "status": "failed",
                            "duration": 176,
                            "start": 1737573648,
                            "stop": 1737573648,
                            "message": "SyntaxError: Unexpected end of JSON input",
                            "trace": "SyntaxError: Unexpected end of JSON input\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js:60:24",
                            "rawStatus": "failed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add a virtual product",
                            "status": "failed",
                            "duration": 195,
                            "start": 1737573655,
                            "stop": 1737573655,
                            "message": "SyntaxError: Unexpected end of JSON input",
                            "trace": "SyntaxError: Unexpected end of JSON input\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js:1245:24",
                            "rawStatus": "failed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can view a single product",
                            "status": "failed",
                            "duration": 181,
                            "start": 1737573665,
                            "stop": 1737573665,
                            "message": "SyntaxError: Unexpected end of JSON input",
                            "trace": "SyntaxError: Unexpected end of JSON input\\n    at Object.simpleTestProduct (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js:36:23)",
                            "rawStatus": "failed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update a single product",
                            "status": "failed",
                            "duration": 189,
                            "start": 1737573666,
                            "stop": 1737573667,
                            "message": "SyntaxError: Unexpected end of JSON input",
                            "trace": "SyntaxError: Unexpected end of JSON input\\n    at Object.simpleTestProduct (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js:36:23)",
                            "rawStatus": "failed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can delete a product",
                            "status": "failed",
                            "duration": 181,
                            "start": 1737573668,
                            "stop": 1737573668,
                            "message": "SyntaxError: Unexpected end of JSON input",
                            "trace": "SyntaxError: Unexpected end of JSON input\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js:1525:19",
                            "rawStatus": "failed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
                            "retries": 1,
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
                            "duration": 140,
                            "start": 1737573649,
                            "stop": 1737573649,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 71,
                            "start": 1737573649,
                            "stop": 1737573650,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 70,
                            "start": 1737573650,
                            "stop": 1737573650,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 72,
                            "start": 1737573650,
                            "stop": 1737573650,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 127,
                            "start": 1737573650,
                            "stop": 1737573650,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 318,
                            "start": 1737573650,
                            "stop": 1737573650,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 93,
                            "start": 1737573649,
                            "stop": 1737573649,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 80,
                            "start": 1737573649,
                            "stop": 1737573649,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 71,
                            "start": 1737573649,
                            "stop": 1737573649,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 74,
                            "start": 1737573649,
                            "stop": 1737573649,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 131,
                            "start": 1737573649,
                            "stop": 1737573649,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 335,
                            "start": 1737573649,
                            "stop": 1737573649,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 81,
                            "start": 1737573650,
                            "stop": 1737573650,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 68,
                            "start": 1737573650,
                            "stop": 1737573650,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 78,
                            "start": 1737573650,
                            "stop": 1737573650,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 79,
                            "start": 1737573650,
                            "stop": 1737573650,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 136,
                            "start": 1737573650,
                            "stop": 1737573651,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 368,
                            "start": 1737573651,
                            "stop": 1737573651,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "status": "failed",
                            "duration": 0,
                            "start": 1737573652,
                            "stop": 1737573652,
                            "message": "SyntaxError: Unexpected end of JSON input",
                            "trace": "SyntaxError: Unexpected end of JSON input\\n    at Object.simpleTestProduct (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js:36:23)",
                            "rawStatus": "failed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product review tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "cannot add a product review with invalid product_id",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573652,
                            "stop": 1737573652,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product review tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "cannot add a duplicate product review",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573652,
                            "stop": 1737573652,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product review tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve a product review",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573652,
                            "stop": 1737573652,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product review tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all product reviews",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573652,
                            "stop": 1737573652,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product review tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update a product review",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573652,
                            "stop": 1737573652,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product review tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can permanently delete a product review",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573652,
                            "stop": 1737573652,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product review tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can batch update product reviews",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573652,
                            "stop": 1737573652,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
                            "retries": 1,
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
                            "duration": 117,
                            "start": 1737573652,
                            "stop": 1737573652,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 89,
                            "start": 1737573652,
                            "stop": 1737573653,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 83,
                            "start": 1737573653,
                            "stop": 1737573653,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 91,
                            "start": 1737573653,
                            "stop": 1737573653,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 130,
                            "start": 1737573653,
                            "stop": 1737573653,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 344,
                            "start": 1737573653,
                            "stop": 1737573653,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 75,
                            "start": 1737573653,
                            "stop": 1737573653,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 66,
                            "start": 1737573653,
                            "stop": 1737573653,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 73,
                            "start": 1737573653,
                            "stop": 1737573653,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 74,
                            "start": 1737573653,
                            "stop": 1737573654,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 131,
                            "start": 1737573654,
                            "stop": 1737573654,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 389,
                            "start": 1737573654,
                            "stop": 1737573654,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "status": "failed",
                            "duration": 192,
                            "start": 1737573656,
                            "stop": 1737573656,
                            "message": "SyntaxError: Unexpected end of JSON input",
                            "trace": "SyntaxError: Unexpected end of JSON input\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js:1271:25",
                            "rawStatus": "failed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product variation tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add a product variation",
                            "status": "failed",
                            "duration": 113,
                            "start": 1737573657,
                            "stop": 1737573658,
                            "message": "Error: \\u001b[2mexpect(\\u001b[22m\\u001b[31mreceived\\u001b[39m\\u001b[2m).\\u001b[22mtoEqual\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m) \\/\\/ deep equality\\u001b[22m\\n\\nExpected: \\u001b[32m201\\u001b[39m\\nReceived: \\u001b[31m404\\u001b[39m",
                            "trace": "Error: \\u001b[2mexpect(\\u001b[22m\\u001b[31mreceived\\u001b[39m\\u001b[2m).\\u001b[22mtoEqual\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m) \\/\\/ deep equality\\u001b[22m\\n\\nExpected: \\u001b[32m201\\u001b[39m\\nReceived: \\u001b[31m404\\u001b[39m\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js:1296:32",
                            "rawStatus": "failed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product variation tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve a product variation",
                            "status": "failed",
                            "duration": 123,
                            "start": 1737573659,
                            "stop": 1737573659,
                            "message": "Error: \\u001b[2mexpect(\\u001b[22m\\u001b[31mreceived\\u001b[39m\\u001b[2m).\\u001b[22mtoEqual\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m) \\/\\/ deep equality\\u001b[22m\\n\\nExpected: \\u001b[32m200\\u001b[39m\\nReceived: \\u001b[31m404\\u001b[39m",
                            "trace": "Error: \\u001b[2mexpect(\\u001b[22m\\u001b[31mreceived\\u001b[39m\\u001b[2m).\\u001b[22mtoEqual\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m) \\/\\/ deep equality\\u001b[22m\\n\\nExpected: \\u001b[32m200\\u001b[39m\\nReceived: \\u001b[31m404\\u001b[39m\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js:1306:32",
                            "rawStatus": "failed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product variation tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all product variations",
                            "status": "failed",
                            "duration": 112,
                            "start": 1737573660,
                            "stop": 1737573660,
                            "message": "Error: \\u001b[2mexpect(\\u001b[22m\\u001b[31mreceived\\u001b[39m\\u001b[2m).\\u001b[22mtoEqual\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m) \\/\\/ deep equality\\u001b[22m\\n\\nExpected: \\u001b[32m200\\u001b[39m\\nReceived: \\u001b[31m404\\u001b[39m",
                            "trace": "Error: \\u001b[2mexpect(\\u001b[22m\\u001b[31mreceived\\u001b[39m\\u001b[2m).\\u001b[22mtoEqual\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m) \\/\\/ deep equality\\u001b[22m\\n\\nExpected: \\u001b[32m200\\u001b[39m\\nReceived: \\u001b[31m404\\u001b[39m\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js:1317:32",
                            "rawStatus": "failed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product variation tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update a product variation",
                            "status": "failed",
                            "duration": 109,
                            "start": 1737573661,
                            "stop": 1737573661,
                            "message": "Error: \\u001b[2mexpect(\\u001b[22m\\u001b[31mreceived\\u001b[39m\\u001b[2m).\\u001b[22mtoEqual\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m) \\/\\/ deep equality\\u001b[22m\\n\\nExpected: \\u001b[32m200\\u001b[39m\\nReceived: \\u001b[31m404\\u001b[39m",
                            "trace": "Error: \\u001b[2mexpect(\\u001b[22m\\u001b[31mreceived\\u001b[39m\\u001b[2m).\\u001b[22mtoEqual\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m) \\/\\/ deep equality\\u001b[22m\\n\\nExpected: \\u001b[32m200\\u001b[39m\\nReceived: \\u001b[31m404\\u001b[39m\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js:1333:32",
                            "rawStatus": "failed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product variation tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can permanently delete a product variation",
                            "status": "failed",
                            "duration": 104,
                            "start": 1737573662,
                            "stop": 1737573663,
                            "message": "Error: \\u001b[2mexpect(\\u001b[22m\\u001b[31mreceived\\u001b[39m\\u001b[2m).\\u001b[22mtoEqual\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m) \\/\\/ deep equality\\u001b[22m\\n\\nExpected: \\u001b[32m200\\u001b[39m\\nReceived: \\u001b[31m404\\u001b[39m",
                            "trace": "Error: \\u001b[2mexpect(\\u001b[22m\\u001b[31mreceived\\u001b[39m\\u001b[2m).\\u001b[22mtoEqual\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m) \\/\\/ deep equality\\u001b[22m\\n\\nExpected: \\u001b[32m200\\u001b[39m\\nReceived: \\u001b[31m404\\u001b[39m\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js:1350:32",
                            "rawStatus": "failed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product variation tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can batch update product variations",
                            "status": "failed",
                            "duration": 121,
                            "start": 1737573664,
                            "stop": 1737573664,
                            "message": "Error: \\u001b[2mexpect(\\u001b[22m\\u001b[31mreceived\\u001b[39m\\u001b[2m).\\u001b[22mtoEqual\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m) \\/\\/ deep equality\\u001b[22m\\n\\nExpected: \\u001b[32m200\\u001b[39m\\nReceived: \\u001b[31m404\\u001b[39m",
                            "trace": "Error: \\u001b[2mexpect(\\u001b[22m\\u001b[31mreceived\\u001b[39m\\u001b[2m).\\u001b[22mtoEqual\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m) \\/\\/ deep equality\\u001b[22m\\n\\nExpected: \\u001b[32m200\\u001b[39m\\nReceived: \\u001b[31m404\\u001b[39m\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js:1395:32",
                            "rawStatus": "failed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Product variation tests: CRUD",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can batch create products",
                            "status": "failed",
                            "duration": 188,
                            "start": 1737573669,
                            "stop": 1737573669,
                            "message": "SyntaxError: Unexpected end of JSON input",
                            "trace": "SyntaxError: Unexpected end of JSON input\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js:1570:25",
                            "rawStatus": "failed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Batch update products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can batch update products",
                            "status": "failed",
                            "duration": 124,
                            "start": 1737573671,
                            "stop": 1737573671,
                            "message": "Error: \\u001b[2mexpect(\\u001b[22m\\u001b[31mreceived\\u001b[39m\\u001b[2m).\\u001b[22mtoEqual\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m) \\/\\/ deep equality\\u001b[22m\\n\\nExpected: \\u001b[32mundefined\\u001b[39m\\nReceived: \\u001b[31mnull\\u001b[39m",
                            "trace": "Error: \\u001b[2mexpect(\\u001b[22m\\u001b[31mreceived\\u001b[39m\\u001b[2m).\\u001b[22mtoEqual\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m) \\/\\/ deep equality\\u001b[22m\\n\\nExpected: \\u001b[32mundefined\\u001b[39m\\nReceived: \\u001b[31mnull\\u001b[39m\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js:1615:18",
                            "rawStatus": "failed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Batch update products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can batch delete products",
                            "status": "failed",
                            "duration": 117,
                            "start": 1737573672,
                            "stop": 1737573672,
                            "message": "Error: \\u001b[2mexpect(\\u001b[22m\\u001b[31mreceived\\u001b[39m\\u001b[2m).\\u001b[22mtoHaveLength\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m)\\u001b[22m\\n\\n\\u001b[1mMatcher error\\u001b[22m: \\u001b[31mreceived\\u001b[39m value must have a length property whose value must be a number\\n\\nReceived has value: \\u001b[31mundefined\\u001b[39m",
                            "trace": "Error: \\u001b[2mexpect(\\u001b[22m\\u001b[31mreceived\\u001b[39m\\u001b[2m).\\u001b[22mtoHaveLength\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m)\\u001b[22m\\n\\n\\u001b[1mMatcher error\\u001b[22m: \\u001b[31mreceived\\u001b[39m value must have a length property whose value must be a number\\n\\nReceived has value: \\u001b[31mundefined\\u001b[39m\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js:1631:41",
                            "rawStatus": "failed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.js > Products API tests: CRUD > Batch update products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create a refund",
                            "status": "failed",
                            "duration": 0,
                            "start": 1737573673,
                            "stop": 1737573673,
                            "message": "SyntaxError: Unexpected end of JSON input",
                            "trace": "SyntaxError: Unexpected end of JSON input\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/refunds\\/refunds.test.js:23:37",
                            "rawStatus": "failed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/refunds\\/refunds.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/refunds\\/refunds.test.js > Refunds API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve a refund",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573674,
                            "stop": 1737573674,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/refunds\\/refunds.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/refunds\\/refunds.test.js > Refunds API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve refund info from refund endpoint",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573674,
                            "stop": 1737573674,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/refunds\\/refunds.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/refunds\\/refunds.test.js > Refunds API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can list all refunds",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573674,
                            "stop": 1737573674,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/refunds\\/refunds.test.js",
                            "retries": 1,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/refunds\\/refunds.test.js > Refunds API tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can delete a refund",
                            "status": "skipped",
                            "duration": 0,
                            "start": 1737573674,
                            "stop": 1737573674,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/refunds\\/refunds.test.js",
                            "retries": 1,
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
                            "duration": 135,
                            "start": 1737573674,
                            "stop": 1737573674,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/reports\\/reports-crud.test.js",
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
                            "duration": 81,
                            "start": 1737573674,
                            "stop": 1737573674,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/reports\\/reports-crud.test.js",
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
                            "duration": 76,
                            "start": 1737573674,
                            "stop": 1737573674,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/reports\\/reports-crud.test.js",
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
                            "duration": 76,
                            "start": 1737573674,
                            "stop": 1737573674,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/reports\\/reports-crud.test.js",
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
                            "duration": 69,
                            "start": 1737573674,
                            "stop": 1737573675,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/reports\\/reports-crud.test.js",
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
                            "duration": 76,
                            "start": 1737573675,
                            "stop": 1737573675,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/reports\\/reports-crud.test.js",
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
                            "duration": 74,
                            "start": 1737573675,
                            "stop": 1737573675,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/reports\\/reports-crud.test.js",
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
                            "duration": 73,
                            "start": 1737573675,
                            "stop": 1737573675,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/reports\\/reports-crud.test.js",
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
                            "duration": 85,
                            "start": 1737573675,
                            "stop": 1737573675,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/settings\\/settings-crud.test.js",
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
                            "duration": 98,
                            "start": 1737573675,
                            "stop": 1737573675,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/settings\\/settings-crud.test.js",
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
                            "duration": 71,
                            "start": 1737573675,
                            "stop": 1737573675,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/settings\\/settings-crud.test.js",
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
                            "duration": 72,
                            "start": 1737573675,
                            "stop": 1737573675,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/settings\\/settings-crud.test.js",
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
                            "duration": 258,
                            "start": 1737573675,
                            "stop": 1737573675,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/settings\\/settings-crud.test.js",
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
                            "duration": 107,
                            "start": 1737573675,
                            "stop": 1737573676,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/settings\\/settings-crud.test.js",
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
                            "duration": 85,
                            "start": 1737573676,
                            "stop": 1737573676,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/settings\\/settings-crud.test.js",
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
                            "duration": 85,
                            "start": 1737573676,
                            "stop": 1737573676,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/settings\\/settings-crud.test.js",
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
                            "duration": 67,
                            "start": 1737573676,
                            "stop": 1737573676,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/settings\\/settings-crud.test.js",
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
                            "duration": 86,
                            "start": 1737573676,
                            "stop": 1737573676,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/settings\\/settings-crud.test.js",
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
                            "duration": 88,
                            "start": 1737573676,
                            "stop": 1737573676,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/settings\\/settings-crud.test.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.js > Settings API tests: CRUD > List all Email settings options",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can retrieve all advanced settings",
                            "status": "passed",
                            "duration": 108,
                            "start": 1737573676,
                            "stop": 1737573676,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/settings\\/settings-crud.test.js",
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
                            "duration": 75,
                            "start": 1737573676,
                            "stop": 1737573676,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/settings\\/settings-crud.test.js",
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
                            "duration": 72,
                            "start": 1737573676,
                            "stop": 1737573676,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/settings\\/settings-crud.test.js",
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
                            "duration": 69,
                            "start": 1737573676,
                            "stop": 1737573676,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/settings\\/settings-crud.test.js",
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
                            "duration": 72,
                            "start": 1737573676,
                            "stop": 1737573676,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/settings\\/settings-crud.test.js",
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
                            "duration": 70,
                            "start": 1737573676,
                            "stop": 1737573676,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/settings\\/settings-crud.test.js",
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
                            "duration": 70,
                            "start": 1737573676,
                            "stop": 1737573676,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/settings\\/settings-crud.test.js",
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
                            "duration": 69,
                            "start": 1737573676,
                            "stop": 1737573677,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/settings\\/settings-crud.test.js",
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
                            "duration": 71,
                            "start": 1737573677,
                            "stop": 1737573677,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/settings\\/settings-crud.test.js",
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
                            "duration": 68,
                            "start": 1737573677,
                            "stop": 1737573677,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/settings\\/settings-crud.test.js",
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
                            "duration": 71,
                            "start": 1737573677,
                            "stop": 1737573677,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/settings\\/settings-crud.test.js",
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
                            "duration": 63,
                            "start": 1737573677,
                            "stop": 1737573677,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/shipping\\/shipping-method.test.js",
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
                            "duration": 71,
                            "start": 1737573677,
                            "stop": 1737573677,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/shipping\\/shipping-method.test.js",
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
                            "duration": 68,
                            "start": 1737573677,
                            "stop": 1737573677,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/shipping\\/shipping-method.test.js",
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
                            "duration": 64,
                            "start": 1737573677,
                            "stop": 1737573677,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/shipping\\/shipping-method.test.js",
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
                            "duration": 63,
                            "start": 1737573677,
                            "stop": 1737573677,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/shipping\\/shipping-method.test.js",
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
                            "duration": 144,
                            "start": 1737573677,
                            "stop": 1737573677,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/shipping\\/shipping-method.test.js",
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
                            "duration": 128,
                            "start": 1737573677,
                            "stop": 1737573677,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/shipping\\/shipping-method.test.js",
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
                            "duration": 129,
                            "start": 1737573677,
                            "stop": 1737573678,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/shipping\\/shipping-method.test.js",
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
                            "duration": 175,
                            "start": 1737573678,
                            "stop": 1737573678,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/shipping\\/shipping-zones.test.js",
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
                            "duration": 67,
                            "start": 1737573678,
                            "stop": 1737573678,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/shipping\\/shipping-zones.test.js",
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
                            "duration": 67,
                            "start": 1737573678,
                            "stop": 1737573678,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/shipping\\/shipping-zones.test.js",
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
                            "duration": 67,
                            "start": 1737573678,
                            "stop": 1737573678,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/shipping\\/shipping-zones.test.js",
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
                            "duration": 69,
                            "start": 1737573678,
                            "stop": 1737573678,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/shipping\\/shipping-zones.test.js",
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
                            "duration": 68,
                            "start": 1737573678,
                            "stop": 1737573678,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/shipping\\/shipping-zones.test.js",
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
                            "duration": 121,
                            "start": 1737573678,
                            "stop": 1737573678,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/shipping\\/shipping-zones.test.js",
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
                            "duration": 69,
                            "start": 1737573678,
                            "stop": 1737573678,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/shipping\\/shipping-zones.test.js",
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
                            "duration": 66,
                            "start": 1737573678,
                            "stop": 1737573678,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/shipping\\/shipping-zones.test.js",
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
                            "duration": 67,
                            "start": 1737573678,
                            "stop": 1737573678,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/shipping\\/shipping-zones.test.js",
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
                            "duration": 72,
                            "start": 1737573678,
                            "stop": 1737573678,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/system-status\\/system-status-crud.test.js",
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
                            "duration": 70,
                            "start": 1737573678,
                            "stop": 1737573679,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/system-status\\/system-status-crud.test.js",
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
                            "duration": 87,
                            "start": 1737573679,
                            "stop": 1737573679,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/system-status\\/system-status-crud.test.js",
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
                            "duration": 71,
                            "start": 1737573679,
                            "stop": 1737573679,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/taxes\\/tax-classes-crud.test.js",
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
                            "duration": 64,
                            "start": 1737573679,
                            "stop": 1737573679,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/taxes\\/tax-classes-crud.test.js",
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
                            "duration": 62,
                            "start": 1737573679,
                            "stop": 1737573679,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/taxes\\/tax-classes-crud.test.js",
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
                            "duration": 64,
                            "start": 1737573679,
                            "stop": 1737573679,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/taxes\\/tax-classes-crud.test.js",
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
                            "duration": 62,
                            "start": 1737573679,
                            "stop": 1737573679,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/taxes\\/tax-classes-crud.test.js",
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
                            "duration": 119,
                            "start": 1737573679,
                            "stop": 1737573679,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/taxes\\/tax-classes-crud.test.js",
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
                            "duration": 74,
                            "start": 1737573679,
                            "stop": 1737573679,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/taxes\\/tax-rates-crud.test.js",
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
                            "duration": 72,
                            "start": 1737573679,
                            "stop": 1737573679,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/taxes\\/tax-rates-crud.test.js",
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
                            "duration": 74,
                            "start": 1737573679,
                            "stop": 1737573679,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/taxes\\/tax-rates-crud.test.js",
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
                            "duration": 73,
                            "start": 1737573679,
                            "stop": 1737573679,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/taxes\\/tax-rates-crud.test.js",
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
                            "duration": 64,
                            "start": 1737573679,
                            "stop": 1737573679,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/taxes\\/tax-rates-crud.test.js",
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
                            "duration": 74,
                            "start": 1737573679,
                            "stop": 1737573680,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/taxes\\/tax-rates-crud.test.js",
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
                            "duration": 200,
                            "start": 1737573680,
                            "stop": 1737573680,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/taxes\\/tax-rates-crud.test.js",
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
                            "duration": 69,
                            "start": 1737573680,
                            "stop": 1737573680,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/taxes\\/tax-rates-crud.test.js",
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
                            "duration": 148,
                            "start": 1737573680,
                            "stop": 1737573680,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/taxes\\/tax-rates-crud.test.js",
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
                            "duration": 112,
                            "start": 1737573680,
                            "stop": 1737573680,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/webhooks\\/webhooks-crud.test.js",
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
                            "duration": 71,
                            "start": 1737573680,
                            "stop": 1737573680,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/webhooks\\/webhooks-crud.test.js",
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
                            "duration": 65,
                            "start": 1737573680,
                            "stop": 1737573680,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/webhooks\\/webhooks-crud.test.js",
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
                            "duration": 68,
                            "start": 1737573680,
                            "stop": 1737573680,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/webhooks\\/webhooks-crud.test.js",
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
                            "duration": 122,
                            "start": 1737573680,
                            "stop": 1737573680,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/webhooks\\/webhooks-crud.test.js",
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
                            "duration": 105,
                            "start": 1737573680,
                            "stop": 1737573681,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/webhooks\\/webhooks-crud.test.js",
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
                            "duration": 167,
                            "start": 1737573681,
                            "stop": 1737573681,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/webhooks\\/webhooks-crud.test.js",
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
                            "duration": 183,
                            "start": 1737573681,
                            "stop": 1737573681,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/webhooks\\/webhooks-crud.test.js",
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
            "debug_log": [
                {
                    "count": "0",
                    "message": "Debug log is ignored for woo-e2e\\/delete_products tests."
                }
            ]
        }
    ]
]';
