<?php return '[
    [
        {
            "test_run_id": 123456,
            "run_id": 123456,
            "test_type": "woo-e2e",
            "test_type_display": "Woo E2E",
            "wordpress_version": "6.0.0-normalized",
            "woocommerce_version": "6.0.0-normalized",
            "php_version": "8.2",
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
                "hpos": false,
                "new_product_editor": false
            },
            "test_results_manager_url": "https:\\/\\/test-results-manager.com",
            "test_results_manager_expiration": 1234567890,
            "test_summary": "Tests: 339 total, 289 passed, 0 failed, 50 skipped",
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
                "numPassedTestSuites": 70,
                "numPendingTestSuites": 21,
                "numTotalTestSuites": 91,
                "numFailedTests": 0,
                "numPassedTests": 289,
                "numPendingTests": 50,
                "numTotalTests": 339,
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
                        "file": "analytics\\/analytics-access.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "WooCommerce Home": [
                                {
                                    "title": "Can access Analytics Reports from Stats Overview",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "analytics\\/analytics-overview.spec.ts",
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
                            ],
                            "Analytics Overview - Manual Import Trigger": [
                                {
                                    "title": "should show manual update trigger in scheduled mode",
                                    "status": "passed"
                                },
                                {
                                    "title": "should hide manual update trigger in immediate mode",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "analytics\\/analytics-settings.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Analytics Settings - Scheduled Import": [
                                {
                                    "title": "should show Immediate mode by default when option is not set",
                                    "status": "passed"
                                },
                                {
                                    "title": "should switch from scheduled to immediate mode with confirmation modal - cancel flow",
                                    "status": "passed"
                                },
                                {
                                    "title": "should switch from scheduled to immediate mode with confirmation modal - confirm flow",
                                    "status": "passed"
                                },
                                {
                                    "title": "should switch from immediate to scheduled mode without confirmation modal",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "basic\\/basic.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Load the home page": [
                                {
                                    "title": "Load the home page",
                                    "status": "passed"
                                }
                            ],
                            "Load wp-admin as admin": [
                                {
                                    "title": "Load wp-admin as admin",
                                    "status": "passed"
                                }
                            ],
                            "Load my account page as customer": [
                                {
                                    "title": "Load my account page as customer",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "basic\\/dashboard-access.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Customer-role users are blocked from accessing the WP Dashboard.": [
                                {
                                    "title": "Customer is redirected from WP Admin home back to the My Account page.",
                                    "status": "passed"
                                },
                                {
                                    "title": "Customer is redirected from WP Admin profile page back to the My Account page.",
                                    "status": "passed"
                                },
                                {
                                    "title": "Customer is redirected from WP Admin using ajax query param back to the My Account page.",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "basic\\/page-loads.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "can load WooCommerce > Home page": [
                                {
                                    "title": "can load WooCommerce > Home page",
                                    "status": "passed"
                                }
                            ],
                            "can load WooCommerce > Orders page": [
                                {
                                    "title": "can load WooCommerce > Orders page",
                                    "status": "passed"
                                }
                            ],
                            "can load WooCommerce > Customers page": [
                                {
                                    "title": "can load WooCommerce > Customers page",
                                    "status": "passed"
                                }
                            ],
                            "can load WooCommerce > Reports page": [
                                {
                                    "title": "can load WooCommerce > Reports page",
                                    "status": "passed"
                                }
                            ],
                            "can load WooCommerce > Settings page": [
                                {
                                    "title": "can load WooCommerce > Settings page",
                                    "status": "passed"
                                }
                            ],
                            "can load WooCommerce > Status page": [
                                {
                                    "title": "can load WooCommerce > Status page",
                                    "status": "passed"
                                }
                            ],
                            "can load Products > All Products page": [
                                {
                                    "title": "can load Products > All Products page",
                                    "status": "passed"
                                }
                            ],
                            "can load Products > Add new product page": [
                                {
                                    "title": "can load Products > Add new product page",
                                    "status": "passed"
                                }
                            ],
                            "can load Products > Categories page": [
                                {
                                    "title": "can load Products > Categories page",
                                    "status": "passed"
                                }
                            ],
                            "can load Products > Tags page": [
                                {
                                    "title": "can load Products > Tags page",
                                    "status": "passed"
                                }
                            ],
                            "can load Products > Attributes page": [
                                {
                                    "title": "can load Products > Attributes page",
                                    "status": "passed"
                                }
                            ],
                            "can load Analytics > Overview page": [
                                {
                                    "title": "can load Analytics > Overview page",
                                    "status": "passed"
                                }
                            ],
                            "can load Analytics > Products page": [
                                {
                                    "title": "can load Analytics > Products page",
                                    "status": "passed"
                                }
                            ],
                            "can load Analytics > Revenue page": [
                                {
                                    "title": "can load Analytics > Revenue page",
                                    "status": "passed"
                                }
                            ],
                            "can load Analytics > Orders page": [
                                {
                                    "title": "can load Analytics > Orders page",
                                    "status": "passed"
                                }
                            ],
                            "can load Analytics > Variations page": [
                                {
                                    "title": "can load Analytics > Variations page",
                                    "status": "passed"
                                }
                            ],
                            "can load Analytics > Categories page": [
                                {
                                    "title": "can load Analytics > Categories page",
                                    "status": "passed"
                                }
                            ],
                            "can load Analytics > Coupons page": [
                                {
                                    "title": "can load Analytics > Coupons page",
                                    "status": "passed"
                                }
                            ],
                            "can load Analytics > Taxes page": [
                                {
                                    "title": "can load Analytics > Taxes page",
                                    "status": "passed"
                                }
                            ],
                            "can load Analytics > Downloads page": [
                                {
                                    "title": "can load Analytics > Downloads page",
                                    "status": "passed"
                                }
                            ],
                            "can load Analytics > Stock page": [
                                {
                                    "title": "can load Analytics > Stock page",
                                    "status": "passed"
                                }
                            ],
                            "can load Analytics > Settings page": [
                                {
                                    "title": "can load Analytics > Settings page",
                                    "status": "passed"
                                }
                            ],
                            "can load Marketing > Overview page": [
                                {
                                    "title": "can load Marketing > Overview page",
                                    "status": "passed"
                                }
                            ],
                            "can load Marketing > Coupons page": [
                                {
                                    "title": "can load Marketing > Coupons page",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "brands\\/create-product-brand.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Merchant can add brands": [
                                {
                                    "title": "Merchant can add brands",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "cart\\/add-to-cart.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Add to Cart behavior": [
                                {
                                    "title": "should add only one product to the cart with AJAX add to cart buttons disabled and \\"Geolocate (with page caching support)\\" as the default customer location",
                                    "status": "passed"
                                },
                                {
                                    "title": "should be able to navigate and remove item from mini cart using keyboard",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "cart\\/cart.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "can undo product removal in classic cart": [
                                {
                                    "title": "can undo product removal in classic cart",
                                    "status": "passed"
                                }
                            ],
                            "can add and remove products, increase quantity and proceed to checkout - blocks cart": [
                                {
                                    "title": "can add and remove products, increase quantity and proceed to checkout - blocks cart",
                                    "status": "passed"
                                }
                            ],
                            "can add and remove products, increase quantity and proceed to checkout - classic cart": [
                                {
                                    "title": "can add and remove products, increase quantity and proceed to checkout - classic cart",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "checkout\\/checkout-link.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Checkout Link Endpoint": [],
                            "Checkout Link Endpoint > Guest user": [
                                {
                                    "title": "Guest user redirected to checkout with correct cart",
                                    "status": "passed"
                                },
                                {
                                    "title": "Guest user sees error when invalid coupon is applied",
                                    "status": "passed"
                                },
                                {
                                    "title": "Guest user sees error when invalid products are provided",
                                    "status": "passed"
                                },
                                {
                                    "title": "Guest user sees error when invalid product is provided",
                                    "status": "passed"
                                },
                                {
                                    "title": "Guest user sees error when invalid link is provided",
                                    "status": "passed"
                                }
                            ],
                            "Checkout Link Endpoint > Logged-in user": [
                                {
                                    "title": "Logged-in user redirected to checkout with correct cart",
                                    "status": "passed"
                                },
                                {
                                    "title": "Logged-in user sees error when invalid coupon is applied",
                                    "status": "passed"
                                },
                                {
                                    "title": "Logged-in user sees error when invalid products are provided",
                                    "status": "passed"
                                },
                                {
                                    "title": "Logged-in user sees error when invalid product is provided",
                                    "status": "passed"
                                },
                                {
                                    "title": "Logged-in user sees error when invalid link is provided",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "checkout\\/checkout-shortcode-custom-place-order-button.spec.ts",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Shortcode Checkout Custom Place Order Button": [
                                {
                                    "title": "clicking custom button triggers validation when form is invalid",
                                    "status": "pending"
                                },
                                {
                                    "title": "switching between gateways shows\\/hides custom button",
                                    "status": "pending"
                                },
                                {
                                    "title": "clicking custom button submits order when form is valid",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "checkout\\/checkout.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "guest can checkout paying with cash on delivery on blocks checkout": [
                                {
                                    "title": "guest can checkout paying with cash on delivery on blocks checkout",
                                    "status": "passed"
                                }
                            ],
                            "guest can checkout paying with cash on delivery on classic checkout": [
                                {
                                    "title": "guest can checkout paying with cash on delivery on classic checkout",
                                    "status": "passed"
                                }
                            ],
                            "guest can create an account at checkout on blocks checkout": [
                                {
                                    "title": "guest can create an account at checkout on blocks checkout",
                                    "status": "passed"
                                }
                            ],
                            "guest can create an account at checkout on classic checkout": [
                                {
                                    "title": "guest can create an account at checkout on classic checkout",
                                    "status": "passed"
                                }
                            ],
                            "logged in customer can checkout with default addresses and direct bank transfer on blocks checkout": [
                                {
                                    "title": "logged in customer can checkout with default addresses and direct bank transfer on blocks checkout",
                                    "status": "passed"
                                }
                            ],
                            "logged in customer can checkout with default addresses and direct bank transfer on classic checkout": [
                                {
                                    "title": "logged in customer can checkout with default addresses and direct bank transfer on classic checkout",
                                    "status": "passed"
                                }
                            ],
                            "customer can login at checkout and place the order with a different shipping address blocks checkout": [
                                {
                                    "title": "customer can login at checkout and place the order with a different shipping address blocks checkout",
                                    "status": "passed"
                                }
                            ],
                            "customer can login at checkout and place the order with a different shipping address classic checkout": [
                                {
                                    "title": "customer can login at checkout and place the order with a different shipping address classic checkout",
                                    "status": "passed"
                                }
                            ],
                            "existing customer can update the billing address and place the order with direct bank transfer on blocks checkout": [
                                {
                                    "title": "existing customer can update the billing address and place the order with direct bank transfer on blocks checkout",
                                    "status": "passed"
                                }
                            ],
                            "existing customer can update the billing address and place the order with direct bank transfer on classic checkout": [
                                {
                                    "title": "existing customer can update the billing address and place the order with direct bank transfer on classic checkout",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "coupons\\/cart-block-coupons.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Cart Block Applying Coupons": [
                                {
                                    "title": "allows cart block to apply coupon of any type",
                                    "status": "passed"
                                },
                                {
                                    "title": "allows cart block to apply multiple coupons",
                                    "status": "passed"
                                },
                                {
                                    "title": "prevents cart block applying same coupon twice",
                                    "status": "passed"
                                },
                                {
                                    "title": "prevents cart block applying coupon with usage limit",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "coupons\\/cart-checkout-coupons.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Cart & Checkout applying coupons": [
                                {
                                    "title": "allows applying coupon of type fixed_cart",
                                    "status": "passed"
                                },
                                {
                                    "title": "allows applying coupon of type percent",
                                    "status": "passed"
                                },
                                {
                                    "title": "allows applying coupon of type fixed_product",
                                    "status": "passed"
                                },
                                {
                                    "title": "prevents applying same coupon twice",
                                    "status": "passed"
                                },
                                {
                                    "title": "allows applying multiple coupons",
                                    "status": "passed"
                                },
                                {
                                    "title": "restores total when coupons are removed",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "coupons\\/cart-checkout-restricted-coupons.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Cart & Checkout Restricted Coupons": [
                                {
                                    "title": "expired coupon cannot be used",
                                    "status": "passed"
                                },
                                {
                                    "title": "coupon requiring min and max amounts and can only be used alone can only be used within limits",
                                    "status": "passed"
                                },
                                {
                                    "title": "coupon cannot be used on sale item",
                                    "status": "passed"
                                },
                                {
                                    "title": "coupon can only be used twice",
                                    "status": "passed"
                                },
                                {
                                    "title": "coupon cannot be used on certain products\\/categories (included product\\/category)",
                                    "status": "passed"
                                },
                                {
                                    "title": "coupon can be used on certain products\\/categories",
                                    "status": "passed"
                                },
                                {
                                    "title": "coupon cannot be used on specific products\\/categories (excluded product\\/category)",
                                    "status": "passed"
                                },
                                {
                                    "title": "coupon can be used on other products\\/categories",
                                    "status": "passed"
                                },
                                {
                                    "title": "coupon cannot be used by any customer on cart (email restricted)",
                                    "status": "passed"
                                },
                                {
                                    "title": "coupon cannot be used by any customer on checkout (email restricted)",
                                    "status": "passed"
                                },
                                {
                                    "title": "coupon can be used by the right customer (email restricted) but only once",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "coupons\\/create-coupon.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Coupon management": [
                                {
                                    "title": "can create new fixedCart coupon",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create new fixedProduct coupon",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create new percentage coupon",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create new expiryDate coupon",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create new freeShipping coupon",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "coupons\\/create-restricted-coupons.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Restricted coupon management": [
                                {
                                    "title": "can create new minimumSpend coupon",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create new maximumSpend coupon",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create new individualUse coupon",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create new excludeSaleItems coupon",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create new productCategories coupon",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create new excludeProductCategories coupon",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create new excludeProductBrands coupon",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create new products coupon",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create new excludeProducts coupon",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create new allowedEmails coupon",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create new usageLimitPerCoupon coupon",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create new usageLimitPerUser coupon",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "customer\\/customer-list.spec.ts",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Merchant > Customer List": [
                                {
                                    "title": "Merchant can view a list of all customers, filter and download",
                                    "status": "pending"
                                },
                                {
                                    "title": "Merchant can view a single customer",
                                    "status": "passed"
                                },
                                {
                                    "title": "Merchant can use advanced filters",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "editor\\/command-palette.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "can use the \\"Add new product\\" command": [
                                {
                                    "title": "can use the \\"Add new product\\" command",
                                    "status": "passed"
                                }
                            ],
                            "can use the \\"Add new order\\" command": [
                                {
                                    "title": "can use the \\"Add new order\\" command",
                                    "status": "passed"
                                }
                            ],
                            "can use the \\"Products\\" command": [
                                {
                                    "title": "can use the \\"Products\\" command",
                                    "status": "passed"
                                }
                            ],
                            "can use the \\"Orders\\" command": [
                                {
                                    "title": "can use the \\"Orders\\" command",
                                    "status": "passed"
                                }
                            ],
                            "can use the product search command": [
                                {
                                    "title": "can use the product search command",
                                    "status": "passed"
                                }
                            ],
                            "can use a settings command": [
                                {
                                    "title": "can use a settings command",
                                    "status": "passed"
                                }
                            ],
                            "can use an analytics command": [
                                {
                                    "title": "can use an analytics command",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "email\\/account-emails.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "New customer should receive an email with login details": [
                                {
                                    "title": "New customer should receive an email with login details",
                                    "status": "passed"
                                }
                            ],
                            "Customer should receive an email when initiating a password reset": [
                                {
                                    "title": "Customer should receive an email when initiating a password reset",
                                    "status": "passed"
                                }
                            ],
                            "Customer should receive an email when password reset initiated from admin": [
                                {
                                    "title": "Customer should receive an email when password reset initiated from admin",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "email\\/editor-tracking-selectors.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "WooCommerce Email Editor Tracking Selectors": [
                                {
                                    "title": "Check selectors for tracking events",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "email\\/order-emails.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "customer receives email for processing order": [
                                {
                                    "title": "customer receives email for processing order",
                                    "status": "passed"
                                }
                            ],
                            "admin receives email for processing order": [
                                {
                                    "title": "admin receives email for processing order",
                                    "status": "passed"
                                }
                            ],
                            "customer receives email for completed order": [
                                {
                                    "title": "customer receives email for completed order",
                                    "status": "passed"
                                }
                            ],
                            "admin receives email for cancelled order": [
                                {
                                    "title": "admin receives email for cancelled order",
                                    "status": "passed"
                                }
                            ],
                            "Merchant can resend order details to customer": [
                                {
                                    "title": "Merchant can resend order details to customer",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "email\\/settings-email-listing.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "WooCommerce Email Settings List View": [
                                {
                                    "title": "Email settings list view renders correctly and allows to edit email status and search",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "email\\/settings-email.spec.ts",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "WooCommerce Email Settings": [
                                {
                                    "title": "See email preview",
                                    "status": "passed"
                                },
                                {
                                    "title": "Email sender options live change in email preview",
                                    "status": "passed"
                                },
                                {
                                    "title": "Live preview when changing email settings",
                                    "status": "passed"
                                },
                                {
                                    "title": "Send email preview",
                                    "status": "pending"
                                },
                                {
                                    "title": "See specific email preview",
                                    "status": "passed"
                                },
                                {
                                    "title": "Choose image in email image url field",
                                    "status": "passed"
                                },
                                {
                                    "title": "See color palette settings",
                                    "status": "passed"
                                },
                                {
                                    "title": "See font family setting",
                                    "status": "passed"
                                },
                                {
                                    "title": "See updated footer text field",
                                    "status": "passed"
                                },
                                {
                                    "title": "Reset color palette with a feature flag",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "email-editor\\/email-editor-loads.spec.ts",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "WooCommerce Email Editor Core": [
                                {
                                    "title": "Can enable the email editor",
                                    "status": "pending"
                                },
                                {
                                    "title": "Can access the email editor",
                                    "status": "pending"
                                },
                                {
                                    "title": "Can preview in new tab",
                                    "status": "pending"
                                },
                                {
                                    "title": "Can send test email",
                                    "status": "pending"
                                },
                                {
                                    "title": "Can edit and save content",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "email-editor\\/email-editor-reset-template.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "WooCommerce Email Editor Reset Template": [
                                {
                                    "title": "Can reset a customized email template to default",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "email-editor\\/email-editor-settings-sidebar.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "WooCommerce Email Editor Settings Sidebar Integration": [
                                {
                                    "title": "Can update email status",
                                    "status": "passed"
                                },
                                {
                                    "title": "Can update email subject and preview text",
                                    "status": "passed"
                                },
                                {
                                    "title": "Can update email recipients",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "marketing\\/overview.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Marketing page": [
                                {
                                    "title": "Marketing Overview page have relevant content",
                                    "status": "passed"
                                },
                                {
                                    "title": "Learning section can be expanded",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "my-account\\/my-account-addresses.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Customer can manage addresses in My Account > Addresses page": [
                                {
                                    "title": "can add billing address from my account",
                                    "status": "passed"
                                },
                                {
                                    "title": "can add shipping address from my account",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "my-account\\/my-account-create-account.spec.ts",
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
                        "file": "my-account\\/my-account-downloads.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Customer can manage downloadable file in My Account > Downloads page": [
                                {
                                    "title": "can see downloadable file and click to download it",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "my-account\\/my-account-pay-order.spec.ts",
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
                        "file": "my-account\\/my-account.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "My account page": [
                                {
                                    "title": "allows customer to login and navigate",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "onboarding\\/add-product-task.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Add Product Task": [
                                {
                                    "title": "Add product task displays options for different product types",
                                    "status": "passed"
                                },
                                {
                                    "title": "Products page redirects to add product task when no products exist",
                                    "status": "passed"
                                },
                                {
                                    "title": "Products page shows products table when products exist",
                                    "status": "passed"
                                },
                                {
                                    "title": "Products page redirects to add product task when no products exist and task list is hidden",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "onboarding\\/launch-your-store.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Launch Your Store - logged in": [
                                {
                                    "title": "Entire site coming soon mode frontend",
                                    "status": "passed"
                                },
                                {
                                    "title": "Store only coming soon mode frontend",
                                    "status": "passed"
                                },
                                {
                                    "title": "Site visibility settings",
                                    "status": "passed"
                                },
                                {
                                    "title": "Homescreen badge coming soon store only",
                                    "status": "passed"
                                },
                                {
                                    "title": "Homescreen badge coming soon entire store",
                                    "status": "passed"
                                },
                                {
                                    "title": "Homescreen badge live",
                                    "status": "passed"
                                }
                            ],
                            "Launch Your Store front end - logged out": [],
                            "Launch Your Store front end - logged out > Block Theme (Twenty Twenty Four)": [
                                {
                                    "title": "Entire site coming soon mode (Block Theme (Twenty Twenty Four))",
                                    "status": "passed"
                                },
                                {
                                    "title": "Store only coming soon mode (Block Theme (Twenty Twenty Four))",
                                    "status": "passed"
                                }
                            ],
                            "Launch Your Store front end - logged out > Classic Theme (Storefront)": [
                                {
                                    "title": "Entire site coming soon mode (Classic Theme (Storefront))",
                                    "status": "passed"
                                },
                                {
                                    "title": "Store only coming soon mode (Classic Theme (Storefront))",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "onboarding\\/onboarding-wizard.spec.ts",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Store owner can complete the core profiler": [
                                {
                                    "title": "Can complete the core profiler skipping extension install",
                                    "status": "passed"
                                },
                                {
                                    "title": "Can complete the core profiler installing default extensions",
                                    "status": "pending"
                                }
                            ],
                            "Store owner can skip the core profiler": [
                                {
                                    "title": "Can skip the guided setup",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "onboarding\\/setup-checklist.spec.ts",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Can hide the task list": [
                                {
                                    "title": "Can hide the task list",
                                    "status": "passed"
                                }
                            ],
                            "Payments task list item links to Payments settings page": [
                                {
                                    "title": "Payments task list item links to Payments settings page",
                                    "status": "passed"
                                }
                            ],
                            "Can connect to WooCommerce.com": [
                                {
                                    "title": "Can connect to WooCommerce.com",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "order\\/create-order.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "WooCommerce Orders > Add new order": [
                                {
                                    "title": "can create a simple guest order",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create an order for an existing customer",
                                    "status": "passed"
                                },
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
                        "file": "order\\/customer-payment-page.spec.ts",
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
                        "file": "order\\/order-bulk-edit.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Bulk edit orders": [
                                {
                                    "title": "can bulk update order status",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "order\\/order-coupon.spec.ts",
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
                        "file": "order\\/order-edit.spec.ts",
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
                                    "title": "can update order status to cancelled",
                                    "status": "passed"
                                },
                                {
                                    "title": "can update order details",
                                    "status": "passed"
                                },
                                {
                                    "title": "can add and delete order notes",
                                    "status": "passed"
                                },
                                {
                                    "title": "can load billing and shipping details",
                                    "status": "passed"
                                },
                                {
                                    "title": "can copy billing address to shipping address",
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
                        "file": "order\\/order-grace-period.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "guest shopper can verify their email address after the grace period": [
                                {
                                    "title": "guest shopper can verify their email address after the grace period",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "order\\/order-refund.spec.ts",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "WooCommerce Orders > Refund an order": [
                                {
                                    "title": "can issue a refund by quantity",
                                    "status": "passed"
                                },
                                {
                                    "title": "can delete an issued refund",
                                    "status": "pending"
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
                        "file": "order\\/order-status-filter.spec.ts",
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
                        "file": "product\\/block-editor\\/create-grouped-product-block-editor.spec.ts",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "General tab": [],
                            "General tab > Grouped product": [
                                {
                                    "title": "can create a grouped product",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "product\\/block-editor\\/create-simple-product-block-editor.spec.ts",
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
                        "file": "product\\/block-editor\\/create-variable-product-block-editor.spec.ts",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Variations tab": [],
                            "Variations tab > Create variable products": [
                                {
                                    "title": "can create a variation option and publish the product",
                                    "status": "pending"
                                },
                                {
                                    "title": "can edit a variation",
                                    "status": "pending"
                                },
                                {
                                    "title": "can delete a variation",
                                    "status": "pending"
                                },
                                {
                                    "title": "can see variations warning and click the CTA",
                                    "status": "pending"
                                },
                                {
                                    "title": "can see single variation warning and click the CTA",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "product\\/block-editor\\/disable-block-product-editor.spec.ts",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Disable block product editor": [
                                {
                                    "title": "is hooked up to sidebar \\"Add New\\"",
                                    "status": "pending"
                                },
                                {
                                    "title": "can be disabled from the header",
                                    "status": "pending"
                                },
                                {
                                    "title": "can be disabled from settings",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "product\\/block-editor\\/linked-product-tab-product-block-editor.spec.ts",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "General tab": [],
                            "General tab > Linked product": [
                                {
                                    "title": "can create a product with linked products",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "product\\/block-editor\\/organization-tab-product-block-editor.spec.ts",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "General tab": [],
                            "General tab > Create product - Organization tab": [
                                {
                                    "title": "can create a simple product with categories, tags and with password required",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "product\\/block-editor\\/product-attributes-block-editor.spec.ts",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "add local attribute (with terms) to the Product": [
                                {
                                    "title": "add local attribute (with terms) to the Product",
                                    "status": "pending"
                                }
                            ],
                            "can add existing attributes": [
                                {
                                    "title": "can add existing attributes",
                                    "status": "pending"
                                }
                            ],
                            "can update product attributes": [
                                {
                                    "title": "can update product attributes",
                                    "status": "pending"
                                }
                            ],
                            "can remove product attributes": [
                                {
                                    "title": "can remove product attributes",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "product\\/block-editor\\/product-edit-block-editor.spec.ts",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Publish dropdown options": [
                                {
                                    "title": "can schedule a product publication",
                                    "status": "pending"
                                },
                                {
                                    "title": "can duplicate a product",
                                    "status": "pending"
                                },
                                {
                                    "title": "can delete a product",
                                    "status": "pending"
                                }
                            ],
                            "can update the general information of a product": [
                                {
                                    "title": "can update the general information of a product",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "product\\/block-editor\\/product-images-block-editor.spec.ts",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "can add images": [
                                {
                                    "title": "can add images",
                                    "status": "pending"
                                }
                            ],
                            "can replace an image": [
                                {
                                    "title": "can replace an image",
                                    "status": "pending"
                                }
                            ],
                            "can remove an image": [
                                {
                                    "title": "can remove an image",
                                    "status": "pending"
                                }
                            ],
                            "can set an image as cover": [
                                {
                                    "title": "can set an image as cover",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "product\\/block-editor\\/product-inventory-block-editor.spec.ts",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "can update sku": [
                                {
                                    "title": "can update sku",
                                    "status": "pending"
                                }
                            ],
                            "can update stock status": [
                                {
                                    "title": "can update stock status",
                                    "status": "pending"
                                }
                            ],
                            "can track stock quantity": [
                                {
                                    "title": "can track stock quantity",
                                    "status": "pending"
                                }
                            ],
                            "can limit purchases": [
                                {
                                    "title": "can limit purchases",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "product\\/create-product-attributes.spec.ts",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "can add custom product attributes": [
                                {
                                    "title": "can add custom product attributes",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "product\\/create-variable-product.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Add variable product": [
                                {
                                    "title": "can create a variable product",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "product\\/create-variations.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Add variations": [
                                {
                                    "title": "can generate variations from product attributes",
                                    "status": "passed"
                                },
                                {
                                    "title": "can manually add a variation",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "product\\/product-create-simple.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "can create a simple virtual product": [
                                {
                                    "title": "can create a simple virtual product",
                                    "status": "passed"
                                }
                            ],
                            "can create a simple non virtual product": [
                                {
                                    "title": "can create a simple non virtual product",
                                    "status": "passed"
                                }
                            ],
                            "can create a simple downloadable product": [
                                {
                                    "title": "can create a simple downloadable product",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "product\\/product-delete.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "can delete a product from edit view": [
                                {
                                    "title": "can delete a product from edit view",
                                    "status": "passed"
                                }
                            ],
                            "can quick delete a product from product list": [
                                {
                                    "title": "can quick delete a product from product list",
                                    "status": "passed"
                                }
                            ],
                            "can permanently delete a product from trash list": [
                                {
                                    "title": "can permanently delete a product from trash list",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "product\\/product-edit.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "can edit a product and save the changes": [
                                {
                                    "title": "can edit a product and save the changes",
                                    "status": "passed"
                                }
                            ],
                            "can bulk edit products": [
                                {
                                    "title": "can bulk edit products",
                                    "status": "passed"
                                }
                            ],
                            "can restore regular price when bulk editing products": [
                                {
                                    "title": "can restore regular price when bulk editing products",
                                    "status": "passed"
                                }
                            ],
                            "can decrease the sale price if the product was not previously in sale when bulk editing products": [
                                {
                                    "title": "can decrease the sale price if the product was not previously in sale when bulk editing products",
                                    "status": "passed"
                                }
                            ],
                            "increasing the sale price from 0 does not change the sale price when bulk editing products": [
                                {
                                    "title": "increasing the sale price from 0 does not change the sale price when bulk editing products",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "product\\/product-export.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Product > Export Selected Products": [
                                {
                                    "title": "should allow exporting a single selected simple product",
                                    "status": "passed"
                                },
                                {
                                    "title": "should allow exporting multiple selected products (simple and variable)",
                                    "status": "passed"
                                },
                                {
                                    "title": "should allow clearing selection from the export page",
                                    "status": "passed"
                                },
                                {
                                    "title": "should show the default export screen when no products are selected",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "product\\/product-grouped.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
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
                        "file": "product\\/product-images.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Products > Product Images": [
                                {
                                    "title": "can set product image",
                                    "status": "passed"
                                },
                                {
                                    "title": "can update the product image",
                                    "status": "passed"
                                },
                                {
                                    "title": "can delete the product image",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create a product gallery",
                                    "status": "passed"
                                },
                                {
                                    "title": "can update a product gallery",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "product\\/product-import-csv.spec.ts",
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
                        "file": "product\\/product-linked-products.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Products > Related products": [
                                {
                                    "title": "add up-sells",
                                    "status": "passed"
                                },
                                {
                                    "title": "remove up-sells",
                                    "status": "passed"
                                },
                                {
                                    "title": "add cross-sells",
                                    "status": "passed"
                                },
                                {
                                    "title": "remove cross-sells",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "product\\/product-reviews.spec.ts",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Product Reviews": [],
                            "Product Reviews > Merchant manages reviews": [
                                {
                                    "title": "can view products reviews list",
                                    "status": "passed"
                                },
                                {
                                    "title": "can filter the reviews by product",
                                    "status": "passed"
                                },
                                {
                                    "title": "can quick edit a product review",
                                    "status": "passed"
                                },
                                {
                                    "title": "can edit a product review",
                                    "status": "passed"
                                },
                                {
                                    "title": "can approve a product review",
                                    "status": "passed"
                                },
                                {
                                    "title": "can mark a product review as spam",
                                    "status": "passed"
                                },
                                {
                                    "title": "can reply to a product review",
                                    "status": "pending"
                                },
                                {
                                    "title": "can delete a product review",
                                    "status": "passed"
                                }
                            ],
                            "Product Reviews > Shopper adds reviews": [
                                {
                                    "title": "shopper can post a review",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "product\\/product-search.spec.ts",
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
                        "file": "product\\/product-settings.spec.ts",
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
                        "file": "product\\/product-tags-attributes.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Browse product tags and attributes from the product page": [
                                {
                                    "title": "should see shop catalog with all its products",
                                    "status": "passed"
                                },
                                {
                                    "title": "should see and sort tags page with all the products",
                                    "status": "passed"
                                },
                                {
                                    "title": "should see and sort attributes page with all its products",
                                    "status": "passed"
                                },
                                {
                                    "title": "can see products showcase",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "product\\/product-variable.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
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
                            "Shopper > Update variable product": [
                                {
                                    "title": "Shopper can change variable attributes to the same value",
                                    "status": "passed"
                                },
                                {
                                    "title": "Shopper can change attributes to combination with dimensions and weight",
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
                        "file": "product\\/update-variations.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Update variations": [
                                {
                                    "title": "can individually edit variations",
                                    "status": "passed"
                                },
                                {
                                    "title": "can bulk edit variations",
                                    "status": "passed"
                                },
                                {
                                    "title": "can delete all variations",
                                    "status": "passed"
                                },
                                {
                                    "title": "can manage stock levels",
                                    "status": "passed"
                                },
                                {
                                    "title": "can set variation defaults",
                                    "status": "passed"
                                },
                                {
                                    "title": "can remove a variation",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "settings\\/consumer-token.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "admin can manage consumer keys": [
                                {
                                    "title": "admin can manage consumer keys",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "settings\\/settings-general.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "WooCommerce General Settings": [
                                {
                                    "title": "Save Changes button is disabled by default and enabled only after changes.",
                                    "status": "passed"
                                },
                                {
                                    "title": "can update settings",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "settings\\/settings-tax.spec.ts",
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
                        "file": "settings\\/settings-woo-com.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "WooCommerce woo.com Settings": [
                                {
                                    "title": "can enable analytics tracking",
                                    "status": "passed"
                                },
                                {
                                    "title": "can enable marketplace suggestions",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "settings\\/webhooks.spec.ts",
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
                        "file": "shipping\\/shipping-classes.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "can add a shipping class with an unique slug": [
                                {
                                    "title": "can add a shipping class with an unique slug",
                                    "status": "passed"
                                }
                            ],
                            "can add a shipping class with an auto-generated slug": [
                                {
                                    "title": "can add a shipping class with an auto-generated slug",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shipping\\/shipping-zones.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "can delete the shipping zone region": [
                                {
                                    "title": "can delete the shipping zone region",
                                    "status": "passed"
                                }
                            ],
                            "can delete the shipping zone method": [
                                {
                                    "title": "can delete the shipping zone method",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shop\\/cart-redirection.spec.ts",
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
                        "file": "shop\\/shop-search-browse-sort.spec.ts",
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
                        "file": "shop\\/shop-title-after-deletion.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Check the title of the shop page after the page has been deleted": [
                                {
                                    "title": "Check the title of the shop page after the page has been deleted",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "user\\/lost-password.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Can go to lost password page and submit the form": [
                                {
                                    "title": "can visit the lost password page from the login page",
                                    "status": "passed"
                                },
                                {
                                    "title": "can submit the lost password form",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "user\\/users-create.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "can create a new Customer": [
                                {
                                    "title": "can create a new Customer",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "user\\/users-manage.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "can update customer data": [
                                {
                                    "title": "can update customer data",
                                    "status": "passed"
                                }
                            ],
                            "can update shop manager data": [
                                {
                                    "title": "can update shop manager data",
                                    "status": "passed"
                                }
                            ],
                            "can delete a customer": [
                                {
                                    "title": "can delete a customer",
                                    "status": "passed"
                                }
                            ],
                            "can delete a shop manager": [
                                {
                                    "title": "can delete a shop manager",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "wp-core\\/create-page.spec.ts",
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
                        "file": "wp-core\\/create-post.spec.ts",
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
                        "file": "wp-core\\/post-comments.spec.ts",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "logged-in customer can comment on a post": [
                                {
                                    "title": "logged-in customer can comment on a post",
                                    "status": "passed"
                                }
                            ]
                        }
                    }
                ],
                "summary": "339 total, 289 passed, 0 failed, 50 skipped."
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
                        "tests": 339,
                        "passed": 289,
                        "failed": 0,
                        "pending": 0,
                        "skipped": 50,
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
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "description": "Skipping installing WC using WC Beta Tester; INSTALL_WC not found.",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/fixtures\\/install-wc.setup.ts",
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
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
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
                                "[IGNORED FOR WOO-E2E]"
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
                            "name": "Can access Analytics Reports from Stats Overview",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/analytics-access.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Navigate to the WooCommerce Home page",
                                    "status": "passed"
                                },
                                {
                                    "name": "Navigate to Revenue Report",
                                    "status": "passed"
                                },
                                {
                                    "name": "Navigate to Orders Report",
                                    "status": "passed"
                                },
                                {
                                    "name": "Navigate to Analytics Overview",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > analytics\\/analytics-access.spec.ts > WooCommerce Home",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "a user should see 3 sections by default - Performance, Charts, and Leaderboards",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/analytics-overview.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Go to Analytics > Overview",
                                    "status": "passed"
                                },
                                {
                                    "name": "Assert that the \\"Performance\\" section is visible",
                                    "status": "passed"
                                },
                                {
                                    "name": "Assert that the \\"Charts\\" section is visible",
                                    "status": "passed"
                                },
                                {
                                    "name": "Assert that the \\"Leaderboards\\" section is visible",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > analytics\\/analytics-overview.spec.ts > Analytics pages",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should allow a user to remove a section",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/analytics-overview.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Go to Analytics > Overview",
                                    "status": "passed"
                                },
                                {
                                    "name": "Remove the Performance section",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the Performance section to be hidden",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > analytics\\/analytics-overview.spec.ts > Analytics pages",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should allow a user to add a section back in",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/analytics-overview.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Go to Analytics > Overview",
                                    "status": "passed"
                                },
                                {
                                    "name": "Send POST request to hide Performance section",
                                    "status": "passed"
                                },
                                {
                                    "name": "Inspect the response payload to verify that Performance section was successfully hidden",
                                    "status": "passed"
                                },
                                {
                                    "name": "Add the Performance section back in.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the Performance section to be added back.",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > analytics\\/analytics-overview.spec.ts > Analytics pages",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should not display move up for the top, or move down for the bottom section",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/analytics-overview.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Go to Analytics > Overview",
                                    "status": "passed"
                                },
                                {
                                    "name": "Check the top section",
                                    "status": "passed"
                                },
                                {
                                    "name": "Check the bottom section",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > analytics\\/analytics-overview.spec.ts > Analytics pages > moving sections",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should allow a user to move a section down",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/analytics-overview.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Go to Analytics > Overview",
                                    "status": "passed"
                                },
                                {
                                    "name": "Move first section down",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the second section to become first, and first becomes second.",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > analytics\\/analytics-overview.spec.ts > Analytics pages > moving sections",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should allow a user to move a section up",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/analytics-overview.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Go to Analytics > Overview",
                                    "status": "passed"
                                },
                                {
                                    "name": "Move second section up",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect second section becomes first section, first becomes second",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > analytics\\/analytics-overview.spec.ts > Analytics pages > moving sections",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should show manual update trigger in scheduled mode",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/analytics-overview.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Go to Analytics > Overview",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > analytics\\/analytics-overview.spec.ts > Analytics Overview - Manual Import Trigger",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should hide manual update trigger in immediate mode",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/analytics-overview.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Go to Analytics > Overview",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > analytics\\/analytics-overview.spec.ts > Analytics Overview - Manual Import Trigger",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should show Immediate mode by default when option is not set",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/analytics-settings.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Go to Analytics > Settings",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > analytics\\/analytics-settings.spec.ts > Analytics Settings - Scheduled Import",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should switch from scheduled to immediate mode with confirmation modal - cancel flow",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/analytics-settings.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Go to Analytics > Settings",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > analytics\\/analytics-settings.spec.ts > Analytics Settings - Scheduled Import",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should switch from scheduled to immediate mode with confirmation modal - confirm flow",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/analytics-settings.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Go to Analytics > Settings",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > analytics\\/analytics-settings.spec.ts > Analytics Settings - Scheduled Import",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should switch from immediate to scheduled mode without confirmation modal",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/analytics-settings.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Go to Analytics > Settings",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > analytics\\/analytics-settings.spec.ts > Analytics Settings - Scheduled Import",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Load the home page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/basic.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/basic.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Load wp-admin as admin",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/basic.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/basic.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Load my account page as customer",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/basic.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/basic.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Customer is redirected from WP Admin home back to the My Account page.",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/dashboard-access.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/dashboard-access.spec.ts > Customer-role users are blocked from accessing the WP Dashboard.",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Customer is redirected from WP Admin profile page back to the My Account page.",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/dashboard-access.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/dashboard-access.spec.ts > Customer-role users are blocked from accessing the WP Dashboard.",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Customer is redirected from WP Admin using ajax query param back to the My Account page.",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/dashboard-access.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/dashboard-access.spec.ts > Customer-role users are blocked from accessing the WP Dashboard.",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can load WooCommerce > Home page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can load WooCommerce > Orders page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can load WooCommerce > Customers page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can load WooCommerce > Reports page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can load WooCommerce > Settings page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can load WooCommerce > Status page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can load Products > All Products page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can load Products > Add new product page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can load Products > Categories page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can load Products > Tags page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can load Products > Attributes page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can load Analytics > Overview page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can load Analytics > Products page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can load Analytics > Revenue page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can load Analytics > Orders page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can load Analytics > Variations page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can load Analytics > Categories page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can load Analytics > Coupons page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can load Analytics > Taxes page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can load Analytics > Downloads page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can load Analytics > Stock page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can load Analytics > Settings page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can load Marketing > Overview page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can load Marketing > Coupons page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Merchant can add brands",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-product-brand.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > brands\\/create-product-brand.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should add only one product to the cart with AJAX add to cart buttons disabled and \\"Geolocate (with page caching support)\\" as the default customer location",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/add-to-cart.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > cart\\/add-to-cart.spec.ts > Add to Cart behavior",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should be able to navigate and remove item from mini cart using keyboard",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/add-to-cart.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Add product to cart and open mini cart",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify and interact with remove button",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify cart is empty",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > cart\\/add-to-cart.spec.ts > Add to Cart behavior",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can undo product removal in classic cart",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "add product to cart",
                                    "status": "passed"
                                },
                                {
                                    "name": "remove product and verify undo link appears",
                                    "status": "passed"
                                },
                                {
                                    "name": "click undo to restore product",
                                    "status": "passed"
                                },
                                {
                                    "name": "remove product again after undo",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify undo link disappears after navigation",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > cart\\/cart.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can add and remove products, increase quantity and proceed to checkout - blocks cart",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "empty cart is displayed",
                                    "status": "passed"
                                },
                                {
                                    "name": "one product in cart is displayed",
                                    "status": "passed"
                                },
                                {
                                    "name": "can increase quantity",
                                    "status": "passed"
                                },
                                {
                                    "name": "can add another product to cart",
                                    "status": "passed"
                                },
                                {
                                    "name": "can proceed to checkout and return",
                                    "status": "passed"
                                },
                                {
                                    "name": "can remove the first product",
                                    "status": "passed"
                                },
                                {
                                    "name": "can remove the last product",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > cart\\/cart.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can add and remove products, increase quantity and proceed to checkout - classic cart",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "empty cart is displayed",
                                    "status": "passed"
                                },
                                {
                                    "name": "one product in cart is displayed",
                                    "status": "passed"
                                },
                                {
                                    "name": "can increase quantity",
                                    "status": "passed"
                                },
                                {
                                    "name": "can add another product to cart",
                                    "status": "passed"
                                },
                                {
                                    "name": "can proceed to checkout and return",
                                    "status": "passed"
                                },
                                {
                                    "name": "can remove the first product",
                                    "status": "passed"
                                },
                                {
                                    "name": "can remove the last product",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > cart\\/cart.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Guest user redirected to checkout with correct cart",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout-link.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout-link.spec.ts > Checkout Link Endpoint > Guest user",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Guest user sees error when invalid coupon is applied",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout-link.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout-link.spec.ts > Checkout Link Endpoint > Guest user",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Guest user sees error when invalid products are provided",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout-link.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout-link.spec.ts > Checkout Link Endpoint > Guest user",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Guest user sees error when invalid product is provided",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout-link.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout-link.spec.ts > Checkout Link Endpoint > Guest user",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Guest user sees error when invalid link is provided",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout-link.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout-link.spec.ts > Checkout Link Endpoint > Guest user",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Logged-in user redirected to checkout with correct cart",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout-link.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout-link.spec.ts > Checkout Link Endpoint > Logged-in user",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Logged-in user sees error when invalid coupon is applied",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout-link.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout-link.spec.ts > Checkout Link Endpoint > Logged-in user",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Logged-in user sees error when invalid products are provided",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout-link.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout-link.spec.ts > Checkout Link Endpoint > Logged-in user",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Logged-in user sees error when invalid product is provided",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout-link.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout-link.spec.ts > Checkout Link Endpoint > Logged-in user",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Logged-in user sees error when invalid link is provided",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout-link.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout-link.spec.ts > Checkout Link Endpoint > Logged-in user",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "clicking custom button triggers validation when form is invalid",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@payments"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout-shortcode-custom-place-order-button.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout-shortcode-custom-place-order-button.spec.ts > Shortcode Checkout Custom Place Order Button",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/checkout\\/checkout-shortcode-custom-place-order-button.spec.ts",
                                            "line": 75,
                                            "column": 15
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "switching between gateways shows\\/hides custom button",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@payments"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout-shortcode-custom-place-order-button.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout-shortcode-custom-place-order-button.spec.ts > Shortcode Checkout Custom Place Order Button",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/checkout\\/checkout-shortcode-custom-place-order-button.spec.ts",
                                            "line": 75,
                                            "column": 15
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "clicking custom button submits order when form is valid",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@payments",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout-shortcode-custom-place-order-button.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout-shortcode-custom-place-order-button.spec.ts > Shortcode Checkout Custom Place Order Button",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/checkout\\/checkout-shortcode-custom-place-order-button.spec.ts",
                                            "line": 75,
                                            "column": 15
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "guest can checkout paying with cash on delivery on blocks checkout",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "guest can checkout paying with cash on delivery on classic checkout",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "guest can create an account at checkout on blocks checkout",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "guest can create an account at checkout on classic checkout",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "logged in customer can checkout with default addresses and direct bank transfer on blocks checkout",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "logged in customer can checkout with default addresses and direct bank transfer on classic checkout",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "customer can login at checkout and place the order with a different shipping address blocks checkout",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "customer can login at checkout and place the order with a different shipping address classic checkout",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "existing customer can update the billing address and place the order with direct bank transfer on blocks checkout",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "existing customer can update the billing address and place the order with direct bank transfer on classic checkout",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "allows cart block to apply coupon of any type",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-block-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > coupons\\/cart-block-coupons.spec.ts > Cart Block Applying Coupons",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "allows cart block to apply multiple coupons",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-block-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > coupons\\/cart-block-coupons.spec.ts > Cart Block Applying Coupons",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "prevents cart block applying same coupon twice",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-block-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > coupons\\/cart-block-coupons.spec.ts > Cart Block Applying Coupons",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "prevents cart block applying coupon with usage limit",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-block-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > coupons\\/cart-block-coupons.spec.ts > Cart Block Applying Coupons",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "allows applying coupon of type fixed_cart",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load cart page and apply coupons",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load checkout page and apply coupons",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/cart-checkout-coupons.spec.ts > Cart & Checkout applying coupons",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "allows applying coupon of type percent",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load cart page and apply coupons",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load checkout page and apply coupons",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/cart-checkout-coupons.spec.ts > Cart & Checkout applying coupons",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "allows applying coupon of type fixed_product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load cart page and apply coupons",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load checkout page and apply coupons",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/cart-checkout-coupons.spec.ts > Cart & Checkout applying coupons",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "prevents applying same coupon twice",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load cart page and try applying same coupon twice",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load checkout page and try applying same coupon twice",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/cart-checkout-coupons.spec.ts > Cart & Checkout applying coupons",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "allows applying multiple coupons",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load cart page and try applying multiple coupons",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load checkout page and try applying multiple coupons",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/cart-checkout-coupons.spec.ts > Cart & Checkout applying coupons",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "restores total when coupons are removed",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load cart page and try restoring total when removed coupons",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load checkout page and try restoring total when removed coupons",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/cart-checkout-coupons.spec.ts > Cart & Checkout applying coupons",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "expired coupon cannot be used",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-restricted-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load cart page and try expired coupon usage",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load checkout page and try expired coupon usage",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/cart-checkout-restricted-coupons.spec.ts > Cart & Checkout Restricted Coupons",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "coupon requiring min and max amounts and can only be used alone can only be used within limits",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-restricted-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load cart page and try limited coupon usage",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load checkout page and try limited coupon usage",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/cart-checkout-restricted-coupons.spec.ts > Cart & Checkout Restricted Coupons",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "coupon cannot be used on sale item",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-restricted-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load cart page and try coupon usage on sale item",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load checkout page and try coupon usage on sale item",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/cart-checkout-restricted-coupons.spec.ts > Cart & Checkout Restricted Coupons",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "coupon can only be used twice",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-restricted-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load cart page and try over limit coupon usage",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load checkout page and try over limit coupon usage",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/cart-checkout-restricted-coupons.spec.ts > Cart & Checkout Restricted Coupons",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "coupon cannot be used on certain products\\/categories (included product\\/category)",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-restricted-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load cart page and try included certain items coupon usage",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load checkout page and try included certain items coupon usage",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/cart-checkout-restricted-coupons.spec.ts > Cart & Checkout Restricted Coupons",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "coupon can be used on certain products\\/categories",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-restricted-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load cart page and try on certain products coupon usage",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load checkout page and try on certain products coupon usage",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/cart-checkout-restricted-coupons.spec.ts > Cart & Checkout Restricted Coupons",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "coupon cannot be used on specific products\\/categories (excluded product\\/category)",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-restricted-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load cart page and try excluded items coupon usage",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load checkout page and try excluded items coupon usage",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/cart-checkout-restricted-coupons.spec.ts > Cart & Checkout Restricted Coupons",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "coupon can be used on other products\\/categories",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-restricted-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load cart page and try coupon usage on other items",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load checkout page and try coupon usage on other items",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/cart-checkout-restricted-coupons.spec.ts > Cart & Checkout Restricted Coupons",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "coupon cannot be used by any customer on cart (email restricted)",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-restricted-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > coupons\\/cart-checkout-restricted-coupons.spec.ts > Cart & Checkout Restricted Coupons",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "coupon cannot be used by any customer on checkout (email restricted)",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-restricted-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > coupons\\/cart-checkout-restricted-coupons.spec.ts > Cart & Checkout Restricted Coupons",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "coupon can be used by the right customer (email restricted) but only once",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-restricted-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > coupons\\/cart-checkout-restricted-coupons.spec.ts > Cart & Checkout Restricted Coupons",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create new fixedCart coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-coupon.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "add new coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "publish the coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify coupon creation",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/create-coupon.spec.ts > Coupon management",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create new fixedProduct coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-coupon.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "add new coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "publish the coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify coupon creation",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/create-coupon.spec.ts > Coupon management",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create new percentage coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-coupon.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "add new coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "publish the coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify coupon creation",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/create-coupon.spec.ts > Coupon management",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create new expiryDate coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-coupon.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "add new coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "publish the coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify coupon creation",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify coupon expiry date",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/create-coupon.spec.ts > Coupon management",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create new freeShipping coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-coupon.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "add new coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "publish the coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify coupon creation",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify free shipping",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/create-coupon.spec.ts > Coupon management",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create new minimumSpend coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-restricted-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "add new coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "set minimum spend coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "publish the coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify coupon creation",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify minimum spend coupon",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/create-restricted-coupons.spec.ts > Restricted coupon management",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create new maximumSpend coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-restricted-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "add new coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "set maximum spend coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "publish the coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify coupon creation",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify maximum spend coupon",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/create-restricted-coupons.spec.ts > Restricted coupon management",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create new individualUse coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-restricted-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "add new coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "set individual use coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "publish the coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify coupon creation",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify individual use coupon",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/create-restricted-coupons.spec.ts > Restricted coupon management",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create new excludeSaleItems coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-restricted-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "add new coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "set exclude sale items coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "publish the coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify coupon creation",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify exclude sale items coupon",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/create-restricted-coupons.spec.ts > Restricted coupon management",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create new productCategories coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-restricted-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "add new coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "set product categories coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "publish the coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify coupon creation",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify product categories coupon",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/create-restricted-coupons.spec.ts > Restricted coupon management",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create new excludeProductCategories coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-restricted-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "add new coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "set exclude product categories coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "publish the coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify coupon creation",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify exclude product categories coupon",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/create-restricted-coupons.spec.ts > Restricted coupon management",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create new excludeProductBrands coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-restricted-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "add new coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "set exclude product brands coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "publish the coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify coupon creation",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/create-restricted-coupons.spec.ts > Restricted coupon management",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create new products coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-restricted-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "add new coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "set products coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "publish the coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify coupon creation",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify products coupon",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/create-restricted-coupons.spec.ts > Restricted coupon management",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create new excludeProducts coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-restricted-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "add new coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "set exclude products coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "publish the coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify coupon creation",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify exclude products coupon",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/create-restricted-coupons.spec.ts > Restricted coupon management",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create new allowedEmails coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-restricted-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "add new coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "set allowed emails coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "publish the coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify coupon creation",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify allowed emails coupon",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/create-restricted-coupons.spec.ts > Restricted coupon management",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create new usageLimitPerCoupon coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-restricted-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "add new coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "set usage limit coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "publish the coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify coupon creation",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify usage limit coupon",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/create-restricted-coupons.spec.ts > Restricted coupon management",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create new usageLimitPerUser coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-restricted-coupons.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "add new coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "set usage limit per user coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "publish the coupon",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify coupon creation",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify usage limit per user coupon",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > coupons\\/create-restricted-coupons.spec.ts > Restricted coupon management",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Merchant can view a list of all customers, filter and download",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/customer-list.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customer\\/customer-list.spec.ts > Merchant > Customer List",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customer\\/customer-list.spec.ts",
                                            "line": 105,
                                            "column": 7
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Merchant can view a single customer",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/customer-list.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Switch to single customer view",
                                    "status": "passed"
                                },
                                {
                                    "name": "Check that the customer is displayed",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > customer\\/customer-list.spec.ts > Merchant > Customer List",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Merchant can use advanced filters",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/customer-list.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Switch to advanced filters",
                                    "status": "passed"
                                },
                                {
                                    "name": "Add a filter for email",
                                    "status": "passed"
                                },
                                {
                                    "name": "Add a filter for country",
                                    "status": "passed"
                                },
                                {
                                    "name": "Apply the filters",
                                    "status": "passed"
                                },
                                {
                                    "name": "Check that the filter is applied",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > customer\\/customer-list.spec.ts > Merchant > Customer List",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can use the \\"Add new product\\" command",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/command-palette.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > editor\\/command-palette.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can use the \\"Add new order\\" command",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/command-palette.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > editor\\/command-palette.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can use the \\"Products\\" command",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/command-palette.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > editor\\/command-palette.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can use the \\"Orders\\" command",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/command-palette.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > editor\\/command-palette.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can use the product search command",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/command-palette.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > editor\\/command-palette.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can use a settings command",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/command-palette.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > editor\\/command-palette.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can use an analytics command",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/command-palette.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > editor\\/command-palette.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "New customer should receive an email with login details",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/account-emails.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "check the email exists",
                                    "status": "passed"
                                },
                                {
                                    "name": "check the email content",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > email\\/account-emails.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Customer should receive an email when initiating a password reset",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/account-emails.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "initiate password reset from my account",
                                    "status": "passed"
                                },
                                {
                                    "name": "check the email exists",
                                    "status": "passed"
                                },
                                {
                                    "name": "check the email content",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > email\\/account-emails.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Customer should receive an email when password reset initiated from admin",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/account-emails.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "admin sends password reset link",
                                    "status": "passed"
                                },
                                {
                                    "name": "check the email exists",
                                    "status": "passed"
                                },
                                {
                                    "name": "check the email content",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > email\\/account-emails.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Check selectors for tracking events",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/editor-tracking-selectors.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > email\\/editor-tracking-selectors.spec.ts > WooCommerce Email Editor Tracking Selectors",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "customer receives email for processing order",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-emails.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "check the email exists",
                                    "status": "passed"
                                },
                                {
                                    "name": "check the email content",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > email\\/order-emails.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "admin receives email for processing order",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-emails.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "check the email exists",
                                    "status": "passed"
                                },
                                {
                                    "name": "check the email content",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > email\\/order-emails.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "customer receives email for completed order",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-emails.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "check the email exists",
                                    "status": "passed"
                                },
                                {
                                    "name": "check the email content",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > email\\/order-emails.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "admin receives email for cancelled order",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-emails.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "check the email exists",
                                    "status": "passed"
                                },
                                {
                                    "name": "check the email content",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > email\\/order-emails.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Merchant can resend order details to customer",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-emails.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > email\\/order-emails.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Email settings list view renders correctly and allows to edit email status and search",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-email-listing.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > email\\/settings-email-listing.spec.ts > WooCommerce Email Settings List View",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "See email preview",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-email.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > email\\/settings-email.spec.ts > WooCommerce Email Settings",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Email sender options live change in email preview",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-email.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > email\\/settings-email.spec.ts > WooCommerce Email Settings",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Live preview when changing email settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@skip-on-external-env"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-email.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > email\\/settings-email.spec.ts > WooCommerce Email Settings",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Send email preview",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-email.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > email\\/settings-email.spec.ts > WooCommerce Email Settings",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/email\\/settings-email.spec.ts",
                                            "line": 178,
                                            "column": 7
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "See specific email preview",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-email.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > email\\/settings-email.spec.ts > WooCommerce Email Settings",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Choose image in email image url field",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-email.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > email\\/settings-email.spec.ts > WooCommerce Email Settings",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "See color palette settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-email.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > email\\/settings-email.spec.ts > WooCommerce Email Settings",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "See font family setting",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-email.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > email\\/settings-email.spec.ts > WooCommerce Email Settings",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "See updated footer text field",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-email.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > email\\/settings-email.spec.ts > WooCommerce Email Settings",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Reset color palette with a feature flag",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-email.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > email\\/settings-email.spec.ts > WooCommerce Email Settings",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Can enable the email editor",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/email-editor-loads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > email-editor\\/email-editor-loads.spec.ts > WooCommerce Email Editor Core",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/email-editor\\/email-editor-loads.spec.ts",
                                            "line": 9,
                                            "column": 15
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Can access the email editor",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/email-editor-loads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > email-editor\\/email-editor-loads.spec.ts > WooCommerce Email Editor Core",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/email-editor\\/email-editor-loads.spec.ts",
                                            "line": 9,
                                            "column": 15
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Can preview in new tab",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/email-editor-loads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > email-editor\\/email-editor-loads.spec.ts > WooCommerce Email Editor Core",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/email-editor\\/email-editor-loads.spec.ts",
                                            "line": 9,
                                            "column": 15
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Can send test email",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/email-editor-loads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > email-editor\\/email-editor-loads.spec.ts > WooCommerce Email Editor Core",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/email-editor\\/email-editor-loads.spec.ts",
                                            "line": 9,
                                            "column": 15
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Can edit and save content",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/email-editor-loads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > email-editor\\/email-editor-loads.spec.ts > WooCommerce Email Editor Core",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/email-editor\\/email-editor-loads.spec.ts",
                                            "line": 9,
                                            "column": 15
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Can reset a customized email template to default",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/email-editor-reset-template.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > email-editor\\/email-editor-reset-template.spec.ts > WooCommerce Email Editor Reset Template",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Can update email status",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/email-editor-settings-sidebar.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > email-editor\\/email-editor-settings-sidebar.spec.ts > WooCommerce Email Editor Settings Sidebar Integration",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Can update email subject and preview text",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/email-editor-settings-sidebar.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > email-editor\\/email-editor-settings-sidebar.spec.ts > WooCommerce Email Editor Settings Sidebar Integration",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Can update email recipients",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/email-editor-settings-sidebar.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > email-editor\\/email-editor-settings-sidebar.spec.ts > WooCommerce Email Editor Settings Sidebar Integration",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Marketing Overview page have relevant content",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/overview.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > marketing\\/overview.spec.ts > Marketing page",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Learning section can be expanded",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@not-e2e",
                                "@non-critical"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/overview.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > marketing\\/overview.spec.ts > Marketing page",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can add billing address from my account",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/my-account-addresses.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > my-account\\/my-account-addresses.spec.ts > Customer can manage addresses in My Account > Addresses page",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can add shipping address from my account",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/my-account-addresses.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > my-account\\/my-account-addresses.spec.ts > Customer can manage addresses in My Account > Addresses page",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create a new account via my account",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/my-account-create-account.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > my-account\\/my-account-create-account.spec.ts > Shopper My Account Create Account",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can see downloadable file and click to download it",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/my-account-downloads.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > my-account\\/my-account-downloads.spec.ts > Customer can manage downloadable file in My Account > Downloads page",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "allows customer to pay for their order in My Account",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/my-account-pay-order.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > my-account\\/my-account-pay-order.spec.ts > Customer can pay for their order through My Account",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "allows customer to login and navigate",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/my-account.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "customer can navigate to Orders page",
                                    "status": "passed"
                                },
                                {
                                    "name": "customer can navigate to Downloads page",
                                    "status": "passed"
                                },
                                {
                                    "name": "customer can navigate to Addresses page",
                                    "status": "passed"
                                },
                                {
                                    "name": "customer can navigate to Account details page",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > my-account\\/my-account.spec.ts > My account page",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Add product task displays options for different product types",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/add-product-task.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > onboarding\\/add-product-task.spec.ts > Add Product Task",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Products page redirects to add product task when no products exist",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/add-product-task.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > onboarding\\/add-product-task.spec.ts > Add Product Task",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Products page shows products table when products exist",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/add-product-task.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > onboarding\\/add-product-task.spec.ts > Add Product Task",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Products page redirects to add product task when no products exist and task list is hidden",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/add-product-task.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > onboarding\\/add-product-task.spec.ts > Add Product Task",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Entire site coming soon mode frontend",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@gutenberg",
                                "@skip-on-wpcom"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/launch-your-store.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > onboarding\\/launch-your-store.spec.ts > Launch Your Store - logged in",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Store only coming soon mode frontend",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@gutenberg",
                                "@skip-on-wpcom"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/launch-your-store.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > onboarding\\/launch-your-store.spec.ts > Launch Your Store - logged in",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Site visibility settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@gutenberg",
                                "@skip-on-wpcom"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/launch-your-store.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > onboarding\\/launch-your-store.spec.ts > Launch Your Store - logged in",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Homescreen badge coming soon store only",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@gutenberg",
                                "@skip-on-wpcom"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/launch-your-store.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > onboarding\\/launch-your-store.spec.ts > Launch Your Store - logged in",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Homescreen badge coming soon entire store",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@gutenberg",
                                "@skip-on-wpcom"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/launch-your-store.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > onboarding\\/launch-your-store.spec.ts > Launch Your Store - logged in",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Homescreen badge live",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@gutenberg",
                                "@skip-on-wpcom"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/launch-your-store.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > onboarding\\/launch-your-store.spec.ts > Launch Your Store - logged in",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Entire site coming soon mode (Block Theme (Twenty Twenty Four))",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/launch-your-store.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > onboarding\\/launch-your-store.spec.ts > Launch Your Store front end - logged out > Block Theme (Twenty Twenty Four)",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Store only coming soon mode (Block Theme (Twenty Twenty Four))",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/launch-your-store.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > onboarding\\/launch-your-store.spec.ts > Launch Your Store front end - logged out > Block Theme (Twenty Twenty Four)",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Entire site coming soon mode (Classic Theme (Storefront))",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/launch-your-store.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > onboarding\\/launch-your-store.spec.ts > Launch Your Store front end - logged out > Classic Theme (Storefront)",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Store only coming soon mode (Classic Theme (Storefront))",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/launch-your-store.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > onboarding\\/launch-your-store.spec.ts > Launch Your Store front end - logged out > Classic Theme (Storefront)",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Can complete the core profiler skipping extension install",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@skip-on-external-env"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/onboarding-wizard.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Intro page and opt in to data sharing",
                                    "status": "passed"
                                },
                                {
                                    "name": "User profile information",
                                    "status": "passed"
                                },
                                {
                                    "name": "Business Information",
                                    "status": "passed"
                                },
                                {
                                    "name": "Extensions -- do not install any",
                                    "status": "passed"
                                },
                                {
                                    "name": "Confirm that core profiler was completed and no extensions installed",
                                    "status": "passed"
                                },
                                {
                                    "name": "Confirm that information from core profiler saved",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > onboarding\\/onboarding-wizard.spec.ts > Store owner can complete the core profiler",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Can complete the core profiler installing default extensions",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@skip-on-external-env"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/onboarding-wizard.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > onboarding\\/onboarding-wizard.spec.ts > Store owner can complete the core profiler",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/onboarding\\/onboarding-wizard.spec.ts",
                                            "line": 205,
                                            "column": 8
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Can skip the guided setup",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@skip-on-external-env"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/onboarding-wizard.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Confirm that the store is in coming soon mode after skipping the core profiler",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > onboarding\\/onboarding-wizard.spec.ts > Store owner can skip the core profiler",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Can hide the task list",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/setup-checklist.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load the WC Admin page.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Hide the task list",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > onboarding\\/setup-checklist.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Payments task list item links to Payments settings page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/setup-checklist.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > onboarding\\/setup-checklist.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Can connect to WooCommerce.com",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/setup-checklist.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > onboarding\\/setup-checklist.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/onboarding\\/setup-checklist.spec.ts",
                                            "line": 117,
                                            "column": 6
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create a simple guest order",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-order.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/create-order.spec.ts > WooCommerce Orders > Add new order",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create an order for an existing customer",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-order.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/create-order.spec.ts > WooCommerce Orders > Add new order",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create new order",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-order.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/create-order.spec.ts > WooCommerce Orders > Add new order",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create new complex order with multiple product types & tax classes",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-order.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/create-order.spec.ts > WooCommerce Orders > Add new order",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should show the customer payment page link on a pending order",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/customer-payment-page.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/customer-payment-page.spec.ts > WooCommerce Merchant Flow: Orders > Customer Payment Page",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should load the customer payment page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/customer-payment-page.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/customer-payment-page.spec.ts > WooCommerce Merchant Flow: Orders > Customer Payment Page",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can pay for the order through the customer payment page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/customer-payment-page.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load the customer payment page",
                                    "status": "passed"
                                },
                                {
                                    "name": "Select payment method and pay for the order",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify the order received page",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > order\\/customer-payment-page.spec.ts > WooCommerce Merchant Flow: Orders > Customer Payment Page",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can bulk update order status",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-bulk-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-bulk-edit.spec.ts > Bulk edit orders",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can apply a coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-coupon.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-coupon.spec.ts > WooCommerce Orders > Apply Coupon",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can remove a coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-coupon.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-coupon.spec.ts > WooCommerce Orders > Apply Coupon",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can view single order",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-edit.spec.ts > Edit order",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can update order status",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-edit.spec.ts > Edit order",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can update order status to cancelled",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-edit.spec.ts > Edit order",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can update order details",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-edit.spec.ts > Edit order",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can add and delete order notes",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-edit.spec.ts > Edit order",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can load billing and shipping details",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Open our test order and select the customer we just created.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load the billing and shipping addresses",
                                    "status": "passed"
                                },
                                {
                                    "name": "Save the order and confirm addresses saved",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > order\\/order-edit.spec.ts > Edit order",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can copy billing address to shipping address",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Open our second test order and select the customer we just created.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load the billing address and then copy it to the shipping address",
                                    "status": "passed"
                                },
                                {
                                    "name": "Save the order and confirm addresses saved",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > order\\/order-edit.spec.ts > Edit order",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can add downloadable product permissions to order without product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-edit.spec.ts > Edit order > Downloadable product permissions",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can add downloadable product permissions to order with product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-edit.spec.ts > Edit order > Downloadable product permissions",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can edit downloadable product permissions",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-edit.spec.ts > Edit order > Downloadable product permissions",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can revoke downloadable product permissions",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-edit.spec.ts > Edit order > Downloadable product permissions",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should not allow downloading a product if download attempts are exceeded",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-edit.spec.ts > Edit order > Downloadable product permissions",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should not allow downloading a product if expiration date has passed",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@hpos",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-edit.spec.ts > Edit order > Downloadable product permissions",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "guest shopper can verify their email address after the grace period",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-grace-period.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "navigate to order confirmation page",
                                    "status": "passed"
                                },
                                {
                                    "name": "simulate cookies cleared, but within 10 minute grace period",
                                    "status": "passed"
                                },
                                {
                                    "name": "simulate cookies cleared, outside 10 minute window",
                                    "status": "passed"
                                },
                                {
                                    "name": "supply incorrect email address for the order, error",
                                    "status": "passed"
                                },
                                {
                                    "name": "supply the correct email address for the order, display order confirmation",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > order\\/order-grace-period.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can issue a refund by quantity",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-refund.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-refund.spec.ts > WooCommerce Orders > Refund an order",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can delete an issued refund",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@payments",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-refund.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-refund.spec.ts > WooCommerce Orders > Refund an order",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/order\\/order-refund.spec.ts",
                                            "line": 121,
                                            "column": 8
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can update order after refunding item without automatic stock adjustment",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-refund.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-refund.spec.ts > WooCommerce Orders > Refund and restock an order item",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n",
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should filter by All",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-status-filter.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-status-filter.spec.ts > WooCommerce Orders > Filter Order by Status",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should filter by Pending payment",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-status-filter.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-status-filter.spec.ts > WooCommerce Orders > Filter Order by Status",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should filter by Processing",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-status-filter.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-status-filter.spec.ts > WooCommerce Orders > Filter Order by Status",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should filter by On hold",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-status-filter.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-status-filter.spec.ts > WooCommerce Orders > Filter Order by Status",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should filter by Completed",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-status-filter.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-status-filter.spec.ts > WooCommerce Orders > Filter Order by Status",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should filter by Cancelled",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-status-filter.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-status-filter.spec.ts > WooCommerce Orders > Filter Order by Status",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should filter by Refunded",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-status-filter.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-status-filter.spec.ts > WooCommerce Orders > Filter Order by Status",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should filter by Failed",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-status-filter.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > order\\/order-status-filter.spec.ts > WooCommerce Orders > Filter Order by Status",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create a grouped product",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-grouped-product-block-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/create-grouped-product-block-editor.spec.ts > General tab > Grouped product",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/create-grouped-product-block-editor.spec.ts",
                                            "line": 58,
                                            "column": 8
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "renders each block without error",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-simple-product-block-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/create-simple-product-block-editor.spec.ts > General tab > Simple product form",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/create-simple-product-block-editor.spec.ts",
                                            "line": 47,
                                            "column": 16
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create a simple product",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-simple-product-block-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/create-simple-product-block-editor.spec.ts > General tab > Create product",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/create-simple-product-block-editor.spec.ts",
                                            "line": 75,
                                            "column": 8
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can not create a product with duplicated SKU",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-simple-product-block-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/create-simple-product-block-editor.spec.ts > General tab > Create product",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "description": "Experimental block-based product editor is officially deprecated since 10.2. See: https:\\/\\/developer.woocommerce.com\\/2025\\/07\\/23\\/10-1-pre-release-updates\\/#:~:text=%F0%9F%8C%85%20Say%20sayonara,the%20near%20future",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/helpers\\/skip-tests.ts",
                                            "line": 8,
                                            "column": 7
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can a shopper add the simple product to the cart",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-simple-product-block-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/create-simple-product-block-editor.spec.ts > General tab > Create product",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "description": "Experimental block-based product editor is officially deprecated since 10.2. See: https:\\/\\/developer.woocommerce.com\\/2025\\/07\\/23\\/10-1-pre-release-updates\\/#:~:text=%F0%9F%8C%85%20Say%20sayonara,the%20near%20future",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/helpers\\/skip-tests.ts",
                                            "line": 8,
                                            "column": 7
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create a variation option and publish the product",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-variable-product-block-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/create-variable-product-block-editor.spec.ts > Variations tab > Create variable products",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/create-variable-product-block-editor.spec.ts",
                                            "line": 43,
                                            "column": 15
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can edit a variation",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-variable-product-block-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/create-variable-product-block-editor.spec.ts > Variations tab > Create variable products",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/create-variable-product-block-editor.spec.ts",
                                            "line": 43,
                                            "column": 15
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can delete a variation",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-variable-product-block-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/create-variable-product-block-editor.spec.ts > Variations tab > Create variable products",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/create-variable-product-block-editor.spec.ts",
                                            "line": 43,
                                            "column": 15
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can see variations warning and click the CTA",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-variable-product-block-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/create-variable-product-block-editor.spec.ts > Variations tab > Create variable products",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/create-variable-product-block-editor.spec.ts",
                                            "line": 43,
                                            "column": 15
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can see single variation warning and click the CTA",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-variable-product-block-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/create-variable-product-block-editor.spec.ts > Variations tab > Create variable products",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/create-variable-product-block-editor.spec.ts",
                                            "line": 43,
                                            "column": 15
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "is hooked up to sidebar \\"Add New\\"",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/disable-block-product-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/disable-block-product-editor.spec.ts > Disable block product editor",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/disable-block-product-editor.spec.ts",
                                            "line": 54,
                                            "column": 8
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can be disabled from the header",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/disable-block-product-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/disable-block-product-editor.spec.ts > Disable block product editor",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "description": "Experimental block-based product editor is officially deprecated since 10.2. See: https:\\/\\/developer.woocommerce.com\\/2025\\/07\\/23\\/10-1-pre-release-updates\\/#:~:text=%F0%9F%8C%85%20Say%20sayonara,the%20near%20future",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/helpers\\/skip-tests.ts",
                                            "line": 8,
                                            "column": 7
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can be disabled from settings",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/disable-block-product-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/disable-block-product-editor.spec.ts > Disable block product editor",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "description": "Experimental block-based product editor is officially deprecated since 10.2. See: https:\\/\\/developer.woocommerce.com\\/2025\\/07\\/23\\/10-1-pre-release-updates\\/#:~:text=%F0%9F%8C%85%20Say%20sayonara,the%20near%20future",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/helpers\\/skip-tests.ts",
                                            "line": 8,
                                            "column": 7
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create a product with linked products",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/linked-product-tab-product-block-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/linked-product-tab-product-block-editor.spec.ts > General tab > Linked product",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/linked-product-tab-product-block-editor.spec.ts",
                                            "line": 90,
                                            "column": 8
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create a simple product with categories, tags and with password required",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/organization-tab-product-block-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/organization-tab-product-block-editor.spec.ts > General tab > Create product - Organization tab",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/organization-tab-product-block-editor.spec.ts",
                                            "line": 44,
                                            "column": 8
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "add local attribute (with terms) to the Product",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-attributes-block-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/product-attributes-block-editor.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "description": "Experimental block-based product editor is officially deprecated since 10.2. See: https:\\/\\/developer.woocommerce.com\\/2025\\/07\\/23\\/10-1-pre-release-updates\\/#:~:text=%F0%9F%8C%85%20Say%20sayonara,the%20near%20future",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/helpers\\/skip-tests.ts",
                                            "line": 8,
                                            "column": 7
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can add existing attributes",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-attributes-block-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/product-attributes-block-editor.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "description": "Experimental block-based product editor is officially deprecated since 10.2. See: https:\\/\\/developer.woocommerce.com\\/2025\\/07\\/23\\/10-1-pre-release-updates\\/#:~:text=%F0%9F%8C%85%20Say%20sayonara,the%20near%20future",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/helpers\\/skip-tests.ts",
                                            "line": 8,
                                            "column": 7
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can update product attributes",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-attributes-block-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/product-attributes-block-editor.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "description": "Experimental block-based product editor is officially deprecated since 10.2. See: https:\\/\\/developer.woocommerce.com\\/2025\\/07\\/23\\/10-1-pre-release-updates\\/#:~:text=%F0%9F%8C%85%20Say%20sayonara,the%20near%20future",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/helpers\\/skip-tests.ts",
                                            "line": 8,
                                            "column": 7
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can remove product attributes",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-attributes-block-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/product-attributes-block-editor.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "description": "Experimental block-based product editor is officially deprecated since 10.2. See: https:\\/\\/developer.woocommerce.com\\/2025\\/07\\/23\\/10-1-pre-release-updates\\/#:~:text=%F0%9F%8C%85%20Say%20sayonara,the%20near%20future",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/helpers\\/skip-tests.ts",
                                            "line": 8,
                                            "column": 7
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can update the general information of a product",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-edit-block-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/product-edit-block-editor.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "description": "Experimental block-based product editor is officially deprecated since 10.2. See: https:\\/\\/developer.woocommerce.com\\/2025\\/07\\/23\\/10-1-pre-release-updates\\/#:~:text=%F0%9F%8C%85%20Say%20sayonara,the%20near%20future",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/helpers\\/skip-tests.ts",
                                            "line": 8,
                                            "column": 7
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can schedule a product publication",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-edit-block-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/product-edit-block-editor.spec.ts > Publish dropdown options",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/product-edit-block-editor.spec.ts",
                                            "line": 96,
                                            "column": 15
                                        }
                                    },
                                    {
                                        "type": "skip",
                                        "description": "Experimental block-based product editor is officially deprecated since 10.2. See: https:\\/\\/developer.woocommerce.com\\/2025\\/07\\/23\\/10-1-pre-release-updates\\/#:~:text=%F0%9F%8C%85%20Say%20sayonara,the%20near%20future",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/helpers\\/skip-tests.ts",
                                            "line": 8,
                                            "column": 7
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can duplicate a product",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-edit-block-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/product-edit-block-editor.spec.ts > Publish dropdown options",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/product-edit-block-editor.spec.ts",
                                            "line": 96,
                                            "column": 15
                                        }
                                    },
                                    {
                                        "type": "skip",
                                        "description": "Experimental block-based product editor is officially deprecated since 10.2. See: https:\\/\\/developer.woocommerce.com\\/2025\\/07\\/23\\/10-1-pre-release-updates\\/#:~:text=%F0%9F%8C%85%20Say%20sayonara,the%20near%20future",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/helpers\\/skip-tests.ts",
                                            "line": 8,
                                            "column": 7
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can delete a product",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-edit-block-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/product-edit-block-editor.spec.ts > Publish dropdown options",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/product-edit-block-editor.spec.ts",
                                            "line": 96,
                                            "column": 15
                                        }
                                    },
                                    {
                                        "type": "skip",
                                        "description": "Experimental block-based product editor is officially deprecated since 10.2. See: https:\\/\\/developer.woocommerce.com\\/2025\\/07\\/23\\/10-1-pre-release-updates\\/#:~:text=%F0%9F%8C%85%20Say%20sayonara,the%20near%20future",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/helpers\\/skip-tests.ts",
                                            "line": 8,
                                            "column": 7
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can add images",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-images-block-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/product-images-block-editor.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "description": "Experimental block-based product editor is officially deprecated since 10.2. See: https:\\/\\/developer.woocommerce.com\\/2025\\/07\\/23\\/10-1-pre-release-updates\\/#:~:text=%F0%9F%8C%85%20Say%20sayonara,the%20near%20future",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/helpers\\/skip-tests.ts",
                                            "line": 8,
                                            "column": 7
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can replace an image",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-images-block-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/product-images-block-editor.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "description": "Experimental block-based product editor is officially deprecated since 10.2. See: https:\\/\\/developer.woocommerce.com\\/2025\\/07\\/23\\/10-1-pre-release-updates\\/#:~:text=%F0%9F%8C%85%20Say%20sayonara,the%20near%20future",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/helpers\\/skip-tests.ts",
                                            "line": 8,
                                            "column": 7
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can remove an image",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-images-block-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/product-images-block-editor.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "description": "Experimental block-based product editor is officially deprecated since 10.2. See: https:\\/\\/developer.woocommerce.com\\/2025\\/07\\/23\\/10-1-pre-release-updates\\/#:~:text=%F0%9F%8C%85%20Say%20sayonara,the%20near%20future",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/helpers\\/skip-tests.ts",
                                            "line": 8,
                                            "column": 7
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can set an image as cover",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-images-block-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/product-images-block-editor.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "description": "Experimental block-based product editor is officially deprecated since 10.2. See: https:\\/\\/developer.woocommerce.com\\/2025\\/07\\/23\\/10-1-pre-release-updates\\/#:~:text=%F0%9F%8C%85%20Say%20sayonara,the%20near%20future",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/helpers\\/skip-tests.ts",
                                            "line": 8,
                                            "column": 7
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can update sku",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-inventory-block-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/product-inventory-block-editor.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "description": "Experimental block-based product editor is officially deprecated since 10.2. See: https:\\/\\/developer.woocommerce.com\\/2025\\/07\\/23\\/10-1-pre-release-updates\\/#:~:text=%F0%9F%8C%85%20Say%20sayonara,the%20near%20future",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/helpers\\/skip-tests.ts",
                                            "line": 8,
                                            "column": 7
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can update stock status",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-inventory-block-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/product-inventory-block-editor.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "description": "Experimental block-based product editor is officially deprecated since 10.2. See: https:\\/\\/developer.woocommerce.com\\/2025\\/07\\/23\\/10-1-pre-release-updates\\/#:~:text=%F0%9F%8C%85%20Say%20sayonara,the%20near%20future",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/helpers\\/skip-tests.ts",
                                            "line": 8,
                                            "column": 7
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can track stock quantity",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-inventory-block-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/product-inventory-block-editor.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "description": "Experimental block-based product editor is officially deprecated since 10.2. See: https:\\/\\/developer.woocommerce.com\\/2025\\/07\\/23\\/10-1-pre-release-updates\\/#:~:text=%F0%9F%8C%85%20Say%20sayonara,the%20near%20future",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/helpers\\/skip-tests.ts",
                                            "line": 8,
                                            "column": 7
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can limit purchases",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-inventory-block-editor.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/block-editor\\/product-inventory-block-editor.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "description": "Experimental block-based product editor is officially deprecated since 10.2. See: https:\\/\\/developer.woocommerce.com\\/2025\\/07\\/23\\/10-1-pre-release-updates\\/#:~:text=%F0%9F%8C%85%20Say%20sayonara,the%20near%20future",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/block-editor\\/helpers\\/skip-tests.ts",
                                            "line": 8,
                                            "column": 7
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can add custom product attributes",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-product-attributes.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/create-product-attributes.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/create-product-attributes.spec.ts",
                                            "line": 157,
                                            "column": 6
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create a variable product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-variable-product.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Go to the \\"Add new product\\" page",
                                    "status": "passed"
                                },
                                {
                                    "name": "Type \\"Variable Product with Three Variations\\" into the \\"Product name\\" input field.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Select the \\"Variable product\\" product type.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Scroll into the \\"Attributes\\" tab and click it.",
                                    "status": "passed"
                                },
                                {
                                    "name": "See if the tour was displayed.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Tour was displayed, so dismiss it.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Wait for the tour\'s dismissal to be saved",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the \\"Variations\\" tab to appear",
                                    "status": "passed"
                                },
                                {
                                    "name": "Save draft.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the \\"Product draft updated.\\" notice to appear.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the product type to be \\"Variable product\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Save product ID for clean up.",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/create-variable-product.spec.ts > Add variable product",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can generate variations from product attributes",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-variations.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Open \\"Edit product\\" page of product id <ID>",
                                    "status": "passed"
                                },
                                {
                                    "name": "Click on the \\"Variations\\" tab.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Click on the \\"Generate variations\\" button.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the number of variations to be 8",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the variation \\"Red, Small, Woo\\" to be generated.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the variation \\"Red, Small, WordPress\\" to be generated.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the variation \\"Red, Medium, Woo\\" to be generated.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the variation \\"Red, Medium, WordPress\\" to be generated.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the variation \\"Green, Small, Woo\\" to be generated.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the variation \\"Green, Small, WordPress\\" to be generated.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the variation \\"Green, Medium, Woo\\" to be generated.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the variation \\"Green, Medium, WordPress\\" to be generated.",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/create-variations.spec.ts > Add variations",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can manually add a variation",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-variations.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Open \\"Edit product\\" page of product id <ID>",
                                    "status": "passed"
                                },
                                {
                                    "name": "Hook up the woocommerce_variations_added jQuery trigger",
                                    "status": "passed"
                                },
                                {
                                    "name": "Click on the \\"Variations\\" tab.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Manually add 3 variations",
                                    "status": "passed"
                                },
                                {
                                    "name": "Click \\"Add manually\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Select \\"Red\\" from the \\"Colour\\" attribute menu",
                                    "status": "passed"
                                },
                                {
                                    "name": "Select \\"Small\\" from the \\"Size\\" attribute menu",
                                    "status": "passed"
                                },
                                {
                                    "name": "Select \\"Woo\\" from the \\"Logo\\" attribute menu",
                                    "status": "passed"
                                },
                                {
                                    "name": "Click \\"Save changes\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the variation Red, Small, Woo to be successfully saved.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Click \\"Add manually\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Select \\"Red\\" from the \\"Colour\\" attribute menu",
                                    "status": "passed"
                                },
                                {
                                    "name": "Select \\"Small\\" from the \\"Size\\" attribute menu",
                                    "status": "passed"
                                },
                                {
                                    "name": "Select \\"WordPress\\" from the \\"Logo\\" attribute menu",
                                    "status": "passed"
                                },
                                {
                                    "name": "Click \\"Save changes\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the variation Red, Small, WordPress to be successfully saved.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Click \\"Add manually\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Select \\"Red\\" from the \\"Colour\\" attribute menu",
                                    "status": "passed"
                                },
                                {
                                    "name": "Select \\"Medium\\" from the \\"Size\\" attribute menu",
                                    "status": "passed"
                                },
                                {
                                    "name": "Select \\"Woo\\" from the \\"Logo\\" attribute menu",
                                    "status": "passed"
                                },
                                {
                                    "name": "Click \\"Save changes\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the variation Red, Medium, Woo to be successfully saved.",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/create-variations.spec.ts > Add variations",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create a simple virtual product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-create-simple.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "add new product",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product name and description",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product price and inventory information",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product attributes",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product advanced information",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product categories",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product tags",
                                    "status": "passed"
                                },
                                {
                                    "name": "add virtual product details",
                                    "status": "passed"
                                },
                                {
                                    "name": "publish the product",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the saved product in frontend",
                                    "status": "passed"
                                },
                                {
                                    "name": "shopper can add the product to cart",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-create-simple.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create a simple non virtual product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-create-simple.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "add new product",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product name and description",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product price and inventory information",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product attributes",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product advanced information",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product categories",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product tags",
                                    "status": "passed"
                                },
                                {
                                    "name": "add shipping details",
                                    "status": "passed"
                                },
                                {
                                    "name": "publish the product",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the saved product in frontend",
                                    "status": "passed"
                                },
                                {
                                    "name": "shopper can add the product to cart",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-create-simple.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create a simple downloadable product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-create-simple.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "add new product",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product name and description",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product price and inventory information",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product attributes",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product advanced information",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product categories",
                                    "status": "passed"
                                },
                                {
                                    "name": "add product tags",
                                    "status": "passed"
                                },
                                {
                                    "name": "publish the product",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the saved product in frontend",
                                    "status": "passed"
                                },
                                {
                                    "name": "shopper can add the product to cart",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-create-simple.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can delete a product from edit view",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-delete.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Navigate to product edit page",
                                    "status": "passed"
                                },
                                {
                                    "name": "Move product to trash",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify product was trashed",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-delete.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can quick delete a product from product list",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-delete.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Navigate to products list page",
                                    "status": "passed"
                                },
                                {
                                    "name": "Move product to trash",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify product was trashed",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-delete.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can permanently delete a product from trash list",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-delete.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Navigate to products trash list page",
                                    "status": "passed"
                                },
                                {
                                    "name": "Permanently delete the product",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify product was permanently deleted",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-delete.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can edit a product and save the changes",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "edit the product name",
                                    "status": "passed"
                                },
                                {
                                    "name": "edit the product description",
                                    "status": "passed"
                                },
                                {
                                    "name": "edit the product price",
                                    "status": "passed"
                                },
                                {
                                    "name": "publish the updated product",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the changes",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-edit.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can bulk edit products",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "select and bulk edit the products",
                                    "status": "passed"
                                },
                                {
                                    "name": "update the regular price",
                                    "status": "passed"
                                },
                                {
                                    "name": "update the sale price",
                                    "status": "passed"
                                },
                                {
                                    "name": "update the stock quantity",
                                    "status": "passed"
                                },
                                {
                                    "name": "save the updates",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the changes",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-edit.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can restore regular price when bulk editing products",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "select and bulk edit the products",
                                    "status": "passed"
                                },
                                {
                                    "name": "update the sale price",
                                    "status": "passed"
                                },
                                {
                                    "name": "save the updates",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the changes",
                                    "status": "passed"
                                },
                                {
                                    "name": "Update products leaving the \\"Sale > Change to\\" empty",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify products have their regular price again",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-edit.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can decrease the sale price if the product was not previously in sale when bulk editing products",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Update products with the \\"Sale > Decrease existing sale price\\" option",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify products have a sale price",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-edit.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "increasing the sale price from 0 does not change the sale price when bulk editing products",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-edit.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Update products with the \\"Sale > Increase existing sale price\\" option",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify products have a sale price",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-edit.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should allow exporting a single selected simple product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-export.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Navigate to product list and select product",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify export button text and link for single selection",
                                    "status": "passed"
                                },
                                {
                                    "name": "Navigate to export page and verify UI elements",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-export.spec.ts > Product > Export Selected Products",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should allow exporting multiple selected products (simple and variable)",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-export.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Navigate to product list and select multiple products",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify export button text and link for multiple selections",
                                    "status": "passed"
                                },
                                {
                                    "name": "Navigate to export page and verify UI elements for multiple products",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-export.spec.ts > Product > Export Selected Products",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should allow clearing selection from the export page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-export.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Navigate to product list, select product, and go to export page",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify export page notice and URL for selected product",
                                    "status": "passed"
                                },
                                {
                                    "name": "Click \'clear your selection\' link",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify redirect to general export page and UI elements",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-export.spec.ts > Product > Export Selected Products",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should show the default export screen when no products are selected",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-export.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Navigate to product list",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify default export button state and navigate to export page",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify UI elements for default export",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-export.spec.ts > Product > Export Selected Products",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should be able to add grouped products to the cart",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-grouped.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-grouped.spec.ts > Grouped Product Page",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should be able to remove grouped products from the cart",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-grouped.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-grouped.spec.ts > Grouped Product Page",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can set product image",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-images.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Navigate to product edit page",
                                    "status": "passed"
                                },
                                {
                                    "name": "Set product image",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify product image was set",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-images.spec.ts > Products > Product Images",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can update the product image",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-images.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Navigate to product edit page",
                                    "status": "passed"
                                },
                                {
                                    "name": "Update product image",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify product image was set",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-images.spec.ts > Products > Product Images",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can delete the product image",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-images.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Navigate to product edit page",
                                    "status": "passed"
                                },
                                {
                                    "name": "Remove product image",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify product image was removed",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-images.spec.ts > Products > Product Images",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create a product gallery",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-images.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Navigate to product edit page",
                                    "status": "passed"
                                },
                                {
                                    "name": "Add product gallery images",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify product gallery",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-images.spec.ts > Products > Product Images",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can update a product gallery",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-images.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Navigate to product edit page",
                                    "status": "passed"
                                },
                                {
                                    "name": "Remove images from product gallery",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify product gallery",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-images.spec.ts > Products > Product Images",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should show error message if you go without providing CSV file",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@not-e2e",
                                "@non-critical"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-import-csv.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-import-csv.spec.ts > Import Products from a CSV file",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/product-import-csv.spec.ts",
                                            "line": 103,
                                            "column": 15
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can upload the CSV file and import products",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-import-csv.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-import-csv.spec.ts > Import Products from a CSV file",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/product-import-csv.spec.ts",
                                            "line": 103,
                                            "column": 15
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can override the existing products via CSV import",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-import-csv.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-import-csv.spec.ts > Import Products from a CSV file",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/product-import-csv.spec.ts",
                                            "line": 103,
                                            "column": 15
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "add up-sells",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-linked-products.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Navigate to product edit page",
                                    "status": "passed"
                                },
                                {
                                    "name": "go to Linked Products",
                                    "status": "passed"
                                },
                                {
                                    "name": "add an up-sell by searching for product name",
                                    "status": "passed"
                                },
                                {
                                    "name": "add an up-sell by searching for product id",
                                    "status": "passed"
                                },
                                {
                                    "name": "update the product",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the up-sell in the store frontend",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-linked-products.spec.ts > Products > Related products",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "remove up-sells",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-linked-products.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "verify the up-sells in the store frontend",
                                    "status": "passed"
                                },
                                {
                                    "name": "Navigate to product edit page",
                                    "status": "passed"
                                },
                                {
                                    "name": "go to Linked Products",
                                    "status": "passed"
                                },
                                {
                                    "name": "remove up-sells for a product",
                                    "status": "passed"
                                },
                                {
                                    "name": "update the product",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the up-sells in the store frontend",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-linked-products.spec.ts > Products > Related products",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "add cross-sells",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-linked-products.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Navigate to product edit page",
                                    "status": "passed"
                                },
                                {
                                    "name": "go to Linked Products",
                                    "status": "passed"
                                },
                                {
                                    "name": "add a cross-sell by searching for product name",
                                    "status": "passed"
                                },
                                {
                                    "name": "add a cross-sell by searching for product id",
                                    "status": "passed"
                                },
                                {
                                    "name": "update the product",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the cross-sell in the store frontend",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-linked-products.spec.ts > Products > Related products",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "remove cross-sells",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-linked-products.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Navigate to product edit page",
                                    "status": "passed"
                                },
                                {
                                    "name": "go to Linked Products",
                                    "status": "passed"
                                },
                                {
                                    "name": "remove cross-sells for a product",
                                    "status": "passed"
                                },
                                {
                                    "name": "update the product",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the cross-sells in the store frontend",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/product-linked-products.spec.ts > Products > Related products",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can view products reviews list",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-reviews.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-reviews.spec.ts > Product Reviews > Merchant manages reviews",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can filter the reviews by product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-reviews.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-reviews.spec.ts > Product Reviews > Merchant manages reviews",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can quick edit a product review",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-reviews.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-reviews.spec.ts > Product Reviews > Merchant manages reviews",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can edit a product review",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-reviews.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-reviews.spec.ts > Product Reviews > Merchant manages reviews",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can approve a product review",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-reviews.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-reviews.spec.ts > Product Reviews > Merchant manages reviews",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can mark a product review as spam",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-reviews.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-reviews.spec.ts > Product Reviews > Merchant manages reviews",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can reply to a product review",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-reviews.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-reviews.spec.ts > Product Reviews > Merchant manages reviews",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip",
                                        "location": {
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/product\\/product-reviews.spec.ts",
                                            "line": 262,
                                            "column": 8
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can delete a product review",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-reviews.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-reviews.spec.ts > Product Reviews > Merchant manages reviews",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "shopper can post a review",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-reviews.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-reviews.spec.ts > Product Reviews > Shopper adds reviews",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can do a partial search for a product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-search.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-search.spec.ts > Products > Search and View a product",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can view a product\'s details after search",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-search.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-search.spec.ts > Products > Search and View a product",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "returns no results for non-existent product search",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-search.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-search.spec.ts > Products > Search and View a product",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can update settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-settings.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-settings.spec.ts > WooCommerce Products > Downloadable Product Settings",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should see shop catalog with all its products",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-tags-attributes.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-tags-attributes.spec.ts > Browse product tags and attributes from the product page",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should see and sort tags page with all the products",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-tags-attributes.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-tags-attributes.spec.ts > Browse product tags and attributes from the product page",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should see and sort attributes page with all its products",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-tags-attributes.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-tags-attributes.spec.ts > Browse product tags and attributes from the product page",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can see products showcase",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-tags-attributes.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-tags-attributes.spec.ts > Browse product tags and attributes from the product page",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should be able to add variation products to the cart",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-variable.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-variable.spec.ts > Variable Product Page",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should be able to remove variation products from the cart",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-variable.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-variable.spec.ts > Variable Product Page",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Shopper can change variable attributes to the same value",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-variable.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-variable.spec.ts > Shopper > Update variable product",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Shopper can change attributes to combination with dimensions and weight",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-variable.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-variable.spec.ts > Shopper > Update variable product",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Shopper can change variable product attributes to variation with a different price",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-variable.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-variable.spec.ts > Shopper > Update variable product",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Shopper can reset variations",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-variable.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > product\\/product-variable.spec.ts > Shopper > Update variable product",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can individually edit variations",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/update-variations.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Go to the \\"Edit product\\" page.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Click on the \\"Variations\\" tab.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expand all variations.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Edit the first variation.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Check the \\"Virtual\\" checkbox.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Set regular price to \\"9.99\\".",
                                    "status": "passed"
                                },
                                {
                                    "name": "Edit the second variation.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Check the \\"Virtual\\" checkbox.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Set regular price to \\"11.99\\".",
                                    "status": "passed"
                                },
                                {
                                    "name": "Edit the third variation.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Check \\"Manage stock?\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Set regular price to \\"20.00\\".",
                                    "status": "passed"
                                },
                                {
                                    "name": "Set the weight and dimensions.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Click \\"Save changes\\".",
                                    "status": "passed"
                                },
                                {
                                    "name": "Click on the \\"Variations\\" tab.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expand all variations.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the first variation to be virtual.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the regular price of the first variation to be \\"9.99\\".",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the second variation to be virtual.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the regular price of the second variation to be \\"11.99\\".",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the \\"Manage stock?\\" checkbox of the third variation to be checked.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the regular price of the third variation to be \\"20.00\\".",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the weight and dimensions of the third variation to be correct.",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/update-variations.spec.ts > Update variations",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can bulk edit variations",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/update-variations.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Go to the \\"Edit product\\" page.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Click on the \\"Variations\\" tab.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Select the \'Toggle \\"Downloadable\\"\' bulk action.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expand all variations.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect all \\"Downloadable\\" checkboxes to be checked.",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/update-variations.spec.ts > Update variations",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can delete all variations",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/update-variations.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Go to the \\"Edit product\\" page.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Click on the \\"Variations\\" tab.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Select the bulk action \\"Delete all variations\\".",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect that there are no more variations.",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/update-variations.spec.ts > Update variations",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can manage stock levels",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/update-variations.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Go to the \\"Edit product\\" page.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Click on the \\"Variations\\" tab.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expand all variations",
                                    "status": "passed"
                                },
                                {
                                    "name": "Check the \\"Manage stock?\\" box",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the \\"Stock status\\" text box to disappear",
                                    "status": "passed"
                                },
                                {
                                    "name": "Enter \\"9.99\\" as the regular price",
                                    "status": "passed"
                                },
                                {
                                    "name": "Enter \\"100\\" as the stock quantity",
                                    "status": "passed"
                                },
                                {
                                    "name": "Select \\"Allow, but notify customer\\" from the \\"Allow backorders?\\" menu",
                                    "status": "passed"
                                },
                                {
                                    "name": "Enter \\"10\\" in the \\"Low stock threshold\\" input field.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Click \\"Save changes\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expand all variations",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the stock quantity to be saved correctly",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the \\"Low stock threshold\\" value to be saved correctly",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the \\"Allow backorders?\\" value to be saved correctly",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/update-variations.spec.ts > Update variations",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can set variation defaults",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/update-variations.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Go to the \\"Edit product\\" page.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Click on the \\"Variations\\" tab.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Wait for block overlay to disappear.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Select variation defaults",
                                    "status": "passed"
                                },
                                {
                                    "name": "Click \\"Save changes\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "View the product from the shop",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the default attributes to be pre-selected",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect \\"Red\\" is selected as the default \\"Colour\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect \\"Small\\" is selected as the default \\"Size\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect \\"WordPress\\" is selected as the default \\"Logo\\"",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/update-variations.spec.ts > Update variations",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can remove a variation",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@gutenberg"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/update-variations.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Go to the \\"Edit product\\" page.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Click on the \\"Variations\\" tab.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Click \\"Remove\\" on a variation",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect the variation to be removed",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > product\\/update-variations.spec.ts > Update variations",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "admin can manage consumer keys",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/consumer-token.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "navigate to rest api settings page",
                                    "status": "passed"
                                },
                                {
                                    "name": "can generate a consumer key",
                                    "status": "passed"
                                },
                                {
                                    "name": "can use the consumer key",
                                    "status": "passed"
                                },
                                {
                                    "name": "can revoke the consumer key",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > settings\\/consumer-token.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Save Changes button is disabled by default and enabled only after changes.",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@non-critical",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-general.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > settings\\/settings-general.spec.ts > WooCommerce General Settings",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can update settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-general.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > settings\\/settings-general.spec.ts > WooCommerce General Settings",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can enable tax calculation",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-tax.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > settings\\/settings-tax.spec.ts > WooCommerce Tax Settings > enable",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can set tax options",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-tax.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > settings\\/settings-tax.spec.ts > WooCommerce Tax Settings",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can add tax classes",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-tax.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > settings\\/settings-tax.spec.ts > WooCommerce Tax Settings",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can set rate settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-tax.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > settings\\/settings-tax.spec.ts > WooCommerce Tax Settings",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can remove tax classes",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-tax.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > settings\\/settings-tax.spec.ts > WooCommerce Tax Settings",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can enable analytics tracking",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@skip-on-wpcom"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-woo-com.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > settings\\/settings-woo-com.spec.ts > WooCommerce woo.com Settings",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can enable marketplace suggestions",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services",
                                "@skip-on-wpcom"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-woo-com.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > settings\\/settings-woo-com.spec.ts > WooCommerce woo.com Settings",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Webhook cannot be bulk deleted without nonce",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/webhooks.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > settings\\/webhooks.spec.ts > Manage webhooks",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can add a shipping class with an unique slug",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shipping-classes.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > shipping\\/shipping-classes.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can add a shipping class with an auto-generated slug",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shipping-classes.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > shipping\\/shipping-classes.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can delete the shipping zone region",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shipping-zones.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > shipping\\/shipping-zones.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can delete the shipping zone method",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shipping-zones.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > shipping\\/shipping-zones.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can redirect user to cart from shop page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@not-e2e",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-redirection.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > shop\\/cart-redirection.spec.ts > Cart > Redirect to cart from shop",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can redirect user to cart from detail page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@not-e2e",
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-redirection.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > shop\\/cart-redirection.spec.ts > Cart > Redirect to cart from shop",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should let user search the store",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shop-search-browse-sort.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Go to the shop and perform the search",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > shop\\/shop-search-browse-sort.spec.ts > Search, browse by categories and sort items in the shop",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should let user browse products by categories",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shop-search-browse-sort.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Go to the shop and browse by the category",
                                    "status": "passed"
                                },
                                {
                                    "name": "Ensure the category page contains all the relevant products",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > shop\\/shop-search-browse-sort.spec.ts > Search, browse by categories and sort items in the shop",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "should let user sort the products in the shop",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@payments",
                                "@services"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shop-search-browse-sort.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Go to the shop and sort by price high to low",
                                    "status": "passed"
                                },
                                {
                                    "name": "Go to the shop and sort by price low to high",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > shop\\/shop-search-browse-sort.spec.ts > Search, browse by categories and sort items in the shop",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Check the title of the shop page after the page has been deleted",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@could-be-lower-level-test"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shop-title-after-deletion.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > shop\\/shop-title-after-deletion.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can visit the lost password page from the login page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/lost-password.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > user\\/lost-password.spec.ts > Can go to lost password page and submit the form",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can submit the lost password form",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/lost-password.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > user\\/lost-password.spec.ts > Can go to lost password page and submit the form",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create a new Customer",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/users-create.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "create a new user",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the new user is displayed in users list",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify you can access the new user edit page",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the new user can login",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > user\\/users-create.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can update customer data",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/users-manage.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "update user data",
                                    "status": "passed"
                                },
                                {
                                    "name": "update billing address",
                                    "status": "passed"
                                },
                                {
                                    "name": "copy shipping address from billing address",
                                    "status": "passed"
                                },
                                {
                                    "name": "save the changes",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the updates",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > user\\/users-manage.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can update shop manager data",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/users-manage.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "update user data",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > user\\/users-manage.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can delete a customer",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/users-manage.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "hover the username and delete",
                                    "status": "passed"
                                },
                                {
                                    "name": "confirm deletion",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the user was deleted",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > user\\/users-manage.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can delete a shop manager",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/users-manage.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "hover the username and delete",
                                    "status": "passed"
                                },
                                {
                                    "name": "confirm deletion",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the user was deleted",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > user\\/users-manage.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create new page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@gutenberg",
                                "@wp-core"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-page.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > wp-core\\/create-page.spec.ts > Can create a new page",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "can create new post",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@gutenberg",
                                "@wp-core"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-post.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > wp-core\\/create-post.spec.ts > Can create a new post",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
                            "extra": {
                                "annotations": []
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "logged-in customer can comment on a post",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@wp-core"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/post-comments.spec.ts",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "disable Jetpack comments if Jetpack is installed and active",
                                    "status": "passed"
                                }
                            ],
                            "suite": "e2e > wp-core\\/post-comments.spec.ts",
                            "attachments": [],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
                            "stderr": [
                                "Warning: Using Basic Auth over HTTP exposes credentials in plaintext!\\n"
                            ],
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
                "generic": []
            }
        }
    ]
]';
