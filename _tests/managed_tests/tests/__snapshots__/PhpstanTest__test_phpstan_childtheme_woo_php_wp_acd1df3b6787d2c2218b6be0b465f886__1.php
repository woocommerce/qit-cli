<?php return '[
    [
        {
            "test_run_id": 123456,
            "run_id": 123456,
            "test_type": "phpstan",
            "test_type_display": "PHPStan",
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
                "id": 1822936,
                "host": "wccom",
                "name": "Bistro",
                "type": "theme"
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
            "version": "1.0.15",
            "update_complete": true,
            "ai_suggestion_status": "none",
            "malware_whitelist_paths": [],
            "workflow_id": "1234567890",
            "runner": "normalized",
            "test_media": [],
            "test_result_json_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "totals": {
                    "errors": 0,
                    "file_errors": 2
                },
                "files": {
                    "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/themes\\/bistro\\/index.php": {
                        "errors": 2,
                        "messages": [
                            {
                                "message": "Instantiated class Bar not found.",
                                "line": 17,
                                "ignorable": true
                            },
                            {
                                "message": "Result of function example_return_void (void) is used.",
                                "line": 18,
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
