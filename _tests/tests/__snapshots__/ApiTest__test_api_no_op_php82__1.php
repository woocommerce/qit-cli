<?php return '[
    [
        {
            "run_id": 123456,
            "test_type": "api",
            "wordpress_version": "6.0.0-normalized",
            "woocommerce_version": "6.0.0-normalized",
            "php_version": "8.2",
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
                "name": "Google Product Feed"
            },
            "client": "qit_cli",
            "event": "cli_development_extension_test",
            "optional_features": {
                "hpos": false,
                "new_product_editor": false
            },
            "test_results_manager_url": "https:\\/\\/test-results-manager.com",
            "test_results_manager_expiration": 1234567890,
            "test_summary": "Test Suites: 0 skipped, 0 failed, 20 passed, 20 total | Tests: 140 skipped, 0 failed, 118 passed, 258 total.",
            "version": "Undefined",
            "update_complete": true,
            "ai_suggestion_status": "none",
            "test_result_json_extracted": "{EXTRACTED}",
            "debug_log_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "numFailedTestSuites": 0,
                "numPassedTestSuites": 20,
                "numPendingTestSuites": 0,
                "numTotalTestSuites": 20,
                "numFailedTests": 0,
                "numPassedTests": 118,
                "numPendingTests": 140,
                "numTotalTests": 258,
                "testResults": [
                    {
                        "file": "coupons\\/coupons.test.js",
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
                        "file": "customers\\/customers-crud.test.js",
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
                                    "title": "can retrieve all customers",
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
                        "file": "data\\/data-crud.test.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Data API tests": [
                                {
                                    "title": "can list all data",
                                    "status": "passed"
                                },
                                {
                                    "title": "can view country data",
                                    "status": "passed"
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
                        "file": "hello\\/hello.test.js",
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
                        "file": "orders\\/order-complex.test.js",
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
                        "file": "orders\\/order-search.test.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Order Search API tests": [
                                {
                                    "title": "can search by orderId",
                                    "status": "passed"
                                },
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
                                    "title": "can return an empty result set when no matches were found",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "orders\\/orders-crud.test.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Orders API tests: CRUD": [],
                            "Orders API tests: CRUD > Create an order": [
                                {
                                    "title": "can create a pending order by default",
                                    "status": "pending"
                                },
                                {
                                    "title": "can create an order with status pending",
                                    "status": "pending"
                                },
                                {
                                    "title": "can create an order with status processing",
                                    "status": "pending"
                                },
                                {
                                    "title": "can create an order with status on-hold",
                                    "status": "pending"
                                },
                                {
                                    "title": "can create an order with status completed",
                                    "status": "pending"
                                },
                                {
                                    "title": "can create an order with status cancelled",
                                    "status": "pending"
                                },
                                {
                                    "title": "can create an order with status refunded",
                                    "status": "pending"
                                },
                                {
                                    "title": "can create an order with status failed",
                                    "status": "pending"
                                }
                            ],
                            "Orders API tests: CRUD > Create an order > Order Notes tests": [
                                {
                                    "title": "can create a order note",
                                    "status": "pending"
                                },
                                {
                                    "title": "can retrieve an order note",
                                    "status": "pending"
                                },
                                {
                                    "title": "can retrieve all order notes",
                                    "status": "pending"
                                },
                                {
                                    "title": "cannot update an order note",
                                    "status": "pending"
                                },
                                {
                                    "title": "can permanently delete an order note",
                                    "status": "pending"
                                }
                            ],
                            "Orders API tests: CRUD > Retrieve an order": [
                                {
                                    "title": "can retrieve an order",
                                    "status": "pending"
                                }
                            ],
                            "Orders API tests: CRUD > Update an order": [
                                {
                                    "title": "can update status of an order to pending",
                                    "status": "pending"
                                },
                                {
                                    "title": "can update status of an order to processing",
                                    "status": "pending"
                                },
                                {
                                    "title": "can update status of an order to on-hold",
                                    "status": "pending"
                                },
                                {
                                    "title": "can update status of an order to completed",
                                    "status": "pending"
                                },
                                {
                                    "title": "can update status of an order to cancelled",
                                    "status": "pending"
                                },
                                {
                                    "title": "can update status of an order to refunded",
                                    "status": "pending"
                                },
                                {
                                    "title": "can update status of an order to failed",
                                    "status": "pending"
                                },
                                {
                                    "title": "can add shipping and billing contacts to an order",
                                    "status": "pending"
                                },
                                {
                                    "title": "can add a product to an order",
                                    "status": "pending"
                                },
                                {
                                    "title": "can pay for an order",
                                    "status": "pending"
                                }
                            ],
                            "Orders API tests: CRUD > Delete an order": [
                                {
                                    "title": "can permanently delete an order",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "orders\\/orders.test.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Orders API tests": [
                                {
                                    "title": "can create an order",
                                    "status": "pending"
                                },
                                {
                                    "title": "can retrieve an order",
                                    "status": "pending"
                                },
                                {
                                    "title": "can add shipping and billing contacts to an order",
                                    "status": "pending"
                                },
                                {
                                    "title": "can permanently delete an order",
                                    "status": "pending"
                                }
                            ],
                            "Orders API tests > List all orders": [
                                {
                                    "title": "pagination",
                                    "status": "pending"
                                },
                                {
                                    "title": "inclusion \\/ exclusion",
                                    "status": "pending"
                                },
                                {
                                    "title": "parent",
                                    "status": "pending"
                                },
                                {
                                    "title": "status",
                                    "status": "pending"
                                },
                                {
                                    "title": "customer",
                                    "status": "pending"
                                },
                                {
                                    "title": "product",
                                    "status": "pending"
                                },
                                {
                                    "title": "dp (precision)",
                                    "status": "pending"
                                },
                                {
                                    "title": "search",
                                    "status": "pending"
                                }
                            ],
                            "Orders API tests > orderby": [
                                {
                                    "title": "default",
                                    "status": "pending"
                                },
                                {
                                    "title": "date",
                                    "status": "pending"
                                },
                                {
                                    "title": "id",
                                    "status": "pending"
                                },
                                {
                                    "title": "include",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "payment-gateways\\/payment-gateways-crud.test.js",
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
                        "file": "products\\/product-list.test.js",
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
                        "file": "products\\/products-crud.test.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Products API tests: CRUD": [
                                {
                                    "title": "can add a simple product",
                                    "status": "pending"
                                },
                                {
                                    "title": "can add a virtual product",
                                    "status": "pending"
                                },
                                {
                                    "title": "can view a single product",
                                    "status": "pending"
                                },
                                {
                                    "title": "can update a single product",
                                    "status": "pending"
                                },
                                {
                                    "title": "can delete a product",
                                    "status": "pending"
                                }
                            ],
                            "Products API tests: CRUD > Product attributes tests: CRUD": [
                                {
                                    "title": "can add a product attribute",
                                    "status": "pending"
                                },
                                {
                                    "title": "can retrieve a product attribute",
                                    "status": "pending"
                                },
                                {
                                    "title": "can retrieve all product attribute",
                                    "status": "pending"
                                },
                                {
                                    "title": "can update a product attribute",
                                    "status": "pending"
                                },
                                {
                                    "title": "can permanently delete a product attribute",
                                    "status": "pending"
                                },
                                {
                                    "title": "can batch update product attributes",
                                    "status": "pending"
                                }
                            ],
                            "Products API tests: CRUD > Product attributes tests: CRUD > Product attribute terms tests: CRUD": [
                                {
                                    "title": "can add a product attribute term",
                                    "status": "pending"
                                },
                                {
                                    "title": "can retrieve a product attribute term",
                                    "status": "pending"
                                },
                                {
                                    "title": "can retrieve all product attribute terms",
                                    "status": "pending"
                                },
                                {
                                    "title": "can update a product attribute term",
                                    "status": "pending"
                                },
                                {
                                    "title": "can permanently delete a product attribute term",
                                    "status": "pending"
                                },
                                {
                                    "title": "can batch update product attribute terms",
                                    "status": "pending"
                                }
                            ],
                            "Products API tests: CRUD > Product categories tests: CRUD": [
                                {
                                    "title": "can add a product category",
                                    "status": "pending"
                                },
                                {
                                    "title": "can retrieve a product category",
                                    "status": "pending"
                                },
                                {
                                    "title": "can retrieve all product categories",
                                    "status": "pending"
                                },
                                {
                                    "title": "can update a product category",
                                    "status": "pending"
                                },
                                {
                                    "title": "can permanently delete a product tag",
                                    "status": "pending"
                                },
                                {
                                    "title": "can batch update product categories",
                                    "status": "pending"
                                }
                            ],
                            "Products API tests: CRUD > Product review tests: CRUD": [
                                {
                                    "title": "can add a product review",
                                    "status": "pending"
                                },
                                {
                                    "title": "cannot add a product review with invalid product_id",
                                    "status": "pending"
                                },
                                {
                                    "title": "cannot add a duplicate product review",
                                    "status": "pending"
                                },
                                {
                                    "title": "can retrieve a product review",
                                    "status": "pending"
                                },
                                {
                                    "title": "can retrieve all product reviews",
                                    "status": "pending"
                                },
                                {
                                    "title": "can update a product review",
                                    "status": "pending"
                                },
                                {
                                    "title": "can permanently delete a product review",
                                    "status": "pending"
                                },
                                {
                                    "title": "can batch update product reviews",
                                    "status": "pending"
                                }
                            ],
                            "Products API tests: CRUD > Product shipping classes tests: CRUD": [
                                {
                                    "title": "can add a product shipping class",
                                    "status": "pending"
                                },
                                {
                                    "title": "can retrieve a product shipping class",
                                    "status": "pending"
                                },
                                {
                                    "title": "can retrieve all product shipping classes",
                                    "status": "pending"
                                },
                                {
                                    "title": "can update a product shipping class",
                                    "status": "pending"
                                },
                                {
                                    "title": "can permanently delete a product shipping class",
                                    "status": "pending"
                                },
                                {
                                    "title": "can batch update product shipping classes",
                                    "status": "pending"
                                }
                            ],
                            "Products API tests: CRUD > Product tags tests: CRUD": [
                                {
                                    "title": "can add a product tag",
                                    "status": "pending"
                                },
                                {
                                    "title": "can retrieve a product tag",
                                    "status": "pending"
                                },
                                {
                                    "title": "can retrieve all product tags",
                                    "status": "pending"
                                },
                                {
                                    "title": "can update a product tag",
                                    "status": "pending"
                                },
                                {
                                    "title": "can permanently delete a product tag",
                                    "status": "pending"
                                },
                                {
                                    "title": "can batch update product tags",
                                    "status": "pending"
                                }
                            ],
                            "Products API tests: CRUD > Product variation tests: CRUD": [
                                {
                                    "title": "can add a variable product",
                                    "status": "pending"
                                },
                                {
                                    "title": "can add a product variation",
                                    "status": "pending"
                                },
                                {
                                    "title": "can retrieve a product variation",
                                    "status": "pending"
                                },
                                {
                                    "title": "can retrieve all product variations",
                                    "status": "pending"
                                },
                                {
                                    "title": "can update a product variation",
                                    "status": "pending"
                                },
                                {
                                    "title": "can permanently delete a product variation",
                                    "status": "pending"
                                },
                                {
                                    "title": "can batch update product variations",
                                    "status": "pending"
                                }
                            ],
                            "Products API tests: CRUD > Batch update products": [
                                {
                                    "title": "can batch create products",
                                    "status": "pending"
                                },
                                {
                                    "title": "can batch update products",
                                    "status": "pending"
                                },
                                {
                                    "title": "can batch delete products",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "refunds\\/refunds.test.js",
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
                        "file": "reports\\/reports-crud.test.js",
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
                        "file": "settings\\/settings-crud.test.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Settings API tests: CRUD": [],
                            "Settings API tests: CRUD > List all settings groups": [
                                {
                                    "title": "can retrieve all settings groups",
                                    "status": "pending"
                                }
                            ],
                            "Settings API tests: CRUD > List all settings options": [
                                {
                                    "title": "can retrieve all general settings",
                                    "status": "pending"
                                }
                            ],
                            "Settings API tests: CRUD > Retrieve a settings option": [
                                {
                                    "title": "can retrieve a settings option",
                                    "status": "pending"
                                }
                            ],
                            "Settings API tests: CRUD > Update a settings option": [
                                {
                                    "title": "can update a settings option",
                                    "status": "pending"
                                }
                            ],
                            "Settings API tests: CRUD > Batch Update a settings option": [
                                {
                                    "title": "can batch update settings options",
                                    "status": "pending"
                                }
                            ],
                            "Settings API tests: CRUD > List all Products settings options": [
                                {
                                    "title": "can retrieve all products settings",
                                    "status": "pending"
                                }
                            ],
                            "Settings API tests: CRUD > List all Tax settings options": [
                                {
                                    "title": "can retrieve all tax settings",
                                    "status": "pending"
                                }
                            ],
                            "Settings API tests: CRUD > List all Shipping settings options": [
                                {
                                    "title": "can retrieve all shipping settings",
                                    "status": "pending"
                                }
                            ],
                            "Settings API tests: CRUD > List all Checkout settings options": [
                                {
                                    "title": "can retrieve all checkout settings",
                                    "status": "pending"
                                }
                            ],
                            "Settings API tests: CRUD > List all Account settings options": [
                                {
                                    "title": "can retrieve all account settings",
                                    "status": "pending"
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
                                    "status": "pending"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email New Order settings": [
                                {
                                    "title": "can retrieve all email new order settings",
                                    "status": "pending"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email Failed Order settings": [
                                {
                                    "title": "can retrieve all email failed order settings",
                                    "status": "pending"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email Customer On Hold Order settings": [
                                {
                                    "title": "can retrieve all email customer on hold order settings",
                                    "status": "pending"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email Customer Processing Order settings": [
                                {
                                    "title": "can retrieve all email customer processing order settings",
                                    "status": "pending"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email Customer Completed Order settings": [
                                {
                                    "title": "can retrieve all email customer completed order settings",
                                    "status": "pending"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email Customer Refunded Order settings": [
                                {
                                    "title": "can retrieve all email customer refunded order settings",
                                    "status": "pending"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email Customer Invoice settings": [
                                {
                                    "title": "can retrieve all email customer invoice settings",
                                    "status": "pending"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email Customer Note settings": [
                                {
                                    "title": "can retrieve all email customer note settings",
                                    "status": "pending"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email Customer Reset Password settings": [
                                {
                                    "title": "can retrieve all email customer reset password settings",
                                    "status": "pending"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email Customer New Account settings": [
                                {
                                    "title": "can retrieve all email customer new account settings",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shipping\\/shipping-method.test.js",
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
                        "file": "shipping\\/shipping-zones.test.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Shipping zones API tests": [
                                {
                                    "title": "cannot delete the default shipping zone \\"Locations not covered by your other zones\\"",
                                    "status": "pending"
                                },
                                {
                                    "title": "cannot update the default shipping zone",
                                    "status": "pending"
                                },
                                {
                                    "title": "can create a shipping zone",
                                    "status": "pending"
                                },
                                {
                                    "title": "can retrieve a shipping zone",
                                    "status": "pending"
                                },
                                {
                                    "title": "can list all shipping zones",
                                    "status": "pending"
                                },
                                {
                                    "title": "can update a shipping zone",
                                    "status": "pending"
                                },
                                {
                                    "title": "can add a shipping region to a shipping zone",
                                    "status": "pending"
                                },
                                {
                                    "title": "can update a shipping region on a shipping zone",
                                    "status": "pending"
                                },
                                {
                                    "title": "can clear\\/delete a shipping region on a shipping zone",
                                    "status": "pending"
                                },
                                {
                                    "title": "can delete a shipping zone",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "system-status\\/system-status-crud.test.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "System Status API tests": [
                                {
                                    "title": "can view all system status tools",
                                    "status": "pending"
                                },
                                {
                                    "title": "can retrieve a system status tool",
                                    "status": "pending"
                                },
                                {
                                    "title": "can run a tool from system status",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "taxes\\/tax-classes-crud.test.js",
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
                        "file": "taxes\\/tax-rates-crud.test.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Tax Rates API tests: CRUD": [],
                            "Tax Rates API tests: CRUD > Create a tax rate": [
                                {
                                    "title": "can create a tax rate",
                                    "status": "pending"
                                }
                            ],
                            "Tax Rates API tests: CRUD > Retrieve after create": [
                                {
                                    "title": "can retrieve a tax rate",
                                    "status": "pending"
                                },
                                {
                                    "title": "can retrieve all tax rates",
                                    "status": "pending"
                                }
                            ],
                            "Tax Rates API tests: CRUD > Update a tax rate": [
                                {
                                    "title": "can update a tax rate",
                                    "status": "pending"
                                },
                                {
                                    "title": "retrieve after update tax rate",
                                    "status": "pending"
                                }
                            ],
                            "Tax Rates API tests: CRUD > Delete a tax rate": [
                                {
                                    "title": "can permanently delete a tax rate",
                                    "status": "pending"
                                }
                            ],
                            "Tax Rates API tests: CRUD > Batch tax rate operations": [
                                {
                                    "title": "can batch create tax rates",
                                    "status": "pending"
                                },
                                {
                                    "title": "can batch update tax rates",
                                    "status": "pending"
                                },
                                {
                                    "title": "can batch delete tax rates",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "webhooks\\/webhooks-crud.test.js",
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
                "summary": "Test Suites: 0 skipped, 0 failed, 20 passed, 20 total | Tests: 140 skipped, 0 failed, 118 passed, 258 total."
            }
        },
        {
            "debug_log": [
                {
                    "count": "3",
                    "message": "The wc_get_min_max_price_meta_query() function is deprecated since version 3.6."
                }
            ]
        }
    ]
]';
