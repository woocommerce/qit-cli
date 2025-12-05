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
            "test_summary": "Errors: 0, File Errors: 3",
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
                    "file_errors": 3
                },
                "files": {
                    "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/plugins\\/woocommerce-product-feeds\\/not-vendor\\/src\\/Baz.php": {
                        "errors": 1,
                        "messages": [
                            {
                                "message": "Instantiated class NotAVendor\\\\SomeOtherUnexistingClassThatPHPStanShouldFlag not found.",
                                "line": 11,
                                "ignorable": true,
                                "tip": "Learn more at https:\\/\\/phpstan.org\\/user-guide\\/discovering-symbols",
                                "identifier": "class.notFound"
                            }
                        ]
                    },
                    "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php": {
                        "errors": 2,
                        "messages": [
                            {
                                "message": "Call to an undefined method SomePrefixedVendor\\\\Bar::get_bar().",
                                "line": 15,
                                "ignorable": true,
                                "identifier": "method.notFound"
                            },
                            {
                                "message": "Instantiated class SomeUnexistingClassThatPHPStanShouldFlag not found.",
                                "line": 20,
                                "ignorable": true,
                                "tip": "Learn more at https:\\/\\/phpstan.org\\/user-guide\\/discovering-symbols",
                                "identifier": "class.notFound"
                            }
                        ]
                    }
                },
                "errors": []
            }
        }
    ]
]';
