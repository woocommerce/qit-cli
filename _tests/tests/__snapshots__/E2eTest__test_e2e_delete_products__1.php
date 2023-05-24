<?php return '[
    [
        {
            "run_id": 123456,
            "test_type": "e2e",
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
                "hpos": false,
                "new_product_editor": false
            },
            "test_results_manager_url": "https:\\/\\/test-results-manager.com",
            "test_results_manager_expiration": 1234567890,
            "test_summary": "Test Suites: 0 skipped, 6 failed, 36 passed, 42 total | Tests: 137 skipped, 7 failed, 43 passed, 187 total.",
            "version": "Zip",
            "test_result_json_extracted": "{EXTRACTED}",
            "debug_log_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "numFailedTestSuites": 6,
                "numPassedTestSuites": 36,
                "numPendingTestSuites": 0,
                "numTotalTestSuites": 42,
                "numFailedTests": 7,
                "numPassedTests": 43,
                "numPendingTests": 137,
                "numTotalTests": 187,
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
                        "file": "activate-and-setup\\/complete-onboarding-wizard.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Store owner can complete onboarding wizard": [
                                {
                                    "title": "can complete the \\"Store Details\\" section",
                                    "status": "passed"
                                },
                                {
                                    "title": "can complete the industry section",
                                    "status": "passed"
                                },
                                {
                                    "title": "can save industry changes when navigating back to \\"Store Details\\"",
                                    "status": "passed"
                                },
                                {
                                    "title": "can discard industry changes when navigating back to \\"Store Details\\"",
                                    "status": "passed"
                                },
                                {
                                    "title": "can complete the product types section",
                                    "status": "passed"
                                },
                                {
                                    "title": "can complete the business section",
                                    "status": "passed"
                                },
                                {
                                    "title": "can unselect all business features and continue",
                                    "status": "pending"
                                }
                            ],
                            "A Liberian store can complete the selective bundle install but does not include WCPay.": [
                                {
                                    "title": "can choose the \\"Other\\" industry",
                                    "status": "passed"
                                },
                                {
                                    "title": "can choose not to install any extensions",
                                    "status": "passed"
                                },
                                {
                                    "title": "should display the choose payments task, and not the WC Pay task",
                                    "status": "pending"
                                }
                            ],
                            "Store owner can go through setup Task List": [
                                {
                                    "title": "can setup shipping",
                                    "status": "pending"
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
                        "has_pending": true,
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
                                }
                            ],
                            "Verifies shipping options from customer perspective": [
                                {
                                    "title": "allows customer to benefit from a free Local pickup if on Mayne Island",
                                    "status": "failed"
                                },
                                {
                                    "title": "allows customer to benefit from a free Free shipping if in BC",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows customer to pay for a Flat rate shipping method",
                                    "status": "pending"
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
                        "status": "failed",
                        "has_pending": true,
                        "tests": {
                            "WooCommerce Merchant Flow: Orders > Customer Payment Page": [
                                {
                                    "title": "should show the customer payment page link on a pending order",
                                    "status": "failed"
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
                        "file": "merchant\\/order-coupon.spec.js",
                        "status": "failed",
                        "has_pending": true,
                        "tests": {
                            "WooCommerce Orders > Apply Coupon": [
                                {
                                    "title": "can apply a coupon",
                                    "status": "failed"
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
                        "status": "failed",
                        "has_pending": true,
                        "tests": {
                            "Edit order": [
                                {
                                    "title": "can view single order",
                                    "status": "passed"
                                },
                                {
                                    "title": "can update order status",
                                    "status": "passed"
                                },
                                {
                                    "title": "can update order details",
                                    "status": "passed"
                                }
                            ],
                            "Edit order > Downloadable product permissions": [
                                {
                                    "title": "can add downloadable product permissions to order without product",
                                    "status": "failed"
                                },
                                {
                                    "title": "can add downloadable product permissions to order with product",
                                    "status": "failed"
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
                                    "title": "Can load Coupons",
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
                        "file": "new-product-editor\\/new-product-editor.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "New product editor": [],
                            "New product editor > Default (disabled)": [
                                {
                                    "title": "is feature flag disabled",
                                    "status": "pending"
                                },
                                {
                                    "title": "is not hooked up to sidebar \\"Add New\\"",
                                    "status": "pending"
                                }
                            ],
                            "New product editor > Enabled": [
                                {
                                    "title": "is feature flag enabled",
                                    "status": "pending"
                                },
                                {
                                    "title": "is hooked up to sidebar \\"Add New\\"",
                                    "status": "pending"
                                },
                                {
                                    "title": "can be disabled from the header",
                                    "status": "pending"
                                },
                                {
                                    "title": "can be disabled from the feedback footer",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/calculate-shipping.spec.js",
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
                        "file": "shopper\\/product-browse-search-sort.spec.js",
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
                        "file": "shopper\\/single-product.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Single Product Page": [
                                {
                                    "title": "should be able to add simple products to the cart",
                                    "status": "pending"
                                },
                                {
                                    "title": "should be able to remove simple products from the cart",
                                    "status": "pending"
                                }
                            ],
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
                        "file": "shopper\\/variable-product-updates.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Shopper > Update variable product": [
                                {
                                    "title": "Shopper can change variable attributes to the same value",
                                    "status": "pending"
                                },
                                {
                                    "title": "Shopper can change attributes to combination with dimentions and weight",
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
                        "file": "smoke-tests\\/upload-plugin.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "undefined plugin can be uploaded and activated": [
                                {
                                    "title": "can upload and activate undefined",
                                    "status": "pending"
                                }
                            ]
                        }
                    }
                ],
                "summary": "Test Suites: 0 skipped, 6 failed, 36 passed, 42 total | Tests: 137 skipped, 7 failed, 43 passed, 187 total."
            }
        },
        {
            "debug_log": [
                {
                    "count": "8",
                    "message": "The Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Options::get_options function is deprecated since version 3.1."
                },
                {
                    "count": "17",
                    "message": "PHP Fatal error: Uncaught Error: Call to a member function get_id() on bool in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/rest-api\\/Controllers\\/Version2\\/class-wc-rest-products-v2-controller.php:1518\\nStack trace:\\n#0 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php(310): WC_REST_Products_V2_Controller->clear_transients(false)\\n#1 \\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php(332): WP_Hook->apply_filters(\'\', Array)\\n#2 \\/var\\/www\\/html\\/wp-includes\\/plugin.php(517): WP_Hook->do_action(Array)\\n#3 \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/rest-api\\/Controllers\\/Version3\\/class-wc-rest-crud-controller.php(207): do_action(\'woocommerce_res...\', false, Object(WP_REST_Request), true)\\n#4 \\/var\\/www\\/html\\/wp-includes\\/rest-api\\/class-wp-rest-server.php(1181): WC_REST_CRUD_Controller->create_item(Object(WP_REST_Request))\\n#5 \\/var\\/www\\/html\\/wp-includes\\/rest-api\\/class-wp-rest-server.php(1028): WP_REST_Server->respond_to_request(Object(WP_REST_Request), \'\\/wc\\/v3\\/products\', Array, NULL)\\n#6 \\/var\\/www\\/html\\/wp-includes\\/rest-api\\/class-wp-rest-server.p in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/rest-api\\/Controllers\\/Version2\\/class-wc-rest-products-v2-controller.php on line 1518\\n"
                },
                {
                    "count": "3",
                    "message": "PHP Warning: Creating default object from empty value in \\/var\\/www\\/html\\/wp-admin\\/includes\\/post.php on line 762"
                },
                {
                    "count": "3",
                    "message": "PHP Notice: Undefined property: stdClass::$ID in \\/var\\/www\\/html\\/wp-admin\\/post-new.php on line 67"
                },
                {
                    "count": "3",
                    "message": "PHP Notice: Undefined property: stdClass::$post_status in \\/var\\/www\\/html\\/wp-admin\\/edit-form-blocks.php on line 91"
                },
                {
                    "count": "3",
                    "message": "PHP Notice: Undefined property: stdClass::$post_type in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/admin\\/class-wc-admin-post-types.php on line 642"
                },
                {
                    "count": "3",
                    "message": "PHP Notice: Undefined property: stdClass::$post_type in \\/var\\/www\\/html\\/wp-admin\\/includes\\/meta-boxes.php on line 1549"
                },
                {
                    "count": "3",
                    "message": "PHP Notice: Undefined property: stdClass::$post_type in \\/var\\/www\\/html\\/wp-includes\\/taxonomy.php on line 276"
                },
                {
                    "count": "3",
                    "message": "PHP Notice: Undefined property: stdClass::$post_type in \\/var\\/www\\/html\\/wp-includes\\/taxonomy.php on line 279"
                },
                {
                    "count": "3",
                    "message": "PHP Notice: Undefined property: stdClass::$post_status in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/admin\\/class-wc-admin-meta-boxes.php on line 202"
                },
                {
                    "count": "3",
                    "message": "PHP Notice: Undefined property: stdClass::$post_type in \\/var\\/www\\/html\\/wp-admin\\/edit-form-blocks.php on line 298"
                },
                {
                    "count": "3",
                    "message": "PHP Notice: Undefined property: stdClass::$post_status in \\/var\\/www\\/html\\/wp-admin\\/includes\\/post.php on line 2413"
                },
                {
                    "count": "3",
                    "message": "PHP Notice: Undefined property: stdClass::$post_type in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/admin\\/class-wc-admin-post-types.php on line 660"
                },
                {
                    "count": "3",
                    "message": "PHP Notice: Undefined property: stdClass::$post_type in \\/var\\/www\\/html\\/wp-admin\\/includes\\/post.php on line 2451"
                },
                {
                    "count": "3",
                    "message": "PHP Notice: Undefined property: stdClass::$post_status in \\/var\\/www\\/html\\/wp-admin\\/includes\\/post.php on line 2452"
                }
            ]
        }
    ]
]';
