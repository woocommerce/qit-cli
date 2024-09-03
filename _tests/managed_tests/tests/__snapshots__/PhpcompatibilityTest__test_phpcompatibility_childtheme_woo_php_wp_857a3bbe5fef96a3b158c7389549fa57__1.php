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
                "id": 1822936,
                "host": "wccom",
                "name": "Bistro",
                "type": "theme"
            },
            "client": "qit_cli",
            "event": "cli_development_extension_test",
            "optional_features": {
                "hpos": false,
                "new_product_editor": false
            },
            "test_results_manager_url": "https:\\/\\/test-results-manager.com",
            "test_results_manager_expiration": 1234567890,
            "test_summary": "Errors: 13 Warnings: 5",
            "debug_log": "",
            "version": "1.0.15",
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
                            "errors": 13,
                            "warnings": 5,
                            "fixable": 1
                        },
                        "files": {
                            "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/themes\\/bistro\\/front-page.php": {
                                "errors": 13,
                                "warnings": 5,
                                "messages": [
                                    {
                                        "message": "Trailing commas are not allowed in function calls in PHP 7.2 or earlier",
                                        "source": "PHPCompatibility.Syntax.NewFunctionCallTrailingComma.FoundInFunctionCall",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 14,
                                        "column": 30
                                    },
                                    {
                                        "message": "The \\"fn\\" keyword for arrow functions is not present in PHP version 7.3 or earlier",
                                        "source": "PHPCompatibility.Keywords.NewKeywords.t_fnFound",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 17,
                                        "column": 18
                                    },
                                    {
                                        "message": "Typed properties are not supported in PHP 7.3 or earlier. Found: string",
                                        "source": "PHPCompatibility.Classes.NewTypedProperties.Found",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 24,
                                        "column": 11
                                    },
                                    {
                                        "message": "Readonly properties are not supported in PHP 8.0 or earlier. Property $foo was declared as readonly.",
                                        "source": "PHPCompatibility.Classes.NewReadonlyProperties.Found",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 24,
                                        "column": 18
                                    },
                                    {
                                        "message": "Readonly classes are not supported in PHP 8.1 or earlier.",
                                        "source": "PHPCompatibility.Classes.NewReadonlyClasses.Found",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 28,
                                        "column": 1
                                    },
                                    {
                                        "message": "Function create_function() is deprecated since PHP 7.2 and removed since PHP 8.0; Use an anonymous function instead",
                                        "source": "PHPCompatibility.FunctionUse.RemovedFunctions.create_functionDeprecatedRemoved",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 34,
                                        "column": 12
                                    },
                                    {
                                        "message": "Specifying an autoloader using an __autoload() function is deprecated since PHP 7.2 and no longer supported since PHP 8.0",
                                        "source": "PHPCompatibility.FunctionNameRestrictions.RemovedMagicAutoload.Removed",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 38,
                                        "column": 1
                                    },
                                    {
                                        "message": "The constant \\"FILTER_FLAG_SCHEME_REQUIRED\\" is deprecated since PHP 7.3 and removed since PHP 8.0",
                                        "source": "PHPCompatibility.Constants.RemovedConstants.filter_flag_scheme_requiredDeprecatedRemoved",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 44,
                                        "column": 55
                                    },
                                    {
                                        "message": "The constant \\"FILTER_FLAG_HOST_REQUIRED\\" is deprecated since PHP 7.3 and removed since PHP 8.0",
                                        "source": "PHPCompatibility.Constants.RemovedConstants.filter_flag_host_requiredDeprecatedRemoved",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 44,
                                        "column": 85
                                    },
                                    {
                                        "message": "Passing the $glue and $pieces parameters in reverse order to implode has been deprecated since PHP 7.4 and is removed since PHP 8.0; $glue should be the first parameter and $pieces the second",
                                        "source": "PHPCompatibility.ParameterValues.RemovedImplodeFlexibleParamOrder.Removed",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 48,
                                        "column": 1
                                    },
                                    {
                                        "message": "Curly brace syntax for accessing array elements and string offsets has been deprecated in PHP 7.4 and removed in PHP 8.0. Found: $array{0}",
                                        "source": "PHPCompatibility.Syntax.RemovedCurlyBraceArrayAccess.Removed",
                                        "severity": 5,
                                        "fixable": true,
                                        "type": "ERROR",
                                        "line": 53,
                                        "column": 18
                                    },
                                    {
                                        "message": "Function get_magic_quotes_gpc() is deprecated since PHP 7.4 and removed since PHP 8.0",
                                        "source": "PHPCompatibility.FunctionUse.RemovedFunctions.get_magic_quotes_gpcDeprecatedRemoved",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 57,
                                        "column": 17
                                    },
                                    {
                                        "message": "Declaring an optional parameter before a required parameter is deprecated since PHP 8.0. Parameter $a is optional, while parameter $b is required. The $a parameter is implicitly treated as a required parameter.",
                                        "source": "PHPCompatibility.FunctionDeclarations.RemovedOptionalBeforeRequiredParam.Deprecated80",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "line": 61,
                                        "column": 26
                                    },
                                    {
                                        "message": "\\"Only Serializable\\" classes are deprecated since PHP 8.1. The magic __serialize() and __unserialize() methods need to be implemented for cross-version compatibility. Missing implementation of: __serialize() and __unserialize()",
                                        "source": "PHPCompatibility.Interfaces.RemovedSerializable.Deprecated",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "line": 73,
                                        "column": 1
                                    },
                                    {
                                        "message": "Function utf8_encode() is deprecated since PHP 8.2; Use mb_convert_encoding(), UConverter::transcode() or iconv instead",
                                        "source": "PHPCompatibility.FunctionUse.RemovedFunctions.utf8_encodeDeprecated",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "line": 85,
                                        "column": 1
                                    },
                                    {
                                        "message": "The function json_validate() is not present in PHP version 8.2 or earlier",
                                        "source": "PHPCompatibility.FunctionUse.NewFunctions.json_validateFound",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "line": 89,
                                        "column": 8
                                    },
                                    {
                                        "message": "Calling get_class() without the $object argument is deprecated since PHP 8.3.",
                                        "source": "PHPCompatibility.ParameterValues.RemovedGetClassNoArgs.ArgMissing",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "line": 95,
                                        "column": 16
                                    },
                                    {
                                        "message": "Calling get_parent_class() without the $object_or_class argument is deprecated since PHP 8.3.",
                                        "source": "PHPCompatibility.ParameterValues.RemovedGetClassNoArgs.ArgMissing",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "line": 99,
                                        "column": 22
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
