<?php return '[
    [
        {
            "test_run_id": 123456,
            "run_id": 123456,
            "test_type": "activation",
            "wordpress_version": "6.0.0-normalized",
            "woocommerce_version": "6.0.0-normalized",
            "php_version": "7.4",
            "max_php_version": "",
            "min_php_version": "",
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
            "test_summary": "7 Errors Detected. (1 Fatal, 2 Warnings, 4 Notices)",
            "debug_log": "",
            "version": "Undefined",
            "update_complete": true,
            "ai_suggestion_status": "none",
            "malware_whitelist_paths": [],
            "test_result_json_extracted": "{EXTRACTED}",
            "syntax_errors_json_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "results_overview": {
                    "total_extensions": "0",
                    "extensions_with_errors": {
                        "": {
                            "\\/": 2,
                            "\\/cart\\/": 2,
                            "\\/my-account\\/": 2,
                            "WP-CLI Plugin Activation": 1
                        }
                    },
                    "error_totals": {
                        "fatal": 1,
                        "notice": 4,
                        "warning": 2,
                        "E_USER_NOTICE": 4,
                        "E_USER_WARNING": 2,
                        "E_ERROR": 1
                    },
                    "summary": "7 Errors Detected. (1 Fatal, 2 Warnings, 4 Notices)",
                    "error_count": 7,
                    "count_extensions_with_errors": 1
                },
                "0": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_NOTICE",
                    "error_message": "Notice on all requests",
                    "error_file": "woocommerce-product-feeds\\/woocommerce-product-feeds.php",
                    "error_line": 16,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php",
                            "line": 16,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 324,
                            "function": "{closure}"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 348,
                            "function": "apply_filters",
                            "class": "WP_Hook",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/plugin.php",
                            "line": 517,
                            "function": "do_action",
                            "class": "WP_Hook",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-settings.php",
                            "line": 695,
                            "function": "do_action"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-config.php",
                            "line": 108,
                            "args": [
                                "\\/var\\/www\\/html\\/wp-settings.php"
                            ],
                            "function": "require_once"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-load.php",
                            "line": 50,
                            "args": [
                                "\\/var\\/www\\/html\\/wp-config.php"
                            ],
                            "function": "require_once"
                        }
                    ],
                    "db_error": ""
                },
                "1": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_WARNING",
                    "error_message": "Warning on all requests",
                    "error_file": "woocommerce-product-feeds\\/woocommerce-product-feeds.php",
                    "error_line": 12,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php",
                            "line": 12,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 324,
                            "function": "{closure}"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 348,
                            "function": "apply_filters",
                            "class": "WP_Hook",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/plugin.php",
                            "line": 565,
                            "function": "do_action",
                            "class": "WP_Hook",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp.php",
                            "line": 830,
                            "function": "do_action_ref_array"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 1336,
                            "function": "main",
                            "class": "WP",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-blog-header.php",
                            "line": 16,
                            "function": "wp"
                        }
                    ],
                    "db_error": ""
                },
                "2": {
                    "activated_alongside": "",
                    "context": "\\/cart\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_NOTICE",
                    "error_message": "Notice on all requests",
                    "error_file": "woocommerce-product-feeds\\/woocommerce-product-feeds.php",
                    "error_line": 16,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php",
                            "line": 16,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 324,
                            "function": "{closure}"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 348,
                            "function": "apply_filters",
                            "class": "WP_Hook",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/plugin.php",
                            "line": 517,
                            "function": "do_action",
                            "class": "WP_Hook",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-settings.php",
                            "line": 695,
                            "function": "do_action"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-config.php",
                            "line": 108,
                            "args": [
                                "\\/var\\/www\\/html\\/wp-settings.php"
                            ],
                            "function": "require_once"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-load.php",
                            "line": 50,
                            "args": [
                                "\\/var\\/www\\/html\\/wp-config.php"
                            ],
                            "function": "require_once"
                        }
                    ],
                    "db_error": ""
                },
                "3": {
                    "activated_alongside": "",
                    "context": "\\/cart\\/",
                    "is_fatal": "Yes",
                    "error_type": "E_ERROR",
                    "error_message": "Call to undefined function call_to_undefined_function()",
                    "error_file": "woocommerce-product-feeds\\/woocommerce-product-feeds.php",
                    "error_line": 9,
                    "backtrace": [
                        {
                            "function": "cd_php_exception_handler"
                        }
                    ],
                    "db_error": ""
                },
                "4": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_NOTICE",
                    "error_message": "Notice on all requests",
                    "error_file": "woocommerce-product-feeds\\/woocommerce-product-feeds.php",
                    "error_line": 16,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php",
                            "line": 16,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 324,
                            "function": "{closure}"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 348,
                            "function": "apply_filters",
                            "class": "WP_Hook",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/plugin.php",
                            "line": 517,
                            "function": "do_action",
                            "class": "WP_Hook",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-settings.php",
                            "line": 695,
                            "function": "do_action"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-config.php",
                            "line": 108,
                            "args": [
                                "\\/var\\/www\\/html\\/wp-settings.php"
                            ],
                            "function": "require_once"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-load.php",
                            "line": 50,
                            "args": [
                                "\\/var\\/www\\/html\\/wp-config.php"
                            ],
                            "function": "require_once"
                        }
                    ],
                    "db_error": ""
                },
                "5": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_WARNING",
                    "error_message": "Warning on all requests",
                    "error_file": "woocommerce-product-feeds\\/woocommerce-product-feeds.php",
                    "error_line": 12,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php",
                            "line": 12,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 324,
                            "function": "{closure}"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 348,
                            "function": "apply_filters",
                            "class": "WP_Hook",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/plugin.php",
                            "line": 565,
                            "function": "do_action",
                            "class": "WP_Hook",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp.php",
                            "line": 830,
                            "function": "do_action_ref_array"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 1336,
                            "function": "main",
                            "class": "WP",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-blog-header.php",
                            "line": 16,
                            "function": "wp"
                        }
                    ],
                    "db_error": ""
                },
                "6": {
                    "activated_alongside": "",
                    "context": "WP-CLI Plugin Activation",
                    "is_fatal": "No",
                    "error_type": "E_USER_NOTICE",
                    "error_message": "Notice on all requests",
                    "error_file": "woocommerce-product-feeds\\/woocommerce-product-feeds.php",
                    "error_line": 16,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php",
                            "line": 16,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 324,
                            "function": "{closure}",
                            "class": "WP_CLI\\\\Runner",
                            "type": "::"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 348,
                            "function": "apply_filters",
                            "class": "WP_Hook",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/plugin.php",
                            "line": 517,
                            "function": "do_action",
                            "class": "WP_Hook",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-settings.php",
                            "line": 695,
                            "function": "do_action"
                        },
                        {
                            "file": "phar:\\/\\/\\/usr\\/local\\/bin\\/wp\\/vendor\\/wp-cli\\/wp-cli\\/php\\/WP_CLI\\/Runner.php",
                            "line": 1363,
                            "args": [
                                "\\/var\\/www\\/html\\/wp-settings.php"
                            ],
                            "function": "require"
                        },
                        {
                            "file": "phar:\\/\\/\\/usr\\/local\\/bin\\/wp\\/vendor\\/wp-cli\\/wp-cli\\/php\\/WP_CLI\\/Runner.php",
                            "line": 1282,
                            "function": "load_wordpress",
                            "class": "WP_CLI\\\\Runner",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                }
            }
        },
        {
            "syntax_errors_json": []
        }
    ]
]';
