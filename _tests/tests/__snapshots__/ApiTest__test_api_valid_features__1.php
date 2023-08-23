<?php return '[
    [
        {
            "run_id": 123456,
            "test_type": "api",
            "wordpress_version": "6.0.0-normalized",
            "woocommerce_version": "6.0.0-normalized",
            "php_version": "7.4",
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
                "name": "Google Product Feed"
            },
            "client": "qit_cli",
            "event": "cli_development_extension_test",
            "optional_features": {
                "hpos": true,
                "new_product_editor": true
            },
            "test_results_manager_url": "https:\\/\\/test-results-manager.com",
            "test_results_manager_expiration": 1234567890,
            "test_summary": "Test Suites: 0 skipped, 20 failed, 0 passed, 20 total | Tests: 90 skipped, 168 failed, 0 passed, 258 total.",
            "version": "Undefined",
            "update_complete": true,
            "ai_suggestion_status": "none",
            "test_result_json_extracted": "{EXTRACTED}",
            "debug_log_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "numFailedTestSuites": 20,
                "numPassedTestSuites": 0,
                "numPendingTestSuites": 0,
                "numTotalTestSuites": 20,
                "numFailedTests": 168,
                "numPassedTests": 0,
                "numPendingTests": 90,
                "numTotalTests": 258,
                "testResults": [
                    {
                        "file": "coupons\\/coupons.test.js",
                        "status": "failed",
                        "has_pending": true,
                        "tests": {
                            "Coupons API tests": [
                                {
                                    "title": "can create a coupon",
                                    "status": "failed"
                                },
                                {
                                    "title": "can retrieve a coupon",
                                    "status": "failed"
                                },
                                {
                                    "title": "can update a coupon",
                                    "status": "failed"
                                },
                                {
                                    "title": "can permanently delete a coupon",
                                    "status": "failed"
                                }
                            ],
                            "Batch update coupons": [
                                {
                                    "title": "can batch create coupons",
                                    "status": "failed"
                                },
                                {
                                    "title": "can batch update coupons",
                                    "status": "failed"
                                },
                                {
                                    "title": "can batch delete coupons",
                                    "status": "failed"
                                }
                            ],
                            "List coupons": [
                                {
                                    "title": "can list all coupons by default",
                                    "status": "failed"
                                },
                                {
                                    "title": "can limit result set to matching code",
                                    "status": "pending"
                                },
                                {
                                    "title": "can paginate results",
                                    "status": "pending"
                                },
                                {
                                    "title": "can limit results to matching string",
                                    "status": "pending"
                                }
                            ],
                            "Add coupon to order": [
                                {
                                    "title": "can add coupon to an order",
                                    "status": "failed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "customers\\/customers-crud.test.js",
                        "status": "failed",
                        "has_pending": true,
                        "tests": {
                            "Customers API tests: CRUD": [],
                            "Customers API tests: CRUD > Retrieve after env setup": [
                                {
                                    "title": "can retrieve admin user",
                                    "status": "failed"
                                },
                                {
                                    "title": "can retrieve subscriber user",
                                    "status": "pending"
                                },
                                {
                                    "title": "retrieve user with id 0 is invalid",
                                    "status": "pending"
                                },
                                {
                                    "title": "can retrieve customers",
                                    "status": "pending"
                                },
                                {
                                    "title": "can retrieve all customers",
                                    "status": "pending"
                                }
                            ],
                            "Customers API tests: CRUD > Create a customer": [
                                {
                                    "title": "can create a customer",
                                    "status": "pending"
                                }
                            ],
                            "Customers API tests: CRUD > Retrieve after create": [
                                {
                                    "title": "can retrieve a customer",
                                    "status": "pending"
                                },
                                {
                                    "title": "can retrieve all customers",
                                    "status": "pending"
                                }
                            ],
                            "Customers API tests: CRUD > Update a customer": [
                                {
                                    "title": "can update the admin user\\/customer",
                                    "status": "pending"
                                },
                                {
                                    "title": "retrieve after update admin",
                                    "status": "pending"
                                },
                                {
                                    "title": "can update the subscriber user\\/customer",
                                    "status": "pending"
                                },
                                {
                                    "title": "retrieve after update subscriber",
                                    "status": "pending"
                                },
                                {
                                    "title": "can update a customer",
                                    "status": "pending"
                                },
                                {
                                    "title": "retrieve after update customer",
                                    "status": "pending"
                                }
                            ],
                            "Customers API tests: CRUD > Delete a customer": [
                                {
                                    "title": "can permanently delete an customer",
                                    "status": "pending"
                                }
                            ],
                            "Customers API tests: CRUD > Batch update customers": [
                                {
                                    "title": "can batch create customers",
                                    "status": "pending"
                                },
                                {
                                    "title": "can batch update customers",
                                    "status": "pending"
                                },
                                {
                                    "title": "can batch delete customers",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "data\\/data-crud.test.js",
                        "status": "failed",
                        "has_pending": false,
                        "tests": {
                            "Data API tests": [
                                {
                                    "title": "can list all data",
                                    "status": "failed"
                                },
                                {
                                    "title": "can view country data",
                                    "status": "failed"
                                },
                                {
                                    "title": "can view all currencies",
                                    "status": "failed"
                                },
                                {
                                    "title": "can view currency data",
                                    "status": "failed"
                                },
                                {
                                    "title": "can view current currency",
                                    "status": "failed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "hello\\/hello.test.js",
                        "status": "failed",
                        "has_pending": false,
                        "tests": {
                            "Test API connectivity": [
                                {
                                    "title": "can access a non-authenticated endpoint",
                                    "status": "failed"
                                },
                                {
                                    "title": "can access an authenticated endpoint",
                                    "status": "failed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "orders\\/order-complex.test.js",
                        "status": "failed",
                        "has_pending": false,
                        "tests": {
                            "Orders API test": [
                                {
                                    "title": "can add complex order",
                                    "status": "failed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "orders\\/order-search.test.js",
                        "status": "failed",
                        "has_pending": true,
                        "tests": {
                            "Order Search API tests": [
                                {
                                    "title": "can search by orderId",
                                    "status": "failed"
                                },
                                {
                                    "title": "can search by billing first name",
                                    "status": "pending"
                                },
                                {
                                    "title": "can search by billing company name",
                                    "status": "pending"
                                },
                                {
                                    "title": "can search by billing address 2",
                                    "status": "pending"
                                },
                                {
                                    "title": "can search by billing city name",
                                    "status": "pending"
                                },
                                {
                                    "title": "can search by billing post code",
                                    "status": "pending"
                                },
                                {
                                    "title": "can search by billing phone",
                                    "status": "pending"
                                },
                                {
                                    "title": "can search by billing state",
                                    "status": "pending"
                                },
                                {
                                    "title": "can search by shipping first name",
                                    "status": "pending"
                                },
                                {
                                    "title": "can search by shipping last name",
                                    "status": "pending"
                                },
                                {
                                    "title": "can search by shipping address 2",
                                    "status": "pending"
                                },
                                {
                                    "title": "can search by shipping city",
                                    "status": "pending"
                                },
                                {
                                    "title": "can search by shipping post code",
                                    "status": "pending"
                                },
                                {
                                    "title": "can search by shipping state",
                                    "status": "pending"
                                },
                                {
                                    "title": "can return an empty result set when no matches were found",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "orders\\/orders-crud.test.js",
                        "status": "failed",
                        "has_pending": true,
                        "tests": {
                            "Orders API tests: CRUD": [],
                            "Orders API tests: CRUD > Create an order": [
                                {
                                    "title": "can create a pending order by default",
                                    "status": "failed"
                                },
                                {
                                    "title": "can create an order with status pending",
                                    "status": "failed"
                                },
                                {
                                    "title": "can create an order with status processing",
                                    "status": "failed"
                                },
                                {
                                    "title": "can create an order with status on-hold",
                                    "status": "failed"
                                },
                                {
                                    "title": "can create an order with status completed",
                                    "status": "failed"
                                },
                                {
                                    "title": "can create an order with status cancelled",
                                    "status": "failed"
                                },
                                {
                                    "title": "can create an order with status refunded",
                                    "status": "failed"
                                },
                                {
                                    "title": "can create an order with status failed",
                                    "status": "failed"
                                }
                            ],
                            "Orders API tests: CRUD > Create an order > Order Notes tests": [
                                {
                                    "title": "can create a order note",
                                    "status": "failed"
                                },
                                {
                                    "title": "can retrieve an order note",
                                    "status": "failed"
                                },
                                {
                                    "title": "can retrieve all order notes",
                                    "status": "failed"
                                },
                                {
                                    "title": "cannot update an order note",
                                    "status": "failed"
                                },
                                {
                                    "title": "can permanently delete an order note",
                                    "status": "failed"
                                }
                            ],
                            "Orders API tests: CRUD > Retrieve an order": [
                                {
                                    "title": "can retrieve an order",
                                    "status": "failed"
                                }
                            ],
                            "Orders API tests: CRUD > Update an order": [
                                {
                                    "title": "can update status of an order to pending",
                                    "status": "failed"
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
                                    "status": "failed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "orders\\/orders.test.js",
                        "status": "failed",
                        "has_pending": true,
                        "tests": {
                            "Orders API tests": [
                                {
                                    "title": "can create an order",
                                    "status": "failed"
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
                        "status": "failed",
                        "has_pending": false,
                        "tests": {
                            "Payment Gateways API tests": [
                                {
                                    "title": "can view all payment gateways",
                                    "status": "failed"
                                },
                                {
                                    "title": "can view a payment gateway",
                                    "status": "failed"
                                },
                                {
                                    "title": "can update a payment gateway",
                                    "status": "failed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "products\\/product-list.test.js",
                        "status": "failed",
                        "has_pending": true,
                        "tests": {
                            "Products API tests: List All Products": [],
                            "Products API tests: List All Products > List all products": [
                                {
                                    "title": "defaults",
                                    "status": "failed"
                                },
                                {
                                    "title": "pagination",
                                    "status": "pending"
                                },
                                {
                                    "title": "search",
                                    "status": "pending"
                                },
                                {
                                    "title": "inclusion \\/ exclusion",
                                    "status": "pending"
                                },
                                {
                                    "title": "slug",
                                    "status": "pending"
                                },
                                {
                                    "title": "sku",
                                    "status": "pending"
                                },
                                {
                                    "title": "type",
                                    "status": "pending"
                                },
                                {
                                    "title": "featured",
                                    "status": "pending"
                                },
                                {
                                    "title": "categories",
                                    "status": "pending"
                                },
                                {
                                    "title": "on sale",
                                    "status": "pending"
                                },
                                {
                                    "title": "price",
                                    "status": "pending"
                                },
                                {
                                    "title": "before \\/ after",
                                    "status": "pending"
                                },
                                {
                                    "title": "attributes",
                                    "status": "pending"
                                },
                                {
                                    "title": "status",
                                    "status": "pending"
                                },
                                {
                                    "title": "shipping class",
                                    "status": "pending"
                                },
                                {
                                    "title": "tax class",
                                    "status": "pending"
                                },
                                {
                                    "title": "stock status",
                                    "status": "pending"
                                },
                                {
                                    "title": "tags",
                                    "status": "pending"
                                },
                                {
                                    "title": "parent",
                                    "status": "pending"
                                }
                            ],
                            "Products API tests: List All Products > List all products > orderby": [
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
                                    "title": "title",
                                    "status": "pending"
                                },
                                {
                                    "title": "slug orderby",
                                    "status": "pending"
                                },
                                {
                                    "title": "price orderby",
                                    "status": "pending"
                                },
                                {
                                    "title": "include",
                                    "status": "pending"
                                },
                                {
                                    "title": "rating (desc)",
                                    "status": "pending"
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
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "products\\/products-crud.test.js",
                        "status": "failed",
                        "has_pending": false,
                        "tests": {
                            "Products API tests: CRUD": [
                                {
                                    "title": "can add a simple product",
                                    "status": "failed"
                                },
                                {
                                    "title": "can add a virtual product",
                                    "status": "failed"
                                },
                                {
                                    "title": "can view a single product",
                                    "status": "failed"
                                },
                                {
                                    "title": "can update a single product",
                                    "status": "failed"
                                },
                                {
                                    "title": "can delete a product",
                                    "status": "failed"
                                }
                            ],
                            "Products API tests: CRUD > Product attributes tests: CRUD": [
                                {
                                    "title": "can add a product attribute",
                                    "status": "failed"
                                },
                                {
                                    "title": "can retrieve a product attribute",
                                    "status": "failed"
                                },
                                {
                                    "title": "can retrieve all product attribute",
                                    "status": "failed"
                                },
                                {
                                    "title": "can update a product attribute",
                                    "status": "failed"
                                },
                                {
                                    "title": "can permanently delete a product attribute",
                                    "status": "failed"
                                },
                                {
                                    "title": "can batch update product attributes",
                                    "status": "failed"
                                }
                            ],
                            "Products API tests: CRUD > Product attributes tests: CRUD > Product attribute terms tests: CRUD": [
                                {
                                    "title": "can add a product attribute term",
                                    "status": "failed"
                                },
                                {
                                    "title": "can retrieve a product attribute term",
                                    "status": "failed"
                                },
                                {
                                    "title": "can retrieve all product attribute terms",
                                    "status": "failed"
                                },
                                {
                                    "title": "can update a product attribute term",
                                    "status": "failed"
                                },
                                {
                                    "title": "can permanently delete a product attribute term",
                                    "status": "failed"
                                },
                                {
                                    "title": "can batch update product attribute terms",
                                    "status": "failed"
                                }
                            ],
                            "Products API tests: CRUD > Product categories tests: CRUD": [
                                {
                                    "title": "can add a product category",
                                    "status": "failed"
                                },
                                {
                                    "title": "can retrieve a product category",
                                    "status": "failed"
                                },
                                {
                                    "title": "can retrieve all product categories",
                                    "status": "failed"
                                },
                                {
                                    "title": "can update a product category",
                                    "status": "failed"
                                },
                                {
                                    "title": "can permanently delete a product tag",
                                    "status": "failed"
                                },
                                {
                                    "title": "can batch update product categories",
                                    "status": "failed"
                                }
                            ],
                            "Products API tests: CRUD > Product review tests: CRUD": [
                                {
                                    "title": "can add a product review",
                                    "status": "failed"
                                },
                                {
                                    "title": "cannot add a product review with invalid product_id",
                                    "status": "failed"
                                },
                                {
                                    "title": "cannot add a duplicate product review",
                                    "status": "failed"
                                },
                                {
                                    "title": "can retrieve a product review",
                                    "status": "failed"
                                },
                                {
                                    "title": "can retrieve all product reviews",
                                    "status": "failed"
                                },
                                {
                                    "title": "can update a product review",
                                    "status": "failed"
                                },
                                {
                                    "title": "can permanently delete a product review",
                                    "status": "failed"
                                },
                                {
                                    "title": "can batch update product reviews",
                                    "status": "failed"
                                }
                            ],
                            "Products API tests: CRUD > Product shipping classes tests: CRUD": [
                                {
                                    "title": "can add a product shipping class",
                                    "status": "failed"
                                },
                                {
                                    "title": "can retrieve a product shipping class",
                                    "status": "failed"
                                },
                                {
                                    "title": "can retrieve all product shipping classes",
                                    "status": "failed"
                                },
                                {
                                    "title": "can update a product shipping class",
                                    "status": "failed"
                                },
                                {
                                    "title": "can permanently delete a product shipping class",
                                    "status": "failed"
                                },
                                {
                                    "title": "can batch update product shipping classes",
                                    "status": "failed"
                                }
                            ],
                            "Products API tests: CRUD > Product tags tests: CRUD": [
                                {
                                    "title": "can add a product tag",
                                    "status": "failed"
                                },
                                {
                                    "title": "can retrieve a product tag",
                                    "status": "failed"
                                },
                                {
                                    "title": "can retrieve all product tags",
                                    "status": "failed"
                                },
                                {
                                    "title": "can update a product tag",
                                    "status": "failed"
                                },
                                {
                                    "title": "can permanently delete a product tag",
                                    "status": "failed"
                                },
                                {
                                    "title": "can batch update product tags",
                                    "status": "failed"
                                }
                            ],
                            "Products API tests: CRUD > Product variation tests: CRUD": [
                                {
                                    "title": "can add a variable product",
                                    "status": "failed"
                                },
                                {
                                    "title": "can add a product variation",
                                    "status": "failed"
                                },
                                {
                                    "title": "can retrieve a product variation",
                                    "status": "failed"
                                },
                                {
                                    "title": "can retrieve all product variations",
                                    "status": "failed"
                                },
                                {
                                    "title": "can update a product variation",
                                    "status": "failed"
                                },
                                {
                                    "title": "can permanently delete a product variation",
                                    "status": "failed"
                                },
                                {
                                    "title": "can batch update product variations",
                                    "status": "failed"
                                }
                            ],
                            "Products API tests: CRUD > Batch update products": [
                                {
                                    "title": "can batch create products",
                                    "status": "failed"
                                },
                                {
                                    "title": "can batch update products",
                                    "status": "failed"
                                },
                                {
                                    "title": "can batch delete products",
                                    "status": "failed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "refunds\\/refunds.test.js",
                        "status": "failed",
                        "has_pending": true,
                        "tests": {
                            "Refunds API tests": [
                                {
                                    "title": "can create a refund",
                                    "status": "failed"
                                },
                                {
                                    "title": "can retrieve a refund",
                                    "status": "pending"
                                },
                                {
                                    "title": "can list all refunds",
                                    "status": "pending"
                                },
                                {
                                    "title": "can delete a refund",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "reports\\/reports-crud.test.js",
                        "status": "failed",
                        "has_pending": false,
                        "tests": {
                            "Reports API tests": [
                                {
                                    "title": "can view all reports",
                                    "status": "failed"
                                },
                                {
                                    "title": "can view sales reports",
                                    "status": "failed"
                                },
                                {
                                    "title": "can view top sellers reports",
                                    "status": "failed"
                                },
                                {
                                    "title": "can view coupons totals",
                                    "status": "failed"
                                },
                                {
                                    "title": "can view customers totals",
                                    "status": "failed"
                                },
                                {
                                    "title": "can view orders totals",
                                    "status": "failed"
                                },
                                {
                                    "title": "can view products totals",
                                    "status": "failed"
                                },
                                {
                                    "title": "can view reviews totals",
                                    "status": "failed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "settings\\/settings-crud.test.js",
                        "status": "failed",
                        "has_pending": false,
                        "tests": {
                            "Settings API tests: CRUD": [],
                            "Settings API tests: CRUD > List all settings groups": [
                                {
                                    "title": "can retrieve all settings groups",
                                    "status": "failed"
                                }
                            ],
                            "Settings API tests: CRUD > List all settings options": [
                                {
                                    "title": "can retrieve all general settings",
                                    "status": "failed"
                                }
                            ],
                            "Settings API tests: CRUD > Retrieve a settings option": [
                                {
                                    "title": "can retrieve a settings option",
                                    "status": "failed"
                                }
                            ],
                            "Settings API tests: CRUD > Update a settings option": [
                                {
                                    "title": "can update a settings option",
                                    "status": "failed"
                                }
                            ],
                            "Settings API tests: CRUD > Batch Update a settings option": [
                                {
                                    "title": "can batch update settings options",
                                    "status": "failed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Products settings options": [
                                {
                                    "title": "can retrieve all products settings",
                                    "status": "failed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Tax settings options": [
                                {
                                    "title": "can retrieve all tax settings",
                                    "status": "failed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Shipping settings options": [
                                {
                                    "title": "can retrieve all shipping settings",
                                    "status": "failed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Checkout settings options": [
                                {
                                    "title": "can retrieve all checkout settings",
                                    "status": "failed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Account settings options": [
                                {
                                    "title": "can retrieve all account settings",
                                    "status": "failed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email settings options": [
                                {
                                    "title": "can retrieve all email settings",
                                    "status": "failed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Advanced settings options": [
                                {
                                    "title": "can retrieve all advanced settings",
                                    "status": "failed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email New Order settings": [
                                {
                                    "title": "can retrieve all email new order settings",
                                    "status": "failed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email Failed Order settings": [
                                {
                                    "title": "can retrieve all email failed order settings",
                                    "status": "failed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email Customer On Hold Order settings": [
                                {
                                    "title": "can retrieve all email customer on hold order settings",
                                    "status": "failed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email Customer Processing Order settings": [
                                {
                                    "title": "can retrieve all email customer processing order settings",
                                    "status": "failed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email Customer Completed Order settings": [
                                {
                                    "title": "can retrieve all email customer completed order settings",
                                    "status": "failed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email Customer Refunded Order settings": [
                                {
                                    "title": "can retrieve all email customer refunded order settings",
                                    "status": "failed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email Customer Invoice settings": [
                                {
                                    "title": "can retrieve all email customer invoice settings",
                                    "status": "failed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email Customer Note settings": [
                                {
                                    "title": "can retrieve all email customer note settings",
                                    "status": "failed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email Customer Reset Password settings": [
                                {
                                    "title": "can retrieve all email customer reset password settings",
                                    "status": "failed"
                                }
                            ],
                            "Settings API tests: CRUD > List all Email Customer New Account settings": [
                                {
                                    "title": "can retrieve all email customer new account settings",
                                    "status": "failed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shipping\\/shipping-method.test.js",
                        "status": "failed",
                        "has_pending": false,
                        "tests": {
                            "Shipping methods API tests": [
                                {
                                    "title": "cannot create a shipping method",
                                    "status": "failed"
                                },
                                {
                                    "title": "can retrieve all shipping methods",
                                    "status": "failed"
                                },
                                {
                                    "title": "can retrieve a shipping method",
                                    "status": "failed"
                                },
                                {
                                    "title": "cannot update a shipping method",
                                    "status": "failed"
                                },
                                {
                                    "title": "cannot delete a shipping method",
                                    "status": "failed"
                                },
                                {
                                    "title": "can add a Flat rate shipping method",
                                    "status": "failed"
                                },
                                {
                                    "title": "can add a Free shipping shipping method",
                                    "status": "failed"
                                },
                                {
                                    "title": "can add a Local pickup shipping method",
                                    "status": "failed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shipping\\/shipping-zones.test.js",
                        "status": "failed",
                        "has_pending": false,
                        "tests": {
                            "Shipping zones API tests": [
                                {
                                    "title": "cannot delete the default shipping zone \\"Locations not covered by your other zones\\"",
                                    "status": "failed"
                                },
                                {
                                    "title": "cannot update the default shipping zone",
                                    "status": "failed"
                                },
                                {
                                    "title": "can create a shipping zone",
                                    "status": "failed"
                                },
                                {
                                    "title": "can retrieve a shipping zone",
                                    "status": "failed"
                                },
                                {
                                    "title": "can list all shipping zones",
                                    "status": "failed"
                                },
                                {
                                    "title": "can update a shipping zone",
                                    "status": "failed"
                                },
                                {
                                    "title": "can add a shipping region to a shipping zone",
                                    "status": "failed"
                                },
                                {
                                    "title": "can update a shipping region on a shipping zone",
                                    "status": "failed"
                                },
                                {
                                    "title": "can clear\\/delete a shipping region on a shipping zone",
                                    "status": "failed"
                                },
                                {
                                    "title": "can delete a shipping zone",
                                    "status": "failed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "system-status\\/system-status-crud.test.js",
                        "status": "failed",
                        "has_pending": false,
                        "tests": {
                            "System Status API tests": [
                                {
                                    "title": "can view all system status tools",
                                    "status": "failed"
                                },
                                {
                                    "title": "can retrieve a system status tool",
                                    "status": "failed"
                                },
                                {
                                    "title": "can run a tool from system status",
                                    "status": "failed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "taxes\\/tax-classes-crud.test.js",
                        "status": "failed",
                        "has_pending": false,
                        "tests": {
                            "Tax Classes API tests: CRUD": [],
                            "Tax Classes API tests: CRUD > Create a tax class": [
                                {
                                    "title": "can enable tax calculations",
                                    "status": "failed"
                                },
                                {
                                    "title": "can create a tax class",
                                    "status": "failed"
                                }
                            ],
                            "Tax Classes API tests: CRUD > Retrieve after create": [
                                {
                                    "title": "can retrieve a tax class",
                                    "status": "failed"
                                },
                                {
                                    "title": "can retrieve all tax classes",
                                    "status": "failed"
                                }
                            ],
                            "Tax Classes API tests: CRUD > Update a tax class": [
                                {
                                    "title": "cannot update a tax class",
                                    "status": "failed"
                                }
                            ],
                            "Tax Classes API tests: CRUD > Delete a tax class": [
                                {
                                    "title": "can permanently delete a tax class",
                                    "status": "failed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "taxes\\/tax-rates-crud.test.js",
                        "status": "failed",
                        "has_pending": false,
                        "tests": {
                            "Tax Rates API tests: CRUD": [],
                            "Tax Rates API tests: CRUD > Create a tax rate": [
                                {
                                    "title": "can create a tax rate",
                                    "status": "failed"
                                }
                            ],
                            "Tax Rates API tests: CRUD > Retrieve after create": [
                                {
                                    "title": "can retrieve a tax rate",
                                    "status": "failed"
                                },
                                {
                                    "title": "can retrieve all tax rates",
                                    "status": "failed"
                                }
                            ],
                            "Tax Rates API tests: CRUD > Update a tax rate": [
                                {
                                    "title": "can update a tax rate",
                                    "status": "failed"
                                },
                                {
                                    "title": "retrieve after update tax rate",
                                    "status": "failed"
                                }
                            ],
                            "Tax Rates API tests: CRUD > Delete a tax rate": [
                                {
                                    "title": "can permanently delete a tax rate",
                                    "status": "failed"
                                }
                            ],
                            "Tax Rates API tests: CRUD > Batch tax rate operations": [
                                {
                                    "title": "can batch create tax rates",
                                    "status": "failed"
                                },
                                {
                                    "title": "can batch update tax rates",
                                    "status": "failed"
                                },
                                {
                                    "title": "can batch delete tax rates",
                                    "status": "failed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "webhooks\\/webhooks-crud.test.js",
                        "status": "failed",
                        "has_pending": false,
                        "tests": {
                            "Webhooks API tests": [],
                            "Webhooks API tests > Create a webhook": [
                                {
                                    "title": "can create a webhook",
                                    "status": "failed"
                                }
                            ],
                            "Webhooks API tests > Retrieve after create": [
                                {
                                    "title": "can retrieve a webhook",
                                    "status": "failed"
                                },
                                {
                                    "title": "can retrieve all webhooks",
                                    "status": "failed"
                                }
                            ],
                            "Webhooks API tests > Update a webhook": [
                                {
                                    "title": "can update a web hook",
                                    "status": "failed"
                                }
                            ],
                            "Webhooks API tests > Delete a webhook": [
                                {
                                    "title": "can permanently delete a webhook",
                                    "status": "failed"
                                }
                            ],
                            "Webhooks API tests > Batch webhook operations": [
                                {
                                    "title": "can batch create webhooks",
                                    "status": "failed"
                                },
                                {
                                    "title": "can batch update webhooks",
                                    "status": "failed"
                                },
                                {
                                    "title": "can batch delete webhooks",
                                    "status": "failed"
                                }
                            ]
                        }
                    }
                ],
                "summary": "Test Suites: 0 skipped, 20 failed, 0 passed, 20 total | Tests: 90 skipped, 168 failed, 0 passed, 258 total."
            }
        },
        {
            "debug_log": [
                {
                    "count": "5",
                    "message": "PHP Notice: New Product Editor is enabled as expected. in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php on line 12"
                }
            ]
        }
    ]
]';
