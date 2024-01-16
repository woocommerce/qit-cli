<?php return '[
    [
        {
            "test_run_id": 123456,
            "run_id": 123456,
            "test_type": "phpcompatibility",
            "wordpress_version": "6.0.0-normalized",
            "woocommerce_version": "6.0.0-normalized",
            "php_version": "7.4",
            "max_php_version": "7.4",
            "min_php_version": "5.6",
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
            "test_summary": "Errors: 5 Warnings: 0",
            "debug_log": "",
            "version": "Undefined",
            "update_complete": true,
            "ai_suggestion_status": "none",
            "test_result_json_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "tool": {
                    "phpcs": {
                        "totals": {
                            "errors": 5,
                            "warnings": 0,
                            "fixable": 0
                        },
                        "files": {
                            "\\/home\\/runner\\/work\\/compatibility-dashboard\\/compatibility-dashboard\\/ci\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php": {
                                "errors": 5,
                                "warnings": 0,
                                "messages": [
                                    {
                                        "message": "Anonymous classes are not supported in PHP 5.6 or earlier",
                                        "source": "PHPCompatibility.Classes.NewAnonymousClasses.Found",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 8,
                                        "column": 19
                                    },
                                    {
                                        "message": "void return type is not present in PHP version 7.0 or earlier",
                                        "source": "PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 11,
                                        "column": 28
                                    },
                                    {
                                        "message": "Anonymous classes are not supported in PHP 5.6 or earlier",
                                        "source": "PHPCompatibility.Classes.NewAnonymousClasses.Found",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 14,
                                        "column": 11
                                    },
                                    {
                                        "message": "Visibility indicators for class constants are not supported in PHP 7.0 or earlier. Found \\"public const\\"",
                                        "source": "PHPCompatibility.Classes.NewConstVisibility.Found",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 14,
                                        "column": 30
                                    },
                                    {
                                        "message": "null coalescing operator (??) is not present in PHP version 5.6 or earlier",
                                        "source": "PHPCompatibility.Operators.NewOperators.t_coalesceFound",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 15,
                                        "column": 26
                                    }
                                ]
                            }
                        }
                    }
                }
            }
        }
    ]
]';
