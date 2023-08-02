<?php return '[
    [
        {
            "run_id": 123456,
            "test_type": "phpstan",
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
            "test_summary": "Errors: 0, File Errors: 2",
            "debug_log": "",
            "version": "Undefined",
            "update_complete": true,
            "ai_suggestion_status": "none",
            "test_result_json_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "totals": {
                    "errors": 0,
                    "file_errors": 2
                },
                "files": {
                    "\\/home\\/runner\\/work\\/compatibility-dashboard\\/compatibility-dashboard\\/ci\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php": {
                        "errors": 2,
                        "messages": [
                            {
                                "message": "Instantiated class Bar not found.",
                                "line": 21,
                                "ignorable": true
                            },
                            {
                                "message": "Result of function example_return_void (void) is used.",
                                "line": 22,
                                "ignorable": true
                            }
                        ]
                    }
                },
                "errors": []
            }
        }
    ]
]';
