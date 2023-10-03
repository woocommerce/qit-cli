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
            "test_summary": "Errors: 7 Warnings: 5",
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
                            "warnings": 5,
                            "fixable": 0
                        },
                        "files": {
                            "\\/home\\/runner\\/work\\/compatibility-dashboard\\/compatibility-dashboard\\/ci\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php": {
                                "errors": 5,
                                "warnings": 5,
                                "messages": [
                                    {
                                        "message": "Processing form data without nonce verification.",
                                        "source": "WordPress.Security.NonceVerification.Missing",
                                        "severity": 10,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\tif ( isset( $_POST[\'foo\'] ) ) { \\/\\/ Should be flagged by Nonce.Missing\\n",
                                        "line": 8,
                                        "column": 14
                                    },
                                    {
                                        "message": "Processing form data without nonce verification.",
                                        "source": "WordPress.Security.NonceVerification.Missing",
                                        "severity": 10,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\t$foo = $_POST[\'foo\']; \\/\\/ Should be flagged by Nonce.Missing and Unsanitized\\n",
                                        "line": 9,
                                        "column": 10
                                    },
                                    {
                                        "message": "Detected usage of a non-sanitized input variable: $_POST[\'foo\']",
                                        "source": "WordPress.Security.ValidatedSanitizedInput.InputNotSanitized",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\t$foo = $_POST[\'foo\']; \\/\\/ Should be flagged by Nonce.Missing and Unsanitized\\n",
                                        "line": 9,
                                        "column": 10
                                    },
                                    {
                                        "message": "All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found \'\\"Unescaped output! $foo\\"\'.",
                                        "source": "WordPress.Security.EscapeOutput.OutputNotEscaped",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\techo \\"Unescaped output! $foo\\"; \\/\\/ All output should be run through an escaping function\\n",
                                        "line": 12,
                                        "column": 8
                                    },
                                    {
                                        "message": "The use of function wp_set_auth_cookie() is discouraged",
                                        "source": "Generic.PHP.ForbiddenFunctions.Discouraged",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "\\t\\twp_set_auth_cookie( 1 ); \\/\\/ Detected usage of a potentially unsafe function.\\n",
                                        "line": 15,
                                        "column": 3
                                    },
                                    {
                                        "message": "The use of function wp_set_current_user() is discouraged",
                                        "source": "Generic.PHP.ForbiddenFunctions.Discouraged",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "\\t\\twp_set_current_user( 1 ); \\/\\/ Detected usage of a potentially unsafe function.\\n",
                                        "line": 16,
                                        "column": 3
                                    },
                                    {
                                        "message": "Processing form data without nonce verification.",
                                        "source": "WordPress.Security.NonceVerification.Recommended",
                                        "severity": 10,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "\\tif ( isset( $_GET[\'foo\'] ) ) { \\/\\/ Should be flagged by Nonce.Recommended\\n",
                                        "line": 19,
                                        "column": 14
                                    },
                                    {
                                        "message": "Processing form data without nonce verification.",
                                        "source": "WordPress.Security.NonceVerification.Recommended",
                                        "severity": 10,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "\\t\\t$foo = $_GET[\'foo\']; \\/\\/ Should be flagged by Nonce.Recommended and Unsanitized\\n",
                                        "line": 20,
                                        "column": 10
                                    },
                                    {
                                        "message": "Detected usage of a non-sanitized input variable: $_GET[\'foo\']",
                                        "source": "WordPress.Security.ValidatedSanitizedInput.InputNotSanitized",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\t$foo = $_GET[\'foo\']; \\/\\/ Should be flagged by Nonce.Recommended and Unsanitized\\n",
                                        "line": 20,
                                        "column": 10
                                    },
                                    {
                                        "message": "Detected usage of the \\"determine_user\\" filter. Please double-check if this filter is safe and ignore this warning to confirm.",
                                        "source": "QITStandard.PHP.DangerousFilters.RiskyFilterDetected",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "add_filter( \'determine_user\', \'callable\' ); \\/\\/ Risky filter warning.",
                                        "line": 25,
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
                                        "fixable": false,
                                        "codeFragment": "\\t\\techo \\"Unescaped output! $foo\\"; \\/\\/ All output should be run through an escaping function"
                                    },
                                    {
                                        "line": 13,
                                        "column": 3,
                                        "type": "ERROR",
                                        "message": "User Input directly used in echo\\/printf statement, leading to Reflected XSS",
                                        "source": "scanner.php.lang.security.xss.direct-reflected",
                                        "severity": 10,
                                        "fixable": false,
                                        "codeFragment": "\\t\\techo \\"Unescaped output! $bar\\"; \\/\\/ phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped"
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
