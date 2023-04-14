<?php return '[
    [
        {
            "run_id": 123456,
            "test_type": "e2e",
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
            "woo_extension": {
                "id": 18619,
                "host": "wccom",
                "name": "Google Product Feed"
            },
            "client": "qit_cli",
            "event": "cli_development_extension_test",
            "optional_features": {
                "hpos": false,
                "cc_blocks": false
            },
            "test_results_manager_url": "https:\\/\\/test-results-manager.com",
            "test_results_manager_expiration": 1234567890,
            "test_summary": "Test Suites: 0 skipped, 0 failed, 42 passed, 42 total | Tests: 8 skipped, 0 failed, 180 passed, 188 total.",
            "version": "Zip",
            "test_result_json_extracted": "{EXTRACTED}",
            "debug_log_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "numFailedTestSuites": 0,
                "numPassedTestSuites": 42,
                "numPendingTestSuites": 0,
                "numTotalTestSuites": 42,
                "numFailedTests": 0,
                "numPassedTests": 180,
                "numPendingTests": 8,
                "numTotalTests": 188,
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
                                },
                                {
                                    "title": "can complete the theme selection section",
                                    "status": "passed"
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
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "WooCommerce Orders > Add new order": [
                                {
                                    "title": "can create new order",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create new complex order with multiple product types & tax classes",
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
                        "status": "passed",
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
                                }
                            ],
                            "Verifies shipping options from customer perspective": [
                                {
                                    "title": "allows customer to benefit from a free Local pickup if on Mayne Island",
                                    "status": "passed"
                                },
                                {
                                    "title": "allows customer to benefit from a free Free shipping if in BC",
                                    "status": "passed"
                                },
                                {
                                    "title": "allows customer to pay for a Flat rate shipping method",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/create-simple-product.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Add New Simple Product Page": [
                                {
                                    "title": "can create simple virtual product",
                                    "status": "passed"
                                },
                                {
                                    "title": "can have a shopper add the simple virtual product to the cart",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create simple non-virtual product",
                                    "status": "passed"
                                },
                                {
                                    "title": "can have a shopper add the simple non-virtual product to the cart",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/customer-payment-page.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "WooCommerce Merchant Flow: Orders > Customer Payment Page": [
                                {
                                    "title": "should show the customer payment page link on a pending order",
                                    "status": "passed"
                                },
                                {
                                    "title": "should load the customer payment page",
                                    "status": "passed"
                                },
                                {
                                    "title": "can pay for the order through the customer payment page",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/order-coupon.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "WooCommerce Orders > Apply Coupon": [
                                {
                                    "title": "can apply a coupon",
                                    "status": "passed"
                                },
                                {
                                    "title": "can remove a coupon",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/order-edit.spec.js",
                        "status": "passed",
                        "has_pending": false,
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
                                    "status": "passed"
                                },
                                {
                                    "title": "can add downloadable product permissions to order with product",
                                    "status": "passed"
                                },
                                {
                                    "title": "can edit downloadable product permissions",
                                    "status": "passed"
                                },
                                {
                                    "title": "can revoke downloadable product permissions",
                                    "status": "passed"
                                },
                                {
                                    "title": "should not allow downloading a product if download attempts are exceeded",
                                    "status": "passed"
                                },
                                {
                                    "title": "should not allow downloading a product if expiration date has passed",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/order-emails.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Merchant > Order Action emails received": [
                                {
                                    "title": "can receive new order email",
                                    "status": "passed"
                                },
                                {
                                    "title": "can resend new order notification",
                                    "status": "passed"
                                },
                                {
                                    "title": "can email invoice\\/order details to customer",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/order-refund.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "WooCommerce Orders > Refund an order": [
                                {
                                    "title": "can issue a refund by quantity",
                                    "status": "passed"
                                },
                                {
                                    "title": "can delete an issued refund",
                                    "status": "passed"
                                }
                            ],
                            "WooCommerce Orders > Refund and restock an order item": [
                                {
                                    "title": "can update order after refunding item without automatic stock adjustment",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/order-search.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "WooCommerce Orders > Search orders": [
                                {
                                    "title": "can search for order by order id",
                                    "status": "passed"
                                },
                                {
                                    "title": "can search for order containing \\"James\\" as the billing first name",
                                    "status": "passed"
                                },
                                {
                                    "title": "can search for order containing \\"Doe\\" as the billing last name",
                                    "status": "passed"
                                },
                                {
                                    "title": "can search for order containing \\"Automattic\\" as the billing company name",
                                    "status": "passed"
                                },
                                {
                                    "title": "can search for order containing \\"address1\\" as the billing first address",
                                    "status": "passed"
                                },
                                {
                                    "title": "can search for order containing \\"address2\\" as the billing second address",
                                    "status": "passed"
                                },
                                {
                                    "title": "can search for order containing \\"San Francisco\\" as the billing city name",
                                    "status": "passed"
                                },
                                {
                                    "title": "can search for order containing \\"94107\\" as the billing post code",
                                    "status": "passed"
                                },
                                {
                                    "title": "can search for order containing \\"john.doe.ordersearch@example.com\\" as the billing email",
                                    "status": "passed"
                                },
                                {
                                    "title": "can search for order containing \\"123456789\\" as the billing phone",
                                    "status": "passed"
                                },
                                {
                                    "title": "can search for order containing \\"CA\\" as the billing state",
                                    "status": "passed"
                                },
                                {
                                    "title": "can search for order containing \\"Tim\\" as the shipping first name",
                                    "status": "passed"
                                },
                                {
                                    "title": "can search for order containing \\"Clark\\" as the shipping last name",
                                    "status": "passed"
                                },
                                {
                                    "title": "can search for order containing \\"Oxford Ave\\" as the shipping first address",
                                    "status": "passed"
                                },
                                {
                                    "title": "can search for order containing \\"Linwood Ave\\" as the shipping second address",
                                    "status": "passed"
                                },
                                {
                                    "title": "can search for order containing \\"Buffalo\\" as the shipping city name",
                                    "status": "passed"
                                },
                                {
                                    "title": "can search for order containing \\"14201\\" as the shipping post code",
                                    "status": "passed"
                                },
                                {
                                    "title": "can search for order containing \\"Wanted Product\\" as the shipping item name",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/order-status-filter.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "WooCommerce Orders > Filter Order by Status": [
                                {
                                    "title": "should filter by All",
                                    "status": "passed"
                                },
                                {
                                    "title": "should filter by Pending payment",
                                    "status": "passed"
                                },
                                {
                                    "title": "should filter by Processing",
                                    "status": "passed"
                                },
                                {
                                    "title": "should filter by On hold",
                                    "status": "passed"
                                },
                                {
                                    "title": "should filter by Completed",
                                    "status": "passed"
                                },
                                {
                                    "title": "should filter by Cancelled",
                                    "status": "passed"
                                },
                                {
                                    "title": "should filter by Refunded",
                                    "status": "passed"
                                },
                                {
                                    "title": "should filter by Failed",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/page-loads.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "WooCommerce Page Load > Load WooCommerce sub pages": [
                                {
                                    "title": "Can load Home",
                                    "status": "passed"
                                },
                                {
                                    "title": "Can load Orders",
                                    "status": "passed"
                                },
                                {
                                    "title": "Can load Customers",
                                    "status": "passed"
                                },
                                {
                                    "title": "Can load Coupons",
                                    "status": "passed"
                                },
                                {
                                    "title": "Can load Reports",
                                    "status": "passed"
                                },
                                {
                                    "title": "Can load Settings",
                                    "status": "passed"
                                },
                                {
                                    "title": "Can load Status",
                                    "status": "passed"
                                }
                            ],
                            "WooCommerce Page Load > Load Products sub pages": [
                                {
                                    "title": "Can load All Products",
                                    "status": "passed"
                                },
                                {
                                    "title": "Can load Add New",
                                    "status": "passed"
                                },
                                {
                                    "title": "Can load Categories",
                                    "status": "passed"
                                },
                                {
                                    "title": "Can load Tags",
                                    "status": "passed"
                                },
                                {
                                    "title": "Can load Attributes",
                                    "status": "passed"
                                }
                            ],
                            "WooCommerce Page Load > Load Marketing sub pages": [
                                {
                                    "title": "Can load Overview",
                                    "status": "passed"
                                },
                                {
                                    "title": "Can load Coupons",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/product-edit.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Products > Edit Product": [
                                {
                                    "title": "can edit a product and save the changes",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/product-import-csv.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Import Products from a CSV file": [
                                {
                                    "title": "should show error message if you go without providing CSV file",
                                    "status": "passed"
                                },
                                {
                                    "title": "can upload the CSV file and import products",
                                    "status": "passed"
                                },
                                {
                                    "title": "can override the existing products via CSV import",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/product-search.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Products > Search and View a product": [
                                {
                                    "title": "can do a partial search for a product",
                                    "status": "passed"
                                },
                                {
                                    "title": "can view a product\'s details after search",
                                    "status": "passed"
                                },
                                {
                                    "title": "returns no results for non-existent product search",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/product-settings.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "WooCommerce Products > Downloadable Product Settings": [
                                {
                                    "title": "can update settings",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/settings-general.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "WooCommerce General Settings": [
                                {
                                    "title": "can update settings",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/settings-tax.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "WooCommerce Tax Settings > enable": [
                                {
                                    "title": "can enable tax calculation",
                                    "status": "passed"
                                }
                            ],
                            "WooCommerce Tax Settings": [
                                {
                                    "title": "can set tax options",
                                    "status": "passed"
                                },
                                {
                                    "title": "can add tax classes",
                                    "status": "passed"
                                },
                                {
                                    "title": "can set rate settings",
                                    "status": "passed"
                                },
                                {
                                    "title": "can remove tax classes",
                                    "status": "passed"
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
                                    "status": "passed"
                                },
                                {
                                    "title": "is not hooked up to sidebar \\"Add New\\"",
                                    "status": "passed"
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
                        "has_pending": false,
                        "tests": {
                            "Cart Calculate Shipping": [
                                {
                                    "title": "allows customer to calculate Free Shipping if in Germany",
                                    "status": "passed"
                                },
                                {
                                    "title": "allows customer to calculate Flat rate and Local pickup if in France",
                                    "status": "passed"
                                },
                                {
                                    "title": "should show correct total cart price after updating quantity",
                                    "status": "passed"
                                },
                                {
                                    "title": "should show correct total cart price with 2 products and flat rate",
                                    "status": "passed"
                                },
                                {
                                    "title": "should show correct total cart price with 2 products without flat rate",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/cart-coupons.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Cart applying coupons": [
                                {
                                    "title": "allows cart to apply coupon of type fixed_cart",
                                    "status": "passed"
                                },
                                {
                                    "title": "allows cart to apply coupon of type percent",
                                    "status": "passed"
                                },
                                {
                                    "title": "allows cart to apply coupon of type fixed_product",
                                    "status": "passed"
                                },
                                {
                                    "title": "prevents cart applying same coupon twice",
                                    "status": "passed"
                                },
                                {
                                    "title": "allows cart to apply multiple coupons",
                                    "status": "passed"
                                },
                                {
                                    "title": "restores cart total when coupons are removed",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/cart-redirection.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Cart > Redirect to cart from shop": [
                                {
                                    "title": "can redirect user to cart from shop page",
                                    "status": "passed"
                                },
                                {
                                    "title": "can redirect user to cart from detail page",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/cart.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Cart page": [
                                {
                                    "title": "should display no item in the cart",
                                    "status": "passed"
                                },
                                {
                                    "title": "should add the product to the cart from the shop page",
                                    "status": "passed"
                                },
                                {
                                    "title": "should increase item quantity when \\"Add to cart\\" of the same product is clicked",
                                    "status": "passed"
                                },
                                {
                                    "title": "should update quantity when updated via quantity input",
                                    "status": "passed"
                                },
                                {
                                    "title": "should remove the item from the cart when remove is clicked",
                                    "status": "passed"
                                },
                                {
                                    "title": "should update subtotal in cart totals when adding product to the cart",
                                    "status": "passed"
                                },
                                {
                                    "title": "should go to the checkout page when \\"Proceed to Checkout\\" is clicked",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/checkout-coupons.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Checkout coupons": [
                                {
                                    "title": "allows checkout to apply coupon of type fixed_cart",
                                    "status": "passed"
                                },
                                {
                                    "title": "allows checkout to apply coupon of type percent",
                                    "status": "passed"
                                },
                                {
                                    "title": "allows checkout to apply coupon of type fixed_product",
                                    "status": "passed"
                                },
                                {
                                    "title": "prevents checkout applying same coupon twice",
                                    "status": "passed"
                                },
                                {
                                    "title": "allows checkout to apply multiple coupons",
                                    "status": "passed"
                                },
                                {
                                    "title": "restores checkout total when coupons are removed",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/checkout-create-account.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Shopper Checkout Create Account": [
                                {
                                    "title": "can create an account during checkout",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/checkout-login.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Shopper Checkout Login Account": [
                                {
                                    "title": "can login to an existing account during checkout",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/checkout.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Checkout page": [
                                {
                                    "title": "should display cart items in order review",
                                    "status": "passed"
                                },
                                {
                                    "title": "allows customer to choose available payment methods",
                                    "status": "passed"
                                },
                                {
                                    "title": "allows customer to fill billing details",
                                    "status": "passed"
                                },
                                {
                                    "title": "allows customer to fill shipping details",
                                    "status": "passed"
                                },
                                {
                                    "title": "allows guest customer to place an order",
                                    "status": "passed"
                                },
                                {
                                    "title": "allows existing customer to place order",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/my-account-create-account.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Shopper My Account Create Account": [
                                {
                                    "title": "can create a new account via my account",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/my-account-pay-order.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Customer can pay for their order through My Account": [
                                {
                                    "title": "allows customer to pay for their order in My Account",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/my-account.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "My account page": [
                                {
                                    "title": "allows customer to login",
                                    "status": "passed"
                                },
                                {
                                    "title": "allows customer to see Orders page",
                                    "status": "passed"
                                },
                                {
                                    "title": "allows customer to see Downloads page",
                                    "status": "passed"
                                },
                                {
                                    "title": "allows customer to see Addresses page",
                                    "status": "passed"
                                },
                                {
                                    "title": "allows customer to see Account details page",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/product-browse-search-sort.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Search, browse by categories and sort items in the shop": [
                                {
                                    "title": "should let user search the store",
                                    "status": "passed"
                                },
                                {
                                    "title": "should let user browse products by categories",
                                    "status": "passed"
                                },
                                {
                                    "title": "should let user sort the products in the shop",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/single-product.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Single Product Page": [
                                {
                                    "title": "should be able to add simple products to the cart",
                                    "status": "passed"
                                },
                                {
                                    "title": "should be able to remove simple products from the cart",
                                    "status": "passed"
                                }
                            ],
                            "Variable Product Page": [
                                {
                                    "title": "should be able to add variation products to the cart",
                                    "status": "passed"
                                },
                                {
                                    "title": "should be able to remove variation products from the cart",
                                    "status": "passed"
                                }
                            ],
                            "Grouped Product Page": [
                                {
                                    "title": "should be able to add grouped products to the cart",
                                    "status": "passed"
                                },
                                {
                                    "title": "should be able to remove grouped products from the cart",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/variable-product-updates.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Shopper > Update variable product": [
                                {
                                    "title": "Shopper can change variable attributes to the same value",
                                    "status": "passed"
                                },
                                {
                                    "title": "Shopper can change attributes to combination with dimentions and weight",
                                    "status": "passed"
                                },
                                {
                                    "title": "Shopper can change variable product attributes to variation with a different price",
                                    "status": "passed"
                                },
                                {
                                    "title": "Shopper can reset variations",
                                    "status": "passed"
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
                "summary": "Test Suites: 0 skipped, 0 failed, 42 passed, 42 total | Tests: 8 skipped, 0 failed, 180 passed, 188 total."
            }
        },
        {
            "debug_log": [
                {
                    "count": "3177",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Database\\\\Migrations\\\\CustomOrderTable\\\\PostToOrderTableMigrator::$table_names is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Database\\/Migrations\\/CustomOrderTable\\/PostToOrderTableMigrator.php on line 25"
                },
                {
                    "count": "3177",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Database\\\\Migrations\\\\CustomOrderTable\\\\PostToOrderAddressTableMigrator::$table_names is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Database\\/Migrations\\/CustomOrderTable\\/PostToOrderAddressTableMigrator.php on line 42"
                },
                {
                    "count": "3177",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Database\\\\Migrations\\\\CustomOrderTable\\\\PostToOrderOpTableMigrator::$table_names is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Database\\/Migrations\\/CustomOrderTable\\/PostToOrderOpTableMigrator.php on line 26"
                },
                {
                    "count": "3177",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Database\\\\Migrations\\\\CustomOrderTable\\\\PostMetaToOrderMetaMigrator::$table_names is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Database\\/Migrations\\/CustomOrderTable\\/PostMetaToOrderMetaMigrator.php on line 43"
                },
                {
                    "count": "2805",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Gateway_Paypal::$testmode is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/gateways\\/paypal\\/class-wc-gateway-paypal.php on line 60"
                },
                {
                    "count": "2805",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Gateway_Paypal::$debug is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/gateways\\/paypal\\/class-wc-gateway-paypal.php on line 61"
                },
                {
                    "count": "2805",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Gateway_Paypal::$email is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/gateways\\/paypal\\/class-wc-gateway-paypal.php on line 62"
                },
                {
                    "count": "2805",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Gateway_Paypal::$receiver_email is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/gateways\\/paypal\\/class-wc-gateway-paypal.php on line 63"
                },
                {
                    "count": "2805",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Gateway_Paypal::$identity_token is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/gateways\\/paypal\\/class-wc-gateway-paypal.php on line 64"
                },
                {
                    "count": "953",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Countries::$countries is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-countries.php on line 51"
                },
                {
                    "count": "606",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Cart::$coupon_discount_totals is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/legacy\\/class-wc-legacy-cart.php on line 266"
                },
                {
                    "count": "606",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Cart::$coupon_discount_tax_totals is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/legacy\\/class-wc-legacy-cart.php on line 266"
                },
                {
                    "count": "2542",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Email_New_Order::$email_type is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/emails\\/class-wc-email.php on line 254"
                },
                {
                    "count": "2542",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Email_Cancelled_Order::$email_type is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/emails\\/class-wc-email.php on line 254"
                },
                {
                    "count": "2542",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Email_Failed_Order::$email_type is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/emails\\/class-wc-email.php on line 254"
                },
                {
                    "count": "2542",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Email_Customer_On_Hold_Order::$email_type is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/emails\\/class-wc-email.php on line 254"
                },
                {
                    "count": "2542",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Email_Customer_Processing_Order::$email_type is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/emails\\/class-wc-email.php on line 254"
                },
                {
                    "count": "2542",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Email_Customer_Completed_Order::$email_type is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/emails\\/class-wc-email.php on line 254"
                },
                {
                    "count": "2542",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Email_Customer_Refunded_Order::$email_type is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/emails\\/class-wc-email.php on line 254"
                },
                {
                    "count": "2542",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Email_Customer_Invoice::$email_type is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/emails\\/class-wc-email.php on line 254"
                },
                {
                    "count": "2542",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Email_Customer_Note::$email_type is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/emails\\/class-wc-email.php on line 254"
                },
                {
                    "count": "2542",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Email_Customer_Reset_Password::$email_type is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/emails\\/class-wc-email.php on line 254"
                },
                {
                    "count": "2542",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Email_Customer_New_Account::$email_type is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/emails\\/class-wc-email.php on line 254"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Features is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Notes is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\NoteActions is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Coupons is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Data is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\DataCountries is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\DataDownloadIPs is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Experiments is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Marketing is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\MarketingOverview is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\MarketingRecommendations is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\MarketingChannels is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\MarketingCampaigns is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\MarketingCampaignTypes is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Options is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Orders is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\PaymentGatewaySuggestions is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Products is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\ProductAttributes is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\ProductAttributeTerms is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\ProductCategories is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\ProductVariations is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\ProductReviews is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\ProductsLowInStock is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\SettingOptions is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Themes is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Plugins is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\OnboardingFreeExtensions is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\OnboardingProductTypes is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\OnboardingProfile is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\OnboardingTasks is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\OnboardingThemes is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\NavigationFavorites is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Taxes is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\MobileAppMagicLink is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\ShippingPartnerSuggestions is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Customers is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Leaderboards is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Import\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Export\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Products\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Variations\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Products\\\\Stats\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Variations\\\\Stats\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Revenue\\\\Stats\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Orders\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Orders\\\\Stats\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Categories\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Taxes\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Taxes\\\\Stats\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Coupons\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Coupons\\\\Stats\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Stock\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Stock\\\\Stats\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Downloads\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Downloads\\\\Stats\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Customers\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Customers\\\\Stats\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "2508",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Init::$Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\PerformanceIndicators\\\\Controller is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Init.php on line 143"
                },
                {
                    "count": "4778",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\StoreApi\\\\Schemas\\\\V1\\\\CheckoutSchema::$image_attachment_schema is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/StoreApi\\/Schemas\\/V1\\/CheckoutSchema.php on line 51"
                },
                {
                    "count": "2804",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Gateway_BACS::$instructions is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/gateways\\/bacs\\/class-wc-gateway-bacs.php on line 49"
                },
                {
                    "count": "2804",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Gateway_BACS::$account_details is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/gateways\\/bacs\\/class-wc-gateway-bacs.php on line 52"
                },
                {
                    "count": "2804",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Gateway_Cheque::$instructions is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/gateways\\/cheque\\/class-wc-gateway-cheque.php on line 41"
                },
                {
                    "count": "2804",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Gateway_COD::$instructions is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/gateways\\/cod\\/class-wc-gateway-cod.php on line 40"
                },
                {
                    "count": "2804",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Gateway_COD::$enable_for_methods is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/gateways\\/cod\\/class-wc-gateway-cod.php on line 41"
                },
                {
                    "count": "2804",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Gateway_COD::$enable_for_virtual is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/gateways\\/cod\\/class-wc-gateway-cod.php on line 42"
                },
                {
                    "count": "597",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Shipping_Flat_Rate::$cost is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/shipping\\/flat-rate\\/class-wc-shipping-flat-rate.php on line 50"
                },
                {
                    "count": "597",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Shipping_Flat_Rate::$type is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/shipping\\/flat-rate\\/class-wc-shipping-flat-rate.php on line 51"
                },
                {
                    "count": "1081",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Shipping_Free_Shipping::$ignore_discounts is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/shipping\\/free-shipping\\/class-wc-shipping-free-shipping.php on line 70"
                },
                {
                    "count": "603",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Shipping_Local_Pickup::$cost is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/shipping\\/local-pickup\\/class-wc-shipping-local-pickup.php on line 53"
                },
                {
                    "count": "4",
                    "message": "PHP Deprecated: Automatic conversion of false to array is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/DataSourcePoller.php on line 138"
                },
                {
                    "count": "8953",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\RemoteInboxNotifications\\\\RuleEvaluator::$get_rule_processor is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/RemoteInboxNotifications\\/RuleEvaluator.php on line 22"
                },
                {
                    "count": "5772",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\RemoteInboxNotifications\\\\OrRuleProcessor::$rule_evaluator is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/RemoteInboxNotifications\\/OrRuleProcessor.php on line 22"
                },
                {
                    "count": "303",
                    "message": "PHP Deprecated: Constant FILTER_SANITIZE_STRING is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/wp-mail-logging\\/src\\/inc\\/Admin\\/SettingsTab.php on line 121"
                },
                {
                    "count": "240",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Internal\\\\Admin\\\\WCAdminAssets::$preloaded_dependencies is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Internal\\/Admin\\/WCAdminAssets.php on line 154"
                },
                {
                    "count": "432",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Countries::$states is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-countries.php on line 169"
                },
                {
                    "count": "251",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Customers\\\\DataStore::$report_columns is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Reports\\/Customers\\/DataStore.php on line 64"
                },
                {
                    "count": "252",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Coupons\\\\DataStore::$report_columns is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Reports\\/Coupons\\/DataStore.php on line 58"
                },
                {
                    "count": "252",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Categories\\\\DataStore::$report_columns is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Reports\\/Categories\\/DataStore.php on line 73"
                },
                {
                    "count": "252",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Products\\\\DataStore::$report_columns is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Reports\\/Products\\/DataStore.php on line 91"
                },
                {
                    "count": "2",
                    "message": "PHP Deprecated: round(): Passing null to parameter #1 ($num) of type int|float is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/PageController.php on line 461"
                },
                {
                    "count": "52",
                    "message": "PHP Deprecated: explode(): Passing null to parameter #2 ($string) of type string is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Options.php on line 72"
                },
                {
                    "count": "44",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\RemoteInboxNotifications\\\\NotRuleProcessor::$rule_evaluator is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/RemoteInboxNotifications\\/NotRuleProcessor.php on line 20"
                },
                {
                    "count": "43",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\RemoteInboxNotifications\\\\PluginsActivatedRuleProcessor::$plugins_provider is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/RemoteInboxNotifications\\/PluginsActivatedRuleProcessor.php on line 22"
                },
                {
                    "count": "66",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\RemoteInboxNotifications\\\\PublishAfterTimeRuleProcessor::$date_time_provider is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/RemoteInboxNotifications\\/PublishAfterTimeRuleProcessor.php on line 22"
                },
                {
                    "count": "44",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\RemoteInboxNotifications\\\\PluginVersionRuleProcessor::$plugins_provider is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/RemoteInboxNotifications\\/PluginVersionRuleProcessor.php on line 24"
                },
                {
                    "count": "4",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\RemoteInboxNotifications\\\\PublishBeforeTimeRuleProcessor::$date_time_provider is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/RemoteInboxNotifications\\/PublishBeforeTimeRuleProcessor.php on line 22"
                },
                {
                    "count": "8",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\RemoteInboxNotifications\\\\WCAdminActiveForRuleProcessor::$wcadmin_active_for_provider is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/RemoteInboxNotifications\\/WCAdminActiveForRuleProcessor.php on line 22"
                },
                {
                    "count": "2",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\RemoteInboxNotifications\\\\ProductCountRuleProcessor::$product_query is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/RemoteInboxNotifications\\/ProductCountRuleProcessor.php on line 22"
                },
                {
                    "count": "3",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\RemoteInboxNotifications\\\\OrderCountRuleProcessor::$orders_provider is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/RemoteInboxNotifications\\/OrderCountRuleProcessor.php on line 20"
                },
                {
                    "count": "120",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Orders\\\\Stats\\\\DataStore::$report_columns is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Reports\\/Orders\\/Stats\\/DataStore.php on line 95"
                },
                {
                    "count": "40",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Products\\\\Stats\\\\DataStore::$report_columns is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Reports\\/Products\\/Stats\\/DataStore.php on line 55"
                },
                {
                    "count": "38",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Variations\\\\Stats\\\\DataStore::$report_columns is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Reports\\/Variations\\/Stats\\/DataStore.php on line 51"
                },
                {
                    "count": "16",
                    "message": "The Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Options::get_options function is deprecated since version 3.1."
                },
                {
                    "count": "1",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Orders\\\\DataStore::$report_columns is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Reports\\/Orders\\/DataStore.php on line 75"
                },
                {
                    "count": "1",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Variations\\\\DataStore::$report_columns is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Reports\\/Variations\\/DataStore.php on line 82"
                },
                {
                    "count": "3",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Taxes\\\\Stats\\\\DataStore::$report_columns is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Reports\\/Taxes\\/Stats\\/DataStore.php on line 59"
                },
                {
                    "count": "1",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Taxes\\\\DataStore::$report_columns is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Reports\\/Taxes\\/DataStore.php on line 65"
                },
                {
                    "count": "2",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Downloads\\\\Stats\\\\DataStore::$report_columns is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Reports\\/Downloads\\/Stats\\/DataStore.php on line 47"
                },
                {
                    "count": "1",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Downloads\\\\DataStore::$report_columns is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/API\\/Reports\\/Downloads\\/DataStore.php on line 62"
                },
                {
                    "count": "303",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Countries::$continents is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-countries.php on line 78"
                },
                {
                    "count": "517",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Shipping_Free_Shipping::$method_order is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-shipping-zone.php on line 191"
                },
                {
                    "count": "517",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Shipping_Free_Shipping::$has_settings is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-shipping-zone.php on line 193"
                },
                {
                    "count": "517",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Shipping_Free_Shipping::$settings_html is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-shipping-zone.php on line 194"
                },
                {
                    "count": "4",
                    "message": "PHP Deprecated: substr(): Passing null to parameter #1 ($string) of type string is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/admin\\/class-wc-admin-dashboard-setup.php on line 96"
                },
                {
                    "count": "103",
                    "message": "PHP Deprecated: Creation of dynamic property Automattic\\\\WooCommerce\\\\Admin\\\\Overrides\\\\Order::$refunds is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-order.php on line 1999"
                },
                {
                    "count": "39",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Shipping_Local_Pickup::$method_order is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-shipping-zone.php on line 191"
                },
                {
                    "count": "39",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Shipping_Local_Pickup::$has_settings is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-shipping-zone.php on line 193"
                },
                {
                    "count": "39",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Shipping_Local_Pickup::$settings_html is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-shipping-zone.php on line 194"
                },
                {
                    "count": "32",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Shipping_Flat_Rate::$method_order is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-shipping-zone.php on line 191"
                },
                {
                    "count": "32",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Shipping_Flat_Rate::$has_settings is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-shipping-zone.php on line 193"
                },
                {
                    "count": "32",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Shipping_Flat_Rate::$settings_html is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-shipping-zone.php on line 194"
                },
                {
                    "count": "21",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Order::$refunds is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-order.php on line 1999"
                },
                {
                    "count": "23",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Coupon::$sort is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-cart-totals.php on line 371"
                },
                {
                    "count": "4",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Coupon::$sort is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-cart-totals.php on line 368"
                },
                {
                    "count": "8",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Coupon::$sort is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-cart-totals.php on line 365"
                },
                {
                    "count": "4",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Order_Item_Product::$legacy_values is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-checkout.php on line 517"
                },
                {
                    "count": "4",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Order_Item_Product::$legacy_cart_item_key is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-checkout.php on line 518"
                },
                {
                    "count": "4",
                    "message": "PHP Deprecated: Creation of dynamic property WC_Order_Item_Shipping::$legacy_package_key is deprecated in \\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/includes\\/class-wc-checkout.php on line 604"
                }
            ]
        }
    ]
]';
