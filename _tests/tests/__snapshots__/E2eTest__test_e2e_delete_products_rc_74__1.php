<?php return '[
    [
        {
            "run_id": 123456,
            "test_type": "e2e",
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
            "test_summary": "Test Suites: 0 skipped, 4 failed, 59 passed, 63 total | Tests: 198 skipped, 7 failed, 41 passed, 246 total.",
            "version": "Undefined",
            "update_complete": true,
            "ai_suggestion_status": "none",
            "malware_whitelist_paths": [],
            "test_result_json_extracted": "{EXTRACTED}",
            "debug_log_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "numFailedTestSuites": 4,
                "numPassedTestSuites": 59,
                "numPendingTestSuites": 0,
                "numTotalTestSuites": 63,
                "numFailedTests": 7,
                "numPassedTests": 41,
                "numPendingTests": 198,
                "numTotalTests": 246,
                "testResults": [
                    {
                        "file": "activate-and-setup\\/basic-setup.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Store owner can finish initial store setup": [
                                {
                                    "title": "can enable tax rates and calculations",
                                    "status": "passed"
                                },
                                {
                                    "title": "can configure permalink settings",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "activate-and-setup\\/setup-onboarding.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Store owner can login and make sure WooCommerce is activated": [
                                {
                                    "title": "can make sure WooCommerce is activated.",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "admin-analytics\\/analytics-overview.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Analytics pages": [
                                {
                                    "title": "a user should see 3 sections by default - Performance, Charts, and Leaderboards",
                                    "status": "passed"
                                },
                                {
                                    "title": "should allow a user to remove a section",
                                    "status": "passed"
                                },
                                {
                                    "title": "should allow a user to add a section back in",
                                    "status": "passed"
                                }
                            ],
                            "Analytics pages > moving sections": [
                                {
                                    "title": "should not display move up for the top, or move down for the bottom section",
                                    "status": "passed"
                                },
                                {
                                    "title": "should allow a user to move a section down",
                                    "status": "passed"
                                },
                                {
                                    "title": "should allow a user to move a section up",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "admin-analytics\\/analytics.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Analytics pages": [
                                {
                                    "title": "A user can view the Overview page without it crashing",
                                    "status": "passed"
                                },
                                {
                                    "title": "A user can view the Products page without it crashing",
                                    "status": "passed"
                                },
                                {
                                    "title": "A user can view the Revenue page without it crashing",
                                    "status": "passed"
                                },
                                {
                                    "title": "A user can view the Orders page without it crashing",
                                    "status": "passed"
                                },
                                {
                                    "title": "A user can view the Variations page without it crashing",
                                    "status": "passed"
                                },
                                {
                                    "title": "A user can view the Categories page without it crashing",
                                    "status": "passed"
                                },
                                {
                                    "title": "A user can view the Coupons page without it crashing",
                                    "status": "passed"
                                },
                                {
                                    "title": "A user can view the Taxes page without it crashing",
                                    "status": "passed"
                                },
                                {
                                    "title": "A user can view the Downloads page without it crashing",
                                    "status": "passed"
                                },
                                {
                                    "title": "A user can view the Stock page without it crashing",
                                    "status": "passed"
                                },
                                {
                                    "title": "A user can view the Settings page without it crashing",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "admin-marketing\\/coupons.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Coupons page": [
                                {
                                    "title": "A user can view the coupons overview without it crashing",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "admin-tasks\\/payment.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Payment setup task": [
                                {
                                    "title": "Can visit the payment setup task from the homescreen if the setup wizard has been skipped",
                                    "status": "passed"
                                },
                                {
                                    "title": "Saving valid bank account transfer details enables the payment method",
                                    "status": "passed"
                                },
                                {
                                    "title": "Enabling cash on delivery enables the payment method",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "admin-tasks\\/webhooks.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Manage webhooks": [
                                {
                                    "title": "Webhook cannot be bulk deleted without nonce",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "basic.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "A basic set of tests to ensure WP, wp-admin and my-account load": [
                                {
                                    "title": "Load the home page",
                                    "status": "passed"
                                }
                            ],
                            "A basic set of tests to ensure WP, wp-admin and my-account load > Sign in as admin": [
                                {
                                    "title": "Load wp-admin",
                                    "status": "passed"
                                }
                            ],
                            "A basic set of tests to ensure WP, wp-admin and my-account load > Sign in as customer": [
                                {
                                    "title": "Load customer my account page",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "customize-store\\/assembler-hub.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Store owner can view Assembler Hub for store customization": [
                                {
                                    "title": "Can view the Assembler Hub page",
                                    "status": "pending"
                                },
                                {
                                    "title": "Visiting change header should show a list of block patterns to choose from",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/command-palette.spec.js",
                        "status": "failed",
                        "has_pending": false,
                        "tests": {
                            "Use Command Palette commands": [
                                {
                                    "title": "can use the \\"Add new product\\" command",
                                    "status": "passed"
                                },
                                {
                                    "title": "can use the \\"Add new order\\" command",
                                    "status": "passed"
                                },
                                {
                                    "title": "can use the \\"Products\\" command",
                                    "status": "passed"
                                },
                                {
                                    "title": "can use the \\"Orders\\" command",
                                    "status": "passed"
                                },
                                {
                                    "title": "can use the product search command",
                                    "status": "failed"
                                },
                                {
                                    "title": "can use a settings command",
                                    "status": "passed"
                                },
                                {
                                    "title": "can use an analytics command",
                                    "status": "failed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/create-order.spec.js",
                        "status": "failed",
                        "has_pending": true,
                        "tests": {
                            "WooCommerce Orders > Add new order": [
                                {
                                    "title": "can create new order",
                                    "status": "failed"
                                },
                                {
                                    "title": "can create new complex order with multiple product types & tax classes",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/create-page.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Can create a new page": [
                                {
                                    "title": "can create new page",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/create-post.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Can create a new post": [
                                {
                                    "title": "can create new post",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/create-shipping-classes.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Merchant can add shipping classes": [
                                {
                                    "title": "can add shipping classes",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/create-shipping-zones.spec.js",
                        "status": "failed",
                        "has_pending": false,
                        "tests": {
                            "WooCommerce Shipping Settings - Add new shipping zone": [
                                {
                                    "title": "add shipping zone for Mayne Island with free Local pickup",
                                    "status": "passed"
                                },
                                {
                                    "title": "add shipping zone for British Columbia with Free shipping",
                                    "status": "passed"
                                },
                                {
                                    "title": "add shipping zone for Canada with Flat rate",
                                    "status": "passed"
                                },
                                {
                                    "title": "add shipping zone with region and then delete the region",
                                    "status": "passed"
                                },
                                {
                                    "title": "add and delete shipping method",
                                    "status": "passed"
                                }
                            ],
                            "Verifies shipping options from customer perspective": [
                                {
                                    "title": "allows customer to benefit from a free Local pickup if on Mayne Island",
                                    "status": "failed"
                                },
                                {
                                    "title": "allows customer to benefit from a free Free shipping if in BC",
                                    "status": "failed"
                                },
                                {
                                    "title": "allows customer to pay for a Flat rate shipping method",
                                    "status": "failed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/create-simple-product.spec.js",
                        "status": "failed",
                        "has_pending": true,
                        "tests": {
                            "Add New Simple Product Page": [
                                {
                                    "title": "can create simple virtual product",
                                    "status": "failed"
                                },
                                {
                                    "title": "can have a shopper add the simple virtual product to the cart",
                                    "status": "pending"
                                },
                                {
                                    "title": "can create simple non-virtual product",
                                    "status": "pending"
                                },
                                {
                                    "title": "can have a shopper add the simple non-virtual product to the cart",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/customer-payment-page.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "WooCommerce Merchant Flow: Orders > Customer Payment Page": [
                                {
                                    "title": "should show the customer payment page link on a pending order",
                                    "status": "pending"
                                },
                                {
                                    "title": "should load the customer payment page",
                                    "status": "pending"
                                },
                                {
                                    "title": "can pay for the order through the customer payment page",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/order-bulk-edit.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Bulk edit orders": [
                                {
                                    "title": "can bulk update order status",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/order-coupon.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "WooCommerce Orders > Apply Coupon": [
                                {
                                    "title": "can apply a coupon",
                                    "status": "pending"
                                },
                                {
                                    "title": "can remove a coupon",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/order-edit.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Edit order": [
                                {
                                    "title": "can view single order",
                                    "status": "pending"
                                },
                                {
                                    "title": "can update order status",
                                    "status": "pending"
                                },
                                {
                                    "title": "can update order status to cancelled",
                                    "status": "pending"
                                },
                                {
                                    "title": "can update order details",
                                    "status": "pending"
                                },
                                {
                                    "title": "can add and delete order notes",
                                    "status": "pending"
                                },
                                {
                                    "title": "can load billing details",
                                    "status": "pending"
                                }
                            ],
                            "Edit order > Downloadable product permissions": [
                                {
                                    "title": "can add downloadable product permissions to order without product",
                                    "status": "pending"
                                },
                                {
                                    "title": "can add downloadable product permissions to order with product",
                                    "status": "pending"
                                },
                                {
                                    "title": "can edit downloadable product permissions",
                                    "status": "pending"
                                },
                                {
                                    "title": "can revoke downloadable product permissions",
                                    "status": "pending"
                                },
                                {
                                    "title": "should not allow downloading a product if download attempts are exceeded",
                                    "status": "pending"
                                },
                                {
                                    "title": "should not allow downloading a product if expiration date has passed",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/order-emails.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Merchant > Order Action emails received": [
                                {
                                    "title": "can receive new order email",
                                    "status": "pending"
                                },
                                {
                                    "title": "can resend new order notification",
                                    "status": "pending"
                                },
                                {
                                    "title": "can email invoice\\/order details to customer",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/order-refund.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "WooCommerce Orders > Refund an order": [
                                {
                                    "title": "can issue a refund by quantity",
                                    "status": "pending"
                                },
                                {
                                    "title": "can delete an issued refund",
                                    "status": "pending"
                                }
                            ],
                            "WooCommerce Orders > Refund and restock an order item": [
                                {
                                    "title": "can update order after refunding item without automatic stock adjustment",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/order-search.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "WooCommerce Orders > Search orders": [
                                {
                                    "title": "can search for order by order id",
                                    "status": "pending"
                                },
                                {
                                    "title": "can search for order containing \\"James\\" as the billing first name",
                                    "status": "pending"
                                },
                                {
                                    "title": "can search for order containing \\"Doe\\" as the billing last name",
                                    "status": "pending"
                                },
                                {
                                    "title": "can search for order containing \\"Automattic\\" as the billing company name",
                                    "status": "pending"
                                },
                                {
                                    "title": "can search for order containing \\"address1\\" as the billing first address",
                                    "status": "pending"
                                },
                                {
                                    "title": "can search for order containing \\"address2\\" as the billing second address",
                                    "status": "pending"
                                },
                                {
                                    "title": "can search for order containing \\"San Francisco\\" as the billing city name",
                                    "status": "pending"
                                },
                                {
                                    "title": "can search for order containing \\"94107\\" as the billing post code",
                                    "status": "pending"
                                },
                                {
                                    "title": "can search for order containing \\"john.doe.ordersearch@example.com\\" as the billing email",
                                    "status": "pending"
                                },
                                {
                                    "title": "can search for order containing \\"123456789\\" as the billing phone",
                                    "status": "pending"
                                },
                                {
                                    "title": "can search for order containing \\"CA\\" as the billing state",
                                    "status": "pending"
                                },
                                {
                                    "title": "can search for order containing \\"Tim\\" as the shipping first name",
                                    "status": "pending"
                                },
                                {
                                    "title": "can search for order containing \\"Clark\\" as the shipping last name",
                                    "status": "pending"
                                },
                                {
                                    "title": "can search for order containing \\"Oxford Ave\\" as the shipping first address",
                                    "status": "pending"
                                },
                                {
                                    "title": "can search for order containing \\"Linwood Ave\\" as the shipping second address",
                                    "status": "pending"
                                },
                                {
                                    "title": "can search for order containing \\"Buffalo\\" as the shipping city name",
                                    "status": "pending"
                                },
                                {
                                    "title": "can search for order containing \\"14201\\" as the shipping post code",
                                    "status": "pending"
                                },
                                {
                                    "title": "can search for order containing \\"Wanted Product\\" as the shipping item name",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/order-status-filter.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "WooCommerce Orders > Filter Order by Status": [
                                {
                                    "title": "should filter by All",
                                    "status": "pending"
                                },
                                {
                                    "title": "should filter by Pending payment",
                                    "status": "pending"
                                },
                                {
                                    "title": "should filter by Processing",
                                    "status": "pending"
                                },
                                {
                                    "title": "should filter by On hold",
                                    "status": "pending"
                                },
                                {
                                    "title": "should filter by Completed",
                                    "status": "pending"
                                },
                                {
                                    "title": "should filter by Cancelled",
                                    "status": "pending"
                                },
                                {
                                    "title": "should filter by Refunded",
                                    "status": "pending"
                                },
                                {
                                    "title": "should filter by Failed",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/page-loads.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "WooCommerce Page Load > Load WooCommerce sub pages": [
                                {
                                    "title": "Can load Home",
                                    "status": "pending"
                                },
                                {
                                    "title": "Can load Orders",
                                    "status": "pending"
                                },
                                {
                                    "title": "Can load Customers",
                                    "status": "pending"
                                },
                                {
                                    "title": "Can load Reports",
                                    "status": "pending"
                                },
                                {
                                    "title": "Can load Settings",
                                    "status": "pending"
                                },
                                {
                                    "title": "Can load Status",
                                    "status": "pending"
                                }
                            ],
                            "WooCommerce Page Load > Load Products sub pages": [
                                {
                                    "title": "Can load All Products",
                                    "status": "pending"
                                },
                                {
                                    "title": "Can load Add New",
                                    "status": "pending"
                                },
                                {
                                    "title": "Can load Categories",
                                    "status": "pending"
                                },
                                {
                                    "title": "Can load Tags",
                                    "status": "pending"
                                },
                                {
                                    "title": "Can load Attributes",
                                    "status": "pending"
                                }
                            ],
                            "WooCommerce Page Load > Load Marketing sub pages": [
                                {
                                    "title": "Can load Overview",
                                    "status": "pending"
                                },
                                {
                                    "title": "Can load Coupons",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/product-edit.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Products > Edit Product": [
                                {
                                    "title": "can edit a product and save the changes",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/product-import-csv.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Import Products from a CSV file": [
                                {
                                    "title": "should show error message if you go without providing CSV file",
                                    "status": "pending"
                                },
                                {
                                    "title": "can upload the CSV file and import products",
                                    "status": "pending"
                                },
                                {
                                    "title": "can override the existing products via CSV import",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/product-search.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Products > Search and View a product": [
                                {
                                    "title": "can do a partial search for a product",
                                    "status": "pending"
                                },
                                {
                                    "title": "can view a product\'s details after search",
                                    "status": "pending"
                                },
                                {
                                    "title": "returns no results for non-existent product search",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/product-settings.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "WooCommerce Products > Downloadable Product Settings": [
                                {
                                    "title": "can update settings",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/products\\/add-variable-product\\/create-product-attributes.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Add product attributes": [
                                {
                                    "title": "can add custom product attributes",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/products\\/add-variable-product\\/create-variable-product.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Add variable product": [
                                {
                                    "title": "can create a variable product",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/products\\/add-variable-product\\/create-variations.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Add variations": [
                                {
                                    "title": "can generate variations from product attributes",
                                    "status": "pending"
                                },
                                {
                                    "title": "can manually add a variation",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/products\\/add-variable-product\\/update-variations.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Update variations": [
                                {
                                    "title": "can individually edit variations",
                                    "status": "pending"
                                },
                                {
                                    "title": "can bulk edit variations",
                                    "status": "pending"
                                },
                                {
                                    "title": "can delete all variations",
                                    "status": "pending"
                                },
                                {
                                    "title": "can manage stock levels",
                                    "status": "pending"
                                },
                                {
                                    "title": "can set variation defaults",
                                    "status": "pending"
                                },
                                {
                                    "title": "can remove a variation",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/products\\/block-editor\\/create-simple-product-block-editor.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "General tab": [],
                            "General tab > Simple product form": [
                                {
                                    "title": "renders each block without error",
                                    "status": "pending"
                                }
                            ],
                            "General tab > Create product": [
                                {
                                    "title": "can create a simple product",
                                    "status": "pending"
                                },
                                {
                                    "title": "can not create a product with duplicated SKU",
                                    "status": "pending"
                                },
                                {
                                    "title": "can a shopper add the simple product to the cart",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/products\\/block-editor\\/enable-block-product-editor.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Enable block product editor": [],
                            "Enable block product editor > Enabled": [
                                {
                                    "title": "is not hooked up to sidebar \\"Add New\\"",
                                    "status": "pending"
                                },
                                {
                                    "title": "can enable the block product editor",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/settings-general.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "WooCommerce General Settings": [
                                {
                                    "title": "can update settings",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/settings-tax.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "WooCommerce Tax Settings > enable": [
                                {
                                    "title": "can enable tax calculation",
                                    "status": "pending"
                                }
                            ],
                            "WooCommerce Tax Settings": [
                                {
                                    "title": "can set tax options",
                                    "status": "pending"
                                },
                                {
                                    "title": "can add tax classes",
                                    "status": "pending"
                                },
                                {
                                    "title": "can set rate settings",
                                    "status": "pending"
                                },
                                {
                                    "title": "can remove tax classes",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/cart-block-calculate-shipping.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Cart Block Calculate Shipping": [
                                {
                                    "title": "create Cart Block page",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows customer to calculate Free Shipping in cart block if in Netherlands",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows customer to calculate Flat rate and Local pickup in cart block if in Portugal",
                                    "status": "pending"
                                },
                                {
                                    "title": "should show correct total cart block price after updating quantity",
                                    "status": "pending"
                                },
                                {
                                    "title": "should show correct total cart block price with 2 different products and flat rate\\/local pickup",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/cart-block-calculate-tax.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Shopper Cart Block Tax Display": [
                                {
                                    "title": "can create Cart Block page",
                                    "status": "pending"
                                },
                                {
                                    "title": "that inclusive tax is displayed properly in Cart Block page",
                                    "status": "pending"
                                },
                                {
                                    "title": "that exclusive tax is displayed properly in Cart Block page",
                                    "status": "pending"
                                }
                            ],
                            "Shopper Cart Block Tax Rounding": [
                                {
                                    "title": "that tax rounding is present at subtotal level",
                                    "status": "pending"
                                },
                                {
                                    "title": "that tax rounding is off at subtotal level",
                                    "status": "pending"
                                }
                            ],
                            "Shopper Cart Block Tax Levels": [
                                {
                                    "title": "that applying taxes in cart block of 4 different levels calculates properly",
                                    "status": "pending"
                                },
                                {
                                    "title": "that applying taxes in Cart Block of 2 different levels (2 excluded) calculates properly",
                                    "status": "pending"
                                }
                            ],
                            "Shipping Cart Block Tax": [
                                {
                                    "title": "that tax is applied in Cart Block to shipping as well as order",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/cart-block-coupons.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Cart Block Applying Coupons": [
                                {
                                    "title": "can create Cart Block page",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows cart block to apply coupon of any type",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows cart block to apply multiple coupons",
                                    "status": "pending"
                                },
                                {
                                    "title": "prevents cart block applying same coupon twice",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/cart-block.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Cart Block page": [
                                {
                                    "title": "can see empty cart block",
                                    "status": "pending"
                                },
                                {
                                    "title": "can add product to cart block, increase quantity, manage cross-sell products and proceed to checkout",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/cart-calculate-shipping.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Cart Calculate Shipping": [
                                {
                                    "title": "allows customer to calculate Free Shipping if in Germany",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows customer to calculate Flat rate and Local pickup if in France",
                                    "status": "pending"
                                },
                                {
                                    "title": "should show correct total cart price after updating quantity",
                                    "status": "pending"
                                },
                                {
                                    "title": "should show correct total cart price with 2 products and flat rate",
                                    "status": "pending"
                                },
                                {
                                    "title": "should show correct total cart price with 2 products without flat rate",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/cart-coupons.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Cart applying coupons": [
                                {
                                    "title": "allows cart to apply coupon of type fixed_cart",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows cart to apply coupon of type percent",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows cart to apply coupon of type fixed_product",
                                    "status": "pending"
                                },
                                {
                                    "title": "prevents cart applying same coupon twice",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows cart to apply multiple coupons",
                                    "status": "pending"
                                },
                                {
                                    "title": "restores cart total when coupons are removed",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/cart-redirection.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Cart > Redirect to cart from shop": [
                                {
                                    "title": "can redirect user to cart from shop page",
                                    "status": "pending"
                                },
                                {
                                    "title": "can redirect user to cart from detail page",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/cart.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Cart page": [
                                {
                                    "title": "should display no item in the cart",
                                    "status": "pending"
                                },
                                {
                                    "title": "should add the product to the cart from the shop page",
                                    "status": "pending"
                                },
                                {
                                    "title": "should increase item quantity when \\"Add to cart\\" of the same product is clicked",
                                    "status": "pending"
                                },
                                {
                                    "title": "should update quantity when updated via quantity input",
                                    "status": "pending"
                                },
                                {
                                    "title": "should remove the item from the cart when remove is clicked",
                                    "status": "pending"
                                },
                                {
                                    "title": "should update subtotal in cart totals when adding product to the cart",
                                    "status": "pending"
                                },
                                {
                                    "title": "should go to the checkout page when \\"Proceed to Checkout\\" is clicked",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/checkout-coupons.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Checkout coupons": [
                                {
                                    "title": "allows checkout to apply coupon of type fixed_cart",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows checkout to apply coupon of type percent",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows checkout to apply coupon of type fixed_product",
                                    "status": "pending"
                                },
                                {
                                    "title": "prevents checkout applying same coupon twice",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows checkout to apply multiple coupons",
                                    "status": "pending"
                                },
                                {
                                    "title": "restores checkout total when coupons are removed",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/checkout-create-account.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Shopper Checkout Create Account": [
                                {
                                    "title": "can create an account during checkout",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/checkout-login.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Shopper Checkout Login Account": [
                                {
                                    "title": "can login to an existing account during checkout",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/checkout.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Checkout page": [
                                {
                                    "title": "should display cart items in order review",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows customer to choose available payment methods",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows customer to fill billing details",
                                    "status": "pending"
                                },
                                {
                                    "title": "warn when customer is missing required details",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows customer to fill shipping details",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows guest customer to place an order",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows existing customer to place order",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/dashboard-access.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Customer-role users are blocked from accessing the WP Dashboard.": [
                                {
                                    "title": "Customer is redirected from WP Admin home back to the My Account page.",
                                    "status": "pending"
                                },
                                {
                                    "title": "Customer is redirected from WP Admin profile page back to the My Account page.",
                                    "status": "pending"
                                },
                                {
                                    "title": "Customer is redirected from WP Admin using ajax query param back to the My Account page.",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/mini-cart.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Mini Cart block page": [
                                {
                                    "title": "can see empty mini cart",
                                    "status": "pending"
                                },
                                {
                                    "title": "can proceed to mini cart, observe it and proceed to the checkout",
                                    "status": "pending"
                                },
                                {
                                    "title": "can see mini cart total price inclusive with tax",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/my-account-addresses.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Customer can manage addresses in My Account > Addresses page": [
                                {
                                    "title": "can add billing address from my account",
                                    "status": "pending"
                                },
                                {
                                    "title": "can add shipping address from my account",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/my-account-create-account.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Shopper My Account Create Account": [
                                {
                                    "title": "can create a new account via my account",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/my-account-downloads.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Customer can manage downloadable file in My Account > Downloads page": [
                                {
                                    "title": "can see downloadable file and click to download it",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/my-account-pay-order.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Customer can pay for their order through My Account": [
                                {
                                    "title": "allows customer to pay for their order in My Account",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/my-account.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "My account page": [
                                {
                                    "title": "allows customer to login",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows customer to see Orders page",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows customer to see Downloads page",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows customer to see Addresses page",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows customer to see Account details page",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/product-grouped.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Grouped Product Page": [
                                {
                                    "title": "should be able to add grouped products to the cart",
                                    "status": "pending"
                                },
                                {
                                    "title": "should be able to remove grouped products from the cart",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/product-simple.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Single Product Page": [
                                {
                                    "title": "should be able to see upsell and related products",
                                    "status": "pending"
                                },
                                {
                                    "title": "should be able to post a review and see it after",
                                    "status": "pending"
                                },
                                {
                                    "title": "should be able to see product description and image",
                                    "status": "pending"
                                },
                                {
                                    "title": "should be able to add simple products to the cart",
                                    "status": "pending"
                                },
                                {
                                    "title": "should be able to remove simple products from the cart",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/product-tags-attributes.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Browse product tags and attributes from the product page": [
                                {
                                    "title": "should see shop catalog with all its products",
                                    "status": "pending"
                                },
                                {
                                    "title": "should see and sort tags page with all the products",
                                    "status": "pending"
                                },
                                {
                                    "title": "should see and sort attributes page with all its products",
                                    "status": "pending"
                                },
                                {
                                    "title": "can see products showcase",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/product-variable.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Variable Product Page": [
                                {
                                    "title": "should be able to add variation products to the cart",
                                    "status": "pending"
                                },
                                {
                                    "title": "should be able to remove variation products from the cart",
                                    "status": "pending"
                                }
                            ],
                            "Shopper > Update variable product": [
                                {
                                    "title": "Shopper can change variable attributes to the same value",
                                    "status": "pending"
                                },
                                {
                                    "title": "Shopper can change attributes to combination with dimensions and weight",
                                    "status": "pending"
                                },
                                {
                                    "title": "Shopper can change variable product attributes to variation with a different price",
                                    "status": "pending"
                                },
                                {
                                    "title": "Shopper can reset variations",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/shop-search-browse-sort.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Search, browse by categories and sort items in the shop": [
                                {
                                    "title": "should let user search the store",
                                    "status": "pending"
                                },
                                {
                                    "title": "should let user browse products by categories",
                                    "status": "pending"
                                },
                                {
                                    "title": "should let user sort the products in the shop",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/tax\\/checkout-calculate-tax.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Tax rates in the cart and checkout": [],
                            "Tax rates in the cart and checkout > Shopper Tax Display Tests": [
                                {
                                    "title": "checks that taxes are calculated properly on totals, inclusive tax displayed properly",
                                    "status": "pending"
                                },
                                {
                                    "title": "checks that taxes are calculated and displayed correctly exclusive on shop, cart and checkout",
                                    "status": "pending"
                                },
                                {
                                    "title": "checks that display suffix is shown",
                                    "status": "pending"
                                }
                            ],
                            "Tax rates in the cart and checkout > Shopper Tax Rounding": [
                                {
                                    "title": "checks rounding at subtotal level",
                                    "status": "pending"
                                },
                                {
                                    "title": "checks rounding off at subtotal level",
                                    "status": "pending"
                                }
                            ],
                            "Tax rates in the cart and checkout > Shopper Tax Levels": [
                                {
                                    "title": "checks applying taxes of 4 different levels",
                                    "status": "pending"
                                },
                                {
                                    "title": "checks applying taxes of 2 different levels (2 excluded)",
                                    "status": "pending"
                                }
                            ],
                            "Tax rates in the cart and checkout > Shipping Tax": [
                                {
                                    "title": "checks that tax is applied to shipping as well as order",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "smoke-tests\\/upload-plugin.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "undefined plugin can be uploaded and activated": [
                                {
                                    "title": "can upload and activate \\"undefined\\"",
                                    "status": "pending"
                                }
                            ]
                        }
                    }
                ],
                "summary": "Test Suites: 0 skipped, 4 failed, 59 passed, 63 total | Tests: 198 skipped, 7 failed, 41 passed, 246 total."
            }
        },
        {
            "debug_log": [
                {
                    "count": "Less than 10",
                    "message": "PHP Notice: Function map_meta_cap was called incorrectly. When checking for the edit_post capability, you must always check it against a specific post. Please see Debugging in WordPress for more information. (This message was added in version 6.1.0.) in \\/var\\/www\\/html\\/wp-includes\\/functions.php on line 6031"
                },
                {
                    "count": "Less than 10",
                    "message": "PHP Notice: Trying to get property \'ID\' of non-object in \\/var\\/www\\/html\\/wp-admin\\/includes\\/post.php on line 2132"
                },
                {
                    "count": "Less than 10",
                    "message": "PHP Notice: Trying to get property \'post_mime_type\' of non-object in \\/var\\/www\\/html\\/wp-admin\\/includes\\/post.php on line 273"
                },
                {
                    "count": "Less than 10",
                    "message": "PHP Notice: Trying to get property \'post_type\' of non-object in \\/var\\/www\\/html\\/wp-admin\\/includes\\/post.php on line 272"
                }
            ]
        }
    ]
]';
