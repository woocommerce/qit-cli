<?php return '[
    [
        {
            "test_run_id": 123456,
            "run_id": 123456,
            "test_type": "woo-e2e",
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
            "test_summary": "412 total, 32 passed, 10 failed, 370 skipped.",
            "version": "Undefined",
            "update_complete": true,
            "ai_suggestion_status": "none",
            "malware_whitelist_paths": [],
            "workflow_id": "1234567890",
            "runner": "normalized",
            "test_result_json_extracted": "{EXTRACTED}",
            "debug_log_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "numFailedTestSuites": 1,
                "numPassedTestSuites": 8,
                "numPendingTestSuites": 100,
                "numTotalTestSuites": 108,
                "numFailedTests": 10,
                "numPassedTests": 32,
                "numPendingTests": 370,
                "numTotalTests": 412,
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
                                    "status": "pending"
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
                        "has_pending": false,
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
                        "status": "failed",
                        "has_pending": true,
                        "tests": {
                            "Assembler -> Color Pickers": [
                                {
                                    "title": "Color pickers should be displayed",
                                    "status": "failed"
                                },
                                {
                                    "title": "Color palette Blueberry Sorbet should be applied",
                                    "status": "failed"
                                },
                                {
                                    "title": "Color palette Ancient Bronze should be applied",
                                    "status": "failed"
                                },
                                {
                                    "title": "Color palette Crimson Tide should be applied",
                                    "status": "failed"
                                },
                                {
                                    "title": "Color palette Purple Twilight should be applied",
                                    "status": "failed"
                                },
                                {
                                    "title": "Color palette Green Thumb should be applied",
                                    "status": "failed"
                                },
                                {
                                    "title": "Color palette Golden Haze should be applied",
                                    "status": "failed"
                                },
                                {
                                    "title": "Color palette Golden Indigo should be applied",
                                    "status": "failed"
                                },
                                {
                                    "title": "Color palette Arctic Dawn should be applied",
                                    "status": "failed"
                                },
                                {
                                    "title": "Color palette Raspberry Chocolate should be applied",
                                    "status": "failed"
                                },
                                {
                                    "title": "Color palette Canary should be applied",
                                    "status": "pending"
                                },
                                {
                                    "title": "Color palette Ice should be applied",
                                    "status": "pending"
                                },
                                {
                                    "title": "Color palette Rustic Rosewood should be applied",
                                    "status": "pending"
                                },
                                {
                                    "title": "Color palette Cinnamon Latte should be applied",
                                    "status": "pending"
                                },
                                {
                                    "title": "Color palette Lightning should be applied",
                                    "status": "pending"
                                },
                                {
                                    "title": "Color palette Aquamarine Night should be applied",
                                    "status": "pending"
                                },
                                {
                                    "title": "Color palette Charcoal should be applied",
                                    "status": "pending"
                                },
                                {
                                    "title": "Color palette Slate should be applied",
                                    "status": "pending"
                                },
                                {
                                    "title": "Color picker should be focused when a color is picked",
                                    "status": "pending"
                                },
                                {
                                    "title": "Selected color palette should be applied on the frontend",
                                    "status": "pending"
                                },
                                {
                                    "title": "Create \\"your own\\" pickers should be visible",
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
                                    "title": "Available homepage should be displayed",
                                    "status": "pending"
                                },
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
                                    "title": "Enabling the \\"use as site icon\\" option should set the image as the site icon",
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
                                    "title": "Can view the Assembler Hub page when the theme is already customized",
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
                                    "title": "it shows the \\"no AI\\" banner when the task is completed and the theme is not the default",
                                    "status": "pending"
                                },
                                {
                                    "title": "it shows the \\"no AI\\" banner, when the task is completed and the theme is not the default",
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
                                },
                                {
                                    "title": "should hide loading screen and steps on subsequent runs",
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
                                    "title": "Clicking on \\"Save\\" in the assembler should go to the transitional page",
                                    "status": "pending"
                                },
                                {
                                    "title": "Clicking on \\"View store\\" should go to the store home page in a new page",
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
                        "file": "merchant\\/command-palette.spec.js",
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
                        "file": "merchant\\/create-cart-block.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Transform Classic Cart To Cart Block": [
                                {
                                    "title": "can transform classic cart to cart block",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/create-checkout-block.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Transform Classic Checkout To Checkout Block": [
                                {
                                    "title": "can transform classic checkout to checkout block",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/create-coupon.spec.js",
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
                        "file": "merchant\\/create-page.spec.js",
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
                        "file": "merchant\\/create-post.spec.js",
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
                        "file": "merchant\\/create-restricted-coupons.spec.js",
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
                        "file": "merchant\\/create-shipping-classes.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Merchant can add shipping classes": [
                                {
                                    "title": "can add shipping classes",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/create-shipping-zones.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "WooCommerce Shipping Settings - Add new shipping zone": [
                                {
                                    "title": "add shipping zone for Mayne Island with free Local pickup",
                                    "status": "pending"
                                },
                                {
                                    "title": "add shipping zone for British Columbia with Free shipping",
                                    "status": "pending"
                                },
                                {
                                    "title": "add shipping zone for Canada with Flat rate",
                                    "status": "pending"
                                },
                                {
                                    "title": "add shipping zone with region and then delete the region",
                                    "status": "pending"
                                },
                                {
                                    "title": "add and delete shipping method",
                                    "status": "pending"
                                }
                            ],
                            "Verifies shipping options from customer perspective": [
                                {
                                    "title": "allows customer to benefit from a free Local pickup if on Mayne Island",
                                    "status": "pending"
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
                        "file": "merchant\\/create-woocommerce-blocks.spec.js",
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
                        "file": "merchant\\/create-woocommerce-patterns.spec.js",
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
                        "file": "merchant\\/customer-list.spec.js",
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
                        "file": "merchant\\/launch-your-store.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Launch Your Store front end - logged in": [
                                {
                                    "title": "Entire site coming soon mode",
                                    "status": "pending"
                                },
                                {
                                    "title": "Store only coming soon mode",
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
                                    "title": "can receive completed email",
                                    "status": "pending"
                                },
                                {
                                    "title": "can receive cancelled order email",
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
                        "file": "merchant\\/product-create-simple.spec.js",
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
                        "file": "merchant\\/product-delete.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Products > Delete Product": [
                                {
                                    "title": "can delete a product from edit view",
                                    "status": "pending"
                                },
                                {
                                    "title": "can quick delete a product from product list",
                                    "status": "pending"
                                },
                                {
                                    "title": "can permanently delete a product from trash list",
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
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/product-images.spec.js",
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
                        "file": "merchant\\/product-linked-products.spec.js",
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
                        "file": "merchant\\/product-reviews.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Product Reviews > Edit Product Review": [
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
                                    "title": "can delete a product review",
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
                        "file": "merchant\\/products\\/block-editor\\/create-grouped-product-block-editor.spec.js",
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
                        "file": "merchant\\/products\\/block-editor\\/create-variable-product-block-editor.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Variations tab": [],
                            "Variations tab > Create variable product": [
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
                        "file": "merchant\\/products\\/block-editor\\/disable-block-product-editor.spec.js",
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
                            "can create and add attributes": [
                                {
                                    "title": "can create and add attributes",
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
                        "file": "merchant\\/products\\/block-editor\\/product-edit-block-editor.spec.js",
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
                        "file": "merchant\\/products\\/block-editor\\/product-images-block-editor.spec.js",
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
                        "file": "merchant\\/products\\/block-editor\\/product-inventory-block-editor.spec.js",
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
                        "file": "merchant\\/settings-woo-com.spec.js",
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
                        "file": "merchant\\/users-create.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "can create a new Customer": [
                                {
                                    "title": "can create a new Customer",
                                    "status": "pending"
                                }
                            ],
                            "can create a new Shop manager": [
                                {
                                    "title": "can create a new Shop manager",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "merchant\\/users-manage.spec.js",
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
                        "file": "shopper\\/account-email-receiving.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Shopper Account Email Receiving": [
                                {
                                    "title": "should receive an email when creating an account",
                                    "status": "pending"
                                },
                                {
                                    "title": "should receive an email when password reset initiated from admin",
                                    "status": "pending"
                                }
                            ],
                            "Shopper Password Reset Email Receiving": [
                                {
                                    "title": "should receive an email when initiating a password reset",
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
                        "file": "shopper\\/cart-block.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Cart Block page": [
                                {
                                    "title": "can see empty cart, add and remove simple & cross sell product, increase to max quantity",
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
                        "file": "shopper\\/cart-checkout-block-calculate-tax.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Shopper Cart & Checkout Block Tax Display": [
                                {
                                    "title": "can create Cart Block page",
                                    "status": "pending"
                                },
                                {
                                    "title": "can create Checkout Block page",
                                    "status": "pending"
                                },
                                {
                                    "title": "that inclusive tax is displayed properly in blockbased Cart & Checkout pages",
                                    "status": "pending"
                                },
                                {
                                    "title": "that exclusive tax is displayed properly in blockbased Cart & Checkout pages",
                                    "status": "pending"
                                }
                            ],
                            "Shopper Cart & Checkout Block Tax Rounding": [
                                {
                                    "title": "that tax rounding is present at subtotal level in blockbased Cart & Checkout pages",
                                    "status": "pending"
                                },
                                {
                                    "title": "that tax rounding is off at subtotal level in blockbased Cart & Checkout pages",
                                    "status": "pending"
                                }
                            ],
                            "Shopper Cart & Checkout Block Tax Levels": [
                                {
                                    "title": "that applying taxes in cart block of 4 different levels calculates properly",
                                    "status": "pending"
                                },
                                {
                                    "title": "that applying taxes in blockbased Cart & Checkout of 2 different levels (2 excluded) calculates properly",
                                    "status": "pending"
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
                        "file": "shopper\\/cart-checkout-coupons.spec.js",
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
                        "file": "shopper\\/cart-checkout-restricted-coupons.spec.js",
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
                                },
                                {
                                    "title": "can manage cross-sell products and maximum item quantity",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/checkout-block-coupons.spec.js",
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
                        "file": "shopper\\/checkout-block.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Checkout Block page": [
                                {
                                    "title": "can see empty checkout block page",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows customer to choose available payment methods",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows customer to fill shipping details",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows customer to fill different shipping and billing details",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows customer to fill shipping details and toggle different billing",
                                    "status": "pending"
                                },
                                {
                                    "title": "can choose different shipping types in the checkout",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows guest customer to place an order",
                                    "status": "pending"
                                },
                                {
                                    "title": "allows existing customer to place an order",
                                    "status": "pending"
                                },
                                {
                                    "title": "can create an account during checkout",
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
                        "file": "shopper\\/launch-your-store.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Launch Your Store front end - logged out": [
                                {
                                    "title": "Entire site coming soon mode",
                                    "status": "pending"
                                },
                                {
                                    "title": "Store only coming soon mode",
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
                                    "title": "can see empty customized mini cart, add and remove product, increase to max quantity, calculate tax and see redirection",
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
                                    "title": "allows customer to login and navigate",
                                    "status": "pending"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/order-email-receiving.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Shopper Order Email Receiving": [
                                {
                                    "title": "should receive order email after purchasing an item",
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
                                    "title": "should be able to post a review and see it after",
                                    "status": "pending"
                                },
                                {
                                    "title": "should be able to see product description",
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
                        "file": "shopper\\/shop-products-filter-by-price.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "Filter items in the shop by product price": [
                                {
                                    "title": "filter products by prices on the created page",
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
                        "file": "shopper\\/shop-title-after-deletion.spec.js",
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
                        "file": "shopper\\/wordpress-post.spec.js",
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
                "summary": "412 total, 32 passed, 10 failed, 370 skipped."
            }
        },
        {
            "debug_log": []
        }
    ]
]';
