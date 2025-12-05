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
            "ctrf_json": "",
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
            "test_summary": "Errors: 0, File Errors: 4",
            "debug_log": "",
            "version": "Undefined",
            "update_complete": true,
            "malware_whitelist_paths": [],
            "workflow_id": "1234567890",
            "runner": "normalized",
            "test_media": [],
            "extension_set": "",
            "phpstan_level": 2,
            "test_variation": "",
            "test_packages": [],
            "test_group_id": "",
            "created_at": "2025-01-01 00:00:00",
            "test_result_json_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "totals": {
                    "errors": 0,
                    "file_errors": 4
                },
                "files": {
                    "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php": {
                        "errors": 4,
                        "messages": [
                            {
                                "message": "Instantiated class Bar not found.",
                                "line": 21,
                                "ignorable": true,
                                "tip": "Learn more at https:\\/\\/phpstan.org\\/user-guide\\/discovering-symbols",
                                "identifier": "class.notFound"
                            },
                            {
                                "message": "Result of function example_return_void (void) is used.",
                                "line": 22,
                                "ignorable": true,
                                "identifier": "function.void"
                            },
                            {
                                "message": "Access to an undefined property Foo::$bar.",
                                "line": 26,
                                "ignorable": true,
                                "tip": "Learn more: https:\\/\\/phpstan.org\\/blog\\/solving-phpstan-access-to-undefined-property",
                                "identifier": "property.notFound"
                            },
                            {
                                "message": "Call to an undefined static method WP_CLI::someNonExistingMethod().",
                                "line": 36,
                                "ignorable": true,
                                "identifier": "staticMethod.notFound"
                            }
                        ]
                    }
                },
                "errors": []
            }
        }
    ]
]';
