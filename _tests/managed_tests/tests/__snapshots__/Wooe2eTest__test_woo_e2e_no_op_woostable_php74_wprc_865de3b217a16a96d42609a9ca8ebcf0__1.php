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
            "ctrf_json": "",
            "status": "cancelled",
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
            "test_summary": "401 total, 0 passed, 0 failed, 401 skipped.",
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
            "debug_log_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "numFailedTestSuites": 0,
                "numPassedTestSuites": 0,
                "numPendingTestSuites": 102,
                "numTotalTestSuites": 102,
                "numFailedTests": 0,
                "numPassedTests": 0,
                "numPendingTests": 401,
                "numTotalTests": 401,
                "testResults": [
                    {
                        "file": "..\\/fixtures\\/token.teardown.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "remove consumer key": [
                                {
                                    "title": "remove consumer key",
                                    "status": "pending"
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
                        "has_pending": true,
                        "tests": {
                            "authenticate admin": [
                                {
                                    "title": "authenticate admin",
                                    "status": "pending"
                                }
                            ],
                            "authenticate customer": [
                                {
                                    "title": "authenticate customer",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "..\\/fixtures\\/token.setup.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "generate consumer key": [
                                {
                                    "title": "generate consumer key",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "..\\/fixtures\\/site.setup.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "configure HPOS": [
                                {
                                    "title": "configure HPOS",
                                    "status": "pending"
                                }
                            ],
                            "convert Cart and Checkout pages to shortcode": [
                                {
                                    "title": "convert Cart and Checkout pages to shortcode",
                                    "status": "pending"
                                }
                            ],
                            "disable coming soon": [
                                {
                                    "title": "disable coming soon",
                                    "status": "pending"
                                }
                            ],
                            "disable onboarding wizard": [
                                {
                                    "title": "disable onboarding wizard",
                                    "status": "pending"
                                }
                            ],
                            "disable new payments settings page": [
                                {
                                    "title": "disable new payments settings page",
                                    "status": "pending"
                                }
                            ],
                            "determine if multisite": [
                                {
                                    "title": "determine if multisite",
                                    "status": "pending"
                                }
                            ],
                            "general settings": [
                                {
                                    "title": "general settings",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "analytics\\/analytics-access.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "WooCommerce Home": [
                                {
                                    "title": "Can access Analytics Reports from Stats Overview",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "analytics\\/analytics-data.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "confirms correct summary numbers on overview page": [
                                {
                                    "title": "confirms correct summary numbers on overview page",
                                    "status": "pending"
                                }
                            ],
                            "downloads revenue report as CSV": [
                                {
                                    "title": "downloads revenue report as CSV",
                                    "status": "pending"
                                }
                            ],
                            "use date filter on overview page": [
                                {
                                    "title": "use date filter on overview page",
                                    "status": "pending"
                                }
                            ],
                            "set custom date range on revenue report": [
                                {
                                    "title": "set custom date range on revenue report",
                                    "status": "pending"
                                }
                            ],
                            "use advanced filters on orders report": [
                                {
                                    "title": "use advanced filters on orders report",
                                    "status": "pending"
                                }
                            ],
                            "use filter by single product on products report": [
                                {
                                    "title": "use filter by single product on products report",
                                    "status": "pending"
                                }
                            ],
                            "analytics settings": [
                                {
                                    "title": "analytics settings",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "analytics\\/analytics-overview.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Analytics pages": [
                                {
                                    "title": "a user should see 3 sections by default - Performance, Charts, and Leaderboards",
                                    "status": "pending"
                                },
                                {
                                    "title": "should allow a user to remove a section",
                                    "status": "pending"
                                },
                                {
                                    "title": "should allow a user to add a section back in",
                                    "status": "pending"
                                }
                            ],
                            "Analytics pages > moving sections": [
                                {
                                    "title": "should not display move up for the top, or move down for the bottom section",
                                    "status": "pending"
                                },
                                {
                                    "title": "should allow a user to move a section down",
                                    "status": "pending"
                                },
                                {
                                    "title": "should allow a user to move a section up",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "basic\\/basic.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Load the home page": [
                                {
                                    "title": "Load the home page",
                                    "status": "pending"
                                }
                            ],
                            "Load wp-admin as admin": [
                                {
                                    "title": "Load wp-admin as admin",
                                    "status": "pending"
                                }
                            ],
                            "Load my account page as customer": [
                                {
                                    "title": "Load my account page as customer",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "basic\\/dashboard-access.spec.js",
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
                        "file": "basic\\/page-loads.spec.js",
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
                            "WooCommerce Page Load > Load Analytics sub pages": [
                                {
                                    "title": "Can load Overview",
                                    "status": "pending"
                                },
                                {
                                    "title": "Can load Products",
                                    "status": "pending"
                                },
                                {
                                    "title": "Can load Revenue",
                                    "status": "pending"
                                },
                                {
                                    "title": "Can load Orders",
                                    "status": "pending"
                                },
                                {
                                    "title": "Can load Variations",
                                    "status": "pending"
                                },
                                {
                                    "title": "Can load Categories",
                                    "status": "pending"
                                },
                                {
                                    "title": "Can load Coupons",
                                    "status": "pending"
                                },
                                {
                                    "title": "Can load Taxes",
                                    "status": "pending"
                                },
                                {
                                    "title": "Can load Downloads",
                                    "status": "pending"
                                },
                                {
                                    "title": "Can load Stock",
                                    "status": "pending"
                                },
                                {
                                    "title": "Can load Settings",
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
                        "file": "brands\\/create-product-brand.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Merchant can add brands": [
                                {
                                    "title": "Merchant can add brands",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "cart\\/add-to-cart.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Add to Cart behavior": [
                                {
                                    "title": "should add only one product to the cart with AJAX add to cart buttons disabled and \\"Geolocate (with page caching support)\\" as the default customer location",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "cart\\/cart.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "check classic cart": [
                                {
                                    "title": "check classic cart",
                                    "status": "pending"
                                }
                            ],
                            "check blocks cart": [
                                {
                                    "title": "check blocks cart",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "checkout\\/checkout.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "guest can checkout paying with cash on delivery on classic checkout": [
                                {
                                    "title": "guest can checkout paying with cash on delivery on classic checkout",
                                    "status": "pending"
                                }
                            ],
                            "guest can checkout paying with cash on delivery on blocks checkout": [
                                {
                                    "title": "guest can checkout paying with cash on delivery on blocks checkout",
                                    "status": "pending"
                                }
                            ],
                            "guest can create an account at checkout on classic checkout": [
                                {
                                    "title": "guest can create an account at checkout on classic checkout",
                                    "status": "pending"
                                }
                            ],
                            "guest can create an account at checkout on blocks checkout": [
                                {
                                    "title": "guest can create an account at checkout on blocks checkout",
                                    "status": "pending"
                                }
                            ],
                            "logged in customer can checkout with default addresses and direct bank transfer on classic checkout": [
                                {
                                    "title": "logged in customer can checkout with default addresses and direct bank transfer on classic checkout",
                                    "status": "pending"
                                }
                            ],
                            "logged in customer can checkout with default addresses and direct bank transfer on blocks checkout": [
                                {
                                    "title": "logged in customer can checkout with default addresses and direct bank transfer on blocks checkout",
                                    "status": "pending"
                                }
                            ],
                            "customer can login at checkout and place the order with a different shipping address classic checkout": [
                                {
                                    "title": "customer can login at checkout and place the order with a different shipping address classic checkout",
                                    "status": "pending"
                                }
                            ],
                            "customer can login at checkout and place the order with a different shipping address blocks checkout": [
                                {
                                    "title": "customer can login at checkout and place the order with a different shipping address blocks checkout",
                                    "status": "pending"
                                }
                            ],
                            "existing customer can update the billing address and place the order with direct bank transfer on classic checkout": [
                                {
                                    "title": "existing customer can update the billing address and place the order with direct bank transfer on classic checkout",
                                    "status": "pending"
                                }
                            ],
                            "existing customer can update the billing address and place the order with direct bank transfer on blocks checkout": [
                                {
                                    "title": "existing customer can update the billing address and place the order with direct bank transfer on blocks checkout",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "coupons\\/cart-block-coupons.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Cart Block Applying Coupons": [
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
                                },
                                {
                                    "title": "prevents cart block applying coupon with usage limit",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "coupons\\/cart-checkout-coupons.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Cart & Checkout applying coupons": [
                                {
                                    "title": "allows applying coupon of type fixed_cart",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows applying coupon of type percent",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows applying coupon of type fixed_product",
                                    "status": "pending"
                                },
                                {
                                    "title": "prevents applying same coupon twice",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows applying multiple coupons",
                                    "status": "pending"
                                },
                                {
                                    "title": "restores total when coupons are removed",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "coupons\\/cart-checkout-restricted-coupons.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Cart & Checkout Restricted Coupons": [
                                {
                                    "title": "expired coupon cannot be used",
                                    "status": "pending"
                                },
                                {
                                    "title": "coupon requiring min and max amounts and can only be used alone can only be used within limits",
                                    "status": "pending"
                                },
                                {
                                    "title": "coupon cannot be used on sale item",
                                    "status": "pending"
                                },
                                {
                                    "title": "coupon can only be used twice",
                                    "status": "pending"
                                },
                                {
                                    "title": "coupon cannot be used on certain products\\/categories (included product\\/category)",
                                    "status": "pending"
                                },
                                {
                                    "title": "coupon can be used on certain products\\/categories",
                                    "status": "pending"
                                },
                                {
                                    "title": "coupon cannot be used on specific products\\/categories (excluded product\\/category)",
                                    "status": "pending"
                                },
                                {
                                    "title": "coupon can be used on other products\\/categories",
                                    "status": "pending"
                                },
                                {
                                    "title": "coupon cannot be used by any customer on cart (email restricted)",
                                    "status": "pending"
                                },
                                {
                                    "title": "coupon cannot be used by any customer on checkout (email restricted)",
                                    "status": "pending"
                                },
                                {
                                    "title": "coupon can be used by the right customer (email restricted) but only once",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "coupons\\/checkout-block-coupons.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Checkout Block Applying Coupons": [
                                {
                                    "title": "allows checkout block to apply coupon of any type",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows checkout block to apply multiple coupons",
                                    "status": "pending"
                                },
                                {
                                    "title": "prevents checkout block applying same coupon twice",
                                    "status": "pending"
                                },
                                {
                                    "title": "prevents checkout block applying coupon with usage limit",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "coupons\\/create-coupon.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Coupon management": [
                                {
                                    "title": "can create new fixedCart coupon",
                                    "status": "pending"
                                },
                                {
                                    "title": "can create new fixedProduct coupon",
                                    "status": "pending"
                                },
                                {
                                    "title": "can create new percentage coupon",
                                    "status": "pending"
                                },
                                {
                                    "title": "can create new expiryDate coupon",
                                    "status": "pending"
                                },
                                {
                                    "title": "can create new freeShipping coupon",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "coupons\\/create-restricted-coupons.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Restricted coupon management": [
                                {
                                    "title": "can create new minimumSpend coupon",
                                    "status": "pending"
                                },
                                {
                                    "title": "can create new maximumSpend coupon",
                                    "status": "pending"
                                },
                                {
                                    "title": "can create new individualUse coupon",
                                    "status": "pending"
                                },
                                {
                                    "title": "can create new excludeSaleItems coupon",
                                    "status": "pending"
                                },
                                {
                                    "title": "can create new productCategories coupon",
                                    "status": "pending"
                                },
                                {
                                    "title": "can create new excludeProductCategories coupon",
                                    "status": "pending"
                                },
                                {
                                    "title": "can create new excludeProductBrands coupon",
                                    "status": "pending"
                                },
                                {
                                    "title": "can create new products coupon",
                                    "status": "pending"
                                },
                                {
                                    "title": "can create new excludeProducts coupon",
                                    "status": "pending"
                                },
                                {
                                    "title": "can create new allowedEmails coupon",
                                    "status": "pending"
                                },
                                {
                                    "title": "can create new usageLimitPerCoupon coupon",
                                    "status": "pending"
                                },
                                {
                                    "title": "can create new usageLimitPerUser coupon",
                                    "status": "pending"
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
                                    "status": "pending"
                                },
                                {
                                    "title": "Merchant can use advanced filters",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "customize-store\\/assembler\\/color-picker.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Assembler -> Color Pickers": [
                                {
                                    "title": "Color pickers should be displayed",
                                    "status": "pending"
                                },
                                {
                                    "title": "Color palette Slate should be applied",
                                    "status": "pending"
                                },
                                {
                                    "title": "Color picker should be focused when a color is picked",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "customize-store\\/assembler\\/font-picker.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Assembler -> Font Picker": [
                                {
                                    "title": "Font pickers should be displayed",
                                    "status": "pending"
                                },
                                {
                                    "title": "Picking a font should trigger an update of fonts on the site preview",
                                    "status": "pending"
                                },
                                {
                                    "title": "Font pickers should be focused when a font is picked",
                                    "status": "pending"
                                },
                                {
                                    "title": "Selected font palette should be applied on the frontend",
                                    "status": "pending"
                                },
                                {
                                    "title": "Clicking opt-in new fonts should be available",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "customize-store\\/assembler\\/footer.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Assembler -> Footers": [
                                {
                                    "title": "Available footers should be displayed",
                                    "status": "pending"
                                },
                                {
                                    "title": "The selected footer should be focused when is clicked",
                                    "status": "pending"
                                },
                                {
                                    "title": "The selected footer should be applied on the frontend",
                                    "status": "pending"
                                },
                                {
                                    "title": "Picking a footer should trigger an update on the site preview",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "customize-store\\/assembler\\/full-composability.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Assembler -> Full composability": [
                                {
                                    "title": "Clicking on \\"Design your homepage\\" should open the Intro sidebar by default",
                                    "status": "pending"
                                },
                                {
                                    "title": "Clicking on a category should open the sidebar for it",
                                    "status": "pending"
                                },
                                {
                                    "title": "Clicking on a pattern should insert it in the preview",
                                    "status": "pending"
                                },
                                {
                                    "title": "Clicking on a pattern should always scroll the page to the inserted pattern",
                                    "status": "pending"
                                },
                                {
                                    "title": "Clicking the \\"Move up\\/down\\" buttons should change the pattern order in the preview",
                                    "status": "pending"
                                },
                                {
                                    "title": "Clicking the \\"Shuffle\\" button on a patterns should replace it for another one",
                                    "status": "pending"
                                },
                                {
                                    "title": "Clicking the \\"Delete\\" button on a pattern should remove it from the preview",
                                    "status": "pending"
                                },
                                {
                                    "title": "Clicking the \\"Add patterns\\" button on the No Blocks view should add a default pattern",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "customize-store\\/assembler\\/header.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Assembler -> headers": [
                                {
                                    "title": "Available headers should be displayed",
                                    "status": "pending"
                                },
                                {
                                    "title": "The selected header should be focused when is clicked",
                                    "status": "pending"
                                },
                                {
                                    "title": "The selected header should be applied on the frontend",
                                    "status": "pending"
                                },
                                {
                                    "title": "Picking a header should trigger an update on the site preview",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "customize-store\\/assembler\\/homepage.spec.js",
                        "status": "passed",
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
                                    "status": "pending"
                                },
                                {
                                    "title": "Should show the \\"Want more patterns?\\" banner with the offline message when the user is offline and tracking is not allowed",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "customize-store\\/assembler\\/logo-picker\\/logo-picker.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Assembler -> Logo Picker": [
                                {
                                    "title": "Logo Picker should be empty initially",
                                    "status": "pending"
                                },
                                {
                                    "title": "Selecting an image should update the site preview",
                                    "status": "pending"
                                },
                                {
                                    "title": "Changing the image width should update the site preview and the frontend",
                                    "status": "pending"
                                },
                                {
                                    "title": "Clicking the Delete button should remove the selected image",
                                    "status": "pending"
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
                                },
                                {
                                    "title": "Clicking on \\"Share feedback\\" should open the survey modal",
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
                        "file": "editor\\/create-woocommerce-blocks.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Add WooCommerce Blocks Into Page": [
                                {
                                    "title": "can insert all WooCommerce blocks into page",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "editor\\/create-woocommerce-patterns.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Add WooCommerce Patterns Into Page": [
                                {
                                    "title": "can insert WooCommerce patterns into page",
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
                        "file": "email\\/settings-email-style-sync.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Email Style Sync": [
                                {
                                    "title": "Auto-sync toggle in email settings works correctly",
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
                                    "title": "See email image url field with a feature flag",
                                    "status": "pending"
                                },
                                {
                                    "title": "Choose image in email image url field",
                                    "status": "pending"
                                },
                                {
                                    "title": "See new color settings with a feature flag",
                                    "status": "pending"
                                },
                                {
                                    "title": "See font family setting with a feature flag",
                                    "status": "pending"
                                },
                                {
                                    "title": "See updated footer text field with a feature flag",
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
                        "file": "onboarding\\/payment-setup-task.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Payment setup task": [
                                {
                                    "title": "Saving valid bank account transfer details enables the payment method",
                                    "status": "pending"
                                },
                                {
                                    "title": "Enabling cash on delivery enables the payment method",
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
                            "Can visit the payment setup task from from the task list": [
                                {
                                    "title": "Can visit the payment setup task from from the task list",
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
                        "file": "order\\/order-search.spec.js",
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
                        "file": "product\\/block-editor\\/enable-block-product-editor.spec.js",
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
                            "can add and use shipping zone for British Columbia, Canada with Local pickup": [
                                {
                                    "title": "can add and use shipping zone for British Columbia, Canada with Local pickup",
                                    "status": "pending"
                                }
                            ],
                            "can add and use shipping zone for British Columbia, Canada with Free shipping": [
                                {
                                    "title": "can add and use shipping zone for British Columbia, Canada with Free shipping",
                                    "status": "pending"
                                }
                            ],
                            "can add and use shipping zone for Canada with Flat rate": [
                                {
                                    "title": "can add and use shipping zone for Canada with Flat rate",
                                    "status": "pending"
                                }
                            ],
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
                "summary": "401 total, 0 passed, 0 failed, 401 skipped."
            }
        },
        {
            "debug_log": {
                "generic": []
            }
        }
    ]
]';
