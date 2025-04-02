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
            "test_summary": "271 total, 267 passed, 0 failed, 4 skipped.",
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
            "test_group_id": "",
            "test_result_json_extracted": "{EXTRACTED}",
            "ctrf_json_extracted": "{EXTRACTED}",
            "debug_log_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "numFailedTestSuites": 0,
                "numPassedTestSuites": 22,
                "numPendingTestSuites": 3,
                "numTotalTestSuites": 25,
                "numFailedTests": 0,
                "numPassedTests": 267,
                "numPendingTests": 4,
                "numTotalTests": 271,
                "testResults": [
                    {
                        "file": "..\\/fixtures\\/token.teardown.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "remove consumer key": [
                                {
                                    "title": "remove consumer key",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "..\\/fixtures\\/install-wc.setup.js",
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
                        "file": "..\\/fixtures\\/auth.setup.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "authenticate admin": [
                                {
                                    "title": "authenticate admin",
                                    "status": "passed"
                                }
                            ],
                            "authenticate customer": [
                                {
                                    "title": "authenticate customer",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "..\\/fixtures\\/token.setup.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "generate consumer key": [
                                {
                                    "title": "generate consumer key",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "..\\/fixtures\\/site.setup.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "configure HPOS": [
                                {
                                    "title": "configure HPOS",
                                    "status": "passed"
                                }
                            ],
                            "convert Cart and Checkout pages to shortcode": [
                                {
                                    "title": "convert Cart and Checkout pages to shortcode",
                                    "status": "passed"
                                }
                            ],
                            "disable coming soon": [
                                {
                                    "title": "disable coming soon",
                                    "status": "passed"
                                }
                            ],
                            "disable onboarding wizard": [
                                {
                                    "title": "disable onboarding wizard",
                                    "status": "passed"
                                }
                            ],
                            "disable new payments settings page": [
                                {
                                    "title": "disable new payments settings page",
                                    "status": "passed"
                                }
                            ],
                            "determine if multisite": [
                                {
                                    "title": "determine if multisite",
                                    "status": "passed"
                                }
                            ],
                            "general settings": [
                                {
                                    "title": "general settings",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
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
                            "Settings API tests: CRUD > List all Email settings options with Email Improvements feature enabled": [
                                {
                                    "title": "can retrieve all email settings with Email Improvements feature enabled",
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
                "summary": "271 total, 267 passed, 0 failed, 4 skipped."
            }
        },
        {
            "ctrf_json": {
                "results": {
                    "tool": {
                        "name": "playwright"
                    },
                    "summary": {
                        "tests": 271,
                        "passed": 267,
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [
                                "e2e-api-access-1743613382700 consumer token successfully created\\n"
                            ],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [
                                "DISABLE_HPOS: undefined\\n",
                                "HPOS configuration (woocommerce_custom_orders_table_enabled): yes - High-performance order storage (recommended)\\n"
                            ],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "disable new payments settings page",
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [
                                "apiRequestContext.post: 400 Bad Request\\nResponse text:\\n\\"Delete option FAILED: woocommerce_gateway_order\\"\\nCall log:\\n\\u001b[2m  - \\u2192 POST http:\\/\\/qit-runner.test\\/wp-json\\/e2e-options\\/delete\\u001b[22m\\n\\u001b[2m    - user-agent: Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.6998.35 Safari\\/537.36\\u001b[22m\\n\\u001b[2m    - accept: *\\/*\\u001b[22m\\n\\u001b[2m    - accept-encoding: gzip,deflate,br\\u001b[22m\\n\\u001b[2m    - Authorization: Basic YWRtaW46cGFzc3dvcmQ=\\u001b[22m\\n\\u001b[2m    - cookie:\\u001b[22m\\n\\u001b[2m    - content-type: application\\/json\\u001b[22m\\n\\u001b[2m    - content-length: 43\\u001b[22m\\n\\u001b[2m  - \\u2190 400 Bad Request\\u001b[22m\\n\\u001b[2m    - server: nginx\\/1.24.0\\u001b[22m\\n\\u001b[2m    - date: Wed, 02 Apr 2025 17:03:38 GMT\\u001b[22m\\n\\u001b[2m    - content-type: application\\/json; charset=UTF-8\\u001b[22m\\n\\u001b[2m    - transfer-encoding: chunked\\u001b[22m\\n\\u001b[2m    - connection: keep-alive\\u001b[22m\\n\\u001b[2m    - x-powered-by: PHP\\/8.2.28\\u001b[22m\\n\\u001b[2m    - x-robots-tag: noindex\\u001b[22m\\n\\u001b[2m    - link: <http:\\/\\/qit-runner.test\\/wp-json\\/>; rel=\\"https:\\/\\/api.w.org\\/\\"\\u001b[22m\\n\\u001b[2m    - x-content-type-options: nosniff\\u001b[22m\\n\\u001b[2m    - access-control-expose-headers: X-WP-Total, X-WP-TotalPages, Link\\u001b[22m\\n\\u001b[2m    - access-control-allow-headers: Authorization, X-WP-Nonce, Content-Disposition, Content-MD5, Content-Type\\u001b[22m\\n\\u001b[2m    - allow: POST\\u001b[22m\\n\\u001b[2m    - expires: Wed, 11 Jan 1984 05:00:00 GMT\\u001b[22m\\n\\u001b[2m    - cache-control: no-cache, must-revalidate, max-age=0, no-store, private\\u001b[22m\\n\\n    at deleteOption \\u001b[90m(\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/\\u001b[39mwoo-api\\/utils\\/options.js:47:4\\u001b[90m)\\u001b[39m\\n    at resetGatewayOrder \\u001b[90m(\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/\\u001b[39mwoo-api\\/utils\\/payments-settings.js:19:3\\u001b[90m)\\u001b[39m\\n    at \\u001b[90m\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/\\u001b[39mwoo-api\\/tests\\/api-tests\\/payment-gateways\\/payment-gateways-crud.test.js:15:3 {\\n  name: \\u001b[32m\'Error\'\\u001b[39m,\\n  [\\u001b[32mSymbol(step)\\u001b[39m]: {\\n    stepId: \\u001b[32m\'pw:api@4\'\\u001b[39m,\\n    location: {\\n      file: \\u001b[32m\'\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/utils\\/options.js\'\\u001b[39m,\\n      line: \\u001b[33m47\\u001b[39m,\\n      column: \\u001b[33m4\\u001b[39m,\\n      function: \\u001b[32m\'deleteOption\'\\u001b[39m\\n    },\\n    category: \\u001b[32m\'pw:api\'\\u001b[39m,\\n    title: \\u001b[32m\'apiRequestContext.post(.\\/wp-json\\/e2e-options\\/delete)\'\\u001b[39m,\\n    apiName: \\u001b[32m\'apiRequestContext.post\'\\u001b[39m,\\n    params: {\\n      url: \\u001b[32m\'.\\/wp-json\\/e2e-options\\/delete\'\\u001b[39m,\\n      params: \\u001b[90mundefined\\u001b[39m,\\n      encodedParams: \\u001b[90mundefined\\u001b[39m,\\n      method: \\u001b[32m\'POST\'\\u001b[39m,\\n      headers: \\u001b[90mundefined\\u001b[39m,\\n      postData: \\u001b[90mundefined\\u001b[39m,\\n      jsonData: \\u001b[32m\'{\\"option_name\\":\\"woocommerce_gateway_order\\"}\'\\u001b[39m,\\n      formData: \\u001b[90mundefined\\u001b[39m,\\n      multipartData: \\u001b[90mundefined\\u001b[39m,\\n      timeout: \\u001b[90mundefined\\u001b[39m,\\n      failOnStatusCode: \\u001b[33mtrue\\u001b[39m,\\n      ignoreHTTPSErrors: \\u001b[90mundefined\\u001b[39m,\\n      maxRedirects: \\u001b[90mundefined\\u001b[39m,\\n      maxRetries: \\u001b[90mundefined\\u001b[39m,\\n      __testHookLookup: \\u001b[90mundefined\\u001b[39m\\n    },\\n    boxedStack: \\u001b[90mundefined\\u001b[39m,\\n    steps: [],\\n    attachmentIndices: [],\\n    info: TestStepInfoImpl {\\n      annotations: [],\\n      _testInfo: \\u001b[36m[TestInfoImpl]\\u001b[39m,\\n      _stepId: \\u001b[32m\'pw:api@4\'\\u001b[39m\\n    },\\n    complete: \\u001b[36m[Function: complete]\\u001b[39m,\\n    endWallTime: \\u001b[33m1743613418575\\u001b[39m,\\n    error: {\\n      message: \\u001b[32m\'Error: apiRequestContext.post: 400 Bad Request\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'Response text:\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\"Delete option FAILED: woocommerce_gateway_order\\"\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'Call log:\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m  - \\u2192 POST http:\\/\\/qit-runner.test\\/wp-json\\/e2e-options\\/delete\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - user-agent: Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.6998.35 Safari\\/537.36\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - accept: *\\/*\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - accept-encoding: gzip,deflate,br\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - Authorization: Basic YWRtaW46cGFzc3dvcmQ=\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - cookie:\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - content-type: application\\/json\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - content-length: 43\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m  - \\u2190 400 Bad Request\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - server: nginx\\/1.24.0\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - date: Wed, 02 Apr 2025 17:03:38 GMT\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - content-type: application\\/json; charset=UTF-8\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - transfer-encoding: chunked\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - connection: keep-alive\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - x-powered-by: PHP\\/8.2.28\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - x-robots-tag: noindex\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - link: <http:\\/\\/qit-runner.test\\/wp-json\\/>; rel=\\"https:\\/\\/api.w.org\\/\\"\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - x-content-type-options: nosniff\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - access-control-expose-headers: X-WP-Total, X-WP-TotalPages, Link\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - access-control-allow-headers: Authorization, X-WP-Nonce, Content-Disposition, Content-MD5, Content-Type\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - allow: POST\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - expires: Wed, 11 Jan 1984 05:00:00 GMT\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - cache-control: no-cache, must-revalidate, max-age=0, no-store, private\\\\x1B[22m\\\\n\'\\u001b[39m,\\n      stack: \\u001b[32m\'Error: apiRequestContext.post: 400 Bad Request\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'Response text:\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\"Delete option FAILED: woocommerce_gateway_order\\"\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'Call log:\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m  - \\u2192 POST http:\\/\\/qit-runner.test\\/wp-json\\/e2e-options\\/delete\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - user-agent: Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.6998.35 Safari\\/537.36\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - accept: *\\/*\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - accept-encoding: gzip,deflate,br\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - Authorization: Basic YWRtaW46cGFzc3dvcmQ=\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - cookie:\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - content-type: application\\/json\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - content-length: 43\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m  - \\u2190 400 Bad Request\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - server: nginx\\/1.24.0\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - date: Wed, 02 Apr 2025 17:03:38 GMT\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - content-type: application\\/json; charset=UTF-8\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - transfer-encoding: chunked\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - connection: keep-alive\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - x-powered-by: PHP\\/8.2.28\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - x-robots-tag: noindex\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - link: <http:\\/\\/qit-runner.test\\/wp-json\\/>; rel=\\"https:\\/\\/api.w.org\\/\\"\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - x-content-type-options: nosniff\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - access-control-expose-headers: X-WP-Total, X-WP-TotalPages, Link\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - access-control-allow-headers: Authorization, X-WP-Nonce, Content-Disposition, Content-MD5, Content-Type\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - allow: POST\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - expires: Wed, 11 Jan 1984 05:00:00 GMT\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\x1B[2m    - cache-control: no-cache, must-revalidate, max-age=0, no-store, private\\\\x1B[22m\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'    at deleteOption (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/utils\\/options.js:47:4)\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'    at resetGatewayOrder (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/utils\\/payments-settings.js:19:3)\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/payment-gateways\\/payment-gateways-crud.test.js:15:3\'\\u001b[39m,\\n      cause: \\u001b[90mundefined\\u001b[39m\\n    }\\n  }\\n}\\n",
                                "apiRequestContext.post: Invalid URL\\n    at setOption \\u001b[90m(\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/\\u001b[39mwoo-api\\/utils\\/options.js:25:4\\u001b[90m)\\u001b[39m\\n    at setNewPaymentsSettingsPage \\u001b[90m(\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/\\u001b[39mwoo-api\\/utils\\/payments-settings.js:6:3\\u001b[90m)\\u001b[39m\\n    at disableNewPaymentsSettingsFeature \\u001b[90m(\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/\\u001b[39mwoo-api\\/tests\\/api-tests\\/payment-gateways\\/payment-gateways-crud.test.js:10:2\\u001b[90m)\\u001b[39m\\n    at \\u001b[90m\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/\\u001b[39mwoo-api\\/tests\\/api-tests\\/payment-gateways\\/payment-gateways-crud.test.js:16:3 {\\n  name: \\u001b[32m\'TypeError\'\\u001b[39m,\\n  [\\u001b[32mSymbol(step)\\u001b[39m]: {\\n    stepId: \\u001b[32m\'pw:api@6\'\\u001b[39m,\\n    location: {\\n      file: \\u001b[32m\'\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/utils\\/options.js\'\\u001b[39m,\\n      line: \\u001b[33m25\\u001b[39m,\\n      column: \\u001b[33m4\\u001b[39m,\\n      function: \\u001b[32m\'setOption\'\\u001b[39m\\n    },\\n    category: \\u001b[32m\'pw:api\'\\u001b[39m,\\n    title: \\u001b[32m\'apiRequestContext.post(.\\/wp-json\\/e2e-options\\/update)\'\\u001b[39m,\\n    apiName: \\u001b[32m\'apiRequestContext.post\'\\u001b[39m,\\n    params: {\\n      url: \\u001b[32m\'.\\/wp-json\\/e2e-options\\/update\'\\u001b[39m,\\n      params: \\u001b[90mundefined\\u001b[39m,\\n      encodedParams: \\u001b[90mundefined\\u001b[39m,\\n      method: \\u001b[32m\'POST\'\\u001b[39m,\\n      headers: \\u001b[90mundefined\\u001b[39m,\\n      postData: \\u001b[90mundefined\\u001b[39m,\\n      jsonData: \\u001b[32m\'{\\"option_name\\":\\"woocommerce_feature_reactify-classic-payments-settings_enabled\\",\\"option_value\\":\\"no\\"}\'\\u001b[39m,\\n      formData: \\u001b[90mundefined\\u001b[39m,\\n      multipartData: \\u001b[90mundefined\\u001b[39m,\\n      timeout: \\u001b[90mundefined\\u001b[39m,\\n      failOnStatusCode: \\u001b[33mtrue\\u001b[39m,\\n      ignoreHTTPSErrors: \\u001b[90mundefined\\u001b[39m,\\n      maxRedirects: \\u001b[90mundefined\\u001b[39m,\\n      maxRetries: \\u001b[90mundefined\\u001b[39m,\\n      __testHookLookup: \\u001b[90mundefined\\u001b[39m\\n    },\\n    boxedStack: \\u001b[90mundefined\\u001b[39m,\\n    steps: [],\\n    attachmentIndices: [],\\n    info: TestStepInfoImpl {\\n      annotations: [],\\n      _testInfo: \\u001b[36m[TestInfoImpl]\\u001b[39m,\\n      _stepId: \\u001b[32m\'pw:api@6\'\\u001b[39m\\n    },\\n    complete: \\u001b[36m[Function: complete]\\u001b[39m,\\n    endWallTime: \\u001b[33m1743613418587\\u001b[39m,\\n    error: {\\n      message: \\u001b[32m\'TypeError: apiRequestContext.post: Invalid URL\'\\u001b[39m,\\n      stack: \\u001b[32m\'TypeError: apiRequestContext.post: Invalid URL\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'    at setOption (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/utils\\/options.js:25:4)\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'    at setNewPaymentsSettingsPage (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/utils\\/payments-settings.js:6:3)\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'    at disableNewPaymentsSettingsFeature (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/payment-gateways\\/payment-gateways-crud.test.js:10:2)\\\\n\'\\u001b[39m +\\n        \\u001b[32m\'    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-api\\/tests\\/api-tests\\/payment-gateways\\/payment-gateways-crud.test.js:16:3\'\\u001b[39m,\\n      cause: \\u001b[90mundefined\\u001b[39m\\n    }\\n  }\\n}\\n"
                            ],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                            "attachments": [],
                            "stdout": [],
                            "stderr": [],
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
                        "message": "WordPress database error Table \'wordpress.wp_wc_tax_rate_classes\' doesn\'t exist for query \\n\\t\\t\\t\\tSELECT * FROM wp_wc_tax_rate_classes ORDER BY name;\\n\\n\\t\\t\\t\\t made by include(\'phar:\\/\\/\\/usr\\/local\\/bin\\/wp\\/php\\/boot-phar.php\'), include(\'phar:\\/\\/\\/usr\\/local\\/bin\\/wp\\/vendor\\/wp-cli\\/wp-cli\\/php\\/wp-cli.php\'), WP_CLI\\\\bootstrap, WP_CLI\\\\Bootstrap\\\\LaunchRunner->process, WP_CLI\\\\Runner->start, WP_CLI\\\\Runner->run_command_and_exit, WP_CLI\\\\Runner->run_command, WP_CLI\\\\Dispatcher\\\\Subcommand->invoke, call_user_func, WP_CLI\\\\Dispatcher\\\\CommandFactory::WP_CLI\\\\Dispatcher\\\\{closure}, call_user_func, Plugin_Command->activate, activate_plugin, plugin_sandbox_scrape, include_once(\'\\/plugins\\/woocommerce\\/woocommerce.php\'), WC, WooCommerce::instance, WooCommerce->__construct, WooCommerce->includes, include_once(\'\\/plugins\\/woocommerce\\/includes\\/class-wc-cli.php\'), WC_CLI->__construct, WC_CLI->hooks, WP_CLI::add_hook, WC_CLI_Runner::after_wp_load, do_action(\'rest_api_init\'), WP_Hook->do_action, WP_Hook->apply_filters, Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init->rest_api_init, WC_REST_Taxes_V1_Controller->register_routes, Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Taxes->get_collection_params, WC_REST_Taxes_V1_Controller->get_collection_params, WP_REST_Controller->get_context_param, WC_REST_Taxes_Controller->get_item_schema, WC_REST_Taxes_V1_Controller->get_item_schema, WC_Tax::get_tax_class_slugs, WC_Tax::get_tax_rate_classes"
                    }
                ]
            }
        }
    ]
]';
