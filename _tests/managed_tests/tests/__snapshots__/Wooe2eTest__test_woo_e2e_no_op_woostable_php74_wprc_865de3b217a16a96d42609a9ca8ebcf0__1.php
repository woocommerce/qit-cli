<?php return '[
    [
        {
            "test_run_id": 123456,
            "run_id": 123456,
            "test_type": "woo-e2e",
            "test_type_display": "Woo E2E",
            "wordpress_version": "6.0.0-normalized",
            "woocommerce_version": "6.0.0-normalized",
            "php_version": "7.4",
            "max_php_version": "",
            "min_php_version": "",
            "additional_woo_plugins": [],
            "additional_wp_plugins": [],
            "test_log": "",
            "performance_results": "",
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
            "test_summary": "Tests: 146 total, 109 passed, 30 failed, 7 skipped",
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
            "iterations": 3,
            "test_group_id": "",
            "created_at": "2025-01-01 00:00:00",
            "test_result_json_extracted": "{EXTRACTED}",
            "ctrf_json_extracted": "{EXTRACTED}",
            "debug_log_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "numFailedTestSuites": 7,
                "numPassedTestSuites": 17,
                "numPendingTestSuites": 78,
                "numTotalTestSuites": 100,
                "numFailedTests": 30,
                "numPassedTests": 109,
                "numPendingTests": 245,
                "numTotalTests": 384,
                "testResults": [
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
                            "authenticate users": [
                                {
                                    "title": "authenticate users",
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
                            "setup site": [
                                {
                                    "title": "setup site",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "analytics\\/analytics-access.spec.js",
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
                        "file": "analytics\\/analytics-data.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "confirms correct summary numbers on overview page": [
                                {
                                    "title": "confirms correct summary numbers on overview page",
                                    "status": "passed"
                                }
                            ],
                            "downloads revenue report as CSV": [
                                {
                                    "title": "downloads revenue report as CSV",
                                    "status": "passed"
                                }
                            ],
                            "use date filter on overview page": [
                                {
                                    "title": "use date filter on overview page",
                                    "status": "passed"
                                }
                            ],
                            "set custom date range on revenue report": [
                                {
                                    "title": "set custom date range on revenue report",
                                    "status": "passed"
                                }
                            ],
                            "use advanced filters on orders report": [
                                {
                                    "title": "use advanced filters on orders report",
                                    "status": "passed"
                                }
                            ],
                            "use filter by single product on products report": [
                                {
                                    "title": "use filter by single product on products report",
                                    "status": "passed"
                                }
                            ],
                            "analytics settings": [
                                {
                                    "title": "analytics settings",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "analytics\\/analytics-overview.spec.js",
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
                        "file": "basic\\/basic.spec.js",
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
                        "file": "basic\\/dashboard-access.spec.js",
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
                        "file": "basic\\/page-loads.spec.js",
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
                        "file": "brands\\/create-product-brand.spec.js",
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
                        "file": "cart\\/add-to-cart.spec.js",
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
                        "file": "cart\\/cart.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
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
                        "file": "checkout\\/checkout-link.spec.js",
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
                        "file": "checkout\\/checkout.spec.js",
                        "status": "passed",
                        "has_pending": true,
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
                                    "status": "pending"
                                }
                            ],
                            "customer can login at checkout and place the order with a different shipping address classic checkout": [
                                {
                                    "title": "customer can login at checkout and place the order with a different shipping address classic checkout",
                                    "status": "pending"
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
                        "file": "coupons\\/cart-block-coupons.spec.js",
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
                        "file": "coupons\\/cart-checkout-coupons.spec.js",
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
                        "file": "coupons\\/cart-checkout-restricted-coupons.spec.js",
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
                        "file": "coupons\\/create-coupon.spec.js",
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
                        "file": "coupons\\/create-restricted-coupons.spec.js",
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
                        "file": "customer\\/customer-list.spec.js",
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
                        "file": "customize-store\\/assembler\\/color-picker.spec.js",
                        "status": "failed",
                        "has_pending": false,
                        "tests": {
                            "Assembler -> Color Pickers": [
                                {
                                    "title": "Color pickers should be displayed",
                                    "status": "failed"
                                },
                                {
                                    "title": "Color palette Slate should be applied",
                                    "status": "failed"
                                },
                                {
                                    "title": "Color picker should be focused when a color is picked",
                                    "status": "failed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "customize-store\\/assembler\\/font-picker.spec.js",
                        "status": "failed",
                        "has_pending": false,
                        "tests": {
                            "Assembler -> Font Picker": [
                                {
                                    "title": "Font pickers should be displayed",
                                    "status": "failed"
                                },
                                {
                                    "title": "Picking a font should trigger an update of fonts on the site preview",
                                    "status": "failed"
                                },
                                {
                                    "title": "Font pickers should be focused when a font is picked",
                                    "status": "failed"
                                },
                                {
                                    "title": "Selected font palette should be applied on the frontend",
                                    "status": "failed"
                                },
                                {
                                    "title": "Clicking opt-in new fonts should be available",
                                    "status": "failed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "customize-store\\/assembler\\/footer.spec.js",
                        "status": "failed",
                        "has_pending": false,
                        "tests": {
                            "Assembler -> Footers": [
                                {
                                    "title": "Available footers should be displayed",
                                    "status": "failed"
                                },
                                {
                                    "title": "The selected footer should be focused when is clicked",
                                    "status": "failed"
                                },
                                {
                                    "title": "The selected footer should be applied on the frontend",
                                    "status": "failed"
                                },
                                {
                                    "title": "Picking a footer should trigger an update on the site preview",
                                    "status": "failed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "customize-store\\/assembler\\/full-composability.spec.js",
                        "status": "failed",
                        "has_pending": false,
                        "tests": {
                            "Assembler -> Full composability": [
                                {
                                    "title": "Clicking on \\"Design your homepage\\" should open the Intro sidebar by default",
                                    "status": "failed"
                                },
                                {
                                    "title": "Clicking on a category should open the sidebar for it",
                                    "status": "failed"
                                },
                                {
                                    "title": "Clicking on a pattern should insert it in the preview",
                                    "status": "failed"
                                },
                                {
                                    "title": "Clicking on a pattern should always scroll the page to the inserted pattern",
                                    "status": "failed"
                                },
                                {
                                    "title": "Clicking the \\"Move up\\/down\\" buttons should change the pattern order in the preview",
                                    "status": "failed"
                                },
                                {
                                    "title": "Clicking the \\"Shuffle\\" button on a patterns should replace it for another one",
                                    "status": "failed"
                                },
                                {
                                    "title": "Clicking the \\"Delete\\" button on a pattern should remove it from the preview",
                                    "status": "failed"
                                },
                                {
                                    "title": "Clicking the \\"Add patterns\\" button on the No Blocks view should add a default pattern",
                                    "status": "failed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "customize-store\\/assembler\\/header.spec.js",
                        "status": "failed",
                        "has_pending": false,
                        "tests": {
                            "Assembler -> headers": [
                                {
                                    "title": "Available headers should be displayed",
                                    "status": "failed"
                                },
                                {
                                    "title": "The selected header should be focused when is clicked",
                                    "status": "failed"
                                },
                                {
                                    "title": "The selected header should be applied on the frontend",
                                    "status": "failed"
                                },
                                {
                                    "title": "Picking a header should trigger an update on the site preview",
                                    "status": "failed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "customize-store\\/assembler\\/homepage.spec.js",
                        "status": "failed",
                        "has_pending": true,
                        "tests": {
                            "Assembler -> Homepage": [
                                {
                                    "title": "The selected homepage should be focused when is clicked",
                                    "status": "pending"
                                },
                                {
                                    "title": "The selected homepage should be visible on the site preview",
                                    "status": "pending"
                                },
                                {
                                    "title": "Selected homepage should be applied on the frontend",
                                    "status": "pending"
                                }
                            ],
                            "Homepage tracking banner": [
                                {
                                    "title": "Should show the \\"Want more patterns?\\" banner with the Opt-in message when tracking is not allowed",
                                    "status": "failed"
                                },
                                {
                                    "title": "Should show the \\"Want more patterns?\\" banner with the offline message when the user is offline and tracking is not allowed",
                                    "status": "failed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "customize-store\\/assembler\\/logo-picker\\/logo-picker.spec.js",
                        "status": "failed",
                        "has_pending": true,
                        "tests": {
                            "Assembler -> Logo Picker": [
                                {
                                    "title": "Logo Picker should be empty initially",
                                    "status": "failed"
                                },
                                {
                                    "title": "Selecting an image should update the site preview",
                                    "status": "failed"
                                },
                                {
                                    "title": "Changing the image width should update the site preview and the frontend",
                                    "status": "failed"
                                },
                                {
                                    "title": "Clicking the Delete button should remove the selected image",
                                    "status": "failed"
                                },
                                {
                                    "title": "Clicking the replace image should open the media gallery",
                                    "status": "pending"
                                },
                                {
                                    "title": "Logo should be visible after header update",
                                    "status": "pending"
                                },
                                {
                                    "title": "The selected image should be visible on the frontend",
                                    "status": "pending"
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
                                    "title": "Can not access the Assembler Hub page when the theme is not customized",
                                    "status": "pending"
                                },
                                {
                                    "title": "Can access the Assembler Hub page when the theme is already customized",
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
                        "file": "customize-store\\/intro.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Store owner can view the Intro page": [
                                {
                                    "title": "it shows the \\"offline banner\\" when the network is offline",
                                    "status": "pending"
                                },
                                {
                                    "title": "it shows the \\"no AI\\" banner on Core when the task is not completed",
                                    "status": "pending"
                                },
                                {
                                    "title": "it shows the \\"no AI customize theme\\" banner when the task is completed",
                                    "status": "pending"
                                },
                                {
                                    "title": "it shows the \\"non default block theme\\" banner when the theme is a block theme different than TT4 and redirects to the editor",
                                    "status": "pending"
                                },
                                {
                                    "title": "clicking on \\"Go to the Customizer\\" with a classic theme should go to the customizer",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "customize-store\\/loading-screen\\/loading-screen.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Assembler - Loading Page": [
                                {
                                    "title": "should display loading screen and steps on first run",
                                    "status": "pending"
                                },
                                {
                                    "title": "should redirect to intro page in case of errors",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "customize-store\\/transitional.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Store owner can view the Transitional page": [
                                {
                                    "title": "Accessing the transitional page when the CYS flow is not completed should redirect to the Intro page",
                                    "status": "pending"
                                },
                                {
                                    "title": "Clicking on \\"Finish customizing\\" in the assembler should go to the transitional page",
                                    "status": "pending"
                                },
                                {
                                    "title": "Clicking on \\"View store\\" should go to the store home page",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "editor\\/command-palette.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "can use the \\"Add new product\\" command": [
                                {
                                    "title": "can use the \\"Add new product\\" command",
                                    "status": "pending"
                                }
                            ],
                            "can use the \\"Add new order\\" command": [
                                {
                                    "title": "can use the \\"Add new order\\" command",
                                    "status": "pending"
                                }
                            ],
                            "can use the \\"Products\\" command": [
                                {
                                    "title": "can use the \\"Products\\" command",
                                    "status": "pending"
                                }
                            ],
                            "can use the \\"Orders\\" command": [
                                {
                                    "title": "can use the \\"Orders\\" command",
                                    "status": "pending"
                                }
                            ],
                            "can use the product search command": [
                                {
                                    "title": "can use the product search command",
                                    "status": "pending"
                                }
                            ],
                            "can use a settings command": [
                                {
                                    "title": "can use a settings command",
                                    "status": "pending"
                                }
                            ],
                            "can use an analytics command": [
                                {
                                    "title": "can use an analytics command",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "email\\/account-emails.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "New customer should receive an email with login details": [
                                {
                                    "title": "New customer should receive an email with login details",
                                    "status": "pending"
                                }
                            ],
                            "Customer should receive an email when initiating a password reset": [
                                {
                                    "title": "Customer should receive an email when initiating a password reset",
                                    "status": "pending"
                                }
                            ],
                            "Customer should receive an email when password reset initiated from admin": [
                                {
                                    "title": "Customer should receive an email when password reset initiated from admin",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "email\\/editor-tracking-selectors.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "WooCommerce Email Editor Tracking Selectors": [
                                {
                                    "title": "Check selectors for tracking events",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "email\\/order-emails.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "customer receives email for processing order": [
                                {
                                    "title": "customer receives email for processing order",
                                    "status": "pending"
                                }
                            ],
                            "admin receives email for processing order": [
                                {
                                    "title": "admin receives email for processing order",
                                    "status": "pending"
                                }
                            ],
                            "customer receives email for completed order": [
                                {
                                    "title": "customer receives email for completed order",
                                    "status": "pending"
                                }
                            ],
                            "admin receives email for cancelled order": [
                                {
                                    "title": "admin receives email for cancelled order",
                                    "status": "pending"
                                }
                            ],
                            "Merchant can resend order details to customer": [
                                {
                                    "title": "Merchant can resend order details to customer",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "email\\/settings-email-listing.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "WooCommerce Email Settings List View": [
                                {
                                    "title": "Email settings list view renders correctly and allows to edit email status and search",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "email\\/settings-email.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "WooCommerce Email Settings": [
                                {
                                    "title": "See email preview",
                                    "status": "pending"
                                },
                                {
                                    "title": "Email sender options live change in email preview",
                                    "status": "pending"
                                },
                                {
                                    "title": "Live preview when changing email settings",
                                    "status": "pending"
                                },
                                {
                                    "title": "Send email preview",
                                    "status": "pending"
                                },
                                {
                                    "title": "See specific email preview",
                                    "status": "pending"
                                },
                                {
                                    "title": "Choose image in email image url field",
                                    "status": "pending"
                                },
                                {
                                    "title": "See color palette settings",
                                    "status": "pending"
                                },
                                {
                                    "title": "See font family setting",
                                    "status": "pending"
                                },
                                {
                                    "title": "See updated footer text field",
                                    "status": "pending"
                                },
                                {
                                    "title": "Reset color palette with a feature flag",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "email-editor\\/email-editor-loads.spec.js",
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
                        "file": "email-editor\\/email-editor-settings-sidebar.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "WooCommerce Email Editor Settings Sidebar Integration": [
                                {
                                    "title": "Can update email status",
                                    "status": "pending"
                                },
                                {
                                    "title": "Can update email subject and preview text",
                                    "status": "pending"
                                },
                                {
                                    "title": "Can update email recipients",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "marketing\\/overview.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Marketing page": [
                                {
                                    "title": "Marketing Overview page have relevant content",
                                    "status": "pending"
                                },
                                {
                                    "title": "Learning section can be expanded",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "my-account\\/my-account-addresses.spec.js",
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
                        "file": "my-account\\/my-account-create-account.spec.js",
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
                        "file": "my-account\\/my-account-downloads.spec.js",
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
                        "file": "my-account\\/my-account-pay-order.spec.js",
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
                        "file": "my-account\\/my-account.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "My account page": [
                                {
                                    "title": "allows customer to login and navigate",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "onboarding\\/add-product-task.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Add Product Task": [
                                {
                                    "title": "Add product task displays options for different product types",
                                    "status": "pending"
                                },
                                {
                                    "title": "Products page redirects to add product task when no products exist",
                                    "status": "pending"
                                },
                                {
                                    "title": "Products page shows products table when products exist",
                                    "status": "pending"
                                },
                                {
                                    "title": "Products page redirects to add product task when no products exist and task list is hidden",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "onboarding\\/launch-your-store.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Launch Your Store - logged in": [
                                {
                                    "title": "Entire site coming soon mode frontend",
                                    "status": "pending"
                                },
                                {
                                    "title": "Store only coming soon mode frontend",
                                    "status": "pending"
                                },
                                {
                                    "title": "Site visibility settings",
                                    "status": "pending"
                                },
                                {
                                    "title": "Homescreen badge coming soon store only",
                                    "status": "pending"
                                },
                                {
                                    "title": "Homescreen badge coming soon entire store",
                                    "status": "pending"
                                },
                                {
                                    "title": "Homescreen badge live",
                                    "status": "pending"
                                }
                            ],
                            "Launch Your Store front end - logged out": [],
                            "Launch Your Store front end - logged out > Block Theme (Twenty Twenty Four)": [
                                {
                                    "title": "Entire site coming soon mode (Block Theme (Twenty Twenty Four))",
                                    "status": "pending"
                                },
                                {
                                    "title": "Store only coming soon mode (Block Theme (Twenty Twenty Four))",
                                    "status": "pending"
                                }
                            ],
                            "Launch Your Store front end - logged out > Classic Theme (Storefront)": [
                                {
                                    "title": "Entire site coming soon mode (Classic Theme (Storefront))",
                                    "status": "pending"
                                },
                                {
                                    "title": "Store only coming soon mode (Classic Theme (Storefront))",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "onboarding\\/onboarding-wizard.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Store owner can complete the core profiler": [
                                {
                                    "title": "Can complete the core profiler skipping extension install",
                                    "status": "pending"
                                },
                                {
                                    "title": "Can complete the core profiler installing default extensions",
                                    "status": "pending"
                                }
                            ],
                            "Store owner can skip the core profiler": [
                                {
                                    "title": "Can skip the guided setup",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "onboarding\\/setup-checklist.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Can hide the task list": [
                                {
                                    "title": "Can hide the task list",
                                    "status": "pending"
                                }
                            ],
                            "Payments task list item links to Payments settings page": [
                                {
                                    "title": "Payments task list item links to Payments settings page",
                                    "status": "pending"
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
                        "file": "order\\/create-order.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "WooCommerce Orders > Add new order": [
                                {
                                    "title": "can create a simple guest order",
                                    "status": "pending"
                                },
                                {
                                    "title": "can create an order for an existing customer",
                                    "status": "pending"
                                },
                                {
                                    "title": "can create new order",
                                    "status": "pending"
                                },
                                {
                                    "title": "can create new complex order with multiple product types & tax classes",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "order\\/customer-payment-page.spec.js",
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
                        "file": "order\\/order-bulk-edit.spec.js",
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
                        "file": "order\\/order-coupon.spec.js",
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
                        "file": "order\\/order-edit.spec.js",
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
                                    "title": "can load billing and shipping details",
                                    "status": "pending"
                                },
                                {
                                    "title": "can copy billing address to shipping address",
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
                        "file": "order\\/order-grace-period.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "guest shopper can verify their email address after the grace period": [
                                {
                                    "title": "guest shopper can verify their email address after the grace period",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "order\\/order-refund.spec.js",
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
                        "file": "order\\/order-status-filter.spec.js",
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
                        "file": "product\\/block-editor\\/create-grouped-product-block-editor.spec.js",
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
                        "file": "product\\/block-editor\\/create-simple-product-block-editor.spec.js",
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
                        "file": "product\\/block-editor\\/create-variable-product-block-editor.spec.js",
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
                        "file": "product\\/block-editor\\/disable-block-product-editor.spec.js",
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
                        "file": "product\\/block-editor\\/linked-product-tab-product-block-editor.spec.js",
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
                        "file": "product\\/block-editor\\/organization-tab-product-block-editor.spec.js",
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
                        "file": "product\\/block-editor\\/product-attributes-block-editor.spec.js",
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
                        "file": "product\\/block-editor\\/product-edit-block-editor.spec.js",
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
                        "file": "product\\/block-editor\\/product-images-block-editor.spec.js",
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
                        "file": "product\\/block-editor\\/product-inventory-block-editor.spec.js",
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
                        "file": "product\\/create-product-attributes.spec.js",
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
                        "file": "product\\/create-variable-product.spec.js",
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
                        "file": "product\\/create-variations.spec.js",
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
                        "file": "product\\/product-create-simple.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "can create a simple virtual product": [
                                {
                                    "title": "can create a simple virtual product",
                                    "status": "pending"
                                }
                            ],
                            "can create a simple non virtual product": [
                                {
                                    "title": "can create a simple non virtual product",
                                    "status": "pending"
                                }
                            ],
                            "can create a simple downloadable product": [
                                {
                                    "title": "can create a simple downloadable product",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "product\\/product-delete.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "can delete a product from edit view": [
                                {
                                    "title": "can delete a product from edit view",
                                    "status": "pending"
                                }
                            ],
                            "can quick delete a product from product list": [
                                {
                                    "title": "can quick delete a product from product list",
                                    "status": "pending"
                                }
                            ],
                            "can permanently delete a product from trash list": [
                                {
                                    "title": "can permanently delete a product from trash list",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "product\\/product-edit.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "can edit a product and save the changes": [
                                {
                                    "title": "can edit a product and save the changes",
                                    "status": "pending"
                                }
                            ],
                            "can bulk edit products": [
                                {
                                    "title": "can bulk edit products",
                                    "status": "pending"
                                }
                            ],
                            "can restore regular price when bulk editing products": [
                                {
                                    "title": "can restore regular price when bulk editing products",
                                    "status": "pending"
                                }
                            ],
                            "can decrease the sale price if the product was not previously in sale when bulk editing products": [
                                {
                                    "title": "can decrease the sale price if the product was not previously in sale when bulk editing products",
                                    "status": "pending"
                                }
                            ],
                            "increasing the sale price from 0 does not change the sale price when bulk editing products": [
                                {
                                    "title": "increasing the sale price from 0 does not change the sale price when bulk editing products",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "product\\/product-export.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Product > Export Selected Products": [
                                {
                                    "title": "should allow exporting a single selected simple product",
                                    "status": "pending"
                                },
                                {
                                    "title": "should allow exporting multiple selected products (simple and variable)",
                                    "status": "pending"
                                },
                                {
                                    "title": "should allow clearing selection from the export page",
                                    "status": "pending"
                                },
                                {
                                    "title": "should show the default export screen when no products are selected",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "product\\/product-grouped.spec.js",
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
                        "file": "product\\/product-images.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Products > Product Images": [
                                {
                                    "title": "can set product image",
                                    "status": "pending"
                                },
                                {
                                    "title": "can update the product image",
                                    "status": "pending"
                                },
                                {
                                    "title": "can delete the product image",
                                    "status": "pending"
                                },
                                {
                                    "title": "can create a product gallery",
                                    "status": "pending"
                                },
                                {
                                    "title": "can update a product gallery",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "product\\/product-import-csv.spec.js",
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
                        "file": "product\\/product-linked-products.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Products > Related products": [
                                {
                                    "title": "add up-sells",
                                    "status": "pending"
                                },
                                {
                                    "title": "remove up-sells",
                                    "status": "pending"
                                },
                                {
                                    "title": "add cross-sells",
                                    "status": "pending"
                                },
                                {
                                    "title": "remove cross-sells",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "product\\/product-reviews.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Product Reviews": [],
                            "Product Reviews > Merchant manages reviews": [
                                {
                                    "title": "can view products reviews list",
                                    "status": "pending"
                                },
                                {
                                    "title": "can filter the reviews by product",
                                    "status": "pending"
                                },
                                {
                                    "title": "can quick edit a product review",
                                    "status": "pending"
                                },
                                {
                                    "title": "can edit a product review",
                                    "status": "pending"
                                },
                                {
                                    "title": "can approve a product review",
                                    "status": "pending"
                                },
                                {
                                    "title": "can mark a product review as spam",
                                    "status": "pending"
                                },
                                {
                                    "title": "can reply to a product review",
                                    "status": "pending"
                                },
                                {
                                    "title": "can delete a product review",
                                    "status": "pending"
                                }
                            ],
                            "Product Reviews > Shopper adds reviews": [
                                {
                                    "title": "shopper can post a review",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "product\\/product-search.spec.js",
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
                        "file": "product\\/product-settings.spec.js",
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
                        "file": "product\\/product-tags-attributes.spec.js",
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
                        "file": "product\\/product-variable.spec.js",
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
                        "file": "product\\/update-variations.spec.js",
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
                        "file": "settings\\/consumer-token.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "admin can manage consumer keys": [
                                {
                                    "title": "admin can manage consumer keys",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "settings\\/settings-general.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "WooCommerce General Settings": [
                                {
                                    "title": "Save Changes button is disabled by default and enabled only after changes.",
                                    "status": "pending"
                                },
                                {
                                    "title": "can update settings",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "settings\\/settings-tax.spec.js",
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
                        "file": "settings\\/settings-woo-com.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "WooCommerce woo.com Settings": [
                                {
                                    "title": "can enable analytics tracking",
                                    "status": "pending"
                                },
                                {
                                    "title": "can enable marketplace suggestions",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "settings\\/webhooks.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Manage webhooks": [
                                {
                                    "title": "Webhook cannot be bulk deleted without nonce",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shipping\\/shipping-classes.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "can add a shipping class with an unique slug": [
                                {
                                    "title": "can add a shipping class with an unique slug",
                                    "status": "pending"
                                }
                            ],
                            "can add a shipping class with an auto-generated slug": [
                                {
                                    "title": "can add a shipping class with an auto-generated slug",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shipping\\/shipping-zones.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "can delete the shipping zone region": [
                                {
                                    "title": "can delete the shipping zone region",
                                    "status": "pending"
                                }
                            ],
                            "can delete the shipping zone method": [
                                {
                                    "title": "can delete the shipping zone method",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shop\\/cart-redirection.spec.js",
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
                        "file": "shop\\/shop-search-browse-sort.spec.js",
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
                        "file": "shop\\/shop-title-after-deletion.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Check the title of the shop page after the page has been deleted": [
                                {
                                    "title": "Check the title of the shop page after the page has been deleted",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "user\\/lost-password.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Can go to lost password page and submit the form": [
                                {
                                    "title": "can visit the lost password page from the login page",
                                    "status": "pending"
                                },
                                {
                                    "title": "can submit the lost password form",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "user\\/users-create.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "can create a new Customer": [
                                {
                                    "title": "can create a new Customer",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "user\\/users-manage.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "can update customer data": [
                                {
                                    "title": "can update customer data",
                                    "status": "pending"
                                }
                            ],
                            "can update shop manager data": [
                                {
                                    "title": "can update shop manager data",
                                    "status": "pending"
                                }
                            ],
                            "can delete a customer": [
                                {
                                    "title": "can delete a customer",
                                    "status": "pending"
                                }
                            ],
                            "can delete a shop manager": [
                                {
                                    "title": "can delete a shop manager",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "wp-core\\/create-page.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Can create a new page": [
                                {
                                    "title": "can create new page",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "wp-core\\/create-post.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Can create a new post": [
                                {
                                    "title": "can create new post",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "wp-core\\/post-comments.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "logged-in customer can comment on a post": [
                                {
                                    "title": "logged-in customer can comment on a post",
                                    "status": "pending"
                                }
                            ]
                        }
                    }
                ],
                "summary": "384 total, 109 passed, 30 failed, 245 skipped."
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
                        "tests": 146,
                        "passed": 109,
                        "failed": 30,
                        "pending": 0,
                        "skipped": 7,
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
                            "filePath": "\\/normalized\\/path\\/install-wc.setup.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "install wc > ..\\/fixtures\\/install-wc.setup.js",
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
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/fixtures\\/install-wc.setup.js",
                                            "line": 22,
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
                            "filePath": "\\/normalized\\/path\\/auth.setup.js",
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
                            "suite": "global authentication > ..\\/fixtures\\/auth.setup.js",
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
                            "filePath": "\\/normalized\\/path\\/site.setup.js",
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
                            "suite": "site setup > ..\\/fixtures\\/site.setup.js",
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
                            "filePath": "\\/normalized\\/path\\/analytics-access.spec.js",
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
                            "suite": "e2e > analytics\\/analytics-access.spec.js > WooCommerce Home",
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
                            "name": "confirms correct summary numbers on overview page",
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
                            "filePath": "\\/normalized\\/path\\/analytics-data.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > analytics\\/analytics-data.spec.js",
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
                            "name": "downloads revenue report as CSV",
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
                            "filePath": "\\/normalized\\/path\\/analytics-data.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > analytics\\/analytics-data.spec.js",
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
                            "name": "use date filter on overview page",
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
                            "filePath": "\\/normalized\\/path\\/analytics-data.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > analytics\\/analytics-data.spec.js",
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
                            "name": "set custom date range on revenue report",
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
                            "filePath": "\\/normalized\\/path\\/analytics-data.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > analytics\\/analytics-data.spec.js",
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
                            "name": "use advanced filters on orders report",
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
                            "filePath": "\\/normalized\\/path\\/analytics-data.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > analytics\\/analytics-data.spec.js",
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
                            "name": "use filter by single product on products report",
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
                            "filePath": "\\/normalized\\/path\\/analytics-data.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > analytics\\/analytics-data.spec.js",
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
                            "name": "analytics settings",
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
                            "filePath": "\\/normalized\\/path\\/analytics-data.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > analytics\\/analytics-data.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/analytics-overview.spec.js",
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
                            "suite": "e2e > analytics\\/analytics-overview.spec.js > Analytics pages",
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
                            "filePath": "\\/normalized\\/path\\/analytics-overview.spec.js",
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
                            "suite": "e2e > analytics\\/analytics-overview.spec.js > Analytics pages",
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
                            "filePath": "\\/normalized\\/path\\/analytics-overview.spec.js",
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
                            "suite": "e2e > analytics\\/analytics-overview.spec.js > Analytics pages",
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
                            "filePath": "\\/normalized\\/path\\/analytics-overview.spec.js",
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
                            "suite": "e2e > analytics\\/analytics-overview.spec.js > Analytics pages > moving sections",
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
                            "filePath": "\\/normalized\\/path\\/analytics-overview.spec.js",
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
                            "suite": "e2e > analytics\\/analytics-overview.spec.js > Analytics pages > moving sections",
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
                            "filePath": "\\/normalized\\/path\\/analytics-overview.spec.js",
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
                            "suite": "e2e > analytics\\/analytics-overview.spec.js > Analytics pages > moving sections",
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
                            "filePath": "\\/normalized\\/path\\/basic.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/basic.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/basic.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/basic.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/basic.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/basic.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/dashboard-access.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/dashboard-access.spec.js > Customer-role users are blocked from accessing the WP Dashboard.",
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
                            "filePath": "\\/normalized\\/path\\/dashboard-access.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/dashboard-access.spec.js > Customer-role users are blocked from accessing the WP Dashboard.",
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
                            "filePath": "\\/normalized\\/path\\/dashboard-access.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/dashboard-access.spec.js > Customer-role users are blocked from accessing the WP Dashboard.",
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
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > basic\\/page-loads.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/create-product-brand.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > brands\\/create-product-brand.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/add-to-cart.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > cart\\/add-to-cart.spec.js > Add to Cart behavior",
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
                            "filePath": "\\/normalized\\/path\\/add-to-cart.spec.js",
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
                            "suite": "e2e > cart\\/add-to-cart.spec.js > Add to Cart behavior",
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
                            "filePath": "\\/normalized\\/path\\/cart.spec.js",
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
                            "suite": "e2e > cart\\/cart.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/cart.spec.js",
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
                            "suite": "e2e > cart\\/cart.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/checkout-link.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout-link.spec.js > Checkout Link Endpoint > Guest user",
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
                            "filePath": "\\/normalized\\/path\\/checkout-link.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout-link.spec.js > Checkout Link Endpoint > Guest user",
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
                            "filePath": "\\/normalized\\/path\\/checkout-link.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout-link.spec.js > Checkout Link Endpoint > Guest user",
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
                            "filePath": "\\/normalized\\/path\\/checkout-link.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout-link.spec.js > Checkout Link Endpoint > Guest user",
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
                            "filePath": "\\/normalized\\/path\\/checkout-link.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout-link.spec.js > Checkout Link Endpoint > Guest user",
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
                            "filePath": "\\/normalized\\/path\\/checkout-link.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout-link.spec.js > Checkout Link Endpoint > Logged-in user",
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
                            "filePath": "\\/normalized\\/path\\/checkout-link.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout-link.spec.js > Checkout Link Endpoint > Logged-in user",
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
                            "filePath": "\\/normalized\\/path\\/checkout-link.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout-link.spec.js > Checkout Link Endpoint > Logged-in user",
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
                            "filePath": "\\/normalized\\/path\\/checkout-link.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout-link.spec.js > Checkout Link Endpoint > Logged-in user",
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
                            "filePath": "\\/normalized\\/path\\/checkout-link.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout-link.spec.js > Checkout Link Endpoint > Logged-in user",
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
                            "filePath": "\\/normalized\\/path\\/checkout.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/checkout.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/checkout.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/checkout.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/checkout.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/checkout.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout.spec.js",
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
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout.spec.js",
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
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/checkout\\/checkout.spec.js",
                                            "line": 381,
                                            "column": 7
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "customer can login at checkout and place the order with a different shipping address classic checkout",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@payments",
                                "@services",
                                "@hpos"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout.spec.js",
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
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/checkout\\/checkout.spec.js",
                                            "line": 381,
                                            "column": 7
                                        }
                                    }
                                ]
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
                            "filePath": "\\/normalized\\/path\\/checkout.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/checkout.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > checkout\\/checkout.spec.js",
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
                            "filePath": "\\/normalized\\/path\\/cart-block-coupons.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > coupons\\/cart-block-coupons.spec.js > Cart Block Applying Coupons",
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
                            "filePath": "\\/normalized\\/path\\/cart-block-coupons.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > coupons\\/cart-block-coupons.spec.js > Cart Block Applying Coupons",
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
                            "filePath": "\\/normalized\\/path\\/cart-block-coupons.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > coupons\\/cart-block-coupons.spec.js > Cart Block Applying Coupons",
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
                            "filePath": "\\/normalized\\/path\\/cart-block-coupons.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > coupons\\/cart-block-coupons.spec.js > Cart Block Applying Coupons",
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
                            "filePath": "\\/normalized\\/path\\/cart-checkout-coupons.spec.js",
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
                            "suite": "e2e > coupons\\/cart-checkout-coupons.spec.js > Cart & Checkout applying coupons",
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
                            "filePath": "\\/normalized\\/path\\/cart-checkout-coupons.spec.js",
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
                            "suite": "e2e > coupons\\/cart-checkout-coupons.spec.js > Cart & Checkout applying coupons",
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
                            "filePath": "\\/normalized\\/path\\/cart-checkout-coupons.spec.js",
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
                            "suite": "e2e > coupons\\/cart-checkout-coupons.spec.js > Cart & Checkout applying coupons",
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
                            "filePath": "\\/normalized\\/path\\/cart-checkout-coupons.spec.js",
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
                            "suite": "e2e > coupons\\/cart-checkout-coupons.spec.js > Cart & Checkout applying coupons",
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
                            "filePath": "\\/normalized\\/path\\/cart-checkout-coupons.spec.js",
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
                            "suite": "e2e > coupons\\/cart-checkout-coupons.spec.js > Cart & Checkout applying coupons",
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
                            "filePath": "\\/normalized\\/path\\/cart-checkout-coupons.spec.js",
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
                            "suite": "e2e > coupons\\/cart-checkout-coupons.spec.js > Cart & Checkout applying coupons",
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
                            "filePath": "\\/normalized\\/path\\/cart-checkout-restricted-coupons.spec.js",
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
                            "suite": "e2e > coupons\\/cart-checkout-restricted-coupons.spec.js > Cart & Checkout Restricted Coupons",
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
                            "filePath": "\\/normalized\\/path\\/cart-checkout-restricted-coupons.spec.js",
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
                            "suite": "e2e > coupons\\/cart-checkout-restricted-coupons.spec.js > Cart & Checkout Restricted Coupons",
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
                            "filePath": "\\/normalized\\/path\\/cart-checkout-restricted-coupons.spec.js",
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
                            "suite": "e2e > coupons\\/cart-checkout-restricted-coupons.spec.js > Cart & Checkout Restricted Coupons",
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
                            "filePath": "\\/normalized\\/path\\/cart-checkout-restricted-coupons.spec.js",
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
                            "suite": "e2e > coupons\\/cart-checkout-restricted-coupons.spec.js > Cart & Checkout Restricted Coupons",
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
                            "filePath": "\\/normalized\\/path\\/cart-checkout-restricted-coupons.spec.js",
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
                            "suite": "e2e > coupons\\/cart-checkout-restricted-coupons.spec.js > Cart & Checkout Restricted Coupons",
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
                            "filePath": "\\/normalized\\/path\\/cart-checkout-restricted-coupons.spec.js",
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
                            "suite": "e2e > coupons\\/cart-checkout-restricted-coupons.spec.js > Cart & Checkout Restricted Coupons",
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
                            "filePath": "\\/normalized\\/path\\/cart-checkout-restricted-coupons.spec.js",
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
                            "suite": "e2e > coupons\\/cart-checkout-restricted-coupons.spec.js > Cart & Checkout Restricted Coupons",
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
                            "filePath": "\\/normalized\\/path\\/cart-checkout-restricted-coupons.spec.js",
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
                            "suite": "e2e > coupons\\/cart-checkout-restricted-coupons.spec.js > Cart & Checkout Restricted Coupons",
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
                            "filePath": "\\/normalized\\/path\\/cart-checkout-restricted-coupons.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > coupons\\/cart-checkout-restricted-coupons.spec.js > Cart & Checkout Restricted Coupons",
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
                            "filePath": "\\/normalized\\/path\\/cart-checkout-restricted-coupons.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > coupons\\/cart-checkout-restricted-coupons.spec.js > Cart & Checkout Restricted Coupons",
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
                            "filePath": "\\/normalized\\/path\\/cart-checkout-restricted-coupons.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > coupons\\/cart-checkout-restricted-coupons.spec.js > Cart & Checkout Restricted Coupons",
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
                            "filePath": "\\/normalized\\/path\\/create-coupon.spec.js",
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
                            "suite": "e2e > coupons\\/create-coupon.spec.js > Coupon management",
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
                            "filePath": "\\/normalized\\/path\\/create-coupon.spec.js",
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
                            "suite": "e2e > coupons\\/create-coupon.spec.js > Coupon management",
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
                            "filePath": "\\/normalized\\/path\\/create-coupon.spec.js",
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
                            "suite": "e2e > coupons\\/create-coupon.spec.js > Coupon management",
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
                            "filePath": "\\/normalized\\/path\\/create-coupon.spec.js",
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
                            "suite": "e2e > coupons\\/create-coupon.spec.js > Coupon management",
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
                            "filePath": "\\/normalized\\/path\\/create-coupon.spec.js",
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
                            "suite": "e2e > coupons\\/create-coupon.spec.js > Coupon management",
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
                            "filePath": "\\/normalized\\/path\\/create-restricted-coupons.spec.js",
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
                            "suite": "e2e > coupons\\/create-restricted-coupons.spec.js > Restricted coupon management",
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
                            "filePath": "\\/normalized\\/path\\/create-restricted-coupons.spec.js",
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
                            "suite": "e2e > coupons\\/create-restricted-coupons.spec.js > Restricted coupon management",
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
                            "filePath": "\\/normalized\\/path\\/create-restricted-coupons.spec.js",
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
                            "suite": "e2e > coupons\\/create-restricted-coupons.spec.js > Restricted coupon management",
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
                            "filePath": "\\/normalized\\/path\\/create-restricted-coupons.spec.js",
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
                            "suite": "e2e > coupons\\/create-restricted-coupons.spec.js > Restricted coupon management",
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
                            "filePath": "\\/normalized\\/path\\/create-restricted-coupons.spec.js",
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
                            "suite": "e2e > coupons\\/create-restricted-coupons.spec.js > Restricted coupon management",
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
                            "filePath": "\\/normalized\\/path\\/create-restricted-coupons.spec.js",
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
                            "suite": "e2e > coupons\\/create-restricted-coupons.spec.js > Restricted coupon management",
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
                            "filePath": "\\/normalized\\/path\\/create-restricted-coupons.spec.js",
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
                            "suite": "e2e > coupons\\/create-restricted-coupons.spec.js > Restricted coupon management",
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
                            "filePath": "\\/normalized\\/path\\/create-restricted-coupons.spec.js",
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
                            "suite": "e2e > coupons\\/create-restricted-coupons.spec.js > Restricted coupon management",
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
                            "filePath": "\\/normalized\\/path\\/create-restricted-coupons.spec.js",
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
                            "suite": "e2e > coupons\\/create-restricted-coupons.spec.js > Restricted coupon management",
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
                            "filePath": "\\/normalized\\/path\\/create-restricted-coupons.spec.js",
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
                            "suite": "e2e > coupons\\/create-restricted-coupons.spec.js > Restricted coupon management",
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
                            "filePath": "\\/normalized\\/path\\/create-restricted-coupons.spec.js",
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
                            "suite": "e2e > coupons\\/create-restricted-coupons.spec.js > Restricted coupon management",
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
                            "filePath": "\\/normalized\\/path\\/create-restricted-coupons.spec.js",
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
                            "suite": "e2e > coupons\\/create-restricted-coupons.spec.js > Restricted coupon management",
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
                            "filePath": "\\/normalized\\/path\\/customer-list.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customer\\/customer-list.spec.js > Merchant > Customer List",
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
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customer\\/customer-list.spec.js",
                                            "line": 98,
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
                            "filePath": "\\/normalized\\/path\\/customer-list.spec.js",
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
                            "suite": "e2e > customer\\/customer-list.spec.js > Merchant > Customer List",
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
                            "filePath": "\\/normalized\\/path\\/customer-list.spec.js",
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
                            "suite": "e2e > customer\\/customer-list.spec.js > Merchant > Customer List",
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
                            "name": "Color pickers should be displayed",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n\\n    at AssemblerPage.waitForLoadingScreenFinish (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/assembler.page.js:21:5)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/color-picker.spec.js:83:30",
                            "snippet": "   at customize-store\\/assembler\\/assembler.page.js:21\\n\\n\\u001b[0m \\u001b[90m 19 |\\u001b[39m \\t\\t\\u001b[36mawait\\u001b[39m frame\\n \\u001b[90m 20 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mgetByRole( \\u001b[32m\'button\'\\u001b[39m\\u001b[33m,\\u001b[39m { name\\u001b[33m:\\u001b[39m \\u001b[32m\'Finish customizing\'\\u001b[39m } )\\n\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 21 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mwaitFor( { timeout\\u001b[33m:\\u001b[39m \\u001b[35m25000\\u001b[39m } )\\u001b[33m;\\u001b[39m\\n \\u001b[90m    |\\u001b[39m \\t\\t\\t \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\n \\u001b[90m 22 |\\u001b[39m \\t}\\n \\u001b[90m 23 |\\u001b[39m\\n \\u001b[90m 24 |\\u001b[39m \\t\\u001b[90m\\/**\\u001b[39m\\u001b[0m",
                            "rawStatus": "failed",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/color-picker.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/color-picker.spec.js > Assembler -> Color Pickers",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--2b0ed-pickers-should-be-displayed-e2e-retry1\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--2b0ed-pickers-should-be-displayed-e2e-retry1\\/video.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--2b0ed-pickers-should-be-displayed-e2e-retry1\\/error-context.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--2b0ed-pickers-should-be-displayed-e2e-retry1\\/trace.zip"
                                }
                            ],
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
                            "name": "Color palette Slate should be applied",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n\\n    at AssemblerPage.waitForLoadingScreenFinish (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/assembler.page.js:21:5)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/color-picker.spec.js:83:30",
                            "snippet": "   at customize-store\\/assembler\\/assembler.page.js:21\\n\\n\\u001b[0m \\u001b[90m 19 |\\u001b[39m \\t\\t\\u001b[36mawait\\u001b[39m frame\\n \\u001b[90m 20 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mgetByRole( \\u001b[32m\'button\'\\u001b[39m\\u001b[33m,\\u001b[39m { name\\u001b[33m:\\u001b[39m \\u001b[32m\'Finish customizing\'\\u001b[39m } )\\n\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 21 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mwaitFor( { timeout\\u001b[33m:\\u001b[39m \\u001b[35m25000\\u001b[39m } )\\u001b[33m;\\u001b[39m\\n \\u001b[90m    |\\u001b[39m \\t\\t\\t \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\n \\u001b[90m 22 |\\u001b[39m \\t}\\n \\u001b[90m 23 |\\u001b[39m\\n \\u001b[90m 24 |\\u001b[39m \\t\\u001b[90m\\/**\\u001b[39m\\u001b[0m",
                            "rawStatus": "failed",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/color-picker.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/color-picker.spec.js > Assembler -> Color Pickers",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--4639a-tte-Slate-should-be-applied-e2e-retry1\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--4639a-tte-Slate-should-be-applied-e2e-retry1\\/video.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--4639a-tte-Slate-should-be-applied-e2e-retry1\\/error-context.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--4639a-tte-Slate-should-be-applied-e2e-retry1\\/trace.zip"
                                }
                            ],
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
                            "name": "Color picker should be focused when a color is picked",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n\\n    at AssemblerPage.waitForLoadingScreenFinish (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/assembler.page.js:21:5)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/color-picker.spec.js:83:30",
                            "snippet": "   at customize-store\\/assembler\\/assembler.page.js:21\\n\\n\\u001b[0m \\u001b[90m 19 |\\u001b[39m \\t\\t\\u001b[36mawait\\u001b[39m frame\\n \\u001b[90m 20 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mgetByRole( \\u001b[32m\'button\'\\u001b[39m\\u001b[33m,\\u001b[39m { name\\u001b[33m:\\u001b[39m \\u001b[32m\'Finish customizing\'\\u001b[39m } )\\n\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 21 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mwaitFor( { timeout\\u001b[33m:\\u001b[39m \\u001b[35m25000\\u001b[39m } )\\u001b[33m;\\u001b[39m\\n \\u001b[90m    |\\u001b[39m \\t\\t\\t \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\n \\u001b[90m 22 |\\u001b[39m \\t}\\n \\u001b[90m 23 |\\u001b[39m\\n \\u001b[90m 24 |\\u001b[39m \\t\\u001b[90m\\/**\\u001b[39m\\u001b[0m",
                            "rawStatus": "failed",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/color-picker.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/color-picker.spec.js > Assembler -> Color Pickers",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--939b9-used-when-a-color-is-picked-e2e-retry1\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--939b9-used-when-a-color-is-picked-e2e-retry1\\/video.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--939b9-used-when-a-color-is-picked-e2e-retry1\\/error-context.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--939b9-used-when-a-color-is-picked-e2e-retry1\\/trace.zip"
                                }
                            ],
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
                            "name": "Font pickers should be displayed",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n\\n    at AssemblerPage.waitForLoadingScreenFinish (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/assembler.page.js:21:5)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/font-picker.spec.js:115:21",
                            "snippet": "   at customize-store\\/assembler\\/assembler.page.js:21\\n\\n\\u001b[0m \\u001b[90m 19 |\\u001b[39m \\t\\t\\u001b[36mawait\\u001b[39m frame\\n \\u001b[90m 20 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mgetByRole( \\u001b[32m\'button\'\\u001b[39m\\u001b[33m,\\u001b[39m { name\\u001b[33m:\\u001b[39m \\u001b[32m\'Finish customizing\'\\u001b[39m } )\\n\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 21 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mwaitFor( { timeout\\u001b[33m:\\u001b[39m \\u001b[35m25000\\u001b[39m } )\\u001b[33m;\\u001b[39m\\n \\u001b[90m    |\\u001b[39m \\t\\t\\t \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\n \\u001b[90m 22 |\\u001b[39m \\t}\\n \\u001b[90m 23 |\\u001b[39m\\n \\u001b[90m 24 |\\u001b[39m \\t\\u001b[90m\\/**\\u001b[39m\\u001b[0m",
                            "rawStatus": "failed",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/font-picker.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/font-picker.spec.js > Assembler -> Font Picker",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--1fe19-pickers-should-be-displayed-e2e-retry1\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--1fe19-pickers-should-be-displayed-e2e-retry1\\/video.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--1fe19-pickers-should-be-displayed-e2e-retry1\\/error-context.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--1fe19-pickers-should-be-displayed-e2e-retry1\\/trace.zip"
                                }
                            ],
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
                            "name": "Picking a font should trigger an update of fonts on the site preview",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n\\n    at AssemblerPage.waitForLoadingScreenFinish (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/assembler.page.js:21:5)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/font-picker.spec.js:115:21",
                            "snippet": "   at customize-store\\/assembler\\/assembler.page.js:21\\n\\n\\u001b[0m \\u001b[90m 19 |\\u001b[39m \\t\\t\\u001b[36mawait\\u001b[39m frame\\n \\u001b[90m 20 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mgetByRole( \\u001b[32m\'button\'\\u001b[39m\\u001b[33m,\\u001b[39m { name\\u001b[33m:\\u001b[39m \\u001b[32m\'Finish customizing\'\\u001b[39m } )\\n\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 21 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mwaitFor( { timeout\\u001b[33m:\\u001b[39m \\u001b[35m25000\\u001b[39m } )\\u001b[33m;\\u001b[39m\\n \\u001b[90m    |\\u001b[39m \\t\\t\\t \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\n \\u001b[90m 22 |\\u001b[39m \\t}\\n \\u001b[90m 23 |\\u001b[39m\\n \\u001b[90m 24 |\\u001b[39m \\t\\u001b[90m\\/**\\u001b[39m\\u001b[0m",
                            "rawStatus": "failed",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/font-picker.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/font-picker.spec.js > Assembler -> Font Picker",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--d5b60-f-fonts-on-the-site-preview-e2e-retry1\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--d5b60-f-fonts-on-the-site-preview-e2e-retry1\\/video.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--d5b60-f-fonts-on-the-site-preview-e2e-retry1\\/error-context.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--d5b60-f-fonts-on-the-site-preview-e2e-retry1\\/trace.zip"
                                }
                            ],
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
                            "name": "Font pickers should be focused when a font is picked",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n\\n    at AssemblerPage.waitForLoadingScreenFinish (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/assembler.page.js:21:5)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/font-picker.spec.js:115:21",
                            "snippet": "   at customize-store\\/assembler\\/assembler.page.js:21\\n\\n\\u001b[0m \\u001b[90m 19 |\\u001b[39m \\t\\t\\u001b[36mawait\\u001b[39m frame\\n \\u001b[90m 20 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mgetByRole( \\u001b[32m\'button\'\\u001b[39m\\u001b[33m,\\u001b[39m { name\\u001b[33m:\\u001b[39m \\u001b[32m\'Finish customizing\'\\u001b[39m } )\\n\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 21 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mwaitFor( { timeout\\u001b[33m:\\u001b[39m \\u001b[35m25000\\u001b[39m } )\\u001b[33m;\\u001b[39m\\n \\u001b[90m    |\\u001b[39m \\t\\t\\t \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\n \\u001b[90m 22 |\\u001b[39m \\t}\\n \\u001b[90m 23 |\\u001b[39m\\n \\u001b[90m 24 |\\u001b[39m \\t\\u001b[90m\\/**\\u001b[39m\\u001b[0m",
                            "rawStatus": "failed",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/font-picker.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/font-picker.spec.js > Assembler -> Font Picker",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--d2203-cused-when-a-font-is-picked-e2e-retry1\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--d2203-cused-when-a-font-is-picked-e2e-retry1\\/video.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--d2203-cused-when-a-font-is-picked-e2e-retry1\\/error-context.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--d2203-cused-when-a-font-is-picked-e2e-retry1\\/trace.zip"
                                }
                            ],
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
                            "name": "Selected font palette should be applied on the frontend",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n\\n    at AssemblerPage.waitForLoadingScreenFinish (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/assembler.page.js:21:5)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/font-picker.spec.js:115:21",
                            "snippet": "   at customize-store\\/assembler\\/assembler.page.js:21\\n\\n\\u001b[0m \\u001b[90m 19 |\\u001b[39m \\t\\t\\u001b[36mawait\\u001b[39m frame\\n \\u001b[90m 20 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mgetByRole( \\u001b[32m\'button\'\\u001b[39m\\u001b[33m,\\u001b[39m { name\\u001b[33m:\\u001b[39m \\u001b[32m\'Finish customizing\'\\u001b[39m } )\\n\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 21 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mwaitFor( { timeout\\u001b[33m:\\u001b[39m \\u001b[35m25000\\u001b[39m } )\\u001b[33m;\\u001b[39m\\n \\u001b[90m    |\\u001b[39m \\t\\t\\t \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\n \\u001b[90m 22 |\\u001b[39m \\t}\\n \\u001b[90m 23 |\\u001b[39m\\n \\u001b[90m 24 |\\u001b[39m \\t\\u001b[90m\\/**\\u001b[39m\\u001b[0m",
                            "rawStatus": "failed",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/font-picker.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/font-picker.spec.js > Assembler -> Font Picker",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--a2649--be-applied-on-the-frontend-e2e-retry1\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--a2649--be-applied-on-the-frontend-e2e-retry1\\/video.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--a2649--be-applied-on-the-frontend-e2e-retry1\\/error-context.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--a2649--be-applied-on-the-frontend-e2e-retry1\\/trace.zip"
                                }
                            ],
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
                            "name": "Clicking opt-in new fonts should be available",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n\\n    at AssemblerPage.waitForLoadingScreenFinish (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/assembler.page.js:21:5)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/font-picker.spec.js:115:21",
                            "snippet": "   at customize-store\\/assembler\\/assembler.page.js:21\\n\\n\\u001b[0m \\u001b[90m 19 |\\u001b[39m \\t\\t\\u001b[36mawait\\u001b[39m frame\\n \\u001b[90m 20 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mgetByRole( \\u001b[32m\'button\'\\u001b[39m\\u001b[33m,\\u001b[39m { name\\u001b[33m:\\u001b[39m \\u001b[32m\'Finish customizing\'\\u001b[39m } )\\n\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 21 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mwaitFor( { timeout\\u001b[33m:\\u001b[39m \\u001b[35m25000\\u001b[39m } )\\u001b[33m;\\u001b[39m\\n \\u001b[90m    |\\u001b[39m \\t\\t\\t \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\n \\u001b[90m 22 |\\u001b[39m \\t}\\n \\u001b[90m 23 |\\u001b[39m\\n \\u001b[90m 24 |\\u001b[39m \\t\\u001b[90m\\/**\\u001b[39m\\u001b[0m",
                            "rawStatus": "failed",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/font-picker.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/font-picker.spec.js > Assembler -> Font Picker",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--96b64-w-fonts-should-be-available-e2e-retry1\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--96b64-w-fonts-should-be-available-e2e-retry1\\/video.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--96b64-w-fonts-should-be-available-e2e-retry1\\/error-context.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--96b64-w-fonts-should-be-available-e2e-retry1\\/trace.zip"
                                }
                            ],
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
                            "name": "Available footers should be displayed",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n\\n    at AssemblerPage.waitForLoadingScreenFinish (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/assembler.page.js:21:5)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/footer.spec.js:67:24",
                            "snippet": "   at customize-store\\/assembler\\/assembler.page.js:21\\n\\n\\u001b[0m \\u001b[90m 19 |\\u001b[39m \\t\\t\\u001b[36mawait\\u001b[39m frame\\n \\u001b[90m 20 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mgetByRole( \\u001b[32m\'button\'\\u001b[39m\\u001b[33m,\\u001b[39m { name\\u001b[33m:\\u001b[39m \\u001b[32m\'Finish customizing\'\\u001b[39m } )\\n\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 21 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mwaitFor( { timeout\\u001b[33m:\\u001b[39m \\u001b[35m25000\\u001b[39m } )\\u001b[33m;\\u001b[39m\\n \\u001b[90m    |\\u001b[39m \\t\\t\\t \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\n \\u001b[90m 22 |\\u001b[39m \\t}\\n \\u001b[90m 23 |\\u001b[39m\\n \\u001b[90m 24 |\\u001b[39m \\t\\u001b[90m\\/**\\u001b[39m\\u001b[0m",
                            "rawStatus": "failed",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/footer.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/footer.spec.js > Assembler -> Footers",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--7dd19-footers-should-be-displayed-e2e-retry1\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--7dd19-footers-should-be-displayed-e2e-retry1\\/video.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--7dd19-footers-should-be-displayed-e2e-retry1\\/error-context.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--7dd19-footers-should-be-displayed-e2e-retry1\\/trace.zip"
                                }
                            ],
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
                            "name": "The selected footer should be focused when is clicked",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n\\n    at AssemblerPage.waitForLoadingScreenFinish (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/assembler.page.js:21:5)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/footer.spec.js:67:24",
                            "snippet": "   at customize-store\\/assembler\\/assembler.page.js:21\\n\\n\\u001b[0m \\u001b[90m 19 |\\u001b[39m \\t\\t\\u001b[36mawait\\u001b[39m frame\\n \\u001b[90m 20 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mgetByRole( \\u001b[32m\'button\'\\u001b[39m\\u001b[33m,\\u001b[39m { name\\u001b[33m:\\u001b[39m \\u001b[32m\'Finish customizing\'\\u001b[39m } )\\n\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 21 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mwaitFor( { timeout\\u001b[33m:\\u001b[39m \\u001b[35m25000\\u001b[39m } )\\u001b[33m;\\u001b[39m\\n \\u001b[90m    |\\u001b[39m \\t\\t\\t \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\n \\u001b[90m 22 |\\u001b[39m \\t}\\n \\u001b[90m 23 |\\u001b[39m\\n \\u001b[90m 24 |\\u001b[39m \\t\\u001b[90m\\/**\\u001b[39m\\u001b[0m",
                            "rawStatus": "failed",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/footer.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/footer.spec.js > Assembler -> Footers",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--fa426--be-focused-when-is-clicked-e2e-retry1\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--fa426--be-focused-when-is-clicked-e2e-retry1\\/video.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--fa426--be-focused-when-is-clicked-e2e-retry1\\/error-context.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--fa426--be-focused-when-is-clicked-e2e-retry1\\/trace.zip"
                                }
                            ],
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
                            "name": "The selected footer should be applied on the frontend",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n\\n    at AssemblerPage.waitForLoadingScreenFinish (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/assembler.page.js:21:5)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/footer.spec.js:67:24",
                            "snippet": "   at customize-store\\/assembler\\/assembler.page.js:21\\n\\n\\u001b[0m \\u001b[90m 19 |\\u001b[39m \\t\\t\\u001b[36mawait\\u001b[39m frame\\n \\u001b[90m 20 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mgetByRole( \\u001b[32m\'button\'\\u001b[39m\\u001b[33m,\\u001b[39m { name\\u001b[33m:\\u001b[39m \\u001b[32m\'Finish customizing\'\\u001b[39m } )\\n\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 21 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mwaitFor( { timeout\\u001b[33m:\\u001b[39m \\u001b[35m25000\\u001b[39m } )\\u001b[33m;\\u001b[39m\\n \\u001b[90m    |\\u001b[39m \\t\\t\\t \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\n \\u001b[90m 22 |\\u001b[39m \\t}\\n \\u001b[90m 23 |\\u001b[39m\\n \\u001b[90m 24 |\\u001b[39m \\t\\u001b[90m\\/**\\u001b[39m\\u001b[0m",
                            "rawStatus": "failed",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/footer.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/footer.spec.js > Assembler -> Footers",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--e732c--be-applied-on-the-frontend-e2e-retry1\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--e732c--be-applied-on-the-frontend-e2e-retry1\\/video.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--e732c--be-applied-on-the-frontend-e2e-retry1\\/error-context.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--e732c--be-applied-on-the-frontend-e2e-retry1\\/trace.zip"
                                }
                            ],
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
                            "name": "Picking a footer should trigger an update on the site preview",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n\\n    at AssemblerPage.waitForLoadingScreenFinish (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/assembler.page.js:21:5)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/footer.spec.js:67:24",
                            "snippet": "   at customize-store\\/assembler\\/assembler.page.js:21\\n\\n\\u001b[0m \\u001b[90m 19 |\\u001b[39m \\t\\t\\u001b[36mawait\\u001b[39m frame\\n \\u001b[90m 20 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mgetByRole( \\u001b[32m\'button\'\\u001b[39m\\u001b[33m,\\u001b[39m { name\\u001b[33m:\\u001b[39m \\u001b[32m\'Finish customizing\'\\u001b[39m } )\\n\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 21 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mwaitFor( { timeout\\u001b[33m:\\u001b[39m \\u001b[35m25000\\u001b[39m } )\\u001b[33m;\\u001b[39m\\n \\u001b[90m    |\\u001b[39m \\t\\t\\t \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\n \\u001b[90m 22 |\\u001b[39m \\t}\\n \\u001b[90m 23 |\\u001b[39m\\n \\u001b[90m 24 |\\u001b[39m \\t\\u001b[90m\\/**\\u001b[39m\\u001b[0m",
                            "rawStatus": "failed",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/footer.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/footer.spec.js > Assembler -> Footers",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--eda16--update-on-the-site-preview-e2e-retry1\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--eda16--update-on-the-site-preview-e2e-retry1\\/video.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--eda16--update-on-the-site-preview-e2e-retry1\\/error-context.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--eda16--update-on-the-site-preview-e2e-retry1\\/trace.zip"
                                }
                            ],
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
                            "name": "Clicking on \\"Design your homepage\\" should open the Intro sidebar by default",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n\\n    at AssemblerPage.waitForLoadingScreenFinish (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/assembler.page.js:21:5)\\n    at prepareAssembler (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/full-composability.spec.js:18:19)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/full-composability.spec.js:123:4",
                            "snippet": "   at customize-store\\/assembler\\/assembler.page.js:21\\n\\n\\u001b[0m \\u001b[90m 19 |\\u001b[39m \\t\\t\\u001b[36mawait\\u001b[39m frame\\n \\u001b[90m 20 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mgetByRole( \\u001b[32m\'button\'\\u001b[39m\\u001b[33m,\\u001b[39m { name\\u001b[33m:\\u001b[39m \\u001b[32m\'Finish customizing\'\\u001b[39m } )\\n\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 21 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mwaitFor( { timeout\\u001b[33m:\\u001b[39m \\u001b[35m25000\\u001b[39m } )\\u001b[33m;\\u001b[39m\\n \\u001b[90m    |\\u001b[39m \\t\\t\\t \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\n \\u001b[90m 22 |\\u001b[39m \\t}\\n \\u001b[90m 23 |\\u001b[39m\\n \\u001b[90m 24 |\\u001b[39m \\t\\u001b[90m\\/**\\u001b[39m\\u001b[0m",
                            "rawStatus": "failed",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/full-composability.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/full-composability.spec.js > Assembler -> Full composability",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--4f6ab-he-Intro-sidebar-by-default-e2e-retry1\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--4f6ab-he-Intro-sidebar-by-default-e2e-retry1\\/video.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--4f6ab-he-Intro-sidebar-by-default-e2e-retry1\\/error-context.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--4f6ab-he-Intro-sidebar-by-default-e2e-retry1\\/trace.zip"
                                }
                            ],
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
                            "name": "Clicking on a category should open the sidebar for it",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n\\n    at AssemblerPage.waitForLoadingScreenFinish (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/assembler.page.js:21:5)\\n    at prepareAssembler (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/full-composability.spec.js:18:19)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/full-composability.spec.js:139:4",
                            "snippet": "   at customize-store\\/assembler\\/assembler.page.js:21\\n\\n\\u001b[0m \\u001b[90m 19 |\\u001b[39m \\t\\t\\u001b[36mawait\\u001b[39m frame\\n \\u001b[90m 20 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mgetByRole( \\u001b[32m\'button\'\\u001b[39m\\u001b[33m,\\u001b[39m { name\\u001b[33m:\\u001b[39m \\u001b[32m\'Finish customizing\'\\u001b[39m } )\\n\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 21 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mwaitFor( { timeout\\u001b[33m:\\u001b[39m \\u001b[35m25000\\u001b[39m } )\\u001b[33m;\\u001b[39m\\n \\u001b[90m    |\\u001b[39m \\t\\t\\t \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\n \\u001b[90m 22 |\\u001b[39m \\t}\\n \\u001b[90m 23 |\\u001b[39m\\n \\u001b[90m 24 |\\u001b[39m \\t\\u001b[90m\\/**\\u001b[39m\\u001b[0m",
                            "rawStatus": "failed",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/full-composability.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/full-composability.spec.js > Assembler -> Full composability",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--6e981-uld-open-the-sidebar-for-it-e2e-retry1\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--6e981-uld-open-the-sidebar-for-it-e2e-retry1\\/video.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--6e981-uld-open-the-sidebar-for-it-e2e-retry1\\/error-context.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--6e981-uld-open-the-sidebar-for-it-e2e-retry1\\/trace.zip"
                                }
                            ],
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
                            "name": "Clicking on a pattern should insert it in the preview",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n\\n    at AssemblerPage.waitForLoadingScreenFinish (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/assembler.page.js:21:5)\\n    at prepareAssembler (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/full-composability.spec.js:18:19)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/full-composability.spec.js:179:4",
                            "snippet": "   at customize-store\\/assembler\\/assembler.page.js:21\\n\\n\\u001b[0m \\u001b[90m 19 |\\u001b[39m \\t\\t\\u001b[36mawait\\u001b[39m frame\\n \\u001b[90m 20 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mgetByRole( \\u001b[32m\'button\'\\u001b[39m\\u001b[33m,\\u001b[39m { name\\u001b[33m:\\u001b[39m \\u001b[32m\'Finish customizing\'\\u001b[39m } )\\n\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 21 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mwaitFor( { timeout\\u001b[33m:\\u001b[39m \\u001b[35m25000\\u001b[39m } )\\u001b[33m;\\u001b[39m\\n \\u001b[90m    |\\u001b[39m \\t\\t\\t \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\n \\u001b[90m 22 |\\u001b[39m \\t}\\n \\u001b[90m 23 |\\u001b[39m\\n \\u001b[90m 24 |\\u001b[39m \\t\\u001b[90m\\/**\\u001b[39m\\u001b[0m",
                            "rawStatus": "failed",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/full-composability.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/full-composability.spec.js > Assembler -> Full composability",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--b6518-ld-insert-it-in-the-preview-e2e-retry1\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--b6518-ld-insert-it-in-the-preview-e2e-retry1\\/video.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--b6518-ld-insert-it-in-the-preview-e2e-retry1\\/error-context.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--b6518-ld-insert-it-in-the-preview-e2e-retry1\\/trace.zip"
                                }
                            ],
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
                            "name": "Clicking on a pattern should always scroll the page to the inserted pattern",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n\\n    at AssemblerPage.waitForLoadingScreenFinish (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/assembler.page.js:21:5)\\n    at prepareAssembler (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/full-composability.spec.js:18:19)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/full-composability.spec.js:212:4",
                            "snippet": "   at customize-store\\/assembler\\/assembler.page.js:21\\n\\n\\u001b[0m \\u001b[90m 19 |\\u001b[39m \\t\\t\\u001b[36mawait\\u001b[39m frame\\n \\u001b[90m 20 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mgetByRole( \\u001b[32m\'button\'\\u001b[39m\\u001b[33m,\\u001b[39m { name\\u001b[33m:\\u001b[39m \\u001b[32m\'Finish customizing\'\\u001b[39m } )\\n\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 21 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mwaitFor( { timeout\\u001b[33m:\\u001b[39m \\u001b[35m25000\\u001b[39m } )\\u001b[33m;\\u001b[39m\\n \\u001b[90m    |\\u001b[39m \\t\\t\\t \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\n \\u001b[90m 22 |\\u001b[39m \\t}\\n \\u001b[90m 23 |\\u001b[39m\\n \\u001b[90m 24 |\\u001b[39m \\t\\u001b[90m\\/**\\u001b[39m\\u001b[0m",
                            "rawStatus": "failed",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/full-composability.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/full-composability.spec.js > Assembler -> Full composability",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--72d5e-age-to-the-inserted-pattern-e2e-retry1\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--72d5e-age-to-the-inserted-pattern-e2e-retry1\\/video.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--72d5e-age-to-the-inserted-pattern-e2e-retry1\\/error-context.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--72d5e-age-to-the-inserted-pattern-e2e-retry1\\/trace.zip"
                                }
                            ],
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
                            "name": "Clicking the \\"Move up\\/down\\" buttons should change the pattern order in the preview",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n\\n    at AssemblerPage.waitForLoadingScreenFinish (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/assembler.page.js:21:5)\\n    at prepareAssembler (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/full-composability.spec.js:18:19)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/full-composability.spec.js:240:4",
                            "snippet": "   at customize-store\\/assembler\\/assembler.page.js:21\\n\\n\\u001b[0m \\u001b[90m 19 |\\u001b[39m \\t\\t\\u001b[36mawait\\u001b[39m frame\\n \\u001b[90m 20 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mgetByRole( \\u001b[32m\'button\'\\u001b[39m\\u001b[33m,\\u001b[39m { name\\u001b[33m:\\u001b[39m \\u001b[32m\'Finish customizing\'\\u001b[39m } )\\n\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 21 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mwaitFor( { timeout\\u001b[33m:\\u001b[39m \\u001b[35m25000\\u001b[39m } )\\u001b[33m;\\u001b[39m\\n \\u001b[90m    |\\u001b[39m \\t\\t\\t \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\n \\u001b[90m 22 |\\u001b[39m \\t}\\n \\u001b[90m 23 |\\u001b[39m\\n \\u001b[90m 24 |\\u001b[39m \\t\\u001b[90m\\/**\\u001b[39m\\u001b[0m",
                            "rawStatus": "failed",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/full-composability.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/full-composability.spec.js > Assembler -> Full composability",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--d7c95-attern-order-in-the-preview-e2e-retry1\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--d7c95-attern-order-in-the-preview-e2e-retry1\\/video.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--d7c95-attern-order-in-the-preview-e2e-retry1\\/error-context.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--d7c95-attern-order-in-the-preview-e2e-retry1\\/trace.zip"
                                }
                            ],
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
                            "name": "Clicking the \\"Shuffle\\" button on a patterns should replace it for another one",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n\\n    at AssemblerPage.waitForLoadingScreenFinish (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/assembler.page.js:21:5)\\n    at prepareAssembler (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/full-composability.spec.js:18:19)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/full-composability.spec.js:281:4",
                            "snippet": "   at customize-store\\/assembler\\/assembler.page.js:21\\n\\n\\u001b[0m \\u001b[90m 19 |\\u001b[39m \\t\\t\\u001b[36mawait\\u001b[39m frame\\n \\u001b[90m 20 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mgetByRole( \\u001b[32m\'button\'\\u001b[39m\\u001b[33m,\\u001b[39m { name\\u001b[33m:\\u001b[39m \\u001b[32m\'Finish customizing\'\\u001b[39m } )\\n\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 21 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mwaitFor( { timeout\\u001b[33m:\\u001b[39m \\u001b[35m25000\\u001b[39m } )\\u001b[33m;\\u001b[39m\\n \\u001b[90m    |\\u001b[39m \\t\\t\\t \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\n \\u001b[90m 22 |\\u001b[39m \\t}\\n \\u001b[90m 23 |\\u001b[39m\\n \\u001b[90m 24 |\\u001b[39m \\t\\u001b[90m\\/**\\u001b[39m\\u001b[0m",
                            "rawStatus": "failed",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/full-composability.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/full-composability.spec.js > Assembler -> Full composability",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--932e6--replace-it-for-another-one-e2e-retry1\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--932e6--replace-it-for-another-one-e2e-retry1\\/video.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--932e6--replace-it-for-another-one-e2e-retry1\\/error-context.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--932e6--replace-it-for-another-one-e2e-retry1\\/trace.zip"
                                }
                            ],
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
                            "name": "Clicking the \\"Delete\\" button on a pattern should remove it from the preview",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n\\n    at AssemblerPage.waitForLoadingScreenFinish (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/assembler.page.js:21:5)\\n    at prepareAssembler (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/full-composability.spec.js:18:19)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/full-composability.spec.js:319:4",
                            "snippet": "   at customize-store\\/assembler\\/assembler.page.js:21\\n\\n\\u001b[0m \\u001b[90m 19 |\\u001b[39m \\t\\t\\u001b[36mawait\\u001b[39m frame\\n \\u001b[90m 20 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mgetByRole( \\u001b[32m\'button\'\\u001b[39m\\u001b[33m,\\u001b[39m { name\\u001b[33m:\\u001b[39m \\u001b[32m\'Finish customizing\'\\u001b[39m } )\\n\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 21 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mwaitFor( { timeout\\u001b[33m:\\u001b[39m \\u001b[35m25000\\u001b[39m } )\\u001b[33m;\\u001b[39m\\n \\u001b[90m    |\\u001b[39m \\t\\t\\t \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\n \\u001b[90m 22 |\\u001b[39m \\t}\\n \\u001b[90m 23 |\\u001b[39m\\n \\u001b[90m 24 |\\u001b[39m \\t\\u001b[90m\\/**\\u001b[39m\\u001b[0m",
                            "rawStatus": "failed",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/full-composability.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/full-composability.spec.js > Assembler -> Full composability",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--ff693--remove-it-from-the-preview-e2e-retry1\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--ff693--remove-it-from-the-preview-e2e-retry1\\/video.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--ff693--remove-it-from-the-preview-e2e-retry1\\/error-context.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--ff693--remove-it-from-the-preview-e2e-retry1\\/trace.zip"
                                }
                            ],
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
                            "name": "Clicking the \\"Add patterns\\" button on the No Blocks view should add a default pattern",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n\\n    at AssemblerPage.waitForLoadingScreenFinish (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/assembler.page.js:21:5)\\n    at prepareAssembler (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/full-composability.spec.js:18:19)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/full-composability.spec.js:335:4",
                            "snippet": "   at customize-store\\/assembler\\/assembler.page.js:21\\n\\n\\u001b[0m \\u001b[90m 19 |\\u001b[39m \\t\\t\\u001b[36mawait\\u001b[39m frame\\n \\u001b[90m 20 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mgetByRole( \\u001b[32m\'button\'\\u001b[39m\\u001b[33m,\\u001b[39m { name\\u001b[33m:\\u001b[39m \\u001b[32m\'Finish customizing\'\\u001b[39m } )\\n\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 21 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mwaitFor( { timeout\\u001b[33m:\\u001b[39m \\u001b[35m25000\\u001b[39m } )\\u001b[33m;\\u001b[39m\\n \\u001b[90m    |\\u001b[39m \\t\\t\\t \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\n \\u001b[90m 22 |\\u001b[39m \\t}\\n \\u001b[90m 23 |\\u001b[39m\\n \\u001b[90m 24 |\\u001b[39m \\t\\u001b[90m\\/**\\u001b[39m\\u001b[0m",
                            "rawStatus": "failed",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/full-composability.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/full-composability.spec.js > Assembler -> Full composability",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--589ba-hould-add-a-default-pattern-e2e-retry1\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--589ba-hould-add-a-default-pattern-e2e-retry1\\/video.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--589ba-hould-add-a-default-pattern-e2e-retry1\\/error-context.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--589ba-hould-add-a-default-pattern-e2e-retry1\\/trace.zip"
                                }
                            ],
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
                            "name": "Available headers should be displayed",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n\\n    at AssemblerPage.waitForLoadingScreenFinish (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/assembler.page.js:21:5)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/header.spec.js:67:24",
                            "snippet": "   at customize-store\\/assembler\\/assembler.page.js:21\\n\\n\\u001b[0m \\u001b[90m 19 |\\u001b[39m \\t\\t\\u001b[36mawait\\u001b[39m frame\\n \\u001b[90m 20 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mgetByRole( \\u001b[32m\'button\'\\u001b[39m\\u001b[33m,\\u001b[39m { name\\u001b[33m:\\u001b[39m \\u001b[32m\'Finish customizing\'\\u001b[39m } )\\n\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 21 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mwaitFor( { timeout\\u001b[33m:\\u001b[39m \\u001b[35m25000\\u001b[39m } )\\u001b[33m;\\u001b[39m\\n \\u001b[90m    |\\u001b[39m \\t\\t\\t \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\n \\u001b[90m 22 |\\u001b[39m \\t}\\n \\u001b[90m 23 |\\u001b[39m\\n \\u001b[90m 24 |\\u001b[39m \\t\\u001b[90m\\/**\\u001b[39m\\u001b[0m",
                            "rawStatus": "failed",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/header.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/header.spec.js > Assembler -> headers",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--7c5ef-headers-should-be-displayed-e2e-retry1\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--7c5ef-headers-should-be-displayed-e2e-retry1\\/video.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--7c5ef-headers-should-be-displayed-e2e-retry1\\/error-context.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--7c5ef-headers-should-be-displayed-e2e-retry1\\/trace.zip"
                                }
                            ],
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
                            "name": "The selected header should be focused when is clicked",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n\\n    at AssemblerPage.waitForLoadingScreenFinish (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/assembler.page.js:21:5)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/header.spec.js:67:24",
                            "snippet": "   at customize-store\\/assembler\\/assembler.page.js:21\\n\\n\\u001b[0m \\u001b[90m 19 |\\u001b[39m \\t\\t\\u001b[36mawait\\u001b[39m frame\\n \\u001b[90m 20 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mgetByRole( \\u001b[32m\'button\'\\u001b[39m\\u001b[33m,\\u001b[39m { name\\u001b[33m:\\u001b[39m \\u001b[32m\'Finish customizing\'\\u001b[39m } )\\n\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 21 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mwaitFor( { timeout\\u001b[33m:\\u001b[39m \\u001b[35m25000\\u001b[39m } )\\u001b[33m;\\u001b[39m\\n \\u001b[90m    |\\u001b[39m \\t\\t\\t \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\n \\u001b[90m 22 |\\u001b[39m \\t}\\n \\u001b[90m 23 |\\u001b[39m\\n \\u001b[90m 24 |\\u001b[39m \\t\\u001b[90m\\/**\\u001b[39m\\u001b[0m",
                            "rawStatus": "failed",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/header.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/header.spec.js > Assembler -> headers",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--66770--be-focused-when-is-clicked-e2e-retry1\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--66770--be-focused-when-is-clicked-e2e-retry1\\/video.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--66770--be-focused-when-is-clicked-e2e-retry1\\/error-context.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--66770--be-focused-when-is-clicked-e2e-retry1\\/trace.zip"
                                }
                            ],
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
                            "name": "The selected header should be applied on the frontend",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n\\n    at AssemblerPage.waitForLoadingScreenFinish (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/assembler.page.js:21:5)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/header.spec.js:67:24",
                            "snippet": "   at customize-store\\/assembler\\/assembler.page.js:21\\n\\n\\u001b[0m \\u001b[90m 19 |\\u001b[39m \\t\\t\\u001b[36mawait\\u001b[39m frame\\n \\u001b[90m 20 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mgetByRole( \\u001b[32m\'button\'\\u001b[39m\\u001b[33m,\\u001b[39m { name\\u001b[33m:\\u001b[39m \\u001b[32m\'Finish customizing\'\\u001b[39m } )\\n\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 21 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mwaitFor( { timeout\\u001b[33m:\\u001b[39m \\u001b[35m25000\\u001b[39m } )\\u001b[33m;\\u001b[39m\\n \\u001b[90m    |\\u001b[39m \\t\\t\\t \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\n \\u001b[90m 22 |\\u001b[39m \\t}\\n \\u001b[90m 23 |\\u001b[39m\\n \\u001b[90m 24 |\\u001b[39m \\t\\u001b[90m\\/**\\u001b[39m\\u001b[0m",
                            "rawStatus": "failed",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/header.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/header.spec.js > Assembler -> headers",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--ba025--be-applied-on-the-frontend-e2e-retry1\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--ba025--be-applied-on-the-frontend-e2e-retry1\\/video.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--ba025--be-applied-on-the-frontend-e2e-retry1\\/error-context.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--ba025--be-applied-on-the-frontend-e2e-retry1\\/trace.zip"
                                }
                            ],
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
                            "name": "Picking a header should trigger an update on the site preview",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n\\n    at AssemblerPage.waitForLoadingScreenFinish (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/assembler.page.js:21:5)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/header.spec.js:67:24",
                            "snippet": "   at customize-store\\/assembler\\/assembler.page.js:21\\n\\n\\u001b[0m \\u001b[90m 19 |\\u001b[39m \\t\\t\\u001b[36mawait\\u001b[39m frame\\n \\u001b[90m 20 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mgetByRole( \\u001b[32m\'button\'\\u001b[39m\\u001b[33m,\\u001b[39m { name\\u001b[33m:\\u001b[39m \\u001b[32m\'Finish customizing\'\\u001b[39m } )\\n\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 21 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mwaitFor( { timeout\\u001b[33m:\\u001b[39m \\u001b[35m25000\\u001b[39m } )\\u001b[33m;\\u001b[39m\\n \\u001b[90m    |\\u001b[39m \\t\\t\\t \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\n \\u001b[90m 22 |\\u001b[39m \\t}\\n \\u001b[90m 23 |\\u001b[39m\\n \\u001b[90m 24 |\\u001b[39m \\t\\u001b[90m\\/**\\u001b[39m\\u001b[0m",
                            "rawStatus": "failed",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/header.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/header.spec.js > Assembler -> headers",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--520ab--update-on-the-site-preview-e2e-retry1\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--520ab--update-on-the-site-preview-e2e-retry1\\/video.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--520ab--update-on-the-site-preview-e2e-retry1\\/error-context.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--520ab--update-on-the-site-preview-e2e-retry1\\/trace.zip"
                                }
                            ],
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
                            "name": "The selected homepage should be focused when is clicked",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e",
                                "@non-critical"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/homepage.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/homepage.spec.js > Assembler -> Homepage",
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
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/homepage.spec.js",
                                            "line": 50,
                                            "column": 10
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "The selected homepage should be visible on the site preview",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e",
                                "@non-critical"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/homepage.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/homepage.spec.js > Assembler -> Homepage",
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
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/homepage.spec.js",
                                            "line": 50,
                                            "column": 10
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Selected homepage should be applied on the frontend",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e",
                                "@non-critical"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/homepage.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/homepage.spec.js > Assembler -> Homepage",
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
                                            "file": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/homepage.spec.js",
                                            "line": 50,
                                            "column": 10
                                        }
                                    }
                                ]
                            },
                            "retryAttempts": []
                        },
                        {
                            "name": "Should show the \\"Want more patterns?\\" banner with the Opt-in message when tracking is not allowed",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n\\n    at AssemblerPage.waitForLoadingScreenFinish (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/assembler.page.js:21:5)\\n    at prepareAssembler (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/homepage.spec.js:20:19)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/homepage.spec.js:294:3",
                            "snippet": "   at customize-store\\/assembler\\/assembler.page.js:21\\n\\n\\u001b[0m \\u001b[90m 19 |\\u001b[39m \\t\\t\\u001b[36mawait\\u001b[39m frame\\n \\u001b[90m 20 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mgetByRole( \\u001b[32m\'button\'\\u001b[39m\\u001b[33m,\\u001b[39m { name\\u001b[33m:\\u001b[39m \\u001b[32m\'Finish customizing\'\\u001b[39m } )\\n\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 21 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mwaitFor( { timeout\\u001b[33m:\\u001b[39m \\u001b[35m25000\\u001b[39m } )\\u001b[33m;\\u001b[39m\\n \\u001b[90m    |\\u001b[39m \\t\\t\\t \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\n \\u001b[90m 22 |\\u001b[39m \\t}\\n \\u001b[90m 23 |\\u001b[39m\\n \\u001b[90m 24 |\\u001b[39m \\t\\u001b[90m\\/**\\u001b[39m\\u001b[0m",
                            "rawStatus": "failed",
                            "tags": [
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/homepage.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/homepage.spec.js > Homepage tracking banner",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--16fd2-hen-tracking-is-not-allowed-e2e-retry1\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--16fd2-hen-tracking-is-not-allowed-e2e-retry1\\/video.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--16fd2-hen-tracking-is-not-allowed-e2e-retry1\\/error-context.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--16fd2-hen-tracking-is-not-allowed-e2e-retry1\\/trace.zip"
                                }
                            ],
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
                            "name": "Should show the \\"Want more patterns?\\" banner with the offline message when the user is offline and tracking is not allowed",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n\\n    at AssemblerPage.waitForLoadingScreenFinish (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/assembler.page.js:21:5)\\n    at prepareAssembler (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/homepage.spec.js:20:19)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/homepage.spec.js:314:3",
                            "snippet": "   at customize-store\\/assembler\\/assembler.page.js:21\\n\\n\\u001b[0m \\u001b[90m 19 |\\u001b[39m \\t\\t\\u001b[36mawait\\u001b[39m frame\\n \\u001b[90m 20 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mgetByRole( \\u001b[32m\'button\'\\u001b[39m\\u001b[33m,\\u001b[39m { name\\u001b[33m:\\u001b[39m \\u001b[32m\'Finish customizing\'\\u001b[39m } )\\n\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 21 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mwaitFor( { timeout\\u001b[33m:\\u001b[39m \\u001b[35m25000\\u001b[39m } )\\u001b[33m;\\u001b[39m\\n \\u001b[90m    |\\u001b[39m \\t\\t\\t \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\n \\u001b[90m 22 |\\u001b[39m \\t}\\n \\u001b[90m 23 |\\u001b[39m\\n \\u001b[90m 24 |\\u001b[39m \\t\\u001b[90m\\/**\\u001b[39m\\u001b[0m",
                            "rawStatus": "failed",
                            "tags": [
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/homepage.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/homepage.spec.js > Homepage tracking banner",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--86a9c-and-tracking-is-not-allowed-e2e-retry1\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--86a9c-and-tracking-is-not-allowed-e2e-retry1\\/video.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--86a9c-and-tracking-is-not-allowed-e2e-retry1\\/error-context.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--86a9c-and-tracking-is-not-allowed-e2e-retry1\\/trace.zip"
                                }
                            ],
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
                            "name": "Logo Picker should be empty initially",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n\\n    at AssemblerPage.waitForLoadingScreenFinish (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/assembler.page.js:21:5)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/logo-picker\\/logo-picker.spec.js:82:31",
                            "snippet": "   at customize-store\\/assembler\\/assembler.page.js:21\\n\\n\\u001b[0m \\u001b[90m 19 |\\u001b[39m \\t\\t\\u001b[36mawait\\u001b[39m frame\\n \\u001b[90m 20 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mgetByRole( \\u001b[32m\'button\'\\u001b[39m\\u001b[33m,\\u001b[39m { name\\u001b[33m:\\u001b[39m \\u001b[32m\'Finish customizing\'\\u001b[39m } )\\n\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 21 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mwaitFor( { timeout\\u001b[33m:\\u001b[39m \\u001b[35m25000\\u001b[39m } )\\u001b[33m;\\u001b[39m\\n \\u001b[90m    |\\u001b[39m \\t\\t\\t \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\n \\u001b[90m 22 |\\u001b[39m \\t}\\n \\u001b[90m 23 |\\u001b[39m\\n \\u001b[90m 24 |\\u001b[39m \\t\\u001b[90m\\/**\\u001b[39m\\u001b[0m",
                            "rawStatus": "failed",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/logo-picker.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/logo-picker\\/logo-picker.spec.js > Assembler -> Logo Picker",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--5d4f3-r-should-be-empty-initially-e2e-retry1\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--5d4f3-r-should-be-empty-initially-e2e-retry1\\/video.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--5d4f3-r-should-be-empty-initially-e2e-retry1\\/error-context.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--5d4f3-r-should-be-empty-initially-e2e-retry1\\/trace.zip"
                                }
                            ],
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
                            "name": "Selecting an image should update the site preview",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n\\n    at AssemblerPage.waitForLoadingScreenFinish (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/assembler.page.js:21:5)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/logo-picker\\/logo-picker.spec.js:82:31",
                            "snippet": "   at customize-store\\/assembler\\/assembler.page.js:21\\n\\n\\u001b[0m \\u001b[90m 19 |\\u001b[39m \\t\\t\\u001b[36mawait\\u001b[39m frame\\n \\u001b[90m 20 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mgetByRole( \\u001b[32m\'button\'\\u001b[39m\\u001b[33m,\\u001b[39m { name\\u001b[33m:\\u001b[39m \\u001b[32m\'Finish customizing\'\\u001b[39m } )\\n\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 21 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mwaitFor( { timeout\\u001b[33m:\\u001b[39m \\u001b[35m25000\\u001b[39m } )\\u001b[33m;\\u001b[39m\\n \\u001b[90m    |\\u001b[39m \\t\\t\\t \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\n \\u001b[90m 22 |\\u001b[39m \\t}\\n \\u001b[90m 23 |\\u001b[39m\\n \\u001b[90m 24 |\\u001b[39m \\t\\u001b[90m\\/**\\u001b[39m\\u001b[0m",
                            "rawStatus": "failed",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/logo-picker.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/logo-picker\\/logo-picker.spec.js > Assembler -> Logo Picker",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--d08c1-uld-update-the-site-preview-e2e-retry1\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--d08c1-uld-update-the-site-preview-e2e-retry1\\/video.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--d08c1-uld-update-the-site-preview-e2e-retry1\\/error-context.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--d08c1-uld-update-the-site-preview-e2e-retry1\\/trace.zip"
                                }
                            ],
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
                            "name": "Changing the image width should update the site preview and the frontend",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n\\n    at AssemblerPage.waitForLoadingScreenFinish (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/assembler.page.js:21:5)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/logo-picker\\/logo-picker.spec.js:82:31",
                            "snippet": "   at customize-store\\/assembler\\/assembler.page.js:21\\n\\n\\u001b[0m \\u001b[90m 19 |\\u001b[39m \\t\\t\\u001b[36mawait\\u001b[39m frame\\n \\u001b[90m 20 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mgetByRole( \\u001b[32m\'button\'\\u001b[39m\\u001b[33m,\\u001b[39m { name\\u001b[33m:\\u001b[39m \\u001b[32m\'Finish customizing\'\\u001b[39m } )\\n\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 21 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mwaitFor( { timeout\\u001b[33m:\\u001b[39m \\u001b[35m25000\\u001b[39m } )\\u001b[33m;\\u001b[39m\\n \\u001b[90m    |\\u001b[39m \\t\\t\\t \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\n \\u001b[90m 22 |\\u001b[39m \\t}\\n \\u001b[90m 23 |\\u001b[39m\\n \\u001b[90m 24 |\\u001b[39m \\t\\u001b[90m\\/**\\u001b[39m\\u001b[0m",
                            "rawStatus": "failed",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/logo-picker.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/logo-picker\\/logo-picker.spec.js > Assembler -> Logo Picker",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--5c727-te-preview-and-the-frontend-e2e-retry1\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--5c727-te-preview-and-the-frontend-e2e-retry1\\/video.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--5c727-te-preview-and-the-frontend-e2e-retry1\\/error-context.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--5c727-te-preview-and-the-frontend-e2e-retry1\\/trace.zip"
                                }
                            ],
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
                            "name": "Clicking the Delete button should remove the selected image",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n",
                            "trace": "TimeoutError: locator.waitFor: Timeout 25000ms exceeded.\\nCall log:\\n\\u001b[2m  - waiting for locator(\'.cys-fullscreen-iframe[style=\\"opacity: 1;\\"]\').contentFrame().getByRole(\'button\', { name: \'Finish customizing\' }) to be visible\\u001b[22m\\n\\n    at AssemblerPage.waitForLoadingScreenFinish (\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/assembler.page.js:21:5)\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/customize-store\\/assembler\\/logo-picker\\/logo-picker.spec.js:82:31",
                            "snippet": "   at customize-store\\/assembler\\/assembler.page.js:21\\n\\n\\u001b[0m \\u001b[90m 19 |\\u001b[39m \\t\\t\\u001b[36mawait\\u001b[39m frame\\n \\u001b[90m 20 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mgetByRole( \\u001b[32m\'button\'\\u001b[39m\\u001b[33m,\\u001b[39m { name\\u001b[33m:\\u001b[39m \\u001b[32m\'Finish customizing\'\\u001b[39m } )\\n\\u001b[31m\\u001b[1m>\\u001b[22m\\u001b[39m\\u001b[90m 21 |\\u001b[39m \\t\\t\\t\\u001b[33m.\\u001b[39mwaitFor( { timeout\\u001b[33m:\\u001b[39m \\u001b[35m25000\\u001b[39m } )\\u001b[33m;\\u001b[39m\\n \\u001b[90m    |\\u001b[39m \\t\\t\\t \\u001b[31m\\u001b[1m^\\u001b[22m\\u001b[39m\\n \\u001b[90m 22 |\\u001b[39m \\t}\\n \\u001b[90m 23 |\\u001b[39m\\n \\u001b[90m 24 |\\u001b[39m \\t\\u001b[90m\\/**\\u001b[39m\\u001b[0m",
                            "rawStatus": "failed",
                            "tags": [
                                "@gutenberg",
                                "@not-e2e"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/logo-picker.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "e2e > customize-store\\/assembler\\/logo-picker\\/logo-picker.spec.js > Assembler -> Logo Picker",
                            "attachments": [
                                {
                                    "name": "screenshot",
                                    "contentType": "image\\/png",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--53dfc-d-remove-the-selected-image-e2e-retry1\\/test-failed-1.png"
                                },
                                {
                                    "name": "video",
                                    "contentType": "video\\/webm",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--53dfc-d-remove-the-selected-image-e2e-retry1\\/video.webm"
                                },
                                {
                                    "name": "error-context",
                                    "contentType": "text\\/markdown",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--53dfc-d-remove-the-selected-image-e2e-retry1\\/error-context.md"
                                },
                                {
                                    "name": "trace",
                                    "contentType": "application\\/zip",
                                    "path": "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/test-results\\/customize-store-assembler--53dfc-d-remove-the-selected-image-e2e-retry1\\/trace.zip"
                                }
                            ],
                            "stdout": [
                                "[IGNORED FOR WOO-E2E]"
                            ],
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
                        "count": "1",
                        "message": "PHP Notice: Undefined property: wpdb::$wc_category_lookup in \\/var\\/www\\/html\\/wp-includes\\/class-wpdb.php on line 788"
                    },
                    {
                        "count": "1",
                        "message": "WordPress database error You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near \'\' at line 1 for query TRUNCATE TABLE made by require(\'wp-blog-header.php\'), require_once(\'wp-load.php\'), require_once(\'wp-config.php\'), require_once(\'wp-settings.php\'), do_action(\'init\'), WP_Hook->do_action, WP_Hook->apply_filters, {closure}, ActionScheduler_QueueRunner->run, ActionScheduler_QueueRunner->do_batch, ActionScheduler_Abstract_QueueRunner->process_action, ActionScheduler_Action->execute, do_action_ref_array(\'generate_category_lookup_table_wrapper\'), WP_Hook->do_action, WP_Hook->apply_filters, WooCommerce->add_generate_category_lookup_table_wrapper, do_action(\'generate_category_lookup_table\'), WP_Hook->do_action, WP_Hook->apply_filters, Automattic\\\\WooCommerce\\\\Internal\\\\Admin\\\\CategoryLookup->regenerate"
                    },
                    {
                        "count": "1",
                        "message": "WordPress database error You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near \'(category_tree_id,category_id) VALUES (17,17),(16,16),(15,15)\' at line 1 for query INSERT IGNORE INTO (category_tree_id,category_id) VALUES (17,17),(16,16),(15,15) made by require(\'wp-blog-header.php\'), require_once(\'wp-load.php\'), require_once(\'wp-config.php\'), require_once(\'wp-settings.php\'), do_action(\'init\'), WP_Hook->do_action, WP_Hook->apply_filters, {closure}, ActionScheduler_QueueRunner->run, ActionScheduler_QueueRunner->do_batch, ActionScheduler_Abstract_QueueRunner->process_action, ActionScheduler_Action->execute, do_action_ref_array(\'generate_category_lookup_table_wrapper\'), WP_Hook->do_action, WP_Hook->apply_filters, WooCommerce->add_generate_category_lookup_table_wrapper, do_action(\'generate_category_lookup_table\'), WP_Hook->do_action, WP_Hook->apply_filters, Automattic\\\\WooCommerce\\\\Internal\\\\Admin\\\\CategoryLookup->regenerate"
                    }
                ]
            }
        }
    ]
]';
