<?php return '[
    [
        {
            "test_run_id": 123456,
            "run_id": 123456,
            "test_type": "phpcompatibility",
            "test_type_display": "PHP Compatibility",
            "wordpress_version": "6.0.0-normalized",
            "woocommerce_version": "6.0.0-normalized",
            "php_version": "7.4",
            "max_php_version": "8.4",
            "min_php_version": "auto",
            "additional_woo_plugins": [],
            "additional_wp_plugins": [],
            "test_log": "",
            "ctrf_json": "",
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
            "test_summary": "Errors: 1 Warnings: 0",
            "debug_log": "",
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
            "test_group_id": "",
            "test_result_json_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "tool": {
                    "phpcs": {
                        "totals": {
                            "errors": 1,
                            "warnings": 0,
                            "fixable": 0
                        },
                        "files": {
                            "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php": {
                                "errors": 1,
                                "warnings": 0,
                                "messages": [
                                    {
                                        "message": "An error occurred during processing; checking has been aborted. The error message was: Invalid PHPCompatibility testVersion provided: \'auto-8.4\'\\\\nThe error originated in the PHPCompatibility.Miscellaneous.RemovedAlternativePHPTags sniff on line 77.",
                                        "source": "Internal.Exception",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 1,
                                        "column": 1
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
