<?php return '[
    [
        {
            "run_id": 123456,
            "test_type": "activation",
            "wordpress_version": "6.0.0-normalized",
            "woocommerce_version": "6.0.0-normalized",
            "php_version": "8.1",
            "additional_woo_plugins": [],
            "additional_wp_plugins": [],
            "test_log": "",
            "status": "warning",
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
            "test_summary": "7 Errors Detected. (0 Fatal, 0 Warnings, 7 Notices)",
            "debug_log": "",
            "version": "Zip",
            "test_result_json_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "results_overview": {
                    "total_extensions": "0",
                    "extensions_with_errors": {
                        "": {
                            "\\/": 3,
                            "\\/cart\\/": 2,
                            "\\/my-account\\/": 2
                        }
                    },
                    "error_totals": {
                        "fatal": 0,
                        "notice": 7,
                        "warning": 0,
                        "E_DEPRECATED": 7
                    },
                    "summary": "7 Errors Detected. (0 Fatal, 0 Warnings, 7 Notices)",
                    "error_count": 7,
                    "count_extensions_with_errors": 1
                },
                "0": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_DEPRECATED",
                    "error_message": "SUT\\\\BarUser implements the Serializable interface, which is deprecated. Implement __serialize() and __unserialize() instead (or in addition, if support for old PHP versions is necessary)",
                    "error_file": "woocommerce-product-feeds\\/woocommerce-product-feeds.php",
                    "error_line": 19,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 308,
                            "function": "SUT\\\\{closure}"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 332,
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
                            "line": 623,
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
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-blog-header.php",
                            "line": 13,
                            "args": [
                                "\\/var\\/www\\/html\\/wp-load.php"
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
                    "error_type": "E_DEPRECATED",
                    "error_message": "strlen(): Passing null to parameter #1 ($string) of type string is deprecated",
                    "error_file": "woocommerce-product-feeds\\/woocommerce-product-feeds.php",
                    "error_line": 43,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php",
                            "line": 43,
                            "function": "strlen"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 308,
                            "function": "SUT\\\\{closure}"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 332,
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
                            "line": 797,
                            "function": "do_action_ref_array"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 1334,
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
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_DEPRECATED",
                    "error_message": "Automatic conversion of false to array is deprecated",
                    "error_file": "Admin\\/DataSourcePoller.php",
                    "error_line": 138,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/DataSourcePoller.php",
                            "line": 112,
                            "function": "read_specs_from_data_sources",
                            "class": "Automattic\\\\WooCommerce\\\\Admin\\\\DataSourcePoller",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Internal\\/Admin\\/RemoteFreeExtensions\\/Init.php",
                            "line": 72,
                            "function": "get_specs_from_data_sources",
                            "class": "Automattic\\\\WooCommerce\\\\Admin\\\\DataSourcePoller",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Internal\\/Admin\\/RemoteFreeExtensions\\/Init.php",
                            "line": 33,
                            "function": "get_specs",
                            "class": "Automattic\\\\WooCommerce\\\\Internal\\\\Admin\\\\RemoteFreeExtensions\\\\Init",
                            "type": "::"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/Features\\/OnboardingTasks\\/Tasks\\/Marketing.php",
                            "line": 84,
                            "function": "get_extensions",
                            "class": "Automattic\\\\WooCommerce\\\\Internal\\\\Admin\\\\RemoteFreeExtensions\\\\Init",
                            "type": "::"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/Features\\/OnboardingTasks\\/Tasks\\/Marketing.php",
                            "line": 73,
                            "function": "get_plugins",
                            "class": "Automattic\\\\WooCommerce\\\\Admin\\\\Features\\\\OnboardingTasks\\\\Tasks\\\\Marketing",
                            "type": "::"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/src\\/Admin\\/Features\\/OnboardingTasks\\/TaskList.php",
                            "line": 304,
                            "function": "can_view",
                            "class": "Automattic\\\\WooCommerce\\\\Admin\\\\Features\\\\OnboardingTasks\\\\Tasks\\\\Marketing",
                            "type": "->"
                        },
                        {
                            "function": "Automattic\\\\WooCommerce\\\\Admin\\\\Features\\\\OnboardingTasks\\\\{closure}",
                            "class": "Automattic\\\\WooCommerce\\\\Admin\\\\Features\\\\OnboardingTasks\\\\TaskList",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "3": {
                    "activated_alongside": "",
                    "context": "\\/cart\\/",
                    "is_fatal": "No",
                    "error_type": "E_DEPRECATED",
                    "error_message": "SUT\\\\BarUser implements the Serializable interface, which is deprecated. Implement __serialize() and __unserialize() instead (or in addition, if support for old PHP versions is necessary)",
                    "error_file": "woocommerce-product-feeds\\/woocommerce-product-feeds.php",
                    "error_line": 19,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 308,
                            "function": "SUT\\\\{closure}"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 332,
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
                            "line": 623,
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
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-blog-header.php",
                            "line": 13,
                            "args": [
                                "\\/var\\/www\\/html\\/wp-load.php"
                            ],
                            "function": "require_once"
                        }
                    ],
                    "db_error": ""
                },
                "4": {
                    "activated_alongside": "",
                    "context": "\\/cart\\/",
                    "is_fatal": "No",
                    "error_type": "E_DEPRECATED",
                    "error_message": "strlen(): Passing null to parameter #1 ($string) of type string is deprecated",
                    "error_file": "woocommerce-product-feeds\\/woocommerce-product-feeds.php",
                    "error_line": 43,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php",
                            "line": 43,
                            "function": "strlen"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 308,
                            "function": "SUT\\\\{closure}"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 332,
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
                            "line": 797,
                            "function": "do_action_ref_array"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 1334,
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
                "5": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_DEPRECATED",
                    "error_message": "SUT\\\\BarUser implements the Serializable interface, which is deprecated. Implement __serialize() and __unserialize() instead (or in addition, if support for old PHP versions is necessary)",
                    "error_file": "woocommerce-product-feeds\\/woocommerce-product-feeds.php",
                    "error_line": 19,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 308,
                            "function": "SUT\\\\{closure}"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 332,
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
                            "line": 623,
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
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-blog-header.php",
                            "line": 13,
                            "args": [
                                "\\/var\\/www\\/html\\/wp-load.php"
                            ],
                            "function": "require_once"
                        }
                    ],
                    "db_error": ""
                },
                "6": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_DEPRECATED",
                    "error_message": "strlen(): Passing null to parameter #1 ($string) of type string is deprecated",
                    "error_file": "woocommerce-product-feeds\\/woocommerce-product-feeds.php",
                    "error_line": 43,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php",
                            "line": 43,
                            "function": "strlen"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 308,
                            "function": "SUT\\\\{closure}"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 332,
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
                            "line": 797,
                            "function": "do_action_ref_array"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 1334,
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
                }
            }
        }
    ]
]';
