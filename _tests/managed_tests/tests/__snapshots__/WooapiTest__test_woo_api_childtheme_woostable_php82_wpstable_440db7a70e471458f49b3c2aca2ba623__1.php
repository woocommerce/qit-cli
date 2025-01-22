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
                "id": 1822936,
                "host": "wccom",
                "name": "Bistro",
                "type": "theme"
            },
            "client": "qit_cli",
            "event": "cli_development_extension_test",
            "optional_features": {
                "hpos": false,
                "new_product_editor": false
            },
            "test_results_manager_url": "https:\\/\\/test-results-manager.com",
            "test_results_manager_expiration": 1234567890,
            "test_summary": "258 total, 255 passed, 0 failed, 3 skipped.",
            "version": "1.0.15",
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
            "test_result_json": {
                "numFailedTestSuites": 0,
                "numPassedTestSuites": 18,
                "numPendingTestSuites": 2,
                "numTotalTestSuites": 20,
                "numFailedTests": 0,
                "numPassedTests": 255,
                "numPendingTests": 3,
                "numTotalTests": 258,
                "testResults": [
                    {
                        "file": "api-tests\\/coupons\\/coupons.test.js",
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
                        "file": "api-tests\\/customers\\/customers-crud.test.js",
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
                        "file": "api-tests\\/data\\/data-crud.test.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Data API tests": [
                                {
                                    "title": "can list all data",
                                    "status": "passed"
                                },
                                {
                                    "title": "can view country data",
                                    "status": "pending"
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
                        "file": "api-tests\\/hello\\/hello.test.js",
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
                        "file": "api-tests\\/orders\\/order-complex.test.js",
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
                        "file": "api-tests\\/orders\\/order-search.test.js",
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
                        "file": "api-tests\\/orders\\/orders-crud.test.js",
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
                        "file": "api-tests\\/orders\\/orders.test.js",
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
                        "file": "api-tests\\/payment-gateways\\/payment-gateways-crud.test.js",
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
                        "file": "api-tests\\/products\\/product-list.test.js",
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
                        "file": "api-tests\\/products\\/products-crud.test.js",
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
                        "file": "api-tests\\/refunds\\/refunds.test.js",
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
                        "file": "api-tests\\/reports\\/reports-crud.test.js",
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
                        "file": "api-tests\\/settings\\/settings-crud.test.js",
                        "status": "passed",
                        "has_pending": false,
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
                                    "status": "passed"
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
                        "file": "api-tests\\/shipping\\/shipping-method.test.js",
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
                        "file": "api-tests\\/shipping\\/shipping-zones.test.js",
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
                        "file": "api-tests\\/system-status\\/system-status-crud.test.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "System Status API tests": [
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
                        "file": "api-tests\\/taxes\\/tax-classes-crud.test.js",
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
                        "file": "api-tests\\/taxes\\/tax-rates-crud.test.js",
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
                        "file": "api-tests\\/webhooks\\/webhooks-crud.test.js",
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
                "summary": "258 total, 255 passed, 0 failed, 3 skipped."
            }
        },
        {
            "ctrf_json": {
                "results": {
                    "tool": {
                        "name": "playwright"
                    },
                    "summary": {
                        "tests": 258,
                        "passed": 255,
                        "failed": 0,
                        "pending": 0,
                        "skipped": 3,
                        "other": 0,
                        "start": 1737573665746,
                        "stop": 1737573728726,
                        "suites": 0
                    },
                    "tests": [
                        {
                            "name": "can create a coupon",
                            "status": "passed",
                            "duration": 134,
                            "start": 1737573666,
                            "stop": 1737573666,
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
                            "duration": 90,
                            "start": 1737573666,
                            "stop": 1737573666,
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
                            "duration": 79,
                            "start": 1737573666,
                            "stop": 1737573666,
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
                            "duration": 142,
                            "start": 1737573666,
                            "stop": 1737573666,
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
                            "duration": 107,
                            "start": 1737573666,
                            "stop": 1737573666,
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
                            "duration": 81,
                            "start": 1737573666,
                            "stop": 1737573666,
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
                            "duration": 218,
                            "start": 1737573666,
                            "stop": 1737573667,
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
                            "start": 1737573667,
                            "stop": 1737573667,
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
                            "duration": 66,
                            "start": 1737573667,
                            "stop": 1737573667,
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
                            "duration": 70,
                            "start": 1737573667,
                            "stop": 1737573667,
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
                            "duration": 70,
                            "start": 1737573667,
                            "stop": 1737573667,
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
                            "duration": 143,
                            "start": 1737573667,
                            "stop": 1737573667,
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
                            "duration": 71,
                            "start": 1737573667,
                            "stop": 1737573668,
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
                            "duration": 77,
                            "start": 1737573668,
                            "stop": 1737573668,
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
                            "duration": 78,
                            "start": 1737573668,
                            "stop": 1737573668,
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
                            "duration": 82,
                            "start": 1737573668,
                            "stop": 1737573668,
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
                            "duration": 73,
                            "start": 1737573668,
                            "stop": 1737573668,
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
                            "duration": 161,
                            "start": 1737573668,
                            "stop": 1737573668,
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
                            "duration": 74,
                            "start": 1737573668,
                            "stop": 1737573668,
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
                            "duration": 74,
                            "start": 1737573668,
                            "stop": 1737573668,
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
                            "duration": 83,
                            "start": 1737573668,
                            "stop": 1737573669,
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
                            "start": 1737573669,
                            "stop": 1737573669,
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
                            "duration": 79,
                            "start": 1737573669,
                            "stop": 1737573669,
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
                            "start": 1737573669,
                            "stop": 1737573669,
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
                            "duration": 86,
                            "start": 1737573669,
                            "stop": 1737573669,
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
                            "start": 1737573669,
                            "stop": 1737573669,
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
                            "duration": 156,
                            "start": 1737573669,
                            "stop": 1737573669,
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
                            "duration": 206,
                            "start": 1737573669,
                            "stop": 1737573669,
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
                            "duration": 97,
                            "start": 1737573669,
                            "stop": 1737573669,
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
                            "duration": 237,
                            "start": 1737573669,
                            "stop": 1737573670,
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
                            "start": 1737573670,
                            "stop": 1737573670,
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
                            "start": 1737573670,
                            "stop": 1737573670,
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
                            "duration": 69,
                            "start": 1737573670,
                            "stop": 1737573670,
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
                            "duration": 69,
                            "start": 1737573670,
                            "stop": 1737573670,
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
                            "duration": 71,
                            "start": 1737573670,
                            "stop": 1737573670,
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
                            "duration": 959,
                            "start": 1737573670,
                            "stop": 1737573671,
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
                            "status": "passed",
                            "duration": 395,
                            "start": 1737573671,
                            "stop": 1737573671,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-complex.test.js",
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
                            "duration": 75,
                            "start": 1737573673,
                            "stop": 1737573673,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js",
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
                            "duration": 86,
                            "start": 1737573673,
                            "stop": 1737573673,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js",
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
                            "duration": 84,
                            "start": 1737573673,
                            "stop": 1737573673,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js",
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
                            "duration": 76,
                            "start": 1737573673,
                            "stop": 1737573673,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js",
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
                            "duration": 75,
                            "start": 1737573673,
                            "stop": 1737573673,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js",
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
                            "duration": 79,
                            "start": 1737573673,
                            "stop": 1737573674,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js",
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
                            "duration": 74,
                            "start": 1737573674,
                            "stop": 1737573674,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js",
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
                            "duration": 75,
                            "start": 1737573674,
                            "stop": 1737573674,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js",
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
                            "duration": 77,
                            "start": 1737573674,
                            "stop": 1737573674,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js",
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
                            "duration": 74,
                            "start": 1737573674,
                            "stop": 1737573674,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js",
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
                            "duration": 72,
                            "start": 1737573674,
                            "stop": 1737573674,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js",
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
                            "duration": 75,
                            "start": 1737573674,
                            "stop": 1737573674,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js",
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
                            "duration": 75,
                            "start": 1737573674,
                            "stop": 1737573674,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js",
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
                            "duration": 79,
                            "start": 1737573674,
                            "stop": 1737573674,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js",
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
                            "duration": 63,
                            "start": 1737573674,
                            "stop": 1737573674,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/order-search.test.js",
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
                            "duration": 104,
                            "start": 1737573674,
                            "stop": 1737573675,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
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
                            "duration": 183,
                            "start": 1737573675,
                            "stop": 1737573675,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
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
                            "duration": 263,
                            "start": 1737573675,
                            "stop": 1737573675,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
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
                            "duration": 257,
                            "start": 1737573675,
                            "stop": 1737573675,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
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
                            "duration": 280,
                            "start": 1737573675,
                            "stop": 1737573676,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
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
                            "duration": 188,
                            "start": 1737573676,
                            "stop": 1737573676,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
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
                            "duration": 191,
                            "start": 1737573676,
                            "stop": 1737573676,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
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
                            "duration": 243,
                            "start": 1737573676,
                            "stop": 1737573676,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
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
                            "duration": 73,
                            "start": 1737573676,
                            "stop": 1737573676,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
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
                            "duration": 71,
                            "start": 1737573676,
                            "stop": 1737573676,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
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
                            "duration": 69,
                            "start": 1737573676,
                            "stop": 1737573676,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
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
                            "duration": 64,
                            "start": 1737573676,
                            "stop": 1737573676,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
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
                            "duration": 122,
                            "start": 1737573676,
                            "stop": 1737573677,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
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
                            "duration": 68,
                            "start": 1737573677,
                            "stop": 1737573677,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
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
                            "duration": 1084,
                            "start": 1737573677,
                            "stop": 1737573678,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
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
                            "duration": 1179,
                            "start": 1737573678,
                            "stop": 1737573679,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
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
                            "duration": 1088,
                            "start": 1737573679,
                            "stop": 1737573680,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
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
                            "duration": 1113,
                            "start": 1737573680,
                            "stop": 1737573681,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
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
                            "duration": 1120,
                            "start": 1737573681,
                            "stop": 1737573682,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
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
                            "duration": 1087,
                            "start": 1737573682,
                            "stop": 1737573683,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
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
                            "duration": 1112,
                            "start": 1737573683,
                            "stop": 1737573685,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
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
                            "duration": 114,
                            "start": 1737573685,
                            "stop": 1737573685,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
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
                            "duration": 115,
                            "start": 1737573685,
                            "stop": 1737573685,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
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
                            "duration": 212,
                            "start": 1737573685,
                            "stop": 1737573685,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
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
                            "duration": 142,
                            "start": 1737573685,
                            "stop": 1737573685,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders-crud.test.js",
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
                            "duration": 128,
                            "start": 1737573685,
                            "stop": 1737573685,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
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
                            "duration": 70,
                            "start": 1737573691,
                            "stop": 1737573691,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
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
                            "duration": 99,
                            "start": 1737573691,
                            "stop": 1737573692,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
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
                            "duration": 155,
                            "start": 1737573692,
                            "stop": 1737573692,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
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
                            "duration": 447,
                            "start": 1737573692,
                            "stop": 1737573692,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
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
                            "duration": 368,
                            "start": 1737573692,
                            "stop": 1737573693,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
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
                            "duration": 232,
                            "start": 1737573693,
                            "stop": 1737573693,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
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
                            "duration": 217,
                            "start": 1737573693,
                            "stop": 1737573693,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
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
                            "duration": 217,
                            "start": 1737573693,
                            "stop": 1737573693,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
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
                            "duration": 93,
                            "start": 1737573693,
                            "stop": 1737573693,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
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
                            "duration": 265,
                            "start": 1737573693,
                            "stop": 1737573694,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
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
                            "duration": 463,
                            "start": 1737573694,
                            "stop": 1737573694,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
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
                            "duration": 154,
                            "start": 1737573694,
                            "stop": 1737573694,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
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
                            "duration": 159,
                            "start": 1737573694,
                            "stop": 1737573694,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
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
                            "duration": 302,
                            "start": 1737573694,
                            "stop": 1737573695,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
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
                            "duration": 196,
                            "start": 1737573695,
                            "stop": 1737573695,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/orders\\/orders.test.js",
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
                            "duration": 71,
                            "start": 1737573699,
                            "stop": 1737573699,
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
                            "duration": 66,
                            "start": 1737573699,
                            "stop": 1737573699,
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
                            "duration": 141,
                            "start": 1737573699,
                            "stop": 1737573699,
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
                            "status": "passed",
                            "duration": 154,
                            "start": 1737573699,
                            "stop": 1737573700,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
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
                            "duration": 444,
                            "start": 1737573704,
                            "stop": 1737573704,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
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
                            "duration": 140,
                            "start": 1737573704,
                            "stop": 1737573704,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
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
                            "duration": 426,
                            "start": 1737573704,
                            "stop": 1737573705,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
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
                            "duration": 129,
                            "start": 1737573705,
                            "stop": 1737573705,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
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
                            "duration": 130,
                            "start": 1737573705,
                            "stop": 1737573705,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
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
                            "duration": 326,
                            "start": 1737573705,
                            "stop": 1737573705,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
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
                            "duration": 211,
                            "start": 1737573705,
                            "stop": 1737573705,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
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
                            "duration": 236,
                            "start": 1737573705,
                            "stop": 1737573706,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
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
                            "duration": 224,
                            "start": 1737573706,
                            "stop": 1737573706,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
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
                            "duration": 282,
                            "start": 1737573706,
                            "stop": 1737573706,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
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
                            "duration": 179,
                            "start": 1737573706,
                            "stop": 1737573706,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
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
                            "duration": 90,
                            "start": 1737573706,
                            "stop": 1737573706,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
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
                            "duration": 128,
                            "start": 1737573707,
                            "stop": 1737573707,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
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
                            "duration": 73,
                            "start": 1737573707,
                            "stop": 1737573707,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
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
                            "duration": 72,
                            "start": 1737573707,
                            "stop": 1737573707,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
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
                            "duration": 74,
                            "start": 1737573707,
                            "stop": 1737573707,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
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
                            "duration": 79,
                            "start": 1737573707,
                            "stop": 1737573707,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
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
                            "duration": 190,
                            "start": 1737573707,
                            "stop": 1737573707,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
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
                            "duration": 124,
                            "start": 1737573707,
                            "stop": 1737573707,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
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
                            "duration": 132,
                            "start": 1737573707,
                            "stop": 1737573707,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
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
                            "duration": 252,
                            "start": 1737573707,
                            "stop": 1737573708,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
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
                            "duration": 354,
                            "start": 1737573708,
                            "stop": 1737573708,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
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
                            "duration": 358,
                            "start": 1737573708,
                            "stop": 1737573708,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
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
                            "duration": 365,
                            "start": 1737573708,
                            "stop": 1737573709,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
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
                            "duration": 202,
                            "start": 1737573709,
                            "stop": 1737573709,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
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
                            "duration": 86,
                            "start": 1737573709,
                            "stop": 1737573709,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
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
                            "duration": 0,
                            "start": 1737573709,
                            "stop": 1737573709,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
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
                            "duration": 0,
                            "start": 1737573709,
                            "stop": 1737573709,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
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
                            "duration": 85,
                            "start": 1737573709,
                            "stop": 1737573709,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/product-list.test.js",
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
                            "duration": 126,
                            "start": 1737573712,
                            "stop": 1737573712,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 225,
                            "start": 1737573718,
                            "stop": 1737573718,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 272,
                            "start": 1737573719,
                            "stop": 1737573719,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 292,
                            "start": 1737573719,
                            "stop": 1737573720,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 260,
                            "start": 1737573720,
                            "stop": 1737573720,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 70,
                            "start": 1737573712,
                            "stop": 1737573713,
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
                            "duration": 66,
                            "start": 1737573713,
                            "stop": 1737573713,
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
                            "duration": 63,
                            "start": 1737573713,
                            "stop": 1737573713,
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
                            "duration": 70,
                            "start": 1737573713,
                            "stop": 1737573713,
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
                            "duration": 124,
                            "start": 1737573713,
                            "stop": 1737573714,
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
                            "duration": 312,
                            "start": 1737573714,
                            "stop": 1737573714,
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
                            "duration": 74,
                            "start": 1737573713,
                            "stop": 1737573713,
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
                            "duration": 65,
                            "start": 1737573713,
                            "stop": 1737573713,
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
                            "duration": 65,
                            "start": 1737573713,
                            "stop": 1737573713,
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
                            "duration": 68,
                            "start": 1737573713,
                            "stop": 1737573713,
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
                            "duration": 122,
                            "start": 1737573713,
                            "stop": 1737573713,
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
                            "duration": 317,
                            "start": 1737573713,
                            "stop": 1737573713,
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
                            "duration": 78,
                            "start": 1737573714,
                            "stop": 1737573714,
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
                            "duration": 67,
                            "start": 1737573714,
                            "stop": 1737573714,
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
                            "duration": 67,
                            "start": 1737573714,
                            "stop": 1737573714,
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
                            "duration": 85,
                            "start": 1737573714,
                            "stop": 1737573714,
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
                            "duration": 130,
                            "start": 1737573714,
                            "stop": 1737573714,
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
                            "duration": 349,
                            "start": 1737573714,
                            "stop": 1737573715,
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
                            "status": "passed",
                            "duration": 101,
                            "start": 1737573715,
                            "stop": 1737573715,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 65,
                            "start": 1737573715,
                            "stop": 1737573715,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 65,
                            "start": 1737573715,
                            "stop": 1737573715,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 67,
                            "start": 1737573715,
                            "stop": 1737573715,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 64,
                            "start": 1737573715,
                            "stop": 1737573715,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 98,
                            "start": 1737573715,
                            "stop": 1737573715,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 147,
                            "start": 1737573715,
                            "stop": 1737573716,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 511,
                            "start": 1737573716,
                            "stop": 1737573716,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 72,
                            "start": 1737573716,
                            "stop": 1737573716,
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
                            "duration": 71,
                            "start": 1737573716,
                            "stop": 1737573716,
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
                            "duration": 70,
                            "start": 1737573716,
                            "stop": 1737573716,
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
                            "duration": 69,
                            "start": 1737573716,
                            "stop": 1737573716,
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
                            "duration": 125,
                            "start": 1737573716,
                            "stop": 1737573716,
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
                            "duration": 321,
                            "start": 1737573716,
                            "stop": 1737573717,
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
                            "duration": 69,
                            "start": 1737573717,
                            "stop": 1737573717,
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
                            "duration": 65,
                            "start": 1737573717,
                            "stop": 1737573717,
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
                            "duration": 67,
                            "start": 1737573717,
                            "stop": 1737573717,
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
                            "duration": 70,
                            "start": 1737573717,
                            "stop": 1737573717,
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
                            "duration": 122,
                            "start": 1737573717,
                            "stop": 1737573717,
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
                            "start": 1737573717,
                            "stop": 1737573718,
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
                            "status": "passed",
                            "duration": 140,
                            "start": 1737573718,
                            "stop": 1737573718,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 140,
                            "start": 1737573718,
                            "stop": 1737573718,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 82,
                            "start": 1737573718,
                            "stop": 1737573718,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 85,
                            "start": 1737573718,
                            "stop": 1737573718,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 119,
                            "start": 1737573718,
                            "stop": 1737573718,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 116,
                            "start": 1737573718,
                            "stop": 1737573719,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 685,
                            "start": 1737573719,
                            "stop": 1737573719,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 179,
                            "start": 1737573720,
                            "stop": 1737573720,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 128,
                            "start": 1737573720,
                            "stop": 1737573720,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 255,
                            "start": 1737573720,
                            "stop": 1737573721,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/products\\/products-crud.test.js",
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
                            "duration": 172,
                            "start": 1737573721,
                            "stop": 1737573721,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/refunds\\/refunds.test.js",
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
                            "duration": 66,
                            "start": 1737573721,
                            "stop": 1737573721,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/refunds\\/refunds.test.js",
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
                            "duration": 66,
                            "start": 1737573721,
                            "stop": 1737573721,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/refunds\\/refunds.test.js",
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
                            "duration": 69,
                            "start": 1737573721,
                            "stop": 1737573721,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/refunds\\/refunds.test.js",
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
                            "duration": 191,
                            "start": 1737573721,
                            "stop": 1737573721,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/refunds\\/refunds.test.js",
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
                            "duration": 73,
                            "start": 1737573722,
                            "stop": 1737573722,
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
                            "duration": 75,
                            "start": 1737573722,
                            "stop": 1737573722,
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
                            "duration": 65,
                            "start": 1737573722,
                            "stop": 1737573722,
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
                            "duration": 67,
                            "start": 1737573722,
                            "stop": 1737573722,
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
                            "duration": 64,
                            "start": 1737573722,
                            "stop": 1737573722,
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
                            "duration": 65,
                            "start": 1737573722,
                            "stop": 1737573722,
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
                            "duration": 67,
                            "start": 1737573722,
                            "stop": 1737573722,
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
                            "duration": 66,
                            "start": 1737573722,
                            "stop": 1737573722,
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
                            "duration": 76,
                            "start": 1737573722,
                            "stop": 1737573722,
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
                            "duration": 104,
                            "start": 1737573722,
                            "stop": 1737573722,
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
                            "duration": 74,
                            "start": 1737573722,
                            "stop": 1737573722,
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
                            "duration": 70,
                            "start": 1737573722,
                            "stop": 1737573723,
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
                            "duration": 242,
                            "start": 1737573723,
                            "stop": 1737573723,
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
                            "duration": 103,
                            "start": 1737573723,
                            "stop": 1737573723,
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
                            "duration": 76,
                            "start": 1737573723,
                            "stop": 1737573723,
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
                            "duration": 76,
                            "start": 1737573723,
                            "stop": 1737573723,
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
                            "duration": 63,
                            "start": 1737573723,
                            "stop": 1737573723,
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
                            "duration": 85,
                            "start": 1737573723,
                            "stop": 1737573723,
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
                            "duration": 79,
                            "start": 1737573723,
                            "stop": 1737573723,
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
                            "duration": 103,
                            "start": 1737573723,
                            "stop": 1737573723,
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
                            "duration": 71,
                            "start": 1737573723,
                            "stop": 1737573723,
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
                            "duration": 73,
                            "start": 1737573723,
                            "stop": 1737573724,
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
                            "duration": 78,
                            "start": 1737573724,
                            "stop": 1737573724,
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
                            "duration": 67,
                            "start": 1737573724,
                            "stop": 1737573724,
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
                            "duration": 67,
                            "start": 1737573724,
                            "stop": 1737573724,
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
                            "start": 1737573724,
                            "stop": 1737573724,
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
                            "duration": 68,
                            "start": 1737573724,
                            "stop": 1737573724,
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
                            "duration": 68,
                            "start": 1737573724,
                            "stop": 1737573724,
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
                            "start": 1737573724,
                            "stop": 1737573724,
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
                            "duration": 68,
                            "start": 1737573724,
                            "stop": 1737573724,
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
                            "duration": 62,
                            "start": 1737573724,
                            "stop": 1737573724,
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
                            "duration": 66,
                            "start": 1737573724,
                            "stop": 1737573724,
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
                            "duration": 63,
                            "start": 1737573724,
                            "stop": 1737573724,
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
                            "duration": 69,
                            "start": 1737573724,
                            "stop": 1737573724,
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
                            "duration": 65,
                            "start": 1737573724,
                            "stop": 1737573724,
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
                            "duration": 127,
                            "start": 1737573724,
                            "stop": 1737573725,
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
                            "duration": 126,
                            "start": 1737573725,
                            "stop": 1737573725,
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
                            "duration": 123,
                            "start": 1737573725,
                            "stop": 1737573725,
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
                            "duration": 169,
                            "start": 1737573725,
                            "stop": 1737573725,
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
                            "duration": 60,
                            "start": 1737573725,
                            "stop": 1737573725,
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
                            "duration": 65,
                            "start": 1737573725,
                            "stop": 1737573725,
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
                            "duration": 63,
                            "start": 1737573725,
                            "stop": 1737573725,
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
                            "duration": 65,
                            "start": 1737573725,
                            "stop": 1737573725,
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
                            "duration": 65,
                            "start": 1737573725,
                            "stop": 1737573725,
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
                            "start": 1737573725,
                            "stop": 1737573725,
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
                            "duration": 67,
                            "start": 1737573725,
                            "stop": 1737573726,
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
                            "duration": 67,
                            "start": 1737573726,
                            "stop": 1737573726,
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
                            "start": 1737573726,
                            "stop": 1737573726,
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
                            "duration": 69,
                            "start": 1737573726,
                            "stop": 1737573726,
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
                            "duration": 67,
                            "start": 1737573726,
                            "stop": 1737573726,
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
                            "duration": 77,
                            "start": 1737573726,
                            "stop": 1737573726,
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
                            "duration": 67,
                            "start": 1737573726,
                            "stop": 1737573726,
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
                            "duration": 62,
                            "start": 1737573726,
                            "stop": 1737573726,
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
                            "duration": 72,
                            "start": 1737573726,
                            "stop": 1737573726,
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
                            "duration": 62,
                            "start": 1737573726,
                            "stop": 1737573726,
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
                            "duration": 61,
                            "start": 1737573726,
                            "stop": 1737573726,
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
                            "duration": 117,
                            "start": 1737573726,
                            "stop": 1737573726,
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
                            "duration": 88,
                            "start": 1737573726,
                            "stop": 1737573726,
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
                            "start": 1737573726,
                            "stop": 1737573727,
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
                            "duration": 71,
                            "start": 1737573727,
                            "stop": 1737573727,
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
                            "duration": 68,
                            "start": 1737573727,
                            "stop": 1737573727,
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
                            "start": 1737573727,
                            "stop": 1737573727,
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
                            "duration": 70,
                            "start": 1737573727,
                            "stop": 1737573727,
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
                            "duration": 183,
                            "start": 1737573727,
                            "stop": 1737573727,
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
                            "duration": 70,
                            "start": 1737573727,
                            "stop": 1737573727,
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
                            "duration": 139,
                            "start": 1737573727,
                            "stop": 1737573727,
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
                            "duration": 132,
                            "start": 1737573727,
                            "stop": 1737573727,
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
                            "duration": 66,
                            "start": 1737573727,
                            "stop": 1737573727,
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
                            "duration": 63,
                            "start": 1737573727,
                            "stop": 1737573727,
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
                            "duration": 66,
                            "start": 1737573727,
                            "stop": 1737573728,
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
                            "duration": 125,
                            "start": 1737573728,
                            "stop": 1737573728,
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
                            "duration": 141,
                            "start": 1737573728,
                            "stop": 1737573728,
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
                            "duration": 183,
                            "start": 1737573728,
                            "stop": 1737573728,
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
                            "duration": 174,
                            "start": 1737573728,
                            "stop": 1737573728,
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
            "debug_log": {
                "generic": []
            }
        }
    ]
]';
