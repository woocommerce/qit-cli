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
            "test_summary": "418 total, 391 passed, 0 failed, 27 skipped.",
            "debug_log": "",
            "version": "Undefined",
            "update_complete": true,
            "ai_suggestion_status": "none",
            "malware_whitelist_paths": [],
            "workflow_id": "1234567890",
            "runner": "normalized",
            "test_result_json_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "numFailedTestSuites": 0,
                "numPassedTestSuites": 89,
                "numPendingTestSuites": 19,
                "numTotalTestSuites": 108,
                "numFailedTests": 0,
                "numPassedTests": 391,
                "numPendingTests": 27,
                "numTotalTests": 418,
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
                        "file": "activate-and-setup\\/core-profiler.spec.js",
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
                                    "title": "Can click skip guided setup",
                                    "status": "passed"
                                },
                                {
                                    "title": "Can connect to WooCommerce.com",
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
                        "file": "admin-marketing\\/overview.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Marketing page": [
                                {
                                    "title": "A user can view the Marketing > Overview page without it crashing",
                                    "status": "passed"
                                },
                                {
                                    "title": "Marketing Overview page have relevant content",
                                    "status": "passed"
                                },
                                {
                                    "title": "Introduction can be dismissed",
                                    "status": "passed"
                                },
                                {
                                    "title": "Learning section can be expanded",
                                    "status": "pending"
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
                                    "title": "Saving valid bank account transfer details enables the payment method",
                                    "status": "passed"
                                },
                                {
                                    "title": "Can visit the payment setup task from the homescreen if the setup wizard has been skipped",
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
                                    "title": "Color palette Blueberry Sorbet should be applied",
                                    "status": "passed"
                                },
                                {
                                    "title": "Color palette Ancient Bronze should be applied",
                                    "status": "passed"
                                },
                                {
                                    "title": "Color palette Crimson Tide should be applied",
                                    "status": "passed"
                                },
                                {
                                    "title": "Color palette Purple Twilight should be applied",
                                    "status": "passed"
                                },
                                {
                                    "title": "Color palette Green Thumb should be applied",
                                    "status": "passed"
                                },
                                {
                                    "title": "Color palette Golden Haze should be applied",
                                    "status": "passed"
                                },
                                {
                                    "title": "Color palette Golden Indigo should be applied",
                                    "status": "passed"
                                },
                                {
                                    "title": "Color palette Arctic Dawn should be applied",
                                    "status": "passed"
                                },
                                {
                                    "title": "Color palette Raspberry Chocolate should be applied",
                                    "status": "passed"
                                },
                                {
                                    "title": "Color palette Canary should be applied",
                                    "status": "passed"
                                },
                                {
                                    "title": "Color palette Ice should be applied",
                                    "status": "passed"
                                },
                                {
                                    "title": "Color palette Rustic Rosewood should be applied",
                                    "status": "passed"
                                },
                                {
                                    "title": "Color palette Cinnamon Latte should be applied",
                                    "status": "passed"
                                },
                                {
                                    "title": "Color palette Lightning should be applied",
                                    "status": "passed"
                                },
                                {
                                    "title": "Color palette Aquamarine Night should be applied",
                                    "status": "passed"
                                },
                                {
                                    "title": "Color palette Charcoal should be applied",
                                    "status": "passed"
                                },
                                {
                                    "title": "Color palette Slate should be applied",
                                    "status": "passed"
                                },
                                {
                                    "title": "Color picker should be focused when a color is picked",
                                    "status": "passed"
                                },
                                {
                                    "title": "Selected color palette should be applied on the frontend",
                                    "status": "passed"
                                },
                                {
                                    "title": "Create \\"your own\\" pickers should be visible",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "customize-store\\/assembler\\/font-picker.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Assembler -> Font Picker": [
                                {
                                    "title": "Font pickers should be displayed",
                                    "status": "passed"
                                },
                                {
                                    "title": "Picking a font should trigger an update of fonts on the site preview",
                                    "status": "passed"
                                },
                                {
                                    "title": "Font pickers should be focused when a font is picked",
                                    "status": "passed"
                                },
                                {
                                    "title": "Selected font palette should be applied on the frontend",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "customize-store\\/assembler\\/footer.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Assembler -> Footers": [
                                {
                                    "title": "Available footers should be displayed",
                                    "status": "passed"
                                },
                                {
                                    "title": "The selected footer should be focused when is clicked",
                                    "status": "passed"
                                },
                                {
                                    "title": "The selected footer should be applied on the frontend",
                                    "status": "passed"
                                },
                                {
                                    "title": "Picking a footer should trigger an update on the site preview",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "customize-store\\/assembler\\/header.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Assembler -> headers": [
                                {
                                    "title": "Available headers should be displayed",
                                    "status": "passed"
                                },
                                {
                                    "title": "The selected header should be focused when is clicked",
                                    "status": "passed"
                                },
                                {
                                    "title": "The selected header should be applied on the frontend",
                                    "status": "passed"
                                },
                                {
                                    "title": "Picking a header should trigger an update on the site preview",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "customize-store\\/assembler\\/homepage.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Assembler -> Homepage -> PTK API is down": [
                                {
                                    "title": "Should show the \\"Want more patterns?\\" banner with the PTK API unavailable message",
                                    "status": "passed"
                                }
                            ],
                            "Assembler -> Homepage": [
                                {
                                    "title": "Assembler -> Homepage",
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
                                    "status": "passed"
                                },
                                {
                                    "title": "Selecting an image should update the site preview",
                                    "status": "passed"
                                },
                                {
                                    "title": "Changing the image width should update the site preview and the frontend",
                                    "status": "passed"
                                },
                                {
                                    "title": "Clicking the Delete button should remove the selected image",
                                    "status": "passed"
                                },
                                {
                                    "title": "Clicking the replace image should open the media gallery",
                                    "status": "passed"
                                },
                                {
                                    "title": "Logo should be visible after header update",
                                    "status": "passed"
                                },
                                {
                                    "title": "Enabling the \\"use as site icon\\" option should set the image as the site icon",
                                    "status": "passed"
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
                        "has_pending": false,
                        "tests": {
                            "Store owner can view Assembler Hub for store customization": [
                                {
                                    "title": "Can not access the Assembler Hub page when the theme is not customized",
                                    "status": "passed"
                                },
                                {
                                    "title": "Can access the Assembler Hub page when the theme is already customized",
                                    "status": "passed"
                                },
                                {
                                    "title": "Visiting change header should show a list of block patterns to choose from",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "customize-store\\/intro.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Store owner can view the Intro page": [
                                {
                                    "title": "it shows the \\"offline banner\\" when the network is offline",
                                    "status": "passed"
                                },
                                {
                                    "title": "it shows the \\"no AI\\" banner on Core when the task is not completed",
                                    "status": "passed"
                                },
                                {
                                    "title": "it shows the \\"no AI customize theme\\" banner when the task is completed",
                                    "status": "passed"
                                },
                                {
                                    "title": "it shows the \\"non default block theme\\" banner when the theme is a block theme different than TT4",
                                    "status": "passed"
                                },
                                {
                                    "title": "clicking on \\"Go to the Customizer\\" with a classic theme should go to the customizer",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "customize-store\\/loading-screen\\/loading-screen.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Assembler - Loading Page": [
                                {
                                    "title": "should display loading screen and steps on first run",
                                    "status": "passed"
                                },
                                {
                                    "title": "should redirect to intro page in case of errors",
                                    "status": "passed"
                                },
                                {
                                    "title": "should hide loading screen and steps on subsequent runs",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "customize-store\\/transitional.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Store owner can view the Transitional page": [
                                {
                                    "title": "Accessing the transitional page when the CYS flow is not completed should redirect to the Intro page",
                                    "status": "passed"
                                },
                                {
                                    "title": "Clicking on \\"Save\\" in the assembler should go to the transitional page",
                                    "status": "passed"
                                },
                                {
                                    "title": "Clicking on \\"View store\\" should go to the store home page in a new page",
                                    "status": "passed"
                                },
                                {
                                    "title": "Clicking on \\"Share feedback\\" should open the survey modal",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/command-palette.spec.js",
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
                        "file": "merchant\\/create-cart-block.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Transform Classic Cart To Cart Block": [
                                {
                                    "title": "can transform classic cart to cart block",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/create-checkout-block.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Transform Classic Checkout To Checkout Block": [
                                {
                                    "title": "can transform classic checkout to checkout block",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/create-coupon.spec.js",
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
                        "file": "merchant\\/create-order.spec.js",
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
                        "file": "merchant\\/create-restricted-coupons.spec.js",
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
                                },
                                {
                                    "title": "add and delete shipping method",
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
                        "file": "merchant\\/create-woocommerce-blocks.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Add WooCommerce Blocks Into Page": [
                                {
                                    "title": "can insert all WooCommerce blocks into page",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/create-woocommerce-patterns.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Add WooCommerce Patterns Into Page": [
                                {
                                    "title": "can insert WooCommerce patterns into page",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/customer-list.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Merchant > Customer List": [
                                {
                                    "title": "Merchant can view a list of all customers, filter and download",
                                    "status": "passed"
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
                        "file": "merchant\\/launch-your-store.spec.js",
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
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/order-bulk-edit.spec.js",
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
                                    "title": "can load billing details",
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
                                    "title": "can receive completed email",
                                    "status": "passed"
                                },
                                {
                                    "title": "can receive cancelled order email",
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
                        "file": "merchant\\/product-create-simple.spec.js",
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
                        "file": "merchant\\/product-delete.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Products > Delete Product": [
                                {
                                    "title": "can delete a product from edit view",
                                    "status": "passed"
                                },
                                {
                                    "title": "can quick delete a product from product list",
                                    "status": "passed"
                                },
                                {
                                    "title": "can permanently delete a product from trash list",
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
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/product-images.spec.js",
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
                        "file": "merchant\\/product-linked-products.spec.js",
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
                        "file": "merchant\\/product-reviews.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Product Reviews > Edit Product Review": [
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
                                    "title": "can delete a product review",
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
                        "file": "merchant\\/products\\/add-variable-product\\/create-product-attributes.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Add product attributes": [
                                {
                                    "title": "can add custom product attributes",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/products\\/add-variable-product\\/create-variable-product.spec.js",
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
                        "file": "merchant\\/products\\/add-variable-product\\/create-variations.spec.js",
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
                        "file": "merchant\\/products\\/add-variable-product\\/update-variations.spec.js",
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
                        "file": "merchant\\/products\\/block-editor\\/create-grouped-product-block-editor.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "General tab": [],
                            "General tab > Grouped product": [
                                {
                                    "title": "can create a grouped product",
                                    "status": "passed"
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
                                    "status": "passed"
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
                        "file": "merchant\\/products\\/block-editor\\/create-variable-product-block-editor.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Variations tab": [],
                            "Variations tab > Create variable products": [
                                {
                                    "title": "can create a variation option and publish the product",
                                    "status": "passed"
                                },
                                {
                                    "title": "can edit a variation",
                                    "status": "passed"
                                },
                                {
                                    "title": "can delete a variation",
                                    "status": "passed"
                                },
                                {
                                    "title": "can see variations warning and click the CTA",
                                    "status": "passed"
                                },
                                {
                                    "title": "can see single variation warning and click the CTA",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/products\\/block-editor\\/disable-block-product-editor.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Disable block product editor": [
                                {
                                    "title": "is hooked up to sidebar \\"Add New\\"",
                                    "status": "passed"
                                },
                                {
                                    "title": "can be disabled from the header",
                                    "status": "pending"
                                },
                                {
                                    "title": "can be disabled from settings",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/products\\/block-editor\\/enable-block-product-editor.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Enable block product editor": [],
                            "Enable block product editor > Enabled": [
                                {
                                    "title": "is not hooked up to sidebar \\"Add New\\"",
                                    "status": "passed"
                                },
                                {
                                    "title": "can enable the block product editor",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/products\\/block-editor\\/linked-product-tab-product-block-editor.spec.js",
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
                        "file": "merchant\\/products\\/block-editor\\/organization-tab-product-block-editor.spec.js",
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
                        "file": "merchant\\/products\\/block-editor\\/product-attributes-block-editor.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "add local attribute (with terms) to the Product": [
                                {
                                    "title": "add local attribute (with terms) to the Product",
                                    "status": "passed"
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
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/products\\/block-editor\\/product-edit-block-editor.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Publish dropdown options": [
                                {
                                    "title": "can schedule a product publication",
                                    "status": "passed"
                                },
                                {
                                    "title": "can duplicate a product",
                                    "status": "passed"
                                },
                                {
                                    "title": "can delete a product",
                                    "status": "passed"
                                }
                            ],
                            "can update the general information of a product": [
                                {
                                    "title": "can update the general information of a product",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/products\\/block-editor\\/product-images-block-editor.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "can add images": [
                                {
                                    "title": "can add images",
                                    "status": "passed"
                                }
                            ],
                            "can replace an image": [
                                {
                                    "title": "can replace an image",
                                    "status": "passed"
                                }
                            ],
                            "can remove an image": [
                                {
                                    "title": "can remove an image",
                                    "status": "passed"
                                }
                            ],
                            "can set an image as cover": [
                                {
                                    "title": "can set an image as cover",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/products\\/block-editor\\/product-inventory-block-editor.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "can update sku": [
                                {
                                    "title": "can update sku",
                                    "status": "passed"
                                }
                            ],
                            "can update stock status": [
                                {
                                    "title": "can update stock status",
                                    "status": "passed"
                                }
                            ],
                            "can track stock quantity": [
                                {
                                    "title": "can track stock quantity",
                                    "status": "passed"
                                }
                            ],
                            "can limit purchases": [
                                {
                                    "title": "can limit purchases",
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
                        "file": "merchant\\/settings-shipping.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "WooCommerce Shipping Settings": [
                                {
                                    "title": "can add shipping methods (free, local, flat rate)",
                                    "status": "pending"
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
                        "file": "merchant\\/settings-woo-com.spec.js",
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
                        "file": "merchant\\/users-create.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "can create a new Customer": [
                                {
                                    "title": "can create a new Customer",
                                    "status": "passed"
                                }
                            ],
                            "can create a new Shop manager": [
                                {
                                    "title": "can create a new Shop manager",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/users-manage.spec.js",
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
                        "file": "shopper\\/account-email-receiving.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Shopper Account Email Receiving": [
                                {
                                    "title": "should receive an email when creating an account",
                                    "status": "passed"
                                },
                                {
                                    "title": "should receive an email when password reset initiated from admin",
                                    "status": "passed"
                                }
                            ],
                            "Shopper Password Reset Email Receiving": [
                                {
                                    "title": "should receive an email when initiating a password reset",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/add-to-cart.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Add to Cart behavior": [
                                {
                                    "title": "should add only one product to the cart with AJAX add to cart buttons disabled and \\"Geolocate (with page caching support)\\" as the default customer location",
                                    "status": "passed"
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
                        "file": "shopper\\/cart-block-coupons.spec.js",
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
                        "file": "shopper\\/cart-block.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Cart Block page": [
                                {
                                    "title": "can see empty cart, add and remove simple & cross sell product, increase to max quantity",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/cart-calculate-shipping.spec.js",
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
                        "file": "shopper\\/cart-checkout-block-calculate-tax.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Shopper Cart & Checkout Block Tax Display": [
                                {
                                    "title": "can create Cart Block page",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create Checkout Block page",
                                    "status": "passed"
                                },
                                {
                                    "title": "that inclusive tax is displayed properly in block-based Cart & Checkout pages",
                                    "status": "passed"
                                },
                                {
                                    "title": "that exclusive tax is displayed properly in block-based Cart & Checkout pages",
                                    "status": "passed"
                                }
                            ],
                            "Shopper Cart & Checkout Block Tax Rounding": [
                                {
                                    "title": "that tax rounding is present at subtotal level in block-based Cart & Checkout pages",
                                    "status": "passed"
                                },
                                {
                                    "title": "that tax rounding is off at subtotal level in block-based Cart & Checkout pages",
                                    "status": "passed"
                                }
                            ],
                            "Shopper Cart & Checkout Block Tax Levels": [
                                {
                                    "title": "that applying taxes in cart block of 4 different levels calculates properly",
                                    "status": "passed"
                                },
                                {
                                    "title": "that applying taxes in block-based Cart & Checkout of 2 different levels (2 excluded) calculates properly",
                                    "status": "passed"
                                }
                            ],
                            "Shipping Cart & Checkout Block Tax": [
                                {
                                    "title": "that tax is applied in Cart Block to shipping as well as order",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/cart-checkout-calculate-tax.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Tax rates in the cart and checkout": [],
                            "Tax rates in the cart and checkout > Shopper Tax Display Tests": [
                                {
                                    "title": "checks that taxes are calculated properly on totals, inclusive tax displayed properly",
                                    "status": "passed"
                                },
                                {
                                    "title": "checks that taxes are calculated and displayed correctly exclusive on shop, cart and checkout",
                                    "status": "passed"
                                },
                                {
                                    "title": "checks that display suffix is shown",
                                    "status": "passed"
                                }
                            ],
                            "Tax rates in the cart and checkout > Shopper Tax Rounding": [
                                {
                                    "title": "checks rounding at subtotal level",
                                    "status": "passed"
                                },
                                {
                                    "title": "checks rounding off at subtotal level",
                                    "status": "passed"
                                }
                            ],
                            "Tax rates in the cart and checkout > Shopper Tax Levels": [
                                {
                                    "title": "checks applying taxes of 4 different levels",
                                    "status": "passed"
                                },
                                {
                                    "title": "checks applying taxes of 2 different levels (2 excluded)",
                                    "status": "passed"
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
                        "file": "shopper\\/cart-checkout-coupons.spec.js",
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
                        "file": "shopper\\/cart-checkout-restricted-coupons.spec.js",
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
                                },
                                {
                                    "title": "can manage cross-sell products and maximum item quantity",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/checkout-block-coupons.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Checkout Block Applying Coupons": [
                                {
                                    "title": "allows checkout block to apply coupon of any type",
                                    "status": "passed"
                                },
                                {
                                    "title": "allows checkout block to apply multiple coupons",
                                    "status": "passed"
                                },
                                {
                                    "title": "prevents checkout block applying same coupon twice",
                                    "status": "passed"
                                },
                                {
                                    "title": "prevents checkout block applying coupon with usage limit",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/checkout-block.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Checkout Block page": [
                                {
                                    "title": "can see empty checkout block page",
                                    "status": "passed"
                                },
                                {
                                    "title": "allows customer to choose available payment methods",
                                    "status": "passed"
                                },
                                {
                                    "title": "allows customer to fill shipping details",
                                    "status": "passed"
                                },
                                {
                                    "title": "allows customer to fill different shipping and billing details",
                                    "status": "passed"
                                },
                                {
                                    "title": "allows customer to fill shipping details and toggle different billing",
                                    "status": "passed"
                                },
                                {
                                    "title": "can choose different shipping types in the checkout",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows guest customer to place an order",
                                    "status": "passed"
                                },
                                {
                                    "title": "allows existing customer to place an order",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create an account during checkout",
                                    "status": "passed"
                                },
                                {
                                    "title": "can create an account during checkout with custom password",
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
                                    "title": "warn when customer is missing required details",
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
                        "file": "shopper\\/dashboard-access.spec.js",
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
                        "file": "shopper\\/launch-your-store.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Launch Your Store front end - logged out": [
                                {
                                    "title": "Entire site coming soon mode",
                                    "status": "passed"
                                },
                                {
                                    "title": "Store only coming soon mode",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/mini-cart.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Mini Cart block page": [
                                {
                                    "title": "can see empty customized mini cart, add and remove product, increase to max quantity, calculate tax and see redirection",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/my-account-addresses.spec.js",
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
                        "file": "shopper\\/my-account-downloads.spec.js",
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
                                    "title": "allows customer to login and navigate",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/order-email-receiving.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Shopper Order Email Receiving": [
                                {
                                    "title": "should receive order email after purchasing an item",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/product-grouped.spec.js",
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
                        "file": "shopper\\/product-simple.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Single Product Page": [
                                {
                                    "title": "should be able to post a review and see it after",
                                    "status": "passed"
                                },
                                {
                                    "title": "should be able to see product description",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/product-tags-attributes.spec.js",
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
                        "file": "shopper\\/shop-products-filter-by-price.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Filter items in the shop by product price": [
                                {
                                    "title": "filter products by prices on the created page",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/shop-search-browse-sort.spec.js",
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
                        "file": "shopper\\/shop-title-after-deletion.spec.js",
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
                        "file": "shopper\\/wordpress-post.spec.js",
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
                    },
                    {
                        "file": "smoke-tests\\/update-woocommerce.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "WooCommerce update": [
                                {
                                    "title": "can update WooCommerce to \\"undefined\\"",
                                    "status": "pending"
                                },
                                {
                                    "title": "can run the database update",
                                    "status": "pending"
                                }
                            ]
                        }
                    }
                ],
                "summary": "418 total, 391 passed, 0 failed, 27 skipped."
            }
        }
    ]
]';
