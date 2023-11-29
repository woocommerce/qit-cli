<?php return '[
    [
        {
            "run_id": 123456,
            "test_type": "activation",
            "wordpress_version": "6.0.0-normalized",
            "woocommerce_version": "6.0.0-normalized",
            "php_version": "8.2",
            "max_php_version": "",
            "min_php_version": "",
            "additional_woo_plugins": [],
            "additional_wp_plugins": [],
            "test_log": "",
            "status": "warning",
            "test_result_aws_url": "https:\\/\\/test-results-aws.com",
            "test_result_aws_expiration": 1234567890,
            "is_development": true,
            "send_notifications": false,
            "woo_extension": {
                "id": 565154,
                "host": "wporg",
                "name": "Storefront",
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
            "test_summary": "1 Errors Detected. (0 Fatal, 0 Warnings, 1 Notices)",
            "debug_log": "",
            "version": "Undefined",
            "update_complete": true,
            "ai_suggestion_status": "none",
            "test_result_json_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "results_overview": {
                    "total_extensions": "0",
                    "extensions_with_errors": {
                        "": {
                            "WP-CLI Plugin Activation": 1
                        }
                    },
                    "error_totals": {
                        "fatal": 0,
                        "notice": 1,
                        "warning": 0,
                        "E_DEPRECATED": 1
                    },
                    "summary": "1 Errors Detected. (0 Fatal, 0 Warnings, 1 Notices)",
                    "error_count": 1,
                    "count_extensions_with_errors": 1
                },
                "0": {
                    "activated_alongside": "",
                    "context": "WP-CLI Plugin Activation",
                    "is_fatal": "No",
                    "error_type": "E_DEPRECATED",
                    "error_message": "Creation of dynamic property Theme_Command::$fetcher is deprecated",
                    "error_file": "phar:\\/\\/\\/usr\\/local\\/bin\\/wp\\/vendor\\/wp-cli\\/extension-command\\/src\\/WP_CLI\\/CommandWithUpgrade.php",
                    "error_line": 45,
                    "backtrace": [
                        {
                            "file": "phar:\\/\\/\\/usr\\/local\\/bin\\/wp\\/vendor\\/wp-cli\\/extension-command\\/src\\/Theme_Command.php",
                            "line": 58,
                            "function": "__construct",
                            "class": "WP_CLI\\\\CommandWithUpgrade",
                            "type": "->"
                        },
                        {
                            "file": "phar:\\/\\/\\/usr\\/local\\/bin\\/wp\\/vendor\\/wp-cli\\/wp-cli\\/php\\/WP_CLI\\/Dispatcher\\/CommandFactory.php",
                            "line": 99,
                            "function": "__construct",
                            "class": "Theme_Command",
                            "type": "->"
                        },
                        {
                            "function": "WP_CLI\\\\Dispatcher\\\\{closure}",
                            "class": "WP_CLI\\\\Dispatcher\\\\CommandFactory",
                            "type": "::"
                        },
                        {
                            "file": "phar:\\/\\/\\/usr\\/local\\/bin\\/wp\\/vendor\\/wp-cli\\/wp-cli\\/php\\/WP_CLI\\/Dispatcher\\/Subcommand.php",
                            "line": 491,
                            "function": "call_user_func"
                        },
                        {
                            "file": "phar:\\/\\/\\/usr\\/local\\/bin\\/wp\\/vendor\\/wp-cli\\/wp-cli\\/php\\/WP_CLI\\/Runner.php",
                            "line": 419,
                            "function": "invoke",
                            "class": "WP_CLI\\\\Dispatcher\\\\Subcommand",
                            "type": "->"
                        },
                        {
                            "file": "phar:\\/\\/\\/usr\\/local\\/bin\\/wp\\/vendor\\/wp-cli\\/wp-cli\\/php\\/WP_CLI\\/Runner.php",
                            "line": 442,
                            "function": "run_command",
                            "class": "WP_CLI\\\\Runner",
                            "type": "->"
                        },
                        {
                            "file": "phar:\\/\\/\\/usr\\/local\\/bin\\/wp\\/vendor\\/wp-cli\\/wp-cli\\/php\\/WP_CLI\\/Runner.php",
                            "line": 1256,
                            "function": "run_command_and_exit",
                            "class": "WP_CLI\\\\Runner",
                            "type": "->"
                        }
                    ],
                    "db_error": ""
                }
            }
        }
    ]
]';
