<?php return '[
    [
        {
            "test_run_id": 123456,
            "run_id": 123456,
            "test_type": "phpstan",
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
            "test_summary": "Errors: 0, File Errors: 2",
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
                "totals": {
                    "errors": 0,
                    "file_errors": 2
                },
                "files": {
                    "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/plugins\\/woocommerce-product-feeds\\/not-vendor\\/src\\/Baz.php": {
                        "errors": 1,
                        "messages": [
                            {
                                "message": "Instantiated class NotAVendor\\\\SomeOtherUnexistingClassThatPHPStanShouldFlag not found.",
                                "line": 11,
                                "ignorable": true
                            }
                        ]
                    },
                    "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php": {
                        "errors": 1,
                        "messages": [
                            {
                                "message": "Instantiated class SomeUnexistingClassThatPHPStanShouldFlag not found.",
                                "line": 20,
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
