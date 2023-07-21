<?php return '[
    [
        {
            "run_id": 123456,
            "test_type": "activation",
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
            "test_summary": "154 Errors Detected. (1 Fatal, 2 Warnings, 3 Notices)",
            "debug_log": "",
            "version": "Undefined",
            "update_complete": true,
            "test_result_json_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "results_overview": {
                    "total_extensions": "0",
                    "extensions_with_errors": {
                        "": {
                            "\\/": 76,
                            "\\/cart\\/": 2,
                            "\\/my-account\\/": 76
                        }
                    },
                    "error_totals": {
                        "fatal": 1,
                        "notice": 3,
                        "warning": 2,
                        "E_USER_NOTICE": 3,
                        "E_USER_WARNING": 2,
                        "E_ERROR": 1
                    },
                    "summary": "154 Errors Detected. (1 Fatal, 2 Warnings, 3 Notices)",
                    "error_count": 154,
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
                            "line": 310,
                            "function": "{closure}"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                            "line": 631,
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
                            "line": 310,
                            "function": "{closure}"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                            "line": 796,
                            "function": "do_action_ref_array"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 1335,
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
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                        }
                    ],
                    "db_error": ""
                },
                "3": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                        }
                    ],
                    "db_error": ""
                },
                "4": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "5": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "6": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "7": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "8": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
                            "function": "apply_filters",
                            "class": "WP_Hook",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "9": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
                            "function": "apply_filters",
                            "class": "WP_Hook",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "10": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                        }
                    ],
                    "db_error": ""
                },
                "11": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                        }
                    ],
                    "db_error": ""
                },
                "12": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
                            "function": "apply_filters",
                            "class": "WP_Hook",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "13": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
                            "function": "apply_filters",
                            "class": "WP_Hook",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "14": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "15": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "16": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "17": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "18": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "19": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "20": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "21": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "22": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "23": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "24": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "25": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "26": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "27": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "28": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "29": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "30": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "31": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "32": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "33": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "34": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "35": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "36": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "37": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "38": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "39": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "40": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "41": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "42": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "43": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "44": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "45": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "46": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "47": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "48": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "49": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "50": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "51": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "52": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "53": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "54": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "55": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "56": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
                            "function": "apply_filters",
                            "class": "WP_Hook",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "57": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
                            "function": "apply_filters",
                            "class": "WP_Hook",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "58": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "59": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "60": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
                            "function": "apply_filters",
                            "class": "WP_Hook",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "61": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
                            "function": "apply_filters",
                            "class": "WP_Hook",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "62": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
                            "function": "apply_filters",
                            "class": "WP_Hook",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "63": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
                            "function": "apply_filters",
                            "class": "WP_Hook",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "64": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
                            "function": "apply_filters",
                            "class": "WP_Hook",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "65": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
                            "function": "apply_filters",
                            "class": "WP_Hook",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "66": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                        }
                    ],
                    "db_error": ""
                },
                "67": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                        }
                    ],
                    "db_error": ""
                },
                "68": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                        }
                    ],
                    "db_error": ""
                },
                "69": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                        }
                    ],
                    "db_error": ""
                },
                "70": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                        }
                    ],
                    "db_error": ""
                },
                "71": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                        }
                    ],
                    "db_error": ""
                },
                "72": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                        }
                    ],
                    "db_error": ""
                },
                "73": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                        }
                    ],
                    "db_error": ""
                },
                "74": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                        }
                    ],
                    "db_error": ""
                },
                "75": {
                    "activated_alongside": "",
                    "context": "\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                        }
                    ],
                    "db_error": ""
                },
                "76": {
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
                            "line": 310,
                            "function": "{closure}"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                            "line": 631,
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
                "77": {
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
                "78": {
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
                            "line": 310,
                            "function": "{closure}"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                            "line": 631,
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
                "79": {
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
                            "line": 310,
                            "function": "{closure}"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                            "line": 796,
                            "function": "do_action_ref_array"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 1335,
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
                "80": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                        }
                    ],
                    "db_error": ""
                },
                "81": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                        }
                    ],
                    "db_error": ""
                },
                "82": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "83": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "84": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "85": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "86": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
                            "function": "apply_filters",
                            "class": "WP_Hook",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "87": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
                            "function": "apply_filters",
                            "class": "WP_Hook",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "88": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                        }
                    ],
                    "db_error": ""
                },
                "89": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                        }
                    ],
                    "db_error": ""
                },
                "90": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
                            "function": "apply_filters",
                            "class": "WP_Hook",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "91": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
                            "function": "apply_filters",
                            "class": "WP_Hook",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "92": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "93": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "94": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "95": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "96": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "97": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "98": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "99": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "100": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "101": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "102": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "103": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "104": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "105": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "106": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "107": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "108": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "109": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "110": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "111": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "112": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "113": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "114": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "115": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "116": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "117": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "118": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "119": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "120": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "121": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "122": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "123": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "124": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "125": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "126": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "127": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "128": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "129": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "130": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "131": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "132": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "133": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "134": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
                            "function": "apply_filters",
                            "class": "WP_Hook",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "135": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
                            "function": "apply_filters",
                            "class": "WP_Hook",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "136": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "137": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "138": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
                            "function": "apply_filters",
                            "class": "WP_Hook",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "139": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
                            "function": "apply_filters",
                            "class": "WP_Hook",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "140": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
                            "function": "apply_filters",
                            "class": "WP_Hook",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "141": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
                            "function": "apply_filters",
                            "class": "WP_Hook",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "142": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
                            "function": "apply_filters",
                            "class": "WP_Hook",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "143": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 305,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
                            "function": "apply_filters",
                            "class": "WP_Hook",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                },
                "144": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                        }
                    ],
                    "db_error": ""
                },
                "145": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                        }
                    ],
                    "db_error": ""
                },
                "146": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                        }
                    ],
                    "db_error": ""
                },
                "147": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                        }
                    ],
                    "db_error": ""
                },
                "148": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                        }
                    ],
                    "db_error": ""
                },
                "149": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                        }
                    ],
                    "db_error": ""
                },
                "150": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                        }
                    ],
                    "db_error": ""
                },
                "151": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                        }
                    ],
                    "db_error": ""
                },
                "152": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 319,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                        }
                    ],
                    "db_error": ""
                },
                "153": {
                    "activated_alongside": "",
                    "context": "\\/my-account\\/",
                    "is_fatal": "No",
                    "error_type": "E_USER_DEPRECATED",
                    "error_message": "Function WP_Scripts::print_inline_script is <strong>deprecated<\\/strong> since version 6.3.0! Use WP_Scripts::get_inline_script_data() or WP_Scripts::get_inline_script_tag() instead.",
                    "error_file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                    "error_line": 5453,
                    "backtrace": [
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/functions.php",
                            "line": 5453,
                            "function": "trigger_error"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-scripts.php",
                            "line": 485,
                            "function": "_deprecated_function"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 320,
                            "function": "print_inline_script",
                            "class": "WP_Scripts",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-content\\/plugins\\/woocommerce\\/packages\\/woocommerce-blocks\\/src\\/BlockTypes\\/MiniCart.php",
                            "line": 207,
                            "function": "append_script_and_deps_src",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 310,
                            "function": "print_lazy_load_scripts",
                            "class": "Automattic\\\\WooCommerce\\\\Blocks\\\\BlockTypes\\\\MiniCart",
                            "type": "->"
                        },
                        {
                            "file": "\\/var\\/www\\/html\\/wp-includes\\/class-wp-hook.php",
                            "line": 334,
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
                        }
                    ],
                    "db_error": ""
                }
            }
        }
    ]
]';
