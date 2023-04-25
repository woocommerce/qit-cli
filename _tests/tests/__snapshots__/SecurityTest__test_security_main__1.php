<?php return '[
    [
        {
            "run_id": 123456,
            "test_type": "security",
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
            "woo_extension": {
                "id": 18619,
                "host": "wccom",
                "name": "Google Product Feed"
            },
            "client": "qit_cli",
            "event": "cli_development_extension_test",
            "optional_features": {
                "hpos": false,
                "cc_blocks": false
            },
            "test_results_manager_url": "https:\\/\\/test-results-manager.com",
            "test_results_manager_expiration": 1234567890,
            "test_summary": "Errors: 4 Warnings: 3",
            "debug_log": "",
            "version": "Zip",
            "test_result_json_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "tool": {
                    "phpcs": {
                        "totals": {
                            "errors": 2,
                            "warnings": 3,
                            "fixable": 0
                        },
                        "files": {
                            "\\/home\\/runner\\/work\\/compatibility-dashboard\\/compatibility-dashboard\\/ci\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php": {
                                "errors": 2,
                                "warnings": 3,
                                "messages": [
                                    {
                                        "message": "Detected usage of a non-sanitized input variable: $_POST[\'foo\']",
                                        "source": "WordPress.Security.ValidatedSanitizedInput.InputNotSanitized",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 9,
                                        "column": 10
                                    },
                                    {
                                        "message": "All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found \'\\"Unescaped output! $foo\\"\'.",
                                        "source": "WordPress.Security.EscapeOutput.OutputNotEscaped",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 12,
                                        "column": 8
                                    },
                                    {
                                        "message": "The use of function wp_set_auth_cookie() is discouraged",
                                        "source": "Generic.PHP.ForbiddenFunctions.Discouraged",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "line": 15,
                                        "column": 3
                                    },
                                    {
                                        "message": "The use of function wp_set_current_user() is discouraged",
                                        "source": "Generic.PHP.ForbiddenFunctions.Discouraged",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "line": 16,
                                        "column": 3
                                    },
                                    {
                                        "message": "Detected usage of the \\"determine_user\\" filter. Please double-check if this filter is safe and ignore this warning to confirm.",
                                        "source": "QITStandard.PHP.DangerousFilters.RiskyFilterDetected",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "line": 20,
                                        "column": 1
                                    }
                                ]
                            }
                        }
                    },
                    "semgrep": {
                        "totals": {
                            "errors": 2,
                            "warnings": 0,
                            "fixable": 0
                        },
                        "files": {
                            "\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php": {
                                "errors": 2,
                                "warnings": 0,
                                "messages": [
                                    {
                                        "line": 12,
                                        "column": 3,
                                        "type": "ERROR",
                                        "message": "User Input directly used in echo\\/printf statement, leading to Reflected XSS",
                                        "source": "scanner.php.lang.security.xss.direct-reflected",
                                        "severity": 10,
                                        "fixable": false
                                    },
                                    {
                                        "line": 13,
                                        "column": 3,
                                        "type": "ERROR",
                                        "message": "User Input directly used in echo\\/printf statement, leading to Reflected XSS",
                                        "source": "scanner.php.lang.security.xss.direct-reflected",
                                        "severity": 10,
                                        "fixable": false
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
