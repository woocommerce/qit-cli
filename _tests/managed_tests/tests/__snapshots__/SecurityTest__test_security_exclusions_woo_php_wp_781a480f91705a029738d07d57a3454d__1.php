<?php return '[
    [
        {
            "test_run_id": 123456,
            "run_id": 123456,
            "test_type": "security",
            "test_type_display": "Security",
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
            "test_summary": "Errors: 15 Warnings: 9",
            "debug_log": "",
            "version": "Undefined",
            "update_complete": true,
            "malware_whitelist_paths": [],
            "workflow_id": "1234567890",
            "runner": "normalized",
            "test_media": [],
            "extension_set": "",
            "phpstan_level": null,
            "test_variation": "",
            "test_packages": [],
            "test_group_id": "",
            "created_at": "2025-01-01 00:00:00",
            "test_result_json_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "tool": {
                    "phpcs": {
                        "totals": {
                            "errors": 8,
                            "warnings": 8,
                            "fixable": 0
                        },
                        "files": {
                            "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/plugins\\/woocommerce-product-feeds\\/exclude-escaped-output\\/woocommerce-product-feeds.php": {
                                "errors": 2,
                                "warnings": 3,
                                "messages": [
                                    {
                                        "message": "Detected usage of a non-sanitized input variable: $_POST[\'foo\']",
                                        "source": "WordPress.Security.ValidatedSanitizedInput.InputNotSanitized",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\t$foo = $_POST[\'foo\']; \\/\\/ Detected usage of a non-sanitized input variable: $_POST[\'foo\']\\n",
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
                                        "message": "Detected usage of the \\"determine_user\\" filter. Please double-check if this filter is safe and ignore this warning to confirm.",
                                        "source": "QITStandard.PHP.DangerousFilters.RiskyFilterDetected",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "add_filter( \'determine_user\', \'callable\' ); \\/\\/ Risky filter warning.",
                                        "line": 20,
                                        "column": 1
                                    }
                                ]
                            },
                            "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/plugins\\/woocommerce-product-feeds\\/semgrep-exclusions\\/woocommerce-product-feeds.php": {
                                "errors": 4,
                                "warnings": 1,
                                "messages": [
                                    {
                                        "message": "Detected usage of a non-sanitized input variable: $_POST[\'foo\']",
                                        "source": "WordPress.Security.ValidatedSanitizedInput.InputNotSanitized",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\t$foo = $_POST[\'foo\'];\\n",
                                        "line": 9,
                                        "column": 10
                                    },
                                    {
                                        "message": "Detected usage of a non-sanitized input variable: $_POST[\'bar\']",
                                        "source": "WordPress.Security.ValidatedSanitizedInput.InputNotSanitized",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\t$bar = $_POST[\'bar\']; \\n",
                                        "line": 10,
                                        "column": 10
                                    },
                                    {
                                        "message": "All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found \'\\"Unescaped output! $foo\\"\'.",
                                        "source": "WordPress.Security.EscapeOutput.OutputNotEscaped",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\techo \\"Unescaped output! $foo\\";\\n",
                                        "line": 12,
                                        "column": 8
                                    },
                                    {
                                        "message": "All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found \'\\"Unescaped output! $bar\\"\'.",
                                        "source": "WordPress.Security.EscapeOutput.OutputNotEscaped",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\techo \\"Unescaped output! $bar\\"; \\/\\/ nosemgrep\\n",
                                        "line": 13,
                                        "column": 8
                                    },
                                    {
                                        "message": "Detected usage of the \\"determine_user\\" filter. Please double-check if this filter is safe and ignore this warning to confirm.",
                                        "source": "QITStandard.PHP.DangerousFilters.RiskyFilterDetected",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "add_filter( \'determine_user\', \'callable\' ); \\/\\/ Risky filter warning.",
                                        "line": 17,
                                        "column": 1
                                    }
                                ]
                            },
                            "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/plugins\\/woocommerce-product-feeds\\/src\\/PluginCode.php": {
                                "errors": 0,
                                "warnings": 0,
                                "messages": []
                            },
                            "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php": {
                                "errors": 2,
                                "warnings": 4,
                                "messages": [
                                    {
                                        "message": "Detected usage of a non-sanitized input variable: $_POST[\'foo\']",
                                        "source": "WordPress.Security.ValidatedSanitizedInput.InputNotSanitized",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\t$foo = $_POST[\'foo\']; \\/\\/ Detected usage of a non-sanitized input variable: $_POST[\'foo\']\\n",
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
                                        "message": "wp_redirect() found. Using wp_safe_redirect(), along with the \\"allowed_redirect_hosts\\" filter if needed, can help avoid any chances of malicious redirects within code. It is also important to remember to call exit() after a redirect so that no other unwanted code is executed.",
                                        "source": "WordPress.Security.SafeRedirect.wp_redirect_wp_redirect",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "\\t\\twp_redirect( $_GET[\'foo\'] ); \\/\\/ Should be flagged by WordPress.Security.SafeRedirect.wp_redirect_wp_redirect.\\n",
                                        "line": 20,
                                        "column": 3
                                    },
                                    {
                                        "message": "Detected usage of the \\"determine_user\\" filter. Please double-check if this filter is safe and ignore this warning to confirm.",
                                        "source": "QITStandard.PHP.DangerousFilters.RiskyFilterDetected",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "add_filter( \'determine_user\', \'callable\' ); \\/\\/ Risky filter warning.",
                                        "line": 27,
                                        "column": 1
                                    }
                                ]
                            }
                        }
                    },
                    "semgrep": {
                        "totals": {
                            "errors": 7,
                            "warnings": 0,
                            "fixable": 0
                        },
                        "files": {
                            "\\/woocommerce-product-feeds\\/exclude-all\\/woocommerce-product-feeds.php": {
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
                            },
                            "\\/woocommerce-product-feeds\\/exclude-escaped-output\\/woocommerce-product-feeds.php": {
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
                            },
                            "\\/woocommerce-product-feeds\\/semgrep-exclusions\\/woocommerce-product-feeds.php": {
                                "errors": 1,
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
                                        "codeFragment": "\\t\\techo \\"Unescaped output! $foo\\";"
                                    }
                                ]
                            },
                            "\\/woocommerce-product-feeds\\/src\\/PluginCode.php": {
                                "errors": 0,
                                "warnings": 0,
                                "messages": []
                            },
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
                    },
                    "composer_audit": [],
                    "npm_audit": [],
                    "wpscan_audit": [],
                    "gitleaks": [
                        {
                            "RuleID": "aws-access-token",
                            "Description": "Identified a pattern that may indicate AWS credentials, risking unauthorized cloud resource access and data breaches on AWS platforms.",
                            "StartLine": 11,
                            "EndLine": 11,
                            "StartColumn": 33,
                            "EndColumn": 52,
                            "Match": "AKIA234567ABCDEF2345",
                            "Secret": "AKIA234567ABCDEF2345",
                            "File": "\\/woocommerce-product-feeds\\/src\\/PluginCode.php",
                            "SymlinkFile": "",
                            "Commit": "",
                            "Entropy": 3.6841838,
                            "Author": "",
                            "Email": "",
                            "Date": "",
                            "Message": "",
                            "Tags": [],
                            "Fingerprint": "\\/woocommerce-product-feeds\\/src\\/PluginCode.php:aws-access-token:11"
                        }
                    ]
                }
            }
        }
    ]
]';
