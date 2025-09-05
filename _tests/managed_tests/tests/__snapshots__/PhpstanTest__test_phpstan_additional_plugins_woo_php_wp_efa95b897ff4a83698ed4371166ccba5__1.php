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
            "test_summary": "Errors: 0, File Errors: 7",
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
            "test_group_id": "",
            "test_result_json_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "totals": {
                    "errors": 0,
                    "file_errors": 7
                },
                "files": {
                    "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php": {
                        "errors": 7,
                        "messages": [
                            {
                                "message": "Instantiated class Automattic\\\\WooCommerce\\\\GoogleListingsAndAds\\\\Container not found.",
                                "line": 13,
                                "ignorable": true,
                                "tip": "Learn more at https:\\/\\/phpstan.org\\/user-guide\\/discovering-symbols",
                                "identifier": "class.notFound"
                            },
                            {
                                "message": "Call to method get() on an unknown class Automattic\\\\WooCommerce\\\\GoogleListingsAndAds\\\\Container.",
                                "line": 16,
                                "ignorable": true,
                                "tip": "Learn more at https:\\/\\/phpstan.org\\/user-guide\\/discovering-symbols",
                                "identifier": "class.notFound"
                            },
                            {
                                "message": "Class Automattic\\\\WooCommerce\\\\GoogleListingsAndAds\\\\Vendor\\\\Psr\\\\Container\\\\ContainerInterface not found.",
                                "line": 16,
                                "ignorable": true,
                                "tip": "Learn more at https:\\/\\/phpstan.org\\/user-guide\\/discovering-symbols",
                                "identifier": "class.notFound"
                            },
                            {
                                "message": "Call to method has() on an unknown class Automattic\\\\WooCommerce\\\\GoogleListingsAndAds\\\\Container.",
                                "line": 18,
                                "ignorable": true,
                                "tip": "Learn more at https:\\/\\/phpstan.org\\/user-guide\\/discovering-symbols",
                                "identifier": "class.notFound"
                            },
                            {
                                "message": "Call to method has() on an unknown class Automattic\\\\WooCommerce\\\\GoogleListingsAndAds\\\\Container.",
                                "line": 19,
                                "ignorable": true,
                                "tip": "Learn more at https:\\/\\/phpstan.org\\/user-guide\\/discovering-symbols",
                                "identifier": "class.notFound"
                            },
                            {
                                "message": "Call to method someNonExistentMethod() on an unknown class Automattic\\\\WooCommerce\\\\GoogleListingsAndAds\\\\Container.",
                                "line": 22,
                                "ignorable": true,
                                "tip": "Learn more at https:\\/\\/phpstan.org\\/user-guide\\/discovering-symbols",
                                "identifier": "class.notFound"
                            },
                            {
                                "message": "Function call_to_undefined_funtion not found.",
                                "line": 25,
                                "ignorable": true,
                                "tip": "Learn more at https:\\/\\/phpstan.org\\/user-guide\\/discovering-symbols",
                                "identifier": "function.notFound"
                            }
                        ]
                    }
                },
                "errors": []
            }
        }
    ]
]';
