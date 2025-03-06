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
            "test_summary": "421 total, 375 passed, 3 failed, 43 skipped.",
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
            "test_result_json_extracted": "{EXTRACTED}",
            "ctrf_json_extracted": "{EXTRACTED}",
            "debug_log_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "numFailedTestSuites": 1,
                "numPassedTestSuites": 83,
                "numPendingTestSuites": 21,
                "numTotalTestSuites": 105,
                "numFailedTests": 3,
                "numPassedTests": 375,
                "numPendingTests": 43,
                "numTotalTests": 421,
                "testResults": [
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
                                },
                                {
                                    "title": "Can connect to WooCommerce.com",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "activate-and-setup\\/stats-overview.spec.js",
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
                        "file": "admin-marketing\\/overview.spec.js",
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
                        "file": "admin-tasks\\/payment.spec.js",
                        "status": "failed",
                        "has_pending": false,
                        "tests": {
                            "Payment setup task": [
                                {
                                    "title": "Saving valid bank account transfer details enables the payment method",
                                    "status": "failed"
                                },
                                {
                                    "title": "Can visit the payment setup task from the homescreen if the setup wizard has been skipped",
                                    "status": "failed"
                                },
                                {
                                    "title": "Enabling cash on delivery enables the payment method",
                                    "status": "failed"
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
                        "has_pending": false,
                        "tests": {
                            "Assembler -> Color Pickers": [
                                {
                                    "title": "Color pickers should be displayed",
                                    "status": "passed"
                                },
                                {
                                    "title": "Color palette Slate should be applied",
                                    "status": "passed"
                                },
                                {
                                    "title": "Color picker should be focused when a color is picked",
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
                                },
                                {
                                    "title": "Clicking opt-in new fonts should be available",
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
                        "file": "customize-store\\/assembler\\/full-composability.spec.js",
                        "status": "passed",
                        "has_pending": false,
                        "tests": {
                            "Assembler -> Full composability": [
                                {
                                    "title": "Clicking on \\"Design your homepage\\" should open the Intro sidebar by default",
                                    "status": "passed"
                                },
                                {
                                    "title": "Clicking on a category should open the sidebar for it",
                                    "status": "passed"
                                },
                                {
                                    "title": "Clicking on a pattern should insert it in the preview",
                                    "status": "passed"
                                },
                                {
                                    "title": "Clicking on a pattern should always scroll the page to the inserted pattern",
                                    "status": "passed"
                                },
                                {
                                    "title": "Clicking the \\"Move up\\/down\\" buttons should change the pattern order in the preview",
                                    "status": "passed"
                                },
                                {
                                    "title": "Clicking the \\"Shuffle\\" button on a patterns should replace it for another one",
                                    "status": "passed"
                                },
                                {
                                    "title": "Clicking the \\"Delete\\" button on a pattern should remove it from the preview",
                                    "status": "passed"
                                },
                                {
                                    "title": "Clicking the \\"Add patterns\\" button on the No Blocks view should add a default pattern",
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
                                    "status": "passed"
                                },
                                {
                                    "title": "Should show the \\"Want more patterns?\\" banner with the offline message when the user is offline and tracking is not allowed",
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "customize-store\\/assembler\\/logo-picker\\/logo-picker.spec.js",
                        "status": "passed",
                        "has_pending": false,
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
                                    "title": "The selected image should be visible on the frontend",
                                    "status": "passed"
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
                                    "title": "it shows the \\"non default block theme\\" banner when the theme is a block theme different than TT4 and redirects to the editor",
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
                                    "title": "Clicking on \\"Finish customizing\\" in the assembler should go to the transitional page",
                                    "status": "passed"
                                },
                                {
                                    "title": "Clicking on \\"View store\\" should go to the store home page",
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
                        "file": "merchant\\/create-product-brand.spec.js",
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
                        "file": "merchant\\/lost-password.spec.js",
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
                        "has_pending": true,
                        "tests": {
                            "Product Reviews": [
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
                        "has_pending": true,
                        "tests": {
                            "Variations tab": [],
                            "Variations tab > Create variable products": [
                                {
                                    "title": "can create a variation option and publish the product",
                                    "status": "passed"
                                },
                                {
                                    "title": "can edit a variation",
                                    "status": "pending"
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
                        "has_pending": false,
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
                                    "status": "passed"
                                }
                            ],
                            "can update product attributes": [
                                {
                                    "title": "can update product attributes",
                                    "status": "passed"
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
                        "file": "merchant\\/settings-email.spec.js",
                        "status": "passed",
                        "has_pending": true,
                        "tests": {
                            "WooCommerce Email Settings": [
                                {
                                    "title": "See email preview with a feature flag",
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
                                    "title": "See specific email preview with a feature flag",
                                    "status": "passed"
                                },
                                {
                                    "title": "See email image url field with a feature flag",
                                    "status": "passed"
                                },
                                {
                                    "title": "Choose image in email image url field",
                                    "status": "passed"
                                },
                                {
                                    "title": "See new color settings with a feature flag",
                                    "status": "passed"
                                },
                                {
                                    "title": "See font family setting with a feature flag",
                                    "status": "passed"
                                },
                                {
                                    "title": "See updated footer text field with a feature flag",
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
                        "has_pending": false,
                        "tests": {
                            "WooCommerce Shipping Settings": [
                                {
                                    "title": "can add shipping methods (free, local, flat rate)",
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
                        "file": "merchant\\/users-create.spec.js",
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
                        "has_pending": true,
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
                                    "status": "pending"
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
                        "has_pending": false,
                        "tests": {
                            "Cart Block Calculate Shipping": [
                                {
                                    "title": "allows customer to calculate Free Shipping in cart block if in Netherlands",
                                    "status": "passed"
                                },
                                {
                                    "title": "allows customer to calculate Flat rate and Local pickup in cart block if in Portugal",
                                    "status": "passed"
                                },
                                {
                                    "title": "should show correct total cart block price after updating quantity",
                                    "status": "passed"
                                },
                                {
                                    "title": "should show correct total cart block price with 2 different products and flat rate\\/local pickup",
                                    "status": "passed"
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
                        "has_pending": false,
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
                                    "status": "passed"
                                }
                            ]
                        }
                    },
                    {
                        "file": "shopper\\/cart-checkout-calculate-tax.spec.js",
                        "status": "passed",
                        "has_pending": false,
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
                                    "status": "passed"
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
                                    "status": "passed"
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
                        "has_pending": true,
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
                            "Launch Your Store front end - logged out": [],
                            "Launch Your Store front end - logged out > Block Theme (Twenty Twenty Four)": [
                                {
                                    "title": "Entire site coming soon mode (function () { [native code] })",
                                    "status": "passed"
                                },
                                {
                                    "title": "Store only coming soon mode (function () { [native code] })",
                                    "status": "passed"
                                }
                            ],
                            "Launch Your Store front end - logged out > Classic Theme (Storefront)": [
                                {
                                    "title": "Entire site coming soon mode (function () { [native code] })",
                                    "status": "passed"
                                },
                                {
                                    "title": "Store only coming soon mode (function () { [native code] })",
                                    "status": "passed"
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
                        "has_pending": true,
                        "tests": {
                            "Grouped Product Page": [
                                {
                                    "title": "should be able to add grouped products to the cart",
                                    "status": "passed"
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
                                    "status": "passed"
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
                        "file": "shopper\\/shop-search-browse-sort.spec.js",
                        "status": "passed",
                        "has_pending": true,
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
                                    "status": "pending"
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
                    }
                ],
                "summary": "421 total, 375 passed, 3 failed, 43 skipped."
            }
        },
        {
            "ctrf_json": {
                "results": {
                    "tool": {
                        "name": "playwright"
                    },
                    "summary": {
                        "tests": 421,
                        "passed": 375,
                        "failed": 3,
                        "pending": 0,
                        "skipped": 43,
                        "other": 0,
                        "start": 1111111111,
                        "stop": 2222222222,
                        "suites": 0
                    },
                    "tests": [
                        {
                            "name": "Can complete the core profiler skipping extension install",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/core-profiler.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > activate-and-setup\\/core-profiler.spec.js > Store owner can complete the core profiler",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "Can complete the core profiler installing default extensions",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/core-profiler.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > activate-and-setup\\/core-profiler.spec.js > Store owner can complete the core profiler",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "Can click skip guided setup",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/core-profiler.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Confirm that the store is in coming soon mode after skipping the core profiler",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > activate-and-setup\\/core-profiler.spec.js > Store owner can skip the core profiler",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Can connect to WooCommerce.com",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/core-profiler.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Go to WC Home and make sure the total sales is visible",
                                    "status": "passed"
                                },
                                {
                                    "name": "Go to the extensions tab and connect store",
                                    "status": "passed"
                                },
                                {
                                    "name": "Check that we are sent to wp.com",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > activate-and-setup\\/core-profiler.spec.js > Store owner can skip the core profiler",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Can access Analytics Reports from Stats Overview",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/stats-overview.spec.js",
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
                            "suite": "ui > activate-and-setup\\/stats-overview.spec.js > WooCommerce Home",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "a user should see 3 sections by default - Performance, Charts, and Leaderboards",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/analytics-overview.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Send GET request to get the current user id",
                                    "status": "passed"
                                },
                                {
                                    "name": "Send POST request to reset all sections",
                                    "status": "passed"
                                },
                                {
                                    "name": "Assert response status is OK",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify that sections were reset",
                                    "status": "passed"
                                },
                                {
                                    "name": "Initialize locators",
                                    "status": "passed"
                                },
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
                                },
                                {
                                    "name": "Send POST request to reset all sections",
                                    "status": "passed"
                                },
                                {
                                    "name": "Assert response status is OK",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify that sections were reset",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > admin-analytics\\/analytics-overview.spec.js > Analytics pages",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should allow a user to remove a section",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                                },
                                {
                                    "name": "Send POST request to reset all sections",
                                    "status": "passed"
                                },
                                {
                                    "name": "Assert response status is OK",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify that sections were reset",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > admin-analytics\\/analytics-overview.spec.js > Analytics pages",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should allow a user to add a section back in",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                                    "name": "Assert response status is OK",
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
                                },
                                {
                                    "name": "Send POST request to reset all sections",
                                    "status": "passed"
                                },
                                {
                                    "name": "Assert response status is OK",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify that sections were reset",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > admin-analytics\\/analytics-overview.spec.js > Analytics pages",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should not display move up for the top, or move down for the bottom section",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                                },
                                {
                                    "name": "Send POST request to reset all sections",
                                    "status": "passed"
                                },
                                {
                                    "name": "Assert response status is OK",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify that sections were reset",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > admin-analytics\\/analytics-overview.spec.js > Analytics pages > moving sections",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should allow a user to move a section down",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                                },
                                {
                                    "name": "Send POST request to reset all sections",
                                    "status": "passed"
                                },
                                {
                                    "name": "Assert response status is OK",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify that sections were reset",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > admin-analytics\\/analytics-overview.spec.js > Analytics pages > moving sections",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should allow a user to move a section up",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                                },
                                {
                                    "name": "Send POST request to reset all sections",
                                    "status": "passed"
                                },
                                {
                                    "name": "Assert response status is OK",
                                    "status": "passed"
                                },
                                {
                                    "name": "Verify that sections were reset",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > admin-analytics\\/analytics-overview.spec.js > Analytics pages > moving sections",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "A user can view the Overview page without it crashing",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/analytics.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > admin-analytics\\/analytics.spec.js > Analytics pages",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "A user can view the Products page without it crashing",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/analytics.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > admin-analytics\\/analytics.spec.js > Analytics pages",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "A user can view the Revenue page without it crashing",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/analytics.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > admin-analytics\\/analytics.spec.js > Analytics pages",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "A user can view the Orders page without it crashing",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/analytics.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > admin-analytics\\/analytics.spec.js > Analytics pages",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "A user can view the Variations page without it crashing",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/analytics.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > admin-analytics\\/analytics.spec.js > Analytics pages",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "A user can view the Categories page without it crashing",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/analytics.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > admin-analytics\\/analytics.spec.js > Analytics pages",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "A user can view the Coupons page without it crashing",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/analytics.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > admin-analytics\\/analytics.spec.js > Analytics pages",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "A user can view the Taxes page without it crashing",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/analytics.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > admin-analytics\\/analytics.spec.js > Analytics pages",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "A user can view the Downloads page without it crashing",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/analytics.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > admin-analytics\\/analytics.spec.js > Analytics pages",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "A user can view the Stock page without it crashing",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/analytics.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > admin-analytics\\/analytics.spec.js > Analytics pages",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "A user can view the Settings page without it crashing",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/analytics.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > admin-analytics\\/analytics.spec.js > Analytics pages",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/overview.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > admin-marketing\\/overview.spec.js > Marketing page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Learning section can be expanded",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/overview.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > admin-marketing\\/overview.spec.js > Marketing page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Saving valid bank account transfer details enables the payment method",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "Error: \\u001b[31mTimed out 30000ms waiting for \\u001b[39m\\u001b[2mexpect(\\u001b[22m\\u001b[31mlocator\\u001b[39m\\u001b[2m).\\u001b[22mtoHaveClass\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m)\\u001b[22m\\n\\nLocator: locator(\'\\/\\/tr[@data-gateway_id=\\"bacs\\"]\\/td[@class=\\"status\\"]\\/a\')\\nExpected string: \\u001b[32m\\"wc-payment-gateway-method-toggle-enabled\\"\\u001b[39m\\nReceived: <element(s) not found>\\nCall log:\\n\\u001b[2m  - expect.toHaveClass with timeout 30000ms\\u001b[22m\\n\\u001b[2m  - waiting for locator(\'\\/\\/tr[@data-gateway_id=\\"bacs\\"]\\/td[@class=\\"status\\"]\\/a\')\\u001b[22m\\n",
                            "trace": "Error: \\u001b[31mTimed out 30000ms waiting for \\u001b[39m\\u001b[2mexpect(\\u001b[22m\\u001b[31mlocator\\u001b[39m\\u001b[2m).\\u001b[22mtoHaveClass\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m)\\u001b[22m\\n\\nLocator: locator(\'\\/\\/tr[@data-gateway_id=\\"bacs\\"]\\/td[@class=\\"status\\"]\\/a\')\\nExpected string: \\u001b[32m\\"wc-payment-gateway-method-toggle-enabled\\"\\u001b[39m\\nReceived: <element(s) not found>\\nCall log:\\n\\u001b[2m  - expect.toHaveClass with timeout 30000ms\\u001b[22m\\n\\u001b[2m  - waiting for locator(\'\\/\\/tr[@data-gateway_id=\\"bacs\\"]\\/td[@class=\\"status\\"]\\/a\')\\u001b[22m\\n\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/admin-tasks\\/payment.spec.js:95:6",
                            "rawStatus": "failed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/payment.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > admin-tasks\\/payment.spec.js > Payment setup task",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Can visit the payment setup task from the homescreen if the setup wizard has been skipped",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "Error: \\u001b[31mTimed out 30000ms waiting for \\u001b[39m\\u001b[2mexpect(\\u001b[22m\\u001b[31mlocator\\u001b[39m\\u001b[2m).\\u001b[22mtoHaveText\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m)\\u001b[22m\\n\\nLocator: locator(\'.woocommerce-layout__header-wrapper > h1\')\\nExpected string: \\u001b[32m\\"Get paid\\"\\u001b[39m\\nReceived string: \\u001b[31m\\"WooCommerce Settings\\"\\u001b[39m\\nCall log:\\n\\u001b[2m  - expect.toHaveText with timeout 30000ms\\u001b[22m\\n\\u001b[2m  - waiting for locator(\'.woocommerce-layout__header-wrapper > h1\')\\u001b[22m\\n\\u001b[2m    30 \\u00d7 locator resolved to <h1 data-wp-c16t=\\"true\\" data-wp-component=\\"Text\\" class=\\"components-truncate components-text woocommerce-layout__header-heading woocommerce-layout__header-left-align css-bc6pwz e19lxcc00\\">\\u2026<\\/h1>\\u001b[22m\\n\\u001b[2m       - unexpected value \\"WooCommerce Settings\\"\\u001b[22m\\n",
                            "trace": "Error: \\u001b[31mTimed out 30000ms waiting for \\u001b[39m\\u001b[2mexpect(\\u001b[22m\\u001b[31mlocator\\u001b[39m\\u001b[2m).\\u001b[22mtoHaveText\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m)\\u001b[22m\\n\\nLocator: locator(\'.woocommerce-layout__header-wrapper > h1\')\\nExpected string: \\u001b[32m\\"Get paid\\"\\u001b[39m\\nReceived string: \\u001b[31m\\"WooCommerce Settings\\"\\u001b[39m\\nCall log:\\n\\u001b[2m  - expect.toHaveText with timeout 30000ms\\u001b[22m\\n\\u001b[2m  - waiting for locator(\'.woocommerce-layout__header-wrapper > h1\')\\u001b[22m\\n\\u001b[2m    30 \\u00d7 locator resolved to <h1 data-wp-c16t=\\"true\\" data-wp-component=\\"Text\\" class=\\"components-truncate components-text woocommerce-layout__header-heading woocommerce-layout__header-left-align css-bc6pwz e19lxcc00\\">\\u2026<\\/h1>\\u001b[22m\\n\\u001b[2m       - unexpected value \\"WooCommerce Settings\\"\\u001b[22m\\n\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/admin-tasks\\/payment.spec.js:108:6",
                            "rawStatus": "failed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/payment.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > admin-tasks\\/payment.spec.js > Payment setup task",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Enabling cash on delivery enables the payment method",
                            "status": "failed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "message": "Error: \\u001b[31mTimed out 30000ms waiting for \\u001b[39m\\u001b[2mexpect(\\u001b[22m\\u001b[31mlocator\\u001b[39m\\u001b[2m).\\u001b[22mtoHaveClass\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m)\\u001b[22m\\n\\nLocator: locator(\'\\/\\/tr[@data-gateway_id=\\"cod\\"]\\/td[@class=\\"status\\"]\\/a\')\\nExpected string: \\u001b[32m\\"wc-payment-gateway-method-toggle-enabled\\"\\u001b[39m\\nReceived: <element(s) not found>\\nCall log:\\n\\u001b[2m  - expect.toHaveClass with timeout 30000ms\\u001b[22m\\n\\u001b[2m  - waiting for locator(\'\\/\\/tr[@data-gateway_id=\\"cod\\"]\\/td[@class=\\"status\\"]\\/a\')\\u001b[22m\\n",
                            "trace": "Error: \\u001b[31mTimed out 30000ms waiting for \\u001b[39m\\u001b[2mexpect(\\u001b[22m\\u001b[31mlocator\\u001b[39m\\u001b[2m).\\u001b[22mtoHaveClass\\u001b[2m(\\u001b[22m\\u001b[32mexpected\\u001b[39m\\u001b[2m)\\u001b[22m\\n\\nLocator: locator(\'\\/\\/tr[@data-gateway_id=\\"cod\\"]\\/td[@class=\\"status\\"]\\/a\')\\nExpected string: \\u001b[32m\\"wc-payment-gateway-method-toggle-enabled\\"\\u001b[39m\\nReceived: <element(s) not found>\\nCall log:\\n\\u001b[2m  - expect.toHaveClass with timeout 30000ms\\u001b[22m\\n\\u001b[2m  - waiting for locator(\'\\/\\/tr[@data-gateway_id=\\"cod\\"]\\/td[@class=\\"status\\"]\\/a\')\\u001b[22m\\n\\n    at \\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/woo-e2e\\/tests\\/admin-tasks\\/payment.spec.js:151:6",
                            "rawStatus": "failed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/payment.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > admin-tasks\\/payment.spec.js > Payment setup task",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Webhook cannot be bulk deleted without nonce",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/webhooks.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > admin-tasks\\/webhooks.spec.js > Manage webhooks",
                            "extra": {
                                "annotations": []
                            }
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
                            "suite": "ui > basic.spec.js",
                            "extra": {
                                "annotations": []
                            }
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
                            "suite": "ui > basic.spec.js",
                            "extra": {
                                "annotations": []
                            }
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
                            "suite": "ui > basic.spec.js",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Color pickers should be displayed",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/color-picker.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/color-picker.spec.js > Assembler -> Color Pickers",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Color palette Slate should be applied",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/color-picker.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/color-picker.spec.js > Assembler -> Color Pickers",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Color picker should be focused when a color is picked",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/color-picker.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/color-picker.spec.js > Assembler -> Color Pickers",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Font pickers should be displayed",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/font-picker.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/font-picker.spec.js > Assembler -> Font Picker",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Picking a font should trigger an update of fonts on the site preview",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/font-picker.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/font-picker.spec.js > Assembler -> Font Picker",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Font pickers should be focused when a font is picked",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/font-picker.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/font-picker.spec.js > Assembler -> Font Picker",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Selected font palette should be applied on the frontend",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/font-picker.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/font-picker.spec.js > Assembler -> Font Picker",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Clicking opt-in new fonts should be available",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/font-picker.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/font-picker.spec.js > Assembler -> Font Picker",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Available footers should be displayed",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/footer.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/footer.spec.js > Assembler -> Footers",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "The selected footer should be focused when is clicked",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/footer.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/footer.spec.js > Assembler -> Footers",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "The selected footer should be applied on the frontend",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/footer.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/footer.spec.js > Assembler -> Footers",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Picking a footer should trigger an update on the site preview",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/footer.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/footer.spec.js > Assembler -> Footers",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Clicking on \\"Design your homepage\\" should open the Intro sidebar by default",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/full-composability.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/full-composability.spec.js > Assembler -> Full composability",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Clicking on a category should open the sidebar for it",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/full-composability.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/full-composability.spec.js > Assembler -> Full composability",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Clicking on a pattern should insert it in the preview",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/full-composability.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/full-composability.spec.js > Assembler -> Full composability",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Clicking on a pattern should always scroll the page to the inserted pattern",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/full-composability.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/full-composability.spec.js > Assembler -> Full composability",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Clicking the \\"Move up\\/down\\" buttons should change the pattern order in the preview",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/full-composability.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/full-composability.spec.js > Assembler -> Full composability",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Clicking the \\"Shuffle\\" button on a patterns should replace it for another one",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/full-composability.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/full-composability.spec.js > Assembler -> Full composability",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Clicking the \\"Delete\\" button on a pattern should remove it from the preview",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/full-composability.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/full-composability.spec.js > Assembler -> Full composability",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Clicking the \\"Add patterns\\" button on the No Blocks view should add a default pattern",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/full-composability.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/full-composability.spec.js > Assembler -> Full composability",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Available headers should be displayed",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/header.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/header.spec.js > Assembler -> headers",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "The selected header should be focused when is clicked",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/header.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/header.spec.js > Assembler -> headers",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "The selected header should be applied on the frontend",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/header.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/header.spec.js > Assembler -> headers",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Picking a header should trigger an update on the site preview",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/header.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/header.spec.js > Assembler -> headers",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "The selected homepage should be focused when is clicked",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/homepage.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/homepage.spec.js > Assembler -> Homepage",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "The selected homepage should be visible on the site preview",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/homepage.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/homepage.spec.js > Assembler -> Homepage",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "Selected homepage should be applied on the frontend",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/homepage.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/homepage.spec.js > Assembler -> Homepage",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "Should show the \\"Want more patterns?\\" banner with the Opt-in message when tracking is not allowed",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/homepage.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/homepage.spec.js > Homepage tracking banner",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Should show the \\"Want more patterns?\\" banner with the offline message when the user is offline and tracking is not allowed",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/homepage.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/homepage.spec.js > Homepage tracking banner",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Logo Picker should be empty initially",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/logo-picker.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/logo-picker\\/logo-picker.spec.js > Assembler -> Logo Picker",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Selecting an image should update the site preview",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/logo-picker.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/logo-picker\\/logo-picker.spec.js > Assembler -> Logo Picker",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Changing the image width should update the site preview and the frontend",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/logo-picker.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/logo-picker\\/logo-picker.spec.js > Assembler -> Logo Picker",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Clicking the Delete button should remove the selected image",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/logo-picker.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/logo-picker\\/logo-picker.spec.js > Assembler -> Logo Picker",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Clicking the replace image should open the media gallery",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/logo-picker.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/logo-picker\\/logo-picker.spec.js > Assembler -> Logo Picker",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Logo should be visible after header update",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/logo-picker.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/logo-picker\\/logo-picker.spec.js > Assembler -> Logo Picker",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "The selected image should be visible on the frontend",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/logo-picker.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler\\/logo-picker\\/logo-picker.spec.js > Assembler -> Logo Picker",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Can not access the Assembler Hub page when the theme is not customized",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/assembler-hub.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler-hub.spec.js > Store owner can view Assembler Hub for store customization",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Can access the Assembler Hub page when the theme is already customized",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/assembler-hub.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler-hub.spec.js > Store owner can view Assembler Hub for store customization",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Visiting change header should show a list of block patterns to choose from",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/assembler-hub.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/assembler-hub.spec.js > Store owner can view Assembler Hub for store customization",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "it shows the \\"offline banner\\" when the network is offline",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/intro.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/intro.spec.js > Store owner can view the Intro page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "it shows the \\"no AI\\" banner on Core when the task is not completed",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/intro.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/intro.spec.js > Store owner can view the Intro page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "it shows the \\"no AI customize theme\\" banner when the task is completed",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/intro.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/intro.spec.js > Store owner can view the Intro page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "it shows the \\"non default block theme\\" banner when the theme is a block theme different than TT4 and redirects to the editor",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/intro.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/intro.spec.js > Store owner can view the Intro page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "clicking on \\"Go to the Customizer\\" with a classic theme should go to the customizer",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/intro.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/intro.spec.js > Store owner can view the Intro page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should display loading screen and steps on first run",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/loading-screen.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/loading-screen\\/loading-screen.spec.js > Assembler - Loading Page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should redirect to intro page in case of errors",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/loading-screen.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/loading-screen\\/loading-screen.spec.js > Assembler - Loading Page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Accessing the transitional page when the CYS flow is not completed should redirect to the Intro page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/transitional.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/transitional.spec.js > Store owner can view the Transitional page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Clicking on \\"Finish customizing\\" in the assembler should go to the transitional page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/transitional.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/transitional.spec.js > Store owner can view the Transitional page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Clicking on \\"View store\\" should go to the store home page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/transitional.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/transitional.spec.js > Store owner can view the Transitional page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Clicking on \\"Share feedback\\" should open the survey modal",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/transitional.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > customize-store\\/transitional.spec.js > Store owner can view the Transitional page",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/command-palette.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/command-palette.spec.js",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/command-palette.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/command-palette.spec.js",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/command-palette.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/command-palette.spec.js",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/command-palette.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/command-palette.spec.js",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/command-palette.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/command-palette.spec.js",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/command-palette.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/command-palette.spec.js",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/command-palette.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/command-palette.spec.js",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create new fixedCart coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > merchant\\/create-coupon.spec.js > Coupon management",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create new fixedProduct coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > merchant\\/create-coupon.spec.js > Coupon management",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create new percentage coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > merchant\\/create-coupon.spec.js > Coupon management",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create new expiryDate coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > merchant\\/create-coupon.spec.js > Coupon management",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create new freeShipping coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > merchant\\/create-coupon.spec.js > Coupon management",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create a simple guest order",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-order.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/create-order.spec.js > WooCommerce Orders > Add new order",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "can create an order for an existing customer",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-order.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/create-order.spec.js > WooCommerce Orders > Add new order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create new order",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-order.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/create-order.spec.js > WooCommerce Orders > Add new order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create new complex order with multiple product types & tax classes",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-order.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/create-order.spec.js > WooCommerce Orders > Add new order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create new page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-page.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/create-page.spec.js > Can create a new page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create new post",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-post.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/create-post.spec.js > Can create a new post",
                            "extra": {
                                "annotations": []
                            }
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
                            "suite": "ui > merchant\\/create-product-brand.spec.js",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create new minimumSpend coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > merchant\\/create-restricted-coupons.spec.js > Restricted coupon management",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create new maximumSpend coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > merchant\\/create-restricted-coupons.spec.js > Restricted coupon management",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create new individualUse coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > merchant\\/create-restricted-coupons.spec.js > Restricted coupon management",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create new excludeSaleItems coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > merchant\\/create-restricted-coupons.spec.js > Restricted coupon management",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create new productCategories coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > merchant\\/create-restricted-coupons.spec.js > Restricted coupon management",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create new excludeProductCategories coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > merchant\\/create-restricted-coupons.spec.js > Restricted coupon management",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create new excludeProductBrands coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > merchant\\/create-restricted-coupons.spec.js > Restricted coupon management",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create new products coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > merchant\\/create-restricted-coupons.spec.js > Restricted coupon management",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create new excludeProducts coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > merchant\\/create-restricted-coupons.spec.js > Restricted coupon management",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create new allowedEmails coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > merchant\\/create-restricted-coupons.spec.js > Restricted coupon management",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create new usageLimitPerCoupon coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > merchant\\/create-restricted-coupons.spec.js > Restricted coupon management",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create new usageLimitPerUser coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > merchant\\/create-restricted-coupons.spec.js > Restricted coupon management",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add shipping classes",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-shipping-classes.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/create-shipping-classes.spec.js > Merchant can add shipping classes",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "add shipping zone for Mayne Island with free Local pickup",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-shipping-zones.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/create-shipping-zones.spec.js > WooCommerce Shipping Settings - Add new shipping zone",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "add shipping zone for British Columbia with Free shipping",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-shipping-zones.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/create-shipping-zones.spec.js > WooCommerce Shipping Settings - Add new shipping zone",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "add shipping zone for Canada with Flat rate",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-shipping-zones.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/create-shipping-zones.spec.js > WooCommerce Shipping Settings - Add new shipping zone",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "add shipping zone with region and then delete the region",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-shipping-zones.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/create-shipping-zones.spec.js > WooCommerce Shipping Settings - Add new shipping zone",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "add and delete shipping method",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-shipping-zones.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/create-shipping-zones.spec.js > WooCommerce Shipping Settings - Add new shipping zone",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "allows customer to benefit from a free Local pickup if on Mayne Island",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-shipping-zones.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/create-shipping-zones.spec.js > Verifies shipping options from customer perspective",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "allows customer to benefit from a free Free shipping if in BC",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-shipping-zones.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/create-shipping-zones.spec.js > Verifies shipping options from customer perspective",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "allows customer to pay for a Flat rate shipping method",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-shipping-zones.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/create-shipping-zones.spec.js > Verifies shipping options from customer perspective",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can insert all WooCommerce blocks into page",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-woocommerce-blocks.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/create-woocommerce-blocks.spec.js > Add WooCommerce Blocks Into Page",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "can insert WooCommerce patterns into page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-woocommerce-patterns.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Insert Hero Product 3 Split pattern",
                                    "status": "passed"
                                },
                                {
                                    "name": "Insert Featured Category Cover Image pattern",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > merchant\\/create-woocommerce-patterns.spec.js > Add WooCommerce Patterns Into Page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Merchant can view a list of all customers, filter and download",
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
                                    "name": "Go to the customers reports page",
                                    "status": "passed"
                                },
                                {
                                    "name": "Check that the customers are displayed in the list",
                                    "status": "passed"
                                },
                                {
                                    "name": "Check that the customer list can be filtered by first name",
                                    "status": "passed"
                                },
                                {
                                    "name": "Hide and display columns",
                                    "status": "passed"
                                },
                                {
                                    "name": "Download the customer list",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > merchant\\/customer-list.spec.js > Merchant > Customer List",
                            "extra": {
                                "annotations": []
                            }
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
                            "suite": "ui > merchant\\/customer-list.spec.js > Merchant > Customer List",
                            "extra": {
                                "annotations": []
                            }
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
                            "suite": "ui > merchant\\/customer-list.spec.js > Merchant > Customer List",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should show the customer payment page link on a pending order",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/customer-payment-page.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/customer-payment-page.spec.js > WooCommerce Merchant Flow: Orders > Customer Payment Page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should load the customer payment page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/customer-payment-page.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/customer-payment-page.spec.js > WooCommerce Merchant Flow: Orders > Customer Payment Page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can pay for the order through the customer payment page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/customer-payment-page.spec.js",
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
                            "suite": "ui > merchant\\/customer-payment-page.spec.js > WooCommerce Merchant Flow: Orders > Customer Payment Page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Entire site coming soon mode frontend",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/launch-your-store.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/launch-your-store.spec.js > Launch Your Store - logged in",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Store only coming soon mode frontend",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/launch-your-store.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/launch-your-store.spec.js > Launch Your Store - logged in",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Site visibility settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/launch-your-store.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/launch-your-store.spec.js > Launch Your Store - logged in",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Homescreen badge coming soon store only",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/launch-your-store.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/launch-your-store.spec.js > Launch Your Store - logged in",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Homescreen badge coming soon entire store",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/launch-your-store.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/launch-your-store.spec.js > Launch Your Store - logged in",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Homescreen badge live",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/launch-your-store.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/launch-your-store.spec.js > Launch Your Store - logged in",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can visit the lost password page from the login page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/lost-password.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/lost-password.spec.js > Can go to lost password page and submit the form",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can submit the lost password form",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/lost-password.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/lost-password.spec.js > Can go to lost password page and submit the form",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can bulk update order status",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-bulk-edit.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-bulk-edit.spec.js > Bulk edit orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can apply a coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-coupon.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-coupon.spec.js > WooCommerce Orders > Apply Coupon",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can remove a coupon",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-coupon.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-coupon.spec.js > WooCommerce Orders > Apply Coupon",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can view single order",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-edit.spec.js > Edit order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update order status",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-edit.spec.js > Edit order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update order status to cancelled",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-edit.spec.js > Edit order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update order details",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-edit.spec.js > Edit order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add and delete order notes",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-edit.spec.js > Edit order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can load billing and shipping details",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.js",
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
                            "suite": "ui > merchant\\/order-edit.spec.js > Edit order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can copy billing address to shipping address",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.js",
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
                            "suite": "ui > merchant\\/order-edit.spec.js > Edit order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add downloadable product permissions to order without product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-edit.spec.js > Edit order > Downloadable product permissions",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add downloadable product permissions to order with product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-edit.spec.js > Edit order > Downloadable product permissions",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can edit downloadable product permissions",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-edit.spec.js > Edit order > Downloadable product permissions",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can revoke downloadable product permissions",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-edit.spec.js > Edit order > Downloadable product permissions",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should not allow downloading a product if download attempts are exceeded",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-edit.spec.js > Edit order > Downloadable product permissions",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should not allow downloading a product if expiration date has passed",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-edit.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-edit.spec.js > Edit order > Downloadable product permissions",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can receive new order email",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-emails.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-emails.spec.js > Merchant > Order Action emails received",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can receive completed email",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-emails.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-emails.spec.js > Merchant > Order Action emails received",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can receive cancelled order email",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-emails.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-emails.spec.js > Merchant > Order Action emails received",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can resend new order notification",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-emails.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-emails.spec.js > Merchant > Order Action emails received",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can email invoice\\/order details to customer",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-emails.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-emails.spec.js > Merchant > Order Action emails received",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can issue a refund by quantity",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-refund.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-refund.spec.js > WooCommerce Orders > Refund an order",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can delete an issued refund",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-refund.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-refund.spec.js > WooCommerce Orders > Refund an order",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "can update order after refunding item without automatic stock adjustment",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-refund.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-refund.spec.js > WooCommerce Orders > Refund and restock an order item",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search for order by order id",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-search.spec.js > WooCommerce Orders > Search orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search for order containing \\"James\\" as the billing first name",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-search.spec.js > WooCommerce Orders > Search orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search for order containing \\"Doe\\" as the billing last name",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-search.spec.js > WooCommerce Orders > Search orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search for order containing \\"Automattic\\" as the billing company name",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-search.spec.js > WooCommerce Orders > Search orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search for order containing \\"address1\\" as the billing first address",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-search.spec.js > WooCommerce Orders > Search orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search for order containing \\"address2\\" as the billing second address",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-search.spec.js > WooCommerce Orders > Search orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search for order containing \\"San Francisco\\" as the billing city name",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-search.spec.js > WooCommerce Orders > Search orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search for order containing \\"94107\\" as the billing post code",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-search.spec.js > WooCommerce Orders > Search orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search for order containing \\"john.doe.ordersearch@example.com\\" as the billing email",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [
                                "@example"
                            ],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-search.spec.js > WooCommerce Orders > Search orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search for order containing \\"123456789\\" as the billing phone",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-search.spec.js > WooCommerce Orders > Search orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search for order containing \\"CA\\" as the billing state",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-search.spec.js > WooCommerce Orders > Search orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search for order containing \\"Tim\\" as the shipping first name",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-search.spec.js > WooCommerce Orders > Search orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search for order containing \\"Clark\\" as the shipping last name",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-search.spec.js > WooCommerce Orders > Search orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search for order containing \\"Oxford Ave\\" as the shipping first address",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-search.spec.js > WooCommerce Orders > Search orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search for order containing \\"Linwood Ave\\" as the shipping second address",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-search.spec.js > WooCommerce Orders > Search orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search for order containing \\"Buffalo\\" as the shipping city name",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-search.spec.js > WooCommerce Orders > Search orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search for order containing \\"14201\\" as the shipping post code",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-search.spec.js > WooCommerce Orders > Search orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can search for order containing \\"Wanted Product\\" as the shipping item name",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-search.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-search.spec.js > WooCommerce Orders > Search orders",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should filter by All",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-status-filter.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-status-filter.spec.js > WooCommerce Orders > Filter Order by Status",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should filter by Pending payment",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-status-filter.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-status-filter.spec.js > WooCommerce Orders > Filter Order by Status",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should filter by Processing",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-status-filter.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-status-filter.spec.js > WooCommerce Orders > Filter Order by Status",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should filter by On hold",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-status-filter.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-status-filter.spec.js > WooCommerce Orders > Filter Order by Status",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should filter by Completed",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-status-filter.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-status-filter.spec.js > WooCommerce Orders > Filter Order by Status",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should filter by Cancelled",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-status-filter.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-status-filter.spec.js > WooCommerce Orders > Filter Order by Status",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should filter by Refunded",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-status-filter.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-status-filter.spec.js > WooCommerce Orders > Filter Order by Status",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should filter by Failed",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-status-filter.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/order-status-filter.spec.js > WooCommerce Orders > Filter Order by Status",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Can load Home",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/page-loads.spec.js > WooCommerce Page Load > Load WooCommerce sub pages",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "Can load Orders",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/page-loads.spec.js > WooCommerce Page Load > Load WooCommerce sub pages",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "Can load Customers",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/page-loads.spec.js > WooCommerce Page Load > Load WooCommerce sub pages",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "Can load Reports",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/page-loads.spec.js > WooCommerce Page Load > Load WooCommerce sub pages",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "Can load Settings",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/page-loads.spec.js > WooCommerce Page Load > Load WooCommerce sub pages",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "Can load Status",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/page-loads.spec.js > WooCommerce Page Load > Load WooCommerce sub pages",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "Can load All Products",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/page-loads.spec.js > WooCommerce Page Load > Load Products sub pages",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "Can load Add New",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/page-loads.spec.js > WooCommerce Page Load > Load Products sub pages",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "Can load Categories",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/page-loads.spec.js > WooCommerce Page Load > Load Products sub pages",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "Can load Tags",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/page-loads.spec.js > WooCommerce Page Load > Load Products sub pages",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "Can load Attributes",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/page-loads.spec.js > WooCommerce Page Load > Load Products sub pages",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "Can load Overview",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/page-loads.spec.js > WooCommerce Page Load > Load Marketing sub pages",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "Can load Coupons",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/page-loads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/page-loads.spec.js > WooCommerce Page Load > Load Marketing sub pages",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "can create a simple virtual product",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-create-simple.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/product-create-simple.spec.js",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "can create a simple non virtual product",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-create-simple.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/product-create-simple.spec.js",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "can create a simple downloadable product",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-create-simple.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/product-create-simple.spec.js",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
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
                            "filePath": "\\/normalized\\/path\\/product-delete.spec.js",
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
                            "suite": "ui > merchant\\/product-delete.spec.js > Products > Delete Product",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/product-delete.spec.js",
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
                            "suite": "ui > merchant\\/product-delete.spec.js > Products > Delete Product",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/product-delete.spec.js",
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
                            "suite": "ui > merchant\\/product-delete.spec.js > Products > Delete Product",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/product-edit.spec.js",
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
                            "suite": "ui > merchant\\/product-edit.spec.js",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/product-edit.spec.js",
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
                            "suite": "ui > merchant\\/product-edit.spec.js",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can restore regular price when bulk editing products",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-edit.spec.js",
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
                            "suite": "ui > merchant\\/product-edit.spec.js",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can decrease the sale price if the product was not previously in sale when bulk editing products",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-edit.spec.js",
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
                            "suite": "ui > merchant\\/product-edit.spec.js",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "increasing the sale price from 0 does not change the sale price when bulk editing products",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-edit.spec.js",
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
                            "suite": "ui > merchant\\/product-edit.spec.js",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/product-images.spec.js",
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
                            "suite": "ui > merchant\\/product-images.spec.js > Products > Product Images",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/product-images.spec.js",
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
                            "suite": "ui > merchant\\/product-images.spec.js > Products > Product Images",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/product-images.spec.js",
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
                            "suite": "ui > merchant\\/product-images.spec.js > Products > Product Images",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/product-images.spec.js",
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
                            "suite": "ui > merchant\\/product-images.spec.js > Products > Product Images",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/product-images.spec.js",
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
                            "suite": "ui > merchant\\/product-images.spec.js > Products > Product Images",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should show error message if you go without providing CSV file",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-import-csv.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/product-import-csv.spec.js > Import Products from a CSV file",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can upload the CSV file and import products",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-import-csv.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/product-import-csv.spec.js > Import Products from a CSV file",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can override the existing products via CSV import",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-import-csv.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/product-import-csv.spec.js > Import Products from a CSV file",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "add up-sells",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-linked-products.spec.js",
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
                            "suite": "ui > merchant\\/product-linked-products.spec.js > Products > Related products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "remove up-sells",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-linked-products.spec.js",
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
                            "suite": "ui > merchant\\/product-linked-products.spec.js > Products > Related products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "add cross-sells",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-linked-products.spec.js",
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
                            "suite": "ui > merchant\\/product-linked-products.spec.js > Products > Related products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "remove cross-sells",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-linked-products.spec.js",
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
                            "suite": "ui > merchant\\/product-linked-products.spec.js > Products > Related products",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/product-reviews.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/product-reviews.spec.js > Product Reviews",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/product-reviews.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/product-reviews.spec.js > Product Reviews",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/product-reviews.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/product-reviews.spec.js > Product Reviews",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/product-reviews.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/product-reviews.spec.js > Product Reviews",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/product-reviews.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/product-reviews.spec.js > Product Reviews",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/product-reviews.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/product-reviews.spec.js > Product Reviews",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/product-reviews.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/product-reviews.spec.js > Product Reviews",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
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
                            "filePath": "\\/normalized\\/path\\/product-reviews.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/product-reviews.spec.js > Product Reviews",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/product-search.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/product-search.spec.js > Products > Search and View a product",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/product-search.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/product-search.spec.js > Products > Search and View a product",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/product-search.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/product-search.spec.js > Products > Search and View a product",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/product-settings.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/product-settings.spec.js > WooCommerce Products > Downloadable Product Settings",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add custom product attributes",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-product-attributes.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Open \\"Edit product\\" page of product id <ID>",
                                    "status": "passed"
                                },
                                {
                                    "name": "Go to the \\"Attributes\\" tab.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Add the attribute \\"Colour\\" with values \\"Red | Green\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Type \\"Colour\\" in the \\"Attribute name\\" input field.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Type the attribute values \\"Red | Green\\".",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect \\"Visible on the product page\\" checkbox to be checked by default",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect \\"Used for variations\\" checkbox to be checked by default",
                                    "status": "passed"
                                },
                                {
                                    "name": "Click \\"Save attributes\\".",
                                    "status": "passed"
                                },
                                {
                                    "name": "Wait for the tour\'s dismissal to be saved",
                                    "status": "passed"
                                },
                                {
                                    "name": "Wait for the loading overlay to disappear.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Click \'Add new\'.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Add the attribute \\"Size\\" with values \\"Small | Medium\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Type \\"Size\\" in the \\"Attribute name\\" input field.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Type the attribute values \\"Small | Medium\\".",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect \\"Visible on the product page\\" checkbox to be checked by default",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect \\"Used for variations\\" checkbox to be checked by default",
                                    "status": "passed"
                                },
                                {
                                    "name": "Click \\"Save attributes\\".",
                                    "status": "passed"
                                },
                                {
                                    "name": "Wait for the tour\'s dismissal to be saved",
                                    "status": "passed"
                                },
                                {
                                    "name": "Wait for the loading overlay to disappear.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Click \'Add new\'.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Add the attribute \\"Logo\\" with values \\"Woo | WordPress\\"",
                                    "status": "passed"
                                },
                                {
                                    "name": "Type \\"Logo\\" in the \\"Attribute name\\" input field.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Type the attribute values \\"Woo | WordPress\\".",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect \\"Visible on the product page\\" checkbox to be checked by default",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect \\"Used for variations\\" checkbox to be checked by default",
                                    "status": "passed"
                                },
                                {
                                    "name": "Click \\"Save attributes\\".",
                                    "status": "passed"
                                },
                                {
                                    "name": "Wait for the tour\'s dismissal to be saved",
                                    "status": "passed"
                                },
                                {
                                    "name": "Wait for the loading overlay to disappear.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Click \\"Update\\".",
                                    "status": "passed"
                                },
                                {
                                    "name": "Go to the \\"Attributes\\" tab.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect \\"Colour\\" to appear on the list of saved attributes, and expand it.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect its details to be saved correctly",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect \\"Size\\" to appear on the list of saved attributes, and expand it.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect its details to be saved correctly",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect \\"Logo\\" to appear on the list of saved attributes, and expand it.",
                                    "status": "passed"
                                },
                                {
                                    "name": "Expect its details to be saved correctly",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > merchant\\/products\\/add-variable-product\\/create-product-attributes.spec.js > Add product attributes",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create a variable product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-variable-product.spec.js",
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
                            "suite": "ui > merchant\\/products\\/add-variable-product\\/create-variable-product.spec.js > Add variable product",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can generate variations from product attributes",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-variations.spec.js",
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
                            "suite": "ui > merchant\\/products\\/add-variable-product\\/create-variations.spec.js > Add variations",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can manually add a variation",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-variations.spec.js",
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
                            "suite": "ui > merchant\\/products\\/add-variable-product\\/create-variations.spec.js > Add variations",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can individually edit variations",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/update-variations.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Create variable product for individual edit test",
                                    "status": "passed"
                                },
                                {
                                    "name": "Create variable product for bulk edit test",
                                    "status": "passed"
                                },
                                {
                                    "name": "Create variable product for \\"delete all\\" test",
                                    "status": "passed"
                                },
                                {
                                    "name": "Create variable product for \\"manage stock\\" test",
                                    "status": "passed"
                                },
                                {
                                    "name": "Create variable product for \\"variation defaults\\" test",
                                    "status": "passed"
                                },
                                {
                                    "name": "Create variable product with 1 variation for \\"remove variation\\" test",
                                    "status": "passed"
                                },
                                {
                                    "name": "Hide variable product tour",
                                    "status": "passed"
                                },
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
                            "suite": "ui > merchant\\/products\\/add-variable-product\\/update-variations.spec.js > Update variations",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can bulk edit variations",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/update-variations.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Create variable product for individual edit test",
                                    "status": "passed"
                                },
                                {
                                    "name": "Create variable product for bulk edit test",
                                    "status": "passed"
                                },
                                {
                                    "name": "Create variable product for \\"delete all\\" test",
                                    "status": "passed"
                                },
                                {
                                    "name": "Create variable product for \\"manage stock\\" test",
                                    "status": "passed"
                                },
                                {
                                    "name": "Create variable product for \\"variation defaults\\" test",
                                    "status": "passed"
                                },
                                {
                                    "name": "Create variable product with 1 variation for \\"remove variation\\" test",
                                    "status": "passed"
                                },
                                {
                                    "name": "Hide variable product tour",
                                    "status": "passed"
                                },
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
                            "suite": "ui > merchant\\/products\\/add-variable-product\\/update-variations.spec.js > Update variations",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can delete all variations",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/update-variations.spec.js",
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
                            "suite": "ui > merchant\\/products\\/add-variable-product\\/update-variations.spec.js > Update variations",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can manage stock levels",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/update-variations.spec.js",
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
                            "suite": "ui > merchant\\/products\\/add-variable-product\\/update-variations.spec.js > Update variations",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can set variation defaults",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/update-variations.spec.js",
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
                            "suite": "ui > merchant\\/products\\/add-variable-product\\/update-variations.spec.js > Update variations",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can remove a variation",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/update-variations.spec.js",
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
                            "suite": "ui > merchant\\/products\\/add-variable-product\\/update-variations.spec.js > Update variations",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create a grouped product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-grouped-product-block-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/products\\/block-editor\\/create-grouped-product-block-editor.spec.js > General tab > Grouped product",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "renders each block without error",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-simple-product-block-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/products\\/block-editor\\/create-simple-product-block-editor.spec.js > General tab > Simple product form",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create a simple product",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-simple-product-block-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/products\\/block-editor\\/create-simple-product-block-editor.spec.js > General tab > Create product",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "can not create a product with duplicated SKU",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-simple-product-block-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/products\\/block-editor\\/create-simple-product-block-editor.spec.js > General tab > Create product",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "can a shopper add the simple product to the cart",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-simple-product-block-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/products\\/block-editor\\/create-simple-product-block-editor.spec.js > General tab > Create product",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "can create a variation option and publish the product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-variable-product-block-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load new product editor, disable tour",
                                    "status": "passed"
                                },
                                {
                                    "name": "Click on General tab, enter product name and summary",
                                    "status": "passed"
                                },
                                {
                                    "name": "Click on Variations tab, add a new attribute",
                                    "status": "passed"
                                },
                                {
                                    "name": "Create global attribute",
                                    "status": "passed"
                                },
                                {
                                    "name": "Add new terms to the attribute",
                                    "status": "passed"
                                },
                                {
                                    "name": "Add prices to variations",
                                    "status": "passed"
                                },
                                {
                                    "name": "Publish the product",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > merchant\\/products\\/block-editor\\/create-variable-product-block-editor.spec.js > Variations tab > Create variable products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can edit a variation",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-variable-product-block-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/products\\/block-editor\\/create-variable-product-block-editor.spec.js > Variations tab > Create variable products",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "can delete a variation",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-variable-product-block-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/products\\/block-editor\\/create-variable-product-block-editor.spec.js > Variations tab > Create variable products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can see variations warning and click the CTA",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-variable-product-block-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/products\\/block-editor\\/create-variable-product-block-editor.spec.js > Variations tab > Create variable products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can see single variation warning and click the CTA",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/create-variable-product-block-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/products\\/block-editor\\/create-variable-product-block-editor.spec.js > Variations tab > Create variable products",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "is hooked up to sidebar \\"Add New\\"",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/disable-block-product-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/products\\/block-editor\\/disable-block-product-editor.spec.js > Disable block product editor",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can be disabled from the header",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/disable-block-product-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/products\\/block-editor\\/disable-block-product-editor.spec.js > Disable block product editor",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "can be disabled from settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/disable-block-product-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/products\\/block-editor\\/disable-block-product-editor.spec.js > Disable block product editor",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "is not hooked up to sidebar \\"Add New\\"",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/enable-block-product-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/products\\/block-editor\\/enable-block-product-editor.spec.js > Enable block product editor > Enabled",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can enable the block product editor",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/enable-block-product-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/products\\/block-editor\\/enable-block-product-editor.spec.js > Enable block product editor > Enabled",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create a product with linked products",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/linked-product-tab-product-block-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/products\\/block-editor\\/linked-product-tab-product-block-editor.spec.js > General tab > Linked product",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "can create a simple product with categories, tags and with password required",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/organization-tab-product-block-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/products\\/block-editor\\/organization-tab-product-block-editor.spec.js > General tab > Create product - Organization tab",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "add local attribute (with terms) to the Product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-attributes-block-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "go to product editor -> Organization tab -> Click on `Add new`",
                                    "status": "passed"
                                },
                                {
                                    "name": "create local attributes with terms",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify attributes in product editor",
                                    "status": "passed"
                                },
                                {
                                    "name": "update the product",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the changes in the store frontend",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > merchant\\/products\\/block-editor\\/product-attributes-block-editor.spec.js",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add existing attributes",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-attributes-block-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "go to product editor, Organization tab",
                                    "status": "passed"
                                },
                                {
                                    "name": "add an existing attribute",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify attributes in product editor",
                                    "status": "passed"
                                },
                                {
                                    "name": "update the product",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify attributes in product editor after product update",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the changes in the store frontend",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > merchant\\/products\\/block-editor\\/product-attributes-block-editor.spec.js",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update product attributes",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-attributes-block-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "go to product editor, Organization tab",
                                    "status": "passed"
                                },
                                {
                                    "name": "update product\'s attribute terms",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify attributes in product editor",
                                    "status": "passed"
                                },
                                {
                                    "name": "update the product",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify attributes in product editor after product update",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the changes in the store frontend",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > merchant\\/products\\/block-editor\\/product-attributes-block-editor.spec.js",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can remove product attributes",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-attributes-block-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "go to product editor, Organization tab",
                                    "status": "passed"
                                },
                                {
                                    "name": "remove product\'s attribute",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the change in product editor",
                                    "status": "passed"
                                },
                                {
                                    "name": "update the product",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the change in product editor after update",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > merchant\\/products\\/block-editor\\/product-attributes-block-editor.spec.js",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update the general information of a product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-edit-block-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "edit the product name",
                                    "status": "passed"
                                },
                                {
                                    "name": "edit the product description and summary",
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
                            "suite": "ui > merchant\\/products\\/block-editor\\/product-edit-block-editor.spec.js",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can schedule a product publication",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-edit-block-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/products\\/block-editor\\/product-edit-block-editor.spec.js > Publish dropdown options",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can duplicate a product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-edit-block-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/products\\/block-editor\\/product-edit-block-editor.spec.js > Publish dropdown options",
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
                            "filePath": "\\/normalized\\/path\\/product-edit-block-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/products\\/block-editor\\/product-edit-block-editor.spec.js > Publish dropdown options",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add images",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-images-block-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "navigate to product edit page",
                                    "status": "passed"
                                },
                                {
                                    "name": "add images",
                                    "status": "passed"
                                },
                                {
                                    "name": "update the product",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify product image was set",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > merchant\\/products\\/block-editor\\/product-images-block-editor.spec.js",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can replace an image",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-images-block-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "navigate to product edit page",
                                    "status": "passed"
                                },
                                {
                                    "name": "replace an image",
                                    "status": "passed"
                                },
                                {
                                    "name": "update the product",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify product image was set",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > merchant\\/products\\/block-editor\\/product-images-block-editor.spec.js",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can remove an image",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-images-block-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "navigate to product edit page",
                                    "status": "passed"
                                },
                                {
                                    "name": "remove an image",
                                    "status": "passed"
                                },
                                {
                                    "name": "update the product",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify product image was set",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > merchant\\/products\\/block-editor\\/product-images-block-editor.spec.js",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can set an image as cover",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-images-block-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "navigate to product edit page",
                                    "status": "passed"
                                },
                                {
                                    "name": "remove an image",
                                    "status": "passed"
                                },
                                {
                                    "name": "update the product",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify product image was set",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > merchant\\/products\\/block-editor\\/product-images-block-editor.spec.js",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update sku",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-inventory-block-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "go to product editor, inventory tab",
                                    "status": "passed"
                                },
                                {
                                    "name": "update the sku value",
                                    "status": "passed"
                                },
                                {
                                    "name": "update the product",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the change in product editor",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the changes in the store frontend",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > merchant\\/products\\/block-editor\\/product-inventory-block-editor.spec.js",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can update stock status",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-inventory-block-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "go to product editor, inventory tab",
                                    "status": "passed"
                                },
                                {
                                    "name": "update the sku value",
                                    "status": "passed"
                                },
                                {
                                    "name": "update the product",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the change in product editor",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the changes in the store frontend",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > merchant\\/products\\/block-editor\\/product-inventory-block-editor.spec.js",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can track stock quantity",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-inventory-block-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "go to product editor, inventory tab",
                                    "status": "passed"
                                },
                                {
                                    "name": "enable track stock quantity",
                                    "status": "passed"
                                },
                                {
                                    "name": "update available quantity",
                                    "status": "passed"
                                },
                                {
                                    "name": "update the product",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the change in product editor",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the changes in the store frontend",
                                    "status": "passed"
                                },
                                {
                                    "name": "return to product editor",
                                    "status": "passed"
                                },
                                {
                                    "name": "update available quantity",
                                    "status": "passed"
                                },
                                {
                                    "name": "update the product",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the change in product editor",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify the changes in the store frontend",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > merchant\\/products\\/block-editor\\/product-inventory-block-editor.spec.js",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can limit purchases",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-inventory-block-editor.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "go to product editor, inventory tab",
                                    "status": "passed"
                                },
                                {
                                    "name": "ensure limit purchases is disabled",
                                    "status": "passed"
                                },
                                {
                                    "name": "add 2 items to cart",
                                    "status": "passed"
                                },
                                {
                                    "name": "return to product editor",
                                    "status": "passed"
                                },
                                {
                                    "name": "enable limit purchases",
                                    "status": "passed"
                                },
                                {
                                    "name": "update the product",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify you cannot order more than 1 item",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > merchant\\/products\\/block-editor\\/product-inventory-block-editor.spec.js",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "See email preview with a feature flag",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-email.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/settings-email.spec.js > WooCommerce Email Settings",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Email sender options live change in email preview",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-email.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/settings-email.spec.js > WooCommerce Email Settings",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Live preview when changing email settings",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-email.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/settings-email.spec.js > WooCommerce Email Settings",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/settings-email.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/settings-email.spec.js > WooCommerce Email Settings",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "See specific email preview with a feature flag",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-email.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/settings-email.spec.js > WooCommerce Email Settings",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "See email image url field with a feature flag",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-email.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/settings-email.spec.js > WooCommerce Email Settings",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/settings-email.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/settings-email.spec.js > WooCommerce Email Settings",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "See new color settings with a feature flag",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-email.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/settings-email.spec.js > WooCommerce Email Settings",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "See font family setting with a feature flag",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-email.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/settings-email.spec.js > WooCommerce Email Settings",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "See updated footer text field with a feature flag",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-email.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/settings-email.spec.js > WooCommerce Email Settings",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/settings-email.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/settings-email.spec.js > WooCommerce Email Settings",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Save Changes button is disabled by default and enabled only after changes.",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-general.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/settings-general.spec.js > WooCommerce General Settings",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/settings-general.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/settings-general.spec.js > WooCommerce General Settings",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can add shipping methods (free, local, flat rate)",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/settings-shipping.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Add shipping zone",
                                    "status": "passed"
                                },
                                {
                                    "name": "Add free shipping method",
                                    "status": "passed"
                                },
                                {
                                    "name": "Add local pickup method",
                                    "status": "passed"
                                },
                                {
                                    "name": "Add flat rate method",
                                    "status": "passed"
                                },
                                {
                                    "name": "Assert shipping methods",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > merchant\\/settings-shipping.spec.js > WooCommerce Shipping Settings",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/settings-tax.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/settings-tax.spec.js > WooCommerce Tax Settings > enable",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/settings-tax.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/settings-tax.spec.js > WooCommerce Tax Settings",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/settings-tax.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/settings-tax.spec.js > WooCommerce Tax Settings",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/settings-tax.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/settings-tax.spec.js > WooCommerce Tax Settings",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/settings-tax.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > merchant\\/settings-tax.spec.js > WooCommerce Tax Settings",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/users-create.spec.js",
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
                            "suite": "ui > merchant\\/users-create.spec.js",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/users-manage.spec.js",
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
                            "suite": "ui > merchant\\/users-manage.spec.js",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/users-manage.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "update user data",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > merchant\\/users-manage.spec.js",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/users-manage.spec.js",
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
                            "suite": "ui > merchant\\/users-manage.spec.js",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/users-manage.spec.js",
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
                            "suite": "ui > merchant\\/users-manage.spec.js",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should receive an email when creating an account",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/account-email-receiving.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "create a new user",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify that the email was sent",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > shopper\\/account-email-receiving.spec.js > Shopper Account Email Receiving",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should receive an email when password reset initiated from admin",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/account-email-receiving.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "create a new user",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify that no email was sent on account creation",
                                    "status": "passed"
                                },
                                {
                                    "name": "initiate password reset from admin",
                                    "status": "passed"
                                },
                                {
                                    "name": "verify that the email was sent",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > shopper\\/account-email-receiving.spec.js > Shopper Account Email Receiving",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should receive an email when initiating a password reset",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/account-email-receiving.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/account-email-receiving.spec.js > Shopper Password Reset Email Receiving",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "should add only one product to the cart with AJAX add to cart buttons disabled and \\"Geolocate (with page caching support)\\" as the default customer location",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/add-to-cart.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/add-to-cart.spec.js > Add to Cart behavior",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "allows customer to calculate Free Shipping in cart block if in Netherlands",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-block-calculate-shipping.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/cart-block-calculate-shipping.spec.js > Cart Block Calculate Shipping",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "allows customer to calculate Flat rate and Local pickup in cart block if in Portugal",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-block-calculate-shipping.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/cart-block-calculate-shipping.spec.js > Cart Block Calculate Shipping",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should show correct total cart block price after updating quantity",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-block-calculate-shipping.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/cart-block-calculate-shipping.spec.js > Cart Block Calculate Shipping",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should show correct total cart block price with 2 different products and flat rate\\/local pickup",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-block-calculate-shipping.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/cart-block-calculate-shipping.spec.js > Cart Block Calculate Shipping",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "allows cart block to apply coupon of any type",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-block-coupons.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/cart-block-coupons.spec.js > Cart Block Applying Coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "allows cart block to apply multiple coupons",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-block-coupons.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/cart-block-coupons.spec.js > Cart Block Applying Coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "prevents cart block applying same coupon twice",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-block-coupons.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/cart-block-coupons.spec.js > Cart Block Applying Coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "prevents cart block applying coupon with usage limit",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-block-coupons.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/cart-block-coupons.spec.js > Cart Block Applying Coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can see empty cart, add and remove simple & cross sell product, increase to max quantity",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-block.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/cart-block.spec.js > Cart Block page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "allows customer to calculate Free Shipping if in Germany",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-calculate-shipping.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/cart-calculate-shipping.spec.js > Cart Calculate Shipping",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "allows customer to calculate Flat rate and Local pickup if in France",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-calculate-shipping.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/cart-calculate-shipping.spec.js > Cart Calculate Shipping",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should show correct total cart price after updating quantity",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-calculate-shipping.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/cart-calculate-shipping.spec.js > Cart Calculate Shipping",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should show correct total cart price with 2 products and flat rate",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-calculate-shipping.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/cart-calculate-shipping.spec.js > Cart Calculate Shipping",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should show correct total cart price with 2 products without flat rate",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-calculate-shipping.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/cart-calculate-shipping.spec.js > Cart Calculate Shipping",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create Cart Block page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-block-calculate-tax.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/cart-checkout-block-calculate-tax.spec.js > Shopper Cart & Checkout Block Tax Display",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create Checkout Block page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-block-calculate-tax.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/cart-checkout-block-calculate-tax.spec.js > Shopper Cart & Checkout Block Tax Display",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "that inclusive tax is displayed properly in block-based Cart & Checkout pages",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-block-calculate-tax.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load cart page and confirm price display",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load checkout page and confirm price display",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > shopper\\/cart-checkout-block-calculate-tax.spec.js > Shopper Cart & Checkout Block Tax Display",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "that exclusive tax is displayed properly in block-based Cart & Checkout pages",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-block-calculate-tax.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load cart page and confirm price display",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load checkout page and confirm price display",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > shopper\\/cart-checkout-block-calculate-tax.spec.js > Shopper Cart & Checkout Block Tax Display",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "that tax rounding is present at subtotal level in block-based Cart & Checkout pages",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-block-calculate-tax.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load cart page and confirm price display",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load checkout page and confirm price display",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > shopper\\/cart-checkout-block-calculate-tax.spec.js > Shopper Cart & Checkout Block Tax Rounding",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "that tax rounding is off at subtotal level in block-based Cart & Checkout pages",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-block-calculate-tax.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load cart page and confirm price display",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load checkout page and confirm price display",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > shopper\\/cart-checkout-block-calculate-tax.spec.js > Shopper Cart & Checkout Block Tax Rounding",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "that applying taxes in cart block of 4 different levels calculates properly",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-block-calculate-tax.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load cart page and confirm price display",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load checkout page and confirm price display",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > shopper\\/cart-checkout-block-calculate-tax.spec.js > Shopper Cart & Checkout Block Tax Levels",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "that applying taxes in block-based Cart & Checkout of 2 different levels (2 excluded) calculates properly",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-block-calculate-tax.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load cart page and confirm price display",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load checkout page and confirm price display",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > shopper\\/cart-checkout-block-calculate-tax.spec.js > Shopper Cart & Checkout Block Tax Levels",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "that tax is applied in Cart Block to shipping as well as order",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-block-calculate-tax.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load cart page and confirm price display",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load checkout page and confirm price display",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > shopper\\/cart-checkout-block-calculate-tax.spec.js > Shipping Cart & Checkout Block Tax",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "checks that taxes are calculated properly on totals, inclusive tax displayed properly",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-calculate-tax.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load shop page, confirm title and confirm price display",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load cart page and confirm price display",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load checkout page and confirm price display",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > shopper\\/cart-checkout-calculate-tax.spec.js > Tax rates in the cart and checkout > Shopper Tax Display Tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "checks that taxes are calculated and displayed correctly exclusive on shop, cart and checkout",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-calculate-tax.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load shop page and confirm price display",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load cart page and confirm price display",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load checkout page and confirm price display",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > shopper\\/cart-checkout-calculate-tax.spec.js > Tax rates in the cart and checkout > Shopper Tax Display Tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "checks that display suffix is shown",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-calculate-tax.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load shop page and confirm price suffix display",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > shopper\\/cart-checkout-calculate-tax.spec.js > Tax rates in the cart and checkout > Shopper Tax Display Tests",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "checks rounding at subtotal level",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-calculate-tax.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load shop page and confirm price display",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load cart page and confirm price display",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > shopper\\/cart-checkout-calculate-tax.spec.js > Tax rates in the cart and checkout > Shopper Tax Rounding",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "checks rounding off at subtotal level",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-calculate-tax.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load shop page and confirm price display",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load cart page and confirm price display",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > shopper\\/cart-checkout-calculate-tax.spec.js > Tax rates in the cart and checkout > Shopper Tax Rounding",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "checks applying taxes of 4 different levels",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-calculate-tax.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load cart page and confirm price display",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load checkout page and confirm taxes displayed",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > shopper\\/cart-checkout-calculate-tax.spec.js > Tax rates in the cart and checkout > Shopper Tax Levels",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "checks applying taxes of 2 different levels (2 excluded)",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-calculate-tax.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load cart page and confirm price display",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load checkout page and confirm taxes displayed",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > shopper\\/cart-checkout-calculate-tax.spec.js > Tax rates in the cart and checkout > Shopper Tax Levels",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "checks that tax is applied to shipping as well as order",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-calculate-tax.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Load cart page and confirm price display",
                                    "status": "passed"
                                },
                                {
                                    "name": "Load checkout page and confirm price display",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > shopper\\/cart-checkout-calculate-tax.spec.js > Tax rates in the cart and checkout > Shipping Tax",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "allows applying coupon of type fixed_cart",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > shopper\\/cart-checkout-coupons.spec.js > Cart & Checkout applying coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "allows applying coupon of type percent",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > shopper\\/cart-checkout-coupons.spec.js > Cart & Checkout applying coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "allows applying coupon of type fixed_product",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > shopper\\/cart-checkout-coupons.spec.js > Cart & Checkout applying coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "prevents applying same coupon twice",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > shopper\\/cart-checkout-coupons.spec.js > Cart & Checkout applying coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "allows applying multiple coupons",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > shopper\\/cart-checkout-coupons.spec.js > Cart & Checkout applying coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "restores total when coupons are removed",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > shopper\\/cart-checkout-coupons.spec.js > Cart & Checkout applying coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "expired coupon cannot be used",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > shopper\\/cart-checkout-restricted-coupons.spec.js > Cart & Checkout Restricted Coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "coupon requiring min and max amounts and can only be used alone can only be used within limits",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > shopper\\/cart-checkout-restricted-coupons.spec.js > Cart & Checkout Restricted Coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "coupon cannot be used on sale item",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > shopper\\/cart-checkout-restricted-coupons.spec.js > Cart & Checkout Restricted Coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "coupon can only be used twice",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > shopper\\/cart-checkout-restricted-coupons.spec.js > Cart & Checkout Restricted Coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "coupon cannot be used on certain products\\/categories (included product\\/category)",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > shopper\\/cart-checkout-restricted-coupons.spec.js > Cart & Checkout Restricted Coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "coupon can be used on certain products\\/categories",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > shopper\\/cart-checkout-restricted-coupons.spec.js > Cart & Checkout Restricted Coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "coupon cannot be used on specific products\\/categories (excluded product\\/category)",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > shopper\\/cart-checkout-restricted-coupons.spec.js > Cart & Checkout Restricted Coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "coupon can be used on other products\\/categories",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
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
                            "suite": "ui > shopper\\/cart-checkout-restricted-coupons.spec.js > Cart & Checkout Restricted Coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "coupon cannot be used by any customer on cart (email restricted)",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-restricted-coupons.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/cart-checkout-restricted-coupons.spec.js > Cart & Checkout Restricted Coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "coupon cannot be used by any customer on checkout (email restricted)",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-restricted-coupons.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/cart-checkout-restricted-coupons.spec.js > Cart & Checkout Restricted Coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "coupon can be used by the right customer (email restricted) but only once",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-checkout-restricted-coupons.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/cart-checkout-restricted-coupons.spec.js > Cart & Checkout Restricted Coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can redirect user to cart from shop page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-redirection.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/cart-redirection.spec.js > Cart > Redirect to cart from shop",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can redirect user to cart from detail page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart-redirection.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/cart-redirection.spec.js > Cart > Redirect to cart from shop",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should display no item in the cart",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/cart.spec.js > Cart page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should add the product to the cart from the shop page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/cart.spec.js > Cart page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should increase item quantity when \\"Add to cart\\" of the same product is clicked",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/cart.spec.js > Cart page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should update quantity when updated via quantity input",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/cart.spec.js > Cart page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should remove the item from the cart when remove is clicked",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/cart.spec.js > Cart page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should update subtotal in cart totals when adding product to the cart",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/cart.spec.js > Cart page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should go to the checkout page when \\"Proceed to Checkout\\" is clicked",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/cart.spec.js > Cart page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can manage cross-sell products and maximum item quantity",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/cart.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/cart.spec.js > Cart page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "allows checkout block to apply coupon of any type",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout-block-coupons.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/checkout-block-coupons.spec.js > Checkout Block Applying Coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "allows checkout block to apply multiple coupons",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout-block-coupons.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/checkout-block-coupons.spec.js > Checkout Block Applying Coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "prevents checkout block applying same coupon twice",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout-block-coupons.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/checkout-block-coupons.spec.js > Checkout Block Applying Coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "prevents checkout block applying coupon with usage limit",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout-block-coupons.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/checkout-block-coupons.spec.js > Checkout Block Applying Coupons",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can see empty checkout block page",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout-block.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/checkout-block.spec.js > Checkout Block page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "allows customer to choose available payment methods",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout-block.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/checkout-block.spec.js > Checkout Block page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "allows customer to fill shipping details",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout-block.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/checkout-block.spec.js > Checkout Block page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "allows customer to fill different shipping and billing details",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout-block.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/checkout-block.spec.js > Checkout Block page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "allows customer to fill shipping details and toggle different billing",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout-block.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/checkout-block.spec.js > Checkout Block page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can choose different shipping types in the checkout",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout-block.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/checkout-block.spec.js > Checkout Block page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "allows guest customer to place an order",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout-block.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/checkout-block.spec.js > Checkout Block page",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "allows existing customer to place an order",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout-block.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/checkout-block.spec.js > Checkout Block page",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "can create an account during checkout",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout-block.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/checkout-block.spec.js > Checkout Block page",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "can create an account during checkout with custom password",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout-block.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/checkout-block.spec.js > Checkout Block page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can create an account during checkout",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout-create-account.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/checkout-create-account.spec.js > Shopper Checkout Create Account",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can login to an existing account during checkout",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout-login.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/checkout-login.spec.js > Shopper Checkout Login Account",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should display cart items in order review",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/checkout.spec.js > Checkout page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "allows customer to choose available payment methods",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/checkout.spec.js > Checkout page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "allows customer to fill billing details",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/checkout.spec.js > Checkout page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "warn when customer is missing required details",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/checkout.spec.js > Checkout page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "allows customer to fill shipping details",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/checkout.spec.js > Checkout page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "allows guest customer to place an order",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/checkout.spec.js > Checkout page",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "allows existing customer to place order",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/checkout.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/checkout.spec.js > Checkout page",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
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
                            "suite": "ui > shopper\\/dashboard-access.spec.js > Customer-role users are blocked from accessing the WP Dashboard.",
                            "extra": {
                                "annotations": []
                            }
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
                            "suite": "ui > shopper\\/dashboard-access.spec.js > Customer-role users are blocked from accessing the WP Dashboard.",
                            "extra": {
                                "annotations": []
                            }
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
                            "suite": "ui > shopper\\/dashboard-access.spec.js > Customer-role users are blocked from accessing the WP Dashboard.",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Entire site coming soon mode (function () { [native code] })",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/launch-your-store.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/launch-your-store.spec.js > Launch Your Store front end - logged out > Block Theme (Twenty Twenty Four)",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Store only coming soon mode (function () { [native code] })",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/launch-your-store.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/launch-your-store.spec.js > Launch Your Store front end - logged out > Block Theme (Twenty Twenty Four)",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Entire site coming soon mode (function () { [native code] })",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/launch-your-store.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/launch-your-store.spec.js > Launch Your Store front end - logged out > Classic Theme (Storefront)",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Store only coming soon mode (function () { [native code] })",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/launch-your-store.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/launch-your-store.spec.js > Launch Your Store front end - logged out > Classic Theme (Storefront)",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can see empty customized mini cart, add and remove product, increase to max quantity, calculate tax and see redirection",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/mini-cart.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/mini-cart.spec.js > Mini Cart block page",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
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
                            "filePath": "\\/normalized\\/path\\/my-account-addresses.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/my-account-addresses.spec.js > Customer can manage addresses in My Account > Addresses page",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/my-account-addresses.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/my-account-addresses.spec.js > Customer can manage addresses in My Account > Addresses page",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/my-account-create-account.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/my-account-create-account.spec.js > Shopper My Account Create Account",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/my-account-downloads.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/my-account-downloads.spec.js > Customer can manage downloadable file in My Account > Downloads page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "allows customer to pay for their order in My Account",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/my-account-pay-order.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/my-account-pay-order.spec.js > Customer can pay for their order through My Account",
                            "extra": {
                                "annotations": []
                            }
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
                            "filePath": "\\/normalized\\/path\\/my-account.spec.js",
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
                            "suite": "ui > shopper\\/my-account.spec.js > My account page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should receive order email after purchasing an item",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/order-email-receiving.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/order-email-receiving.spec.js > Shopper Order Email Receiving",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should be able to add grouped products to the cart",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-grouped.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/product-grouped.spec.js > Grouped Product Page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should be able to remove grouped products from the cart",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-grouped.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/product-grouped.spec.js > Grouped Product Page",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "should be able to post a review and see it after",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-simple.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/product-simple.spec.js > Single Product Page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should be able to see product description",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-simple.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/product-simple.spec.js > Single Product Page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should see shop catalog with all its products",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-tags-attributes.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/product-tags-attributes.spec.js > Browse product tags and attributes from the product page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should see and sort tags page with all the products",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-tags-attributes.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/product-tags-attributes.spec.js > Browse product tags and attributes from the product page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should see and sort attributes page with all its products",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-tags-attributes.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/product-tags-attributes.spec.js > Browse product tags and attributes from the product page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "can see products showcase",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-tags-attributes.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/product-tags-attributes.spec.js > Browse product tags and attributes from the product page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should be able to add variation products to the cart",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-variable.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/product-variable.spec.js > Variable Product Page",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should be able to remove variation products from the cart",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-variable.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/product-variable.spec.js > Variable Product Page",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "Shopper can change variable attributes to the same value",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-variable.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/product-variable.spec.js > Shopper > Update variable product",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Shopper can change attributes to combination with dimensions and weight",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-variable.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/product-variable.spec.js > Shopper > Update variable product",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Shopper can change variable product attributes to variation with a different price",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-variable.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/product-variable.spec.js > Shopper > Update variable product",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "Shopper can reset variations",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/product-variable.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/product-variable.spec.js > Shopper > Update variable product",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should let user search the store",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shop-search-browse-sort.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [
                                {
                                    "name": "Go to the shop and perform the search",
                                    "status": "passed"
                                }
                            ],
                            "suite": "ui > shopper\\/shop-search-browse-sort.spec.js > Search, browse by categories and sort items in the shop",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should let user browse products by categories",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shop-search-browse-sort.spec.js",
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
                            "suite": "ui > shopper\\/shop-search-browse-sort.spec.js > Search, browse by categories and sort items in the shop",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "should let user sort the products in the shop",
                            "status": "skipped",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "skipped",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shop-search-browse-sort.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/shop-search-browse-sort.spec.js > Search, browse by categories and sort items in the shop",
                            "extra": {
                                "annotations": [
                                    {
                                        "type": "skip"
                                    }
                                ]
                            }
                        },
                        {
                            "name": "Check the title of the shop page after the page has been deleted",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/shop-title-after-deletion.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/shop-title-after-deletion.spec.js",
                            "extra": {
                                "annotations": []
                            }
                        },
                        {
                            "name": "logged-in customer can comment on a post",
                            "status": "passed",
                            "duration": 999,
                            "start": 1111111111,
                            "stop": 2222222222,
                            "rawStatus": "passed",
                            "tags": [],
                            "type": "e2e",
                            "filePath": "\\/normalized\\/path\\/wordpress-post.spec.js",
                            "retries": 0,
                            "flaky": false,
                            "steps": [],
                            "suite": "ui > shopper\\/wordpress-post.spec.js",
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
                "generic": []
            }
        }
    ]
]';
