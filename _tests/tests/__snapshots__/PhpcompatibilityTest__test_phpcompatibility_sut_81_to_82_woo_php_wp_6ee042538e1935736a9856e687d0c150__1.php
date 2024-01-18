<?php return '[
    [
        {
            "test_run_id": 123456,
            "run_id": 123456,
            "test_type": "phpcompatibility",
            "wordpress_version": "6.0.0-normalized",
            "woocommerce_version": "6.0.0-normalized",
            "php_version": "7.4",
            "max_php_version": "8.2",
            "min_php_version": "8.1",
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
            "test_summary": "Errors: 9 Warnings: 3",
            "debug_log": "",
            "version": "Undefined",
            "update_complete": true,
            "ai_suggestion_status": "none",
            "malware_whitelist_paths": [],
            "test_result_json_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "tool": {
                    "phpcs": {
                        "totals": {
                            "errors": 9,
                            "warnings": 3,
                            "fixable": 1
                        },
                        "files": {
                            "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php": {
                                "errors": 9,
                                "warnings": 3,
                                "messages": [
                                    {
                                        "message": "Readonly classes are not supported in PHP 8.1 or earlier.",
                                        "source": "PHPCompatibility.Classes.NewReadonlyClasses.Found",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 33,
                                        "column": 1
                                    },
                                    {
                                        "message": "Function create_function() is deprecated since PHP 7.2 and removed since PHP 8.0; Use an anonymous function instead",
                                        "source": "PHPCompatibility.FunctionUse.RemovedFunctions.create_functionDeprecatedRemoved",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 39,
                                        "column": 12
                                    },
                                    {
                                        "message": "Specifying an autoloader using an __autoload() function is deprecated since PHP 7.2 and no longer supported since PHP 8.0",
                                        "source": "PHPCompatibility.FunctionNameRestrictions.RemovedMagicAutoload.Removed",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 43,
                                        "column": 1
                                    },
                                    {
                                        "message": "The constant \\"FILTER_FLAG_SCHEME_REQUIRED\\" is deprecated since PHP 7.3 and removed since PHP 8.0",
                                        "source": "PHPCompatibility.Constants.RemovedConstants.filter_flag_scheme_requiredDeprecatedRemoved",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 49,
                                        "column": 55
                                    },
                                    {
                                        "message": "The constant \\"FILTER_FLAG_HOST_REQUIRED\\" is deprecated since PHP 7.3 and removed since PHP 8.0",
                                        "source": "PHPCompatibility.Constants.RemovedConstants.filter_flag_host_requiredDeprecatedRemoved",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 49,
                                        "column": 85
                                    },
                                    {
                                        "message": "Passing the $glue and $pieces parameters in reverse order to implode has been deprecated since PHP 7.4 and is removed since PHP 8.0; $glue should be the first parameter and $pieces the second",
                                        "source": "PHPCompatibility.ParameterValues.RemovedImplodeFlexibleParamOrder.Removed",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 53,
                                        "column": 1
                                    },
                                    {
                                        "message": "Curly brace syntax for accessing array elements and string offsets has been deprecated in PHP 7.4 and removed in PHP 8.0. Found: $array{0}",
                                        "source": "PHPCompatibility.Syntax.RemovedCurlyBraceArrayAccess.Removed",
                                        "severity": 5,
                                        "fixable": true,
                                        "type": "ERROR",
                                        "line": 58,
                                        "column": 18
                                    },
                                    {
                                        "message": "Function get_magic_quotes_gpc() is deprecated since PHP 7.4 and removed since PHP 8.0",
                                        "source": "PHPCompatibility.FunctionUse.RemovedFunctions.get_magic_quotes_gpcDeprecatedRemoved",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 62,
                                        "column": 17
                                    },
                                    {
                                        "message": "Declaring a required parameter after an optional one is deprecated since PHP 8.0. Parameter $a is optional, while parameter $b is required.",
                                        "source": "PHPCompatibility.FunctionDeclarations.RemovedOptionalBeforeRequiredParam.Deprecated",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "line": 66,
                                        "column": 35
                                    },
                                    {
                                        "message": "\\"Only Serializable\\" classes are deprecated since PHP 8.1. The magic __serialize() and __unserialize() methods need to be implemented for cross-version compatibility. Missing implementation of: __serialize() and __unserialize()",
                                        "source": "PHPCompatibility.Interfaces.RemovedSerializable.Deprecated",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "line": 78,
                                        "column": 1
                                    },
                                    {
                                        "message": "Function utf8_encode() is deprecated since PHP 8.2; Use mb_convert_encoding(), UConverter::transcode() or iconv instead",
                                        "source": "PHPCompatibility.FunctionUse.RemovedFunctions.utf8_encodeDeprecated",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "line": 90,
                                        "column": 1
                                    },
                                    {
                                        "message": "The function json_validate() is not present in PHP version 8.2 or earlier",
                                        "source": "PHPCompatibility.FunctionUse.NewFunctions.json_validateFound",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 94,
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
