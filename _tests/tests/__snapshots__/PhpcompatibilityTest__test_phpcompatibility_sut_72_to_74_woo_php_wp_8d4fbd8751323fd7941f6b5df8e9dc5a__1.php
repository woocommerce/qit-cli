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
            "min_php_version": "7.2",
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
            "test_summary": "Errors: 6 Warnings: 7",
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
                "tool": {
                    "phpcs": {
                        "totals": {
                            "errors": 6,
                            "warnings": 7,
                            "fixable": 1
                        },
                        "files": {
                            "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php": {
                                "errors": 6,
                                "warnings": 7,
                                "messages": [
                                    {
                                        "message": "Trailing commas are not allowed in function calls in PHP 7.2 or earlier",
                                        "source": "PHPCompatibility.Syntax.NewFunctionCallTrailingComma.FoundInFunctionCall",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 18,
                                        "column": 30
                                    },
                                    {
                                        "message": "The \\"fn\\" keyword for arrow functions is not present in PHP version 7.3 or earlier",
                                        "source": "PHPCompatibility.Keywords.NewKeywords.t_fnFound",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 21,
                                        "column": 18
                                    },
                                    {
                                        "message": "Typed properties are not supported in PHP 7.3 or earlier. Found: string",
                                        "source": "PHPCompatibility.Classes.NewTypedProperties.Found",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 28,
                                        "column": 11
                                    },
                                    {
                                        "message": "Readonly properties are not supported in PHP 8.0 or earlier. Property $foo was declared as readonly.",
                                        "source": "PHPCompatibility.Classes.NewReadonlyProperties.Found",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 28,
                                        "column": 18
                                    },
                                    {
                                        "message": "Readonly classes are not supported in PHP 8.1 or earlier.",
                                        "source": "PHPCompatibility.Classes.NewReadonlyClasses.Found",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 32,
                                        "column": 1
                                    },
                                    {
                                        "message": "Function create_function() is deprecated since PHP 7.2; Use an anonymous function instead",
                                        "source": "PHPCompatibility.FunctionUse.RemovedFunctions.create_functionDeprecated",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "line": 38,
                                        "column": 12
                                    },
                                    {
                                        "message": "Specifying an autoloader using an __autoload() function is deprecated since PHP 7.2",
                                        "source": "PHPCompatibility.FunctionNameRestrictions.RemovedMagicAutoload.Deprecated",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "line": 42,
                                        "column": 1
                                    },
                                    {
                                        "message": "The constant \\"FILTER_FLAG_SCHEME_REQUIRED\\" is deprecated since PHP 7.3",
                                        "source": "PHPCompatibility.Constants.RemovedConstants.filter_flag_scheme_requiredDeprecated",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "line": 48,
                                        "column": 55
                                    },
                                    {
                                        "message": "The constant \\"FILTER_FLAG_HOST_REQUIRED\\" is deprecated since PHP 7.3",
                                        "source": "PHPCompatibility.Constants.RemovedConstants.filter_flag_host_requiredDeprecated",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "line": 48,
                                        "column": 85
                                    },
                                    {
                                        "message": "Passing the $glue and $pieces parameters in reverse order to implode has been deprecated since PHP 7.4; $glue should be the first parameter and $pieces the second",
                                        "source": "PHPCompatibility.ParameterValues.RemovedImplodeFlexibleParamOrder.Deprecated",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "line": 52,
                                        "column": 1
                                    },
                                    {
                                        "message": "Curly brace syntax for accessing array elements and string offsets has been deprecated in PHP 7.4. Found: $array{0}",
                                        "source": "PHPCompatibility.Syntax.RemovedCurlyBraceArrayAccess.Deprecated",
                                        "severity": 5,
                                        "fixable": true,
                                        "type": "WARNING",
                                        "line": 57,
                                        "column": 18
                                    },
                                    {
                                        "message": "Function get_magic_quotes_gpc() is deprecated since PHP 7.4",
                                        "source": "PHPCompatibility.FunctionUse.RemovedFunctions.get_magic_quotes_gpcDeprecated",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "line": 61,
                                        "column": 17
                                    },
                                    {
                                        "message": "The function json_validate() is not present in PHP version 8.2 or earlier",
                                        "source": "PHPCompatibility.FunctionUse.NewFunctions.json_validateFound",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 93,
                                        "column": 8
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
