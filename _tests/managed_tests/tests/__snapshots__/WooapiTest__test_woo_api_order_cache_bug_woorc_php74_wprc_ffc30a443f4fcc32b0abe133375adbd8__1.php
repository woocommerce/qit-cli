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
            "event": "cli_development_extension_test",
            "optional_features": {
                "hpos": true,
                "new_product_editor": false
            },
            "test_results_manager_url": "https:\\/\\/test-results-manager.com",
            "test_results_manager_expiration": 1234567890,
            "test_summary": "Tests: 265 total, 260 passed, 0 failed, 5 skipped",
            "version": "Undefined",
            "update_complete": true,
            "malware_whitelist_paths": [],
            "workflow_id": "1234567890",
            "runner": "normalized",
            "test_media": [],
            "extension_set": "",
            "phpstan_level": null,
            "test_variation": "",
            "test_packages": [],
            "test_group_id": "",
            "created_at": "2025-01-01 00:00:00",
            "test_result_json_extracted": "{EXTRACTED}",
            "ctrf_json_extracted": "{EXTRACTED}",
            "debug_log_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "numFailedTestSuites": 0,
                "numPassedTestSuites": 19,
                "numPendingTestSuites": 4,
                "numTotalTestSuites": 23,
                "numFailedTests": 0,
                "numPassedTests": 260,
                "numPendingTests": 5,
                "numTotalTests": 265,
                "testResults": [
                    {
                        "file": "..\\/fixtures\\/install-wc.setup.ts",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Install WC using WC Beta Tester": [
                                {
                                    "title": "Install WC using WC Beta Tester",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "..\\/fixtures\\/auth.setup.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "authenticate users": [
                                {
                                    "title": "authenticate users",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "..\\/fixtures\\/site.setup.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "setup site": [
                                {
                                    "title": "setup site",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "api-tests\\/coupons\\/coupons.test.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Coupons API tests": [
                                {
                                    "title": "can create a coupon",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve a coupon",
                                    "status": "passed"
                                },
                                {
                                    "title": "can update a coupon",
                                    "status": "passed"
                                },
                                {
                                    "title": "can permanently delete a coupon",
                                    "status": "passed"
                                }
                            ],
                            "Batch update coupons": [
                                {
                                    "title": "can batch create coupons",
                                    "status": "passed"
                                },
                                {
                                    "title": "can batch update coupons",
                                    "status": "passed"
                                },
                                {
                                    "title": "can batch delete coupons",
                                    "status": "passed"
                                }
                            ],
                            "List coupons": [
                                {
                                    "title": "can list all coupons by default",
                                    "status": "passed"
                                },
                                {
                                    "title": "can limit result set to matching code",
                                    "status": "passed"
                                },
                                {
                                    "title": "can paginate results",
                                    "status": "passed"
                                },
                                {
                                    "title": "can limit results to matching string",
                                    "status": "passed"
                                }
                            ],
                            "Add coupon to order": [
                                {
                                    "title": "can add coupon to an order",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "api-tests\\/customers\\/customers-crud.test.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Customers API tests: CRUD": [],
                            "Customers API tests: CRUD > Retrieve after env setup": [
                                {
                                    "title": "can retrieve admin user",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve subscriber user",
                                    "status": "passed"
                                },
                                {
                                    "title": "retrieve user with id 0 is invalid",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve customers",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve all customers",
                                    "status": "passed"
                                }
                            ],
                            "Customers API tests: CRUD > Create a customer": [
                                {
                                    "title": "can create a customer",
                                    "status": "passed"
                                }
                            ],
                            "Customers API tests: CRUD > Retrieve after create": [
                                {
                                    "title": "can retrieve a customer",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve all customers after create",
                                    "status": "passed"
                                }
                            ],
                            "Customers API tests: CRUD > Update a customer": [
                                {
                                    "title": "can update the admin user\\/customer",
                                    "status": "passed"
                                },
                                {
                                    "title": "retrieve after update admin",
                                    "status": "passed"
                                },
                                {
                                    "title": "can update the subscriber user\\/customer",
                                    "status": "passed"
                                },
                                {
                                    "title": "retrieve after update subscriber",
                                    "status": "passed"
                                },
                                {
                                    "title": "can update a customer",
                                    "status": "passed"
                                },
                                {
                                    "title": "retrieve after update customer",
                                    "status": "passed"
                                }
                            ],
                            "Customers API tests: CRUD > Delete a customer": [
                                {
                                    "title": "can permanently delete an customer",
                                    "status": "passed"
                                }
                            ],
                            "Customers API tests: CRUD > Batch update customers": [
                                {
                                    "title": "can batch create customers",
                                    "status": "passed"
                                },
                                {
                                    "title": "can batch update customers",
                                    "status": "passed"
                                },
                                {
                                    "title": "can batch delete customers",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "api-tests\\/data\\/data-crud.test.ts",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Data API tests": [
                                {
                                    "title": "can list all data",
                                    "status": "passed"
                                },
                                {
                                    "title": "can view all continents",
                                    "status": "passed"
                                },
                                {
                                    "title": "can view continent data",
                                    "status": "passed"
                                },
                                {
                                    "title": "can view country data",
                                    "status": "pending"
                                },
                                {
                                    "title": "can view all currencies",
                                    "status": "passed"
                                },
                                {
                                    "title": "can view currency data",
                                    "status": "passed"
                                },
                                {
                                    "title": "can view current currency",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "api-tests\\/hello\\/hello.test.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Test API connectivity": [
                                {
                                    "title": "can access a non-authenticated endpoint",
                                    "status": "passed"
                                },
                                {
                                    "title": "can access an authenticated endpoint",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "api-tests\\/orders\\/order-complex.test.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Orders API test": [
                                {
                                    "title": "can add complex order",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "api-tests\\/orders\\/order-search.test.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Order Search API tests": [
                                {
                                    "title": "can search by billing first name",
                                    "status": "passed"
                                },
                                {
                                    "title": "can search by billing company name",
                                    "status": "passed"
                                },
                                {
                                    "title": "can search by billing address 2",
                                    "status": "passed"
                                },
                                {
                                    "title": "can search by billing city name",
                                    "status": "passed"
                                },
                                {
                                    "title": "can search by billing post code",
                                    "status": "passed"
                                },
                                {
                                    "title": "can search by billing phone",
                                    "status": "passed"
                                },
                                {
                                    "title": "can search by billing state",
                                    "status": "passed"
                                },
                                {
                                    "title": "can search by shipping first name",
                                    "status": "passed"
                                },
                                {
                                    "title": "can search by shipping last name",
                                    "status": "passed"
                                },
                                {
                                    "title": "can search by shipping address 2",
                                    "status": "passed"
                                },
                                {
                                    "title": "can search by shipping city",
                                    "status": "passed"
                                },
                                {
                                    "title": "can search by shipping post code",
                                    "status": "passed"
                                },
                                {
                                    "title": "can search by shipping state",
                                    "status": "passed"
                                },
                                {
                                    "title": "can search by orderId",
                                    "status": "passed"
                                },
                                {
                                    "title": "can return an empty result set when no matches were found",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "api-tests\\/orders\\/orders-crud.test.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Orders API tests: CRUD": [],
                            "Orders API tests: CRUD > Create an order": [
                                {
                                    "title": "can create a pending order by default",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create an order with status pending",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create an order with status processing",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create an order with status on-hold",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create an order with status completed",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create an order with status cancelled",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create an order with status refunded",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create an order with status failed",
                                    "status": "passed"
                                }
                            ],
                            "Orders API tests: CRUD > Create an order > Order Notes tests": [
                                {
                                    "title": "can create a order note",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve an order note",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve all order notes",
                                    "status": "passed"
                                },
                                {
                                    "title": "cannot update an order note",
                                    "status": "passed"
                                },
                                {
                                    "title": "can permanently delete an order note",
                                    "status": "passed"
                                }
                            ],
                            "Orders API tests: CRUD > Retrieve an order": [
                                {
                                    "title": "can retrieve an order",
                                    "status": "passed"
                                }
                            ],
                            "Orders API tests: CRUD > Update an order": [
                                {
                                    "title": "can update status of an order to pending",
                                    "status": "passed"
                                },
                                {
                                    "title": "can update status of an order to processing",
                                    "status": "passed"
                                },
                                {
                                    "title": "can update status of an order to on-hold",
                                    "status": "passed"
                                },
                                {
                                    "title": "can update status of an order to completed",
                                    "status": "passed"
                                },
                                {
                                    "title": "can update status of an order to cancelled",
                                    "status": "passed"
                                },
                                {
                                    "title": "can update status of an order to refunded",
                                    "status": "passed"
                                },
                                {
                                    "title": "can update status of an order to failed",
                                    "status": "passed"
                                },
                                {
                                    "title": "can add shipping and billing contacts to an order",
                                    "status": "passed"
                                },
                                {
                                    "title": "can add a product to an order",
                                    "status": "passed"
                                },
                                {
                                    "title": "can pay for an order",
                                    "status": "passed"
                                }
                            ],
                            "Orders API tests: CRUD > Delete an order": [
                                {
                                    "title": "can permanently delete an order",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "api-tests\\/orders\\/orders.test.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Orders API tests": [
                                {
                                    "title": "can create an order",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve an order",
                                    "status": "passed"
                                },
                                {
                                    "title": "can add shipping and billing contacts to an order",
                                    "status": "passed"
                                },
                                {
                                    "title": "can permanently delete an order",
                                    "status": "passed"
                                }
                            ],
                            "Orders API tests > List all orders": [
                                {
                                    "title": "pagination",
                                    "status": "passed"
                                },
                                {
                                    "title": "inclusion \\/ exclusion",
                                    "status": "passed"
                                },
                                {
                                    "title": "parent",
                                    "status": "passed"
                                },
                                {
                                    "title": "status",
                                    "status": "passed"
                                },
                                {
                                    "title": "customer",
                                    "status": "passed"
                                },
                                {
                                    "title": "product",
                                    "status": "passed"
                                },
                                {
                                    "title": "dp (precision)",
                                    "status": "passed"
                                },
                                {
                                    "title": "search",
                                    "status": "passed"
                                }
                            ],
                            "Orders API tests > orderby": [
                                {
                                    "title": "default",
                                    "status": "passed"
                                },
                                {
                                    "title": "date",
                                    "status": "passed"
                                },
                                {
                                    "title": "id",
                                    "status": "passed"
                                },
                                {
                                    "title": "include",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "api-tests\\/payment-gateways\\/payment-gateways-crud.test.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Payment Gateways API tests": [
                                {
                                    "title": "can view all payment gateways",
                                    "status": "passed"
                                },
                                {
                                    "title": "can view a payment gateway",
                                    "status": "passed"
                                },
                                {
                                    "title": "can update a payment gateway",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "api-tests\\/products\\/product-list.test.ts",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Products API tests: List All Products": [],
                            "Products API tests: List All Products > List all products": [
                                {
                                    "title": "defaults",
                                    "status": "passed"
                                },
                                {
                                    "title": "pagination",
                                    "status": "passed"
                                },
                                {
                                    "title": "search",
                                    "status": "passed"
                                },
                                {
                                    "title": "inclusion \\/ exclusion",
                                    "status": "passed"
                                },
                                {
                                    "title": "slug",
                                    "status": "passed"
                                },
                                {
                                    "title": "sku",
                                    "status": "passed"
                                },
                                {
                                    "title": "type",
                                    "status": "passed"
                                },
                                {
                                    "title": "featured",
                                    "status": "passed"
                                },
                                {
                                    "title": "categories",
                                    "status": "passed"
                                },
                                {
                                    "title": "on sale",
                                    "status": "passed"
                                },
                                {
                                    "title": "price",
                                    "status": "passed"
                                },
                                {
                                    "title": "before \\/ after",
                                    "status": "passed"
                                },
                                {
                                    "title": "attributes",
                                    "status": "passed"
                                },
                                {
                                    "title": "status",
                                    "status": "passed"
                                },
                                {
                                    "title": "shipping class",
                                    "status": "passed"
                                },
                                {
                                    "title": "tax class",
                                    "status": "passed"
                                },
                                {
                                    "title": "stock status",
                                    "status": "passed"
                                },
                                {
                                    "title": "tags",
                                    "status": "passed"
                                },
                                {
                                    "title": "parent",
                                    "status": "passed"
                                }
                            ],
                            "Products API tests: List All Products > List all products > orderby": [
                                {
                                    "title": "default",
                                    "status": "passed"
                                },
                                {
                                    "title": "date",
                                    "status": "passed"
                                },
                                {
                                    "title": "id",
                                    "status": "passed"
                                },
                                {
                                    "title": "title",
                                    "status": "passed"
                                },
                                {
                                    "title": "slug orderby",
                                    "status": "passed"
                                },
                                {
                                    "title": "price orderby",
                                    "status": "passed"
                                },
                                {
                                    "title": "include",
                                    "status": "passed"
                                },
                                {
                                    "title": "rating (desc)",
                                    "status": "passed"
                                },
                                {
                                    "title": "rating (asc)",
                                    "status": "pending"
                                },
                                {
                                    "title": "popularity (asc)",
                                    "status": "pending"
                                },
                                {
                                    "title": "popularity (desc)",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "api-tests\\/products\\/products-crud.test.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Products API tests: CRUD": [
                                {
                                    "title": "can add a simple product",
                                    "status": "passed"
                                },
                                {
                                    "title": "can add a virtual product",
                                    "status": "passed"
                                },
                                {
                                    "title": "can view a single product",
                                    "status": "passed"
                                },
                                {
                                    "title": "can update a single product",
                                    "status": "passed"
                                },
                                {
                                    "title": "can delete a product",
                                    "status": "passed"
                                }
                            ],
                            "Products API tests: CRUD > Product attributes tests: CRUD": [
                                {
                                    "title": "can add a product attribute",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve a product attribute",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve all product attribute",
                                    "status": "passed"
                                },
                                {
                                    "title": "can update a product attribute",
                                    "status": "passed"
                                },
                                {
                                    "title": "can permanently delete a product attribute",
                                    "status": "passed"
                                },
                                {
                                    "title": "can batch update product attributes",
                                    "status": "passed"
                                }
                            ],
                            "Products API tests: CRUD > Product attributes tests: CRUD > Product attribute terms tests: CRUD": [
                                {
                                    "title": "can add a product attribute term",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve a product attribute term",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve all product attribute terms",
                                    "status": "passed"
                                },
                                {
                                    "title": "can update a product attribute term",
                                    "status": "passed"
                                },
                                {
                                    "title": "can permanently delete a product attribute term",
                                    "status": "passed"
                                },
                                {
                                    "title": "can batch update product attribute terms",
                                    "status": "passed"
                                }
                            ],
                            "Products API tests: CRUD > Product categories tests: CRUD": [
                                {
                                    "title": "can add a product category",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve a product category",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve all product categories",
                                    "status": "passed"
                                },
                                {
                                    "title": "can update a product category",
                                    "status": "passed"
                                },
                                {
                                    "title": "can permanently delete a product category",
                                    "status": "passed"
                                },
                                {
                                    "title": "can batch update product categories",
                                    "status": "passed"
                                }
                            ],
                            "Products API tests: CRUD > Product review tests: CRUD": [
                                {
                                    "title": "can add a product review",
                                    "status": "passed"
                                },
                                {
                                    "title": "cannot add a product review with invalid product_id",
                                    "status": "passed"
                                },
                                {
                                    "title": "cannot add a duplicate product review",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve a product review",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve all product reviews",
                                    "status": "passed"
                                },
                                {
                                    "title": "can update a product review",
                                    "status": "passed"
                                },
                                {
                                    "title": "can permanently delete a product review",
                                    "status": "passed"
                                },
                                {
                                    "title": "can batch update product reviews",
                                    "status": "passed"
                                }
                            ],
                            "Products API tests: CRUD > Product shipping classes tests: CRUD": [
                                {
                                    "title": "can add a product shipping class",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve a product shipping class",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve all product shipping classes",
                                    "status": "passed"
                                },
                                {
                                    "title": "can update a product shipping class",
                                    "status": "passed"
                                },
                                {
                                    "title": "can permanently delete a product shipping class",
                                    "status": "passed"
                                },
                                {
                                    "title": "can batch update product shipping classes",
                                    "status": "passed"
                                }
                            ],
                            "Products API tests: CRUD > Product tags tests: CRUD": [
                                {
                                    "title": "can add a product tag",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve a product tag",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve all product tags",
                                    "status": "passed"
                                },
                                {
                                    "title": "can update a product tag",
                                    "status": "passed"
                                },
                                {
                                    "title": "can permanently delete a product tag",
                                    "status": "passed"
                                },
                                {
                                    "title": "can batch update product tags",
                                    "status": "passed"
                                }
                            ],
                            "Products API tests: CRUD > Product variation tests: CRUD": [
                                {
                                    "title": "can add a variable product",
                                    "status": "passed"
                                },
                                {
                                    "title": "can add a product variation",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve a product variation",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve all product variations",
                                    "status": "passed"
                                },
                                {
                                    "title": "can update a product variation",
                                    "status": "passed"
                                },
                                {
                                    "title": "can permanently delete a product variation",
                                    "status": "passed"
                                },
                                {
                                    "title": "can batch update product variations",
                                    "status": "passed"
                                }
                            ],
                            "Products API tests: CRUD > Batch update products": [
                                {
                                    "title": "can batch create products",
                                    "status": "passed"
                                },
                                {
                                    "title": "can batch update products",
                                    "status": "passed"
                                },
                                {
                                    "title": "can batch delete products",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "api-tests\\/refunds\\/refunds.test.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Refunds API tests": [
                                {
                                    "title": "can create a refund",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve a refund",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve refund info from refund endpoint",
                                    "status": "passed"
                                },
                                {
                                    "title": "can list all refunds",
                                    "status": "passed"
                                },
                                {
                                    "title": "can delete a refund",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "api-tests\\/reports\\/reports-crud.test.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Reports API tests": [
                                {
                                    "title": "can view all reports",
                                    "status": "passed"
                                },
                                {
                                    "title": "can view sales reports",
                                    "status": "passed"
                                },
                                {
                                    "title": "can view top sellers reports",
                                    "status": "passed"
                                },
                                {
                                    "title": "can view coupons totals",
                                    "status": "passed"
                                },
                                {
                                    "title": "can view customers totals",
                                    "status": "passed"
                                },
                                {
                                    "title": "can view orders totals",
                                    "status": "passed"
                                },
                                {
                                    "title": "can view products totals",
                                    "status": "passed"
                                },
                                {
                                    "title": "can view reviews totals",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "api-tests\\/settings\\/settings-crud.test.ts",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Settings API tests: CRUD": [],
                            "Settings API tests: CRUD > List all settings groups": [
                                {
                                    "title": "can retrieve all settings groups",
                                    "status": "passed"
                                }
                            ],
                            "Settings API tests: CRUD > List all settings options": [
                                {
                                    "title": "can retrieve all general settings",
                                    "status": "passed"
                                }
                            ],
                            "Settings API tests: CRUD > Retrieve a settings option": [
                                {
                                    "title": "can retrieve a settings option",
                                    "status": "passed"
                                }
                            ],
                            "Settings API tests: CRUD > Update a settings option": [
                                {
                                    "title": "can update a settings option",
                                    "status": "passed"
                                }
                            ],
                            "Settings API tests: CRUD > Batch Update a settings option": [
                                {
                                    "title": "can batch update settings options",
                                    "status": "passed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Products settings options": [
                                {
                                    "title": "can retrieve all products settings",
                                    "status": "passed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Tax settings options": [
                                {
                                    "title": "can retrieve all tax settings",
                                    "status": "passed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Shipping settings options": [
                                {
                                    "title": "can retrieve all shipping settings",
                                    "status": "passed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Checkout settings options": [
                                {
                                    "title": "can retrieve all checkout settings",
                                    "status": "passed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Account settings options": [
                                {
                                    "title": "can retrieve all account settings",
                                    "status": "passed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email settings options": [
                                {
                                    "title": "can retrieve all email settings",
                                    "status": "pending"
                                }
                            ],
                            "Settings API tests: CRUD > List all Advanced settings options": [
                                {
                                    "title": "can retrieve all advanced settings",
                                    "status": "passed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email New Order settings": [
                                {
                                    "title": "can retrieve all email new order settings",
                                    "status": "passed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email Failed Order settings": [
                                {
                                    "title": "can retrieve all email failed order settings",
                                    "status": "passed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email Customer On Hold Order settings": [
                                {
                                    "title": "can retrieve all email customer on hold order settings",
                                    "status": "passed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email Customer Processing Order settings": [
                                {
                                    "title": "can retrieve all email customer processing order settings",
                                    "status": "passed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email Customer Completed Order settings": [
                                {
                                    "title": "can retrieve all email customer completed order settings",
                                    "status": "passed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email Customer Refunded Order settings": [
                                {
                                    "title": "can retrieve all email customer refunded order settings",
                                    "status": "passed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email Customer Invoice settings": [
                                {
                                    "title": "can retrieve all email customer invoice settings",
                                    "status": "passed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email Customer Note settings": [
                                {
                                    "title": "can retrieve all email customer note settings",
                                    "status": "passed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email Customer Reset Password settings": [
                                {
                                    "title": "can retrieve all email customer reset password settings",
                                    "status": "passed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email Customer New Account settings": [
                                {
                                    "title": "can retrieve all email customer new account settings",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "api-tests\\/shipping\\/shipping-method.test.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Shipping methods API tests": [
                                {
                                    "title": "cannot create a shipping method",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve all shipping methods",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve a shipping method",
                                    "status": "passed"
                                },
                                {
                                    "title": "cannot update a shipping method",
                                    "status": "passed"
                                },
                                {
                                    "title": "cannot delete a shipping method",
                                    "status": "passed"
                                },
                                {
                                    "title": "can add a Flat rate shipping method",
                                    "status": "passed"
                                },
                                {
                                    "title": "can add a Free shipping shipping method",
                                    "status": "passed"
                                },
                                {
                                    "title": "can add a Local pickup shipping method",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "api-tests\\/shipping\\/shipping-zones.test.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Shipping zones API tests": [
                                {
                                    "title": "cannot delete the default shipping zone \\"Locations not covered by your other zones\\"",
                                    "status": "passed"
                                },
                                {
                                    "title": "cannot update the default shipping zone",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create a shipping zone",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve a shipping zone",
                                    "status": "passed"
                                },
                                {
                                    "title": "can list all shipping zones",
                                    "status": "passed"
                                },
                                {
                                    "title": "can update a shipping zone",
                                    "status": "passed"
                                },
                                {
                                    "title": "can add a shipping region to a shipping zone",
                                    "status": "passed"
                                },
                                {
                                    "title": "can update a shipping region on a shipping zone",
                                    "status": "passed"
                                },
                                {
                                    "title": "can clear\\/delete a shipping region on a shipping zone",
                                    "status": "passed"
                                },
                                {
                                    "title": "can delete a shipping zone",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "api-tests\\/system-status\\/system-status-crud.test.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "System Status API tests": [
                                {
                                    "title": "can view all system status items",
                                    "status": "passed"
                                },
                                {
                                    "title": "can view all system status tools",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve a system status tool",
                                    "status": "passed"
                                },
                                {
                                    "title": "can run a tool from system status",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "api-tests\\/taxes\\/tax-classes-crud.test.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Tax Classes API tests: CRUD": [],
                            "Tax Classes API tests: CRUD > Create a tax class": [
                                {
                                    "title": "can enable tax calculations",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create a tax class",
                                    "status": "passed"
                                }
                            ],
                            "Tax Classes API tests: CRUD > Retrieve after create": [
                                {
                                    "title": "can retrieve a tax class",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve all tax classes",
                                    "status": "passed"
                                }
                            ],
                            "Tax Classes API tests: CRUD > Update a tax class": [
                                {
                                    "title": "cannot update a tax class",
                                    "status": "passed"
                                }
                            ],
                            "Tax Classes API tests: CRUD > Delete a tax class": [
                                {
                                    "title": "can permanently delete a tax class",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "api-tests\\/taxes\\/tax-rates-crud.test.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Tax Rates API tests: CRUD": [],
                            "Tax Rates API tests: CRUD > Create a tax rate": [
                                {
                                    "title": "can create a tax rate",
                                    "status": "passed"
                                }
                            ],
                            "Tax Rates API tests: CRUD > Retrieve after create": [
                                {
                                    "title": "can retrieve a tax rate",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve all tax rates",
                                    "status": "passed"
                                }
                            ],
                            "Tax Rates API tests: CRUD > Update a tax rate": [
                                {
                                    "title": "can update a tax rate",
                                    "status": "passed"
                                },
                                {
                                    "title": "retrieve after update tax rate",
                                    "status": "passed"
                                }
                            ],
                            "Tax Rates API tests: CRUD > Delete a tax rate": [
                                {
                                    "title": "can permanently delete a tax rate",
                                    "status": "passed"
                                }
                            ],
                            "Tax Rates API tests: CRUD > Batch tax rate operations": [
                                {
                                    "title": "can batch create tax rates",
                                    "status": "passed"
                                },
                                {
                                    "title": "can batch update tax rates",
                                    "status": "passed"
                                },
                                {
                                    "title": "can batch delete tax rates",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "api-tests\\/webhooks\\/webhooks-crud.test.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Webhooks API tests": [],
                            "Webhooks API tests > Create a webhook": [
                                {
                                    "title": "can create a webhook",
                                    "status": "passed"
                                }
                            ],
                            "Webhooks API tests > Retrieve after create": [
                                {
                                    "title": "can retrieve a webhook",
                                    "status": "passed"
                                },
                                {
                                    "title": "can retrieve all webhooks",
                                    "status": "passed"
                                }
                            ],
                            "Webhooks API tests > Update a webhook": [
                                {
                                    "title": "can update a web hook",
                                    "status": "passed"
                                }
                            ],
                            "Webhooks API tests > Delete a webhook": [
                                {
                                    "title": "can permanently delete a webhook",
                                    "status": "passed"
                                }
                            ],
                            "Webhooks API tests > Batch webhook operations": [
                                {
                                    "title": "can batch create webhooks",
                                    "status": "passed"
                                },
                                {
                                    "title": "can batch update webhooks",
                                    "status": "passed"
                                },
                                {
                                    "title": "can batch delete webhooks",
                                    "status": "passed"
                                }
                            ]
                        }
                    }
                ],
                "summary": "265 total, 260 passed, 0 failed, 5 skipped."
            }
        },
        {
            "ctrf_json": {
                "reportFormat": "CTRF",
                "specVersion": "0.0.0",
                "reportId": "normalized-report-id",
                "timestamp": "2025-01-01T00:00:00.000Z",
                "generatedBy": "playwright-ctrf-json-reporter",
                "results": {
                    "tool": {
                        "name": "playwright"
                    },
                    "summary": {
                        "tests": 265,
                        "passed": 260,
                        "failed": 0,
                        "pending": 0,
                        "skipped": 5,
                        "other": 0,
                        "start": 1111111111,
                        "stop": 2222222222,
                        "suites": 0
                    },
                    "tests": [
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
                            "stdout": [],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "description": "Skipping installing WC using WC Beta Tester; INSTALL_WC not found.",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/fixtures\\/install-wc.setup.ts",
                                            "line": 23,
                                            "column": 8
                                        }
                                    }
                                ]
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
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
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
                                }
                            ],
                            "suite": "site setup > ..\\/fixtures\\/site.setup.ts",
                            "attachments": [],
                            "stdout": [
                                "DISABLE_HPOS: undefined\\n",
                                "Trying to switch on HPOS...\\n",
                                "HPOS Switched on successfully\\n",
                                "HPOS configuration (woocommerce_custom_orders_table_enabled): yes - High-performance order storage (recommended)\\n"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Failed to update onboarding profile: \\u001b[90mundefined\\u001b[39m\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/coupons.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/coupons\\/coupons.test.ts > Coupons API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/coupons.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/coupons\\/coupons.test.ts > Coupons API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/coupons.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/coupons\\/coupons.test.ts > Coupons API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/coupons.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/coupons\\/coupons.test.ts > Coupons API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/coupons.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/coupons\\/coupons.test.ts > Batch update coupons",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/coupons.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/coupons\\/coupons.test.ts > Batch update coupons",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/coupons.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/coupons\\/coupons.test.ts > Batch update coupons",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/coupons.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/coupons\\/coupons.test.ts > List coupons",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/coupons.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/coupons\\/coupons.test.ts > List coupons",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/coupons.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/coupons\\/coupons.test.ts > List coupons",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/coupons.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/coupons\\/coupons.test.ts > List coupons",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/coupons.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/coupons\\/coupons.test.ts > Add coupon to order",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.ts > Customers API tests: CRUD > Retrieve after env setup",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.ts > Customers API tests: CRUD > Retrieve after env setup",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.ts > Customers API tests: CRUD > Retrieve after env setup",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.ts > Customers API tests: CRUD > Retrieve after env setup",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.ts > Customers API tests: CRUD > Retrieve after env setup",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.ts > Customers API tests: CRUD > Create a customer",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.ts > Customers API tests: CRUD > Retrieve after create",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.ts > Customers API tests: CRUD > Retrieve after create",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.ts > Customers API tests: CRUD > Update a customer",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.ts > Customers API tests: CRUD > Update a customer",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.ts > Customers API tests: CRUD > Update a customer",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.ts > Customers API tests: CRUD > Update a customer",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.ts > Customers API tests: CRUD > Update a customer",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.ts > Customers API tests: CRUD > Update a customer",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.ts > Customers API tests: CRUD > Delete a customer",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.ts > Customers API tests: CRUD > Batch update customers",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.ts > Customers API tests: CRUD > Batch update customers",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/customers-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/customers\\/customers-crud.test.ts > Customers API tests: CRUD > Batch update customers",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/data-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/data\\/data-crud.test.ts > Data API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can view all continents",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/data-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/data\\/data-crud.test.ts > Data API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can view continent data",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/data-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/data\\/data-crud.test.ts > Data API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/data-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/data\\/data-crud.test.ts > Data API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/data\\/data-crud.test.ts",
                                            "line": 3995,
                                            "column": 7
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can view all currencies",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/data-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/data\\/data-crud.test.ts > Data API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/data-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/data\\/data-crud.test.ts > Data API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/data-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/data\\/data-crud.test.ts > Data API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/hello.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/hello\\/hello.test.ts > Test API connectivity",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/hello.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/hello\\/hello.test.ts > Test API connectivity",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/order-complex.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-complex.test.ts > Orders API test",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/order-search.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.ts > Order Search API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/order-search.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.ts > Order Search API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/order-search.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.ts > Order Search API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/order-search.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.ts > Order Search API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/order-search.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.ts > Order Search API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/order-search.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.ts > Order Search API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/order-search.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.ts > Order Search API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/order-search.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.ts > Order Search API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/order-search.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.ts > Order Search API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/order-search.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.ts > Order Search API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/order-search.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.ts > Order Search API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/order-search.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.ts > Order Search API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/order-search.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.ts > Order Search API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/order-search.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.ts > Order Search API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/order-search.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/order-search.test.ts > Order Search API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.ts > Orders API tests: CRUD > Create an order",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.ts > Orders API tests: CRUD > Create an order",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.ts > Orders API tests: CRUD > Create an order",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.ts > Orders API tests: CRUD > Create an order",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.ts > Orders API tests: CRUD > Create an order",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.ts > Orders API tests: CRUD > Create an order",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.ts > Orders API tests: CRUD > Create an order",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.ts > Orders API tests: CRUD > Create an order",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.ts > Orders API tests: CRUD > Create an order > Order Notes tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.ts > Orders API tests: CRUD > Create an order > Order Notes tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.ts > Orders API tests: CRUD > Create an order > Order Notes tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.ts > Orders API tests: CRUD > Create an order > Order Notes tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.ts > Orders API tests: CRUD > Create an order > Order Notes tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.ts > Orders API tests: CRUD > Retrieve an order",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.ts > Orders API tests: CRUD > Update an order",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.ts > Orders API tests: CRUD > Update an order",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.ts > Orders API tests: CRUD > Update an order",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.ts > Orders API tests: CRUD > Update an order",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.ts > Orders API tests: CRUD > Update an order",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.ts > Orders API tests: CRUD > Update an order",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.ts > Orders API tests: CRUD > Update an order",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.ts > Orders API tests: CRUD > Update an order",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.ts > Orders API tests: CRUD > Update an order",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.ts > Orders API tests: CRUD > Update an order",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders-crud.test.ts > Orders API tests: CRUD > Delete an order",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.ts > Orders API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.ts > Orders API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.ts > Orders API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.ts > Orders API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.ts > Orders API tests > List all orders",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.ts > Orders API tests > List all orders",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.ts > Orders API tests > List all orders",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.ts > Orders API tests > List all orders",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.ts > Orders API tests > List all orders",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.ts > Orders API tests > List all orders",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.ts > Orders API tests > List all orders",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.ts > Orders API tests > List all orders",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.ts > Orders API tests > orderby",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.ts > Orders API tests > orderby",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.ts > Orders API tests > orderby",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/orders.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/orders\\/orders.test.ts > Orders API tests > orderby",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/payment-gateways-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/payment-gateways\\/payment-gateways-crud.test.ts > Payment Gateways API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/payment-gateways-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/payment-gateways\\/payment-gateways-crud.test.ts > Payment Gateways API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/payment-gateways-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/payment-gateways\\/payment-gateways-crud.test.ts > Payment Gateways API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/product-list.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.ts > Products API tests: List All Products > List all products",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/product-list.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.ts > Products API tests: List All Products > List all products",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/product-list.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.ts > Products API tests: List All Products > List all products",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/product-list.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.ts > Products API tests: List All Products > List all products",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/product-list.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.ts > Products API tests: List All Products > List all products",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/product-list.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.ts > Products API tests: List All Products > List all products",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/product-list.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.ts > Products API tests: List All Products > List all products",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/product-list.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.ts > Products API tests: List All Products > List all products",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/product-list.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.ts > Products API tests: List All Products > List all products",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/product-list.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.ts > Products API tests: List All Products > List all products",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/product-list.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.ts > Products API tests: List All Products > List all products",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/product-list.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.ts > Products API tests: List All Products > List all products",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/product-list.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.ts > Products API tests: List All Products > List all products",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/product-list.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.ts > Products API tests: List All Products > List all products",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/product-list.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.ts > Products API tests: List All Products > List all products",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/product-list.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.ts > Products API tests: List All Products > List all products",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/product-list.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.ts > Products API tests: List All Products > List all products",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/product-list.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.ts > Products API tests: List All Products > List all products",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/product-list.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.ts > Products API tests: List All Products > List all products",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/product-list.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.ts > Products API tests: List All Products > List all products > orderby",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/product-list.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.ts > Products API tests: List All Products > List all products > orderby",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/product-list.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.ts > Products API tests: List All Products > List all products > orderby",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/product-list.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.ts > Products API tests: List All Products > List all products > orderby",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/product-list.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.ts > Products API tests: List All Products > List all products > orderby",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/product-list.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.ts > Products API tests: List All Products > List all products > orderby",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/product-list.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.ts > Products API tests: List All Products > List all products > orderby",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/product-list.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.ts > Products API tests: List All Products > List all products > orderby",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/product-list.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.ts > Products API tests: List All Products > List all products > orderby",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.ts",
                                            "line": 3279,
                                            "column": 9
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/product-list.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.ts > Products API tests: List All Products > List all products > orderby",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.ts",
                                            "line": 3299,
                                            "column": 9
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/product-list.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/product-list.test.ts > Products API tests: List All Products > List all products > orderby",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product attributes tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product attributes tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product attributes tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product attributes tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product attributes tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product attributes tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product attributes tests: CRUD > Product attribute terms tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product attributes tests: CRUD > Product attribute terms tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product attributes tests: CRUD > Product attribute terms tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product attributes tests: CRUD > Product attribute terms tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product attributes tests: CRUD > Product attribute terms tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product attributes tests: CRUD > Product attribute terms tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product categories tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product categories tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product categories tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product categories tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product categories tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product categories tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product review tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product review tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product review tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product review tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product review tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product review tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product review tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product review tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product shipping classes tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product shipping classes tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product shipping classes tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product shipping classes tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product shipping classes tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product shipping classes tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product tags tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product tags tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product tags tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product tags tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product tags tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product tags tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product variation tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product variation tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product variation tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product variation tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product variation tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product variation tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Product variation tests: CRUD",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Batch update products",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Batch update products",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/products-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/products\\/products-crud.test.ts > Products API tests: CRUD > Batch update products",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/refunds.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/refunds\\/refunds.test.ts > Refunds API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/refunds.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/refunds\\/refunds.test.ts > Refunds API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/refunds.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/refunds\\/refunds.test.ts > Refunds API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/refunds.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/refunds\\/refunds.test.ts > Refunds API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/refunds.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/refunds\\/refunds.test.ts > Refunds API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/reports-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/reports\\/reports-crud.test.ts > Reports API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/reports-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/reports\\/reports-crud.test.ts > Reports API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/reports-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/reports\\/reports-crud.test.ts > Reports API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/reports-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/reports\\/reports-crud.test.ts > Reports API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/reports-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/reports\\/reports-crud.test.ts > Reports API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/reports-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/reports\\/reports-crud.test.ts > Reports API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/reports-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/reports\\/reports-crud.test.ts > Reports API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/reports-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/reports\\/reports-crud.test.ts > Reports API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.ts > Settings API tests: CRUD > List all settings groups",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.ts > Settings API tests: CRUD > List all settings options",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.ts > Settings API tests: CRUD > Retrieve a settings option",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.ts > Settings API tests: CRUD > Update a settings option",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.ts > Settings API tests: CRUD > Batch Update a settings option",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.ts > Settings API tests: CRUD > List all Products settings options",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.ts > Settings API tests: CRUD > List all Tax settings options",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.ts > Settings API tests: CRUD > List all Shipping settings options",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.ts > Settings API tests: CRUD > List all Checkout settings options",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.ts > Settings API tests: CRUD > List all Account settings options",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can retrieve all email settings",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.ts > Settings API tests: CRUD > List all Email settings options",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/settings\\/settings-crud.test.ts",
                                            "line": 1407,
                                            "column": 8
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.ts > Settings API tests: CRUD > List all Advanced settings options",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can retrieve all email new order settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@skip-on-pressable",
                                "@skip-on-wpcom"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.ts > Settings API tests: CRUD > List all Email New Order settings",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can retrieve all email failed order settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@skip-on-pressable",
                                "@skip-on-wpcom"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.ts > Settings API tests: CRUD > List all Email Failed Order settings",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.ts > Settings API tests: CRUD > List all Email Customer On Hold Order settings",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.ts > Settings API tests: CRUD > List all Email Customer Processing Order settings",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.ts > Settings API tests: CRUD > List all Email Customer Completed Order settings",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.ts > Settings API tests: CRUD > List all Email Customer Refunded Order settings",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.ts > Settings API tests: CRUD > List all Email Customer Invoice settings",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.ts > Settings API tests: CRUD > List all Email Customer Note settings",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.ts > Settings API tests: CRUD > List all Email Customer Reset Password settings",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/settings-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/settings\\/settings-crud.test.ts > Settings API tests: CRUD > List all Email Customer New Account settings",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/shipping-method.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-method.test.ts > Shipping methods API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/shipping-method.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-method.test.ts > Shipping methods API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/shipping-method.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-method.test.ts > Shipping methods API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/shipping-method.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-method.test.ts > Shipping methods API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/shipping-method.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-method.test.ts > Shipping methods API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/shipping-method.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-method.test.ts > Shipping methods API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/shipping-method.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-method.test.ts > Shipping methods API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/shipping-method.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-method.test.ts > Shipping methods API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/shipping-zones.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-zones.test.ts > Shipping zones API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/shipping-zones.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-zones.test.ts > Shipping zones API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/shipping-zones.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-zones.test.ts > Shipping zones API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/shipping-zones.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-zones.test.ts > Shipping zones API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/shipping-zones.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-zones.test.ts > Shipping zones API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/shipping-zones.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-zones.test.ts > Shipping zones API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/shipping-zones.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-zones.test.ts > Shipping zones API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/shipping-zones.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-zones.test.ts > Shipping zones API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/shipping-zones.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-zones.test.ts > Shipping zones API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/shipping-zones.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/shipping\\/shipping-zones.test.ts > Shipping zones API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can view all system status items",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/system-status-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Call API to view all system status items",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify \\"environment\\" fields",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify \\"environment.external_object_cache\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify \\"database\\" fields",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify \\"database.database_tables\\" fields",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify \\"database.database_tables.woocommerce\\" fields",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify \\"database.database_tables.other\\" fields",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify \\"database.database_size\\" fields",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify \\"active_plugins\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify plugin \\"WooCommerce Enable COT\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify plugin \\"JSON Basic Authentication\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify plugin \\"Disable All WordPress Updates\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify plugin \\"API - Order Cache Bug\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify plugin \\"WooCommerce Reset\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify plugin \\"WooCommerce\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify \\"dropins_mu_plugins\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify \\"dropins_mu_plugins.dropins\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify \\"dropins_mu_plugins.mu_plugins\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify \\"theme\\" fields.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify \\"settings\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify \\"settings.taxonomies\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify \\"settings.product_visibility_terms\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify \\"security\\" fields",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify \\"pages\\" array",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify page \\"Shop base\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify page \\"Cart\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify page \\"Checkout\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify page \\"My account\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify page \\"Terms and conditions\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify \\"post_type_counts\\" array",
                                    "status": "passed"
                                }
                            ],
                            "suite": "api > api-tests\\/system-status\\/system-status-crud.test.ts > System Status API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/system-status-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/system-status\\/system-status-crud.test.ts > System Status API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/system-status-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/system-status\\/system-status-crud.test.ts > System Status API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/system-status-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/system-status\\/system-status-crud.test.ts > System Status API tests",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/tax-classes-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/taxes\\/tax-classes-crud.test.ts > Tax Classes API tests: CRUD > Create a tax class",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/tax-classes-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/taxes\\/tax-classes-crud.test.ts > Tax Classes API tests: CRUD > Create a tax class",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/tax-classes-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/taxes\\/tax-classes-crud.test.ts > Tax Classes API tests: CRUD > Retrieve after create",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/tax-classes-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/taxes\\/tax-classes-crud.test.ts > Tax Classes API tests: CRUD > Retrieve after create",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/tax-classes-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/taxes\\/tax-classes-crud.test.ts > Tax Classes API tests: CRUD > Update a tax class",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/tax-classes-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/taxes\\/tax-classes-crud.test.ts > Tax Classes API tests: CRUD > Delete a tax class",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/tax-rates-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/taxes\\/tax-rates-crud.test.ts > Tax Rates API tests: CRUD > Create a tax rate",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/tax-rates-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/taxes\\/tax-rates-crud.test.ts > Tax Rates API tests: CRUD > Retrieve after create",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/tax-rates-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/taxes\\/tax-rates-crud.test.ts > Tax Rates API tests: CRUD > Retrieve after create",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/tax-rates-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/taxes\\/tax-rates-crud.test.ts > Tax Rates API tests: CRUD > Update a tax rate",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/tax-rates-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/taxes\\/tax-rates-crud.test.ts > Tax Rates API tests: CRUD > Update a tax rate",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/tax-rates-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/taxes\\/tax-rates-crud.test.ts > Tax Rates API tests: CRUD > Delete a tax rate",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/tax-rates-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/taxes\\/tax-rates-crud.test.ts > Tax Rates API tests: CRUD > Batch tax rate operations",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/tax-rates-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/taxes\\/tax-rates-crud.test.ts > Tax Rates API tests: CRUD > Batch tax rate operations",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/tax-rates-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/taxes\\/tax-rates-crud.test.ts > Tax Rates API tests: CRUD > Batch tax rate operations",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/webhooks-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/webhooks\\/webhooks-crud.test.ts > Webhooks API tests > Create a webhook",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/webhooks-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/webhooks\\/webhooks-crud.test.ts > Webhooks API tests > Retrieve after create",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/webhooks-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/webhooks\\/webhooks-crud.test.ts > Webhooks API tests > Retrieve after create",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/webhooks-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/webhooks\\/webhooks-crud.test.ts > Webhooks API tests > Update a webhook",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/webhooks-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/webhooks\\/webhooks-crud.test.ts > Webhooks API tests > Delete a webhook",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/webhooks-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/webhooks\\/webhooks-crud.test.ts > Webhooks API tests > Batch webhook operations",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/webhooks-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/webhooks\\/webhooks-crud.test.ts > Webhooks API tests > Batch webhook operations",
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
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
                            "filePath": "\\/normalized\\/path\\/webhooks-crud.test.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "api > api-tests\\/webhooks\\/webhooks-crud.test.ts > Webhooks API tests > Batch webhook operations",
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
                "generic": [
                    {
                        "count": "Between 500 and 999, normalized to 750",
                        "message": "PHP Notice:  $order is Automattic\\\\WooCommerce\\\\Admin\\\\Overrides\\\\Order as expected. in wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php on line 41"
                    }
                ]
            }
        }
    ]
]';
