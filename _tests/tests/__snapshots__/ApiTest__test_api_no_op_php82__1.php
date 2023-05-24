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
            "test_summary": "Test Suites: 0 skipped, 0 failed, 20 passed, 20 total | Tests: 2 skipped, 0 failed, 256 passed, 258 total.",
            "version": "Zip",
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
                "numPassedTests": 256,
                "numPendingTests": 2,
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
                        "file": "orders\\/orders.test.js",
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
                        "file": "payment-gateways\\/payment-gateways-crud.test.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Payment Gateways API tests": [
                                {
                                    "title": "can view all payment gatways",
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
                                    "title": "can permanently delete a product tag",
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
                                    "title": "can retrieve all email customer processsing order settings",
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
                        "file": "system-status\\/system-status-crud.test.js",
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
                "summary": "Test Suites: 0 skipped, 0 failed, 20 passed, 20 total | Tests: 2 skipped, 0 failed, 256 passed, 258 total."
            }
        },
        {
            "debug_log": [
                {
                    "count": "610",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Database\\\\Migrations\\\\CustomOrderTable\\\\PostToOrderTableMigrator::$table_names is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Database\\/Migrations\\/CustomOrderTable\\/PostToOrderTableMigrator.php on line 25"
                },
                {
                    "count": "610",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Database\\\\Migrations\\\\CustomOrderTable\\\\PostToOrderAddressTableMigrator::$table_names is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Database\\/Migrations\\/CustomOrderTable\\/PostToOrderAddressTableMigrator.php on line 42"
                },
                {
                    "count": "610",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Database\\\\Migrations\\\\CustomOrderTable\\\\PostToOrderOpTableMigrator::$table_names is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Database\\/Migrations\\/CustomOrderTable\\/PostToOrderOpTableMigrator.php on line 26"
                },
                {
                    "count": "610",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Database\\\\Migrations\\\\CustomOrderTable\\\\PostMetaToOrderMetaMigrator::$table_names is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Database\\/Migrations\\/CustomOrderTable\\/PostMetaToOrderMetaMigrator.php on line 43"
                },
                {
                    "count": "610",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Gateway_Paypal::$testmode is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/gateways\\/paypal\\/class-wc-gateway-paypal.php on line 60"
                },
                {
                    "count": "610",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Gateway_Paypal::$debug is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/gateways\\/paypal\\/class-wc-gateway-paypal.php on line 61"
                },
                {
                    "count": "610",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Gateway_Paypal::$email is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/gateways\\/paypal\\/class-wc-gateway-paypal.php on line 62"
                },
                {
                    "count": "610",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Gateway_Paypal::$receiver_email is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/gateways\\/paypal\\/class-wc-gateway-paypal.php on line 63"
                },
                {
                    "count": "610",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Gateway_Paypal::$identity_token is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/gateways\\/paypal\\/class-wc-gateway-paypal.php on line 64"
                },
                {
                    "count": "38",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Countries::$countries is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-countries.php on line 51"
                },
                {
                    "count": "6",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Cart::$coupon_discount_totals is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/legacy\\/class-wc-legacy-cart.php on line 266"
                },
                {
                    "count": "6",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Cart::$coupon_discount_tax_totals is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/legacy\\/class-wc-legacy-cart.php on line 266"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Email_New_Order::$email_type is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/emails\\/class-wc-email.php on line 254"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Email_Cancelled_Order::$email_type is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/emails\\/class-wc-email.php on line 254"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Email_Failed_Order::$email_type is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/emails\\/class-wc-email.php on line 254"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Email_Customer_On_Hold_Order::$email_type is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/emails\\/class-wc-email.php on line 254"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Email_Customer_Processing_Order::$email_type is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/emails\\/class-wc-email.php on line 254"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Email_Customer_Completed_Order::$email_type is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/emails\\/class-wc-email.php on line 254"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Email_Customer_Refunded_Order::$email_type is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/emails\\/class-wc-email.php on line 254"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Email_Customer_Invoice::$email_type is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/emails\\/class-wc-email.php on line 254"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Email_Customer_Note::$email_type is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/emails\\/class-wc-email.php on line 254"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Email_Customer_Reset_Password::$email_type is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/emails\\/class-wc-email.php on line 254"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Email_Customer_New_Account::$email_type is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/emails\\/class-wc-email.php on line 254"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Features is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Notes is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\NoteActions is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Coupons is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Data is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\DataCountries is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\DataDownloadIPs is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Experiments is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Marketing is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\MarketingOverview is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\MarketingRecommendations is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\MarketingChannels is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\MarketingCampaigns is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\MarketingCampaignTypes is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Options is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Orders is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\PaymentGatewaySuggestions is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Products is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\ProductAttributes is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\ProductAttributeTerms is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\ProductCategories is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\ProductVariations is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\ProductReviews is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\ProductsLowInStock is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\SettingOptions is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Themes is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Plugins is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\OnboardingFreeExtensions is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\OnboardingProductTypes is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\OnboardingProfile is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\OnboardingTasks is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\OnboardingThemes is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\NavigationFavorites is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Taxes is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\MobileAppMagicLink is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\ShippingPartnerSuggestions is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Customers is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Leaderboards is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Import\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Export\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Products\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Variations\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Products\\\\Stats\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Variations\\\\Stats\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Revenue\\\\Stats\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Orders\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Orders\\\\Stats\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Categories\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Taxes\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Taxes\\\\Stats\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Coupons\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Coupons\\\\Stats\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Stock\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Stock\\\\Stats\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Downloads\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Downloads\\\\Stats\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Customers\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Customers\\\\Stats\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "608",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\PerformanceIndicators\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "609",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Gateway_BACS::$instructions is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/gateways\\/bacs\\/class-wc-gateway-bacs.php on line 49"
                },
                {
                    "count": "609",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Gateway_BACS::$account_details is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/gateways\\/bacs\\/class-wc-gateway-bacs.php on line 52"
                },
                {
                    "count": "609",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Gateway_Cheque::$instructions is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/gateways\\/cheque\\/class-wc-gateway-cheque.php on line 41"
                },
                {
                    "count": "609",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Gateway_COD::$instructions is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/gateways\\/cod\\/class-wc-gateway-cod.php on line 40"
                },
                {
                    "count": "609",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Gateway_COD::$enable_for_methods is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/gateways\\/cod\\/class-wc-gateway-cod.php on line 41"
                },
                {
                    "count": "609",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Gateway_COD::$enable_for_virtual is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/gateways\\/cod\\/class-wc-gateway-cod.php on line 42"
                },
                {
                    "count": "32",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Shipping_Flat_Rate::$cost is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/shipping\\/flat-rate\\/class-wc-shipping-flat-rate.php on line 50"
                },
                {
                    "count": "32",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Shipping_Flat_Rate::$type is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/shipping\\/flat-rate\\/class-wc-shipping-flat-rate.php on line 51"
                },
                {
                    "count": "32",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Shipping_Free_Shipping::$ignore_discounts is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/shipping\\/free-shipping\\/class-wc-shipping-free-shipping.php on line 70"
                },
                {
                    "count": "32",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Shipping_Local_Pickup::$cost is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/shipping\\/local-pickup\\/class-wc-shipping-local-pickup.php on line 53"
                },
                {
                    "count": "2",
                    "message": "PHP Deprecated: Automatic conversion of false to array is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/DataSourcePoller.php on line 138"
                },
                {
                    "count": "317",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\RemoteInboxNotifications\\\\RuleEvaluator::$get_rule_processor is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/RemoteInboxNotifications\\/RuleEvaluator.php on line 22"
                },
                {
                    "count": "316",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\RemoteInboxNotifications\\\\OrRuleProcessor::$rule_evaluator is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/RemoteInboxNotifications\\/OrRuleProcessor.php on line 22"
                },
                {
                    "count": "29",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Countries::$states is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-countries.php on line 169"
                },
                {
                    "count": "20",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Order::$refunds is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-order.php on line 1999"
                },
                {
                    "count": "93",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\Overrides\\\\Order::$refunds is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-order.php on line 1999"
                },
                {
                    "count": "6",
                    "message": "PHP Deprecated: stripslashes(): Passing null to parameter #1 ($string) of type string is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/rest-api\\/Controllers\\/Version1\\/class-wc-rest-product-attributes-v1-controller.php on line 272"
                },
                {
                    "count": "2",
                    "message": "PHP Deprecated: stripslashes(): Passing null to parameter #1 ($string) of type string is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/rest-api\\/Controllers\\/Version1\\/class-wc-rest-product-attributes-v1-controller.php on line 342"
                },
                {
                    "count": "3",
                    "message": "The wc_get_min_max_price_meta_query() function is deprecated since version 3.6."
                },
                {
                    "count": "2",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Shipping_Flat_Rate::$method_order is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-shipping-zone.php on line 191"
                },
                {
                    "count": "2",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Shipping_Flat_Rate::$has_settings is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-shipping-zone.php on line 193"
                },
                {
                    "count": "2",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Shipping_Flat_Rate::$settings_html is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-shipping-zone.php on line 194"
                },
                {
                    "count": "2",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Shipping_Free_Shipping::$method_order is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-shipping-zone.php on line 191"
                },
                {
                    "count": "2",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Shipping_Free_Shipping::$has_settings is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-shipping-zone.php on line 193"
                },
                {
                    "count": "2",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Shipping_Free_Shipping::$settings_html is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-shipping-zone.php on line 194"
                },
                {
                    "count": "2",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Shipping_Local_Pickup::$method_order is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-shipping-zone.php on line 191"
                },
                {
                    "count": "2",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Shipping_Local_Pickup::$has_settings is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-shipping-zone.php on line 193"
                },
                {
                    "count": "2",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Shipping_Local_Pickup::$settings_html is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-shipping-zone.php on line 194"
                },
                {
                    "count": "1",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Countries::$continents is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-countries.php on line 78"
                }
            ]
        }
    ]
]';
