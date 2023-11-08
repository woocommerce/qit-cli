<?php return '[
    [
        {
            "run_id": 123456,
            "test_type": "e2e",
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
            "test_summary": "Test failed before it was executed.",
            "version": "Undefined",
            "update_complete": true,
            "ai_suggestion_status": "none",
            "test_result_json_extracted": "{EXTRACTED}",
            "debug_log_extracted": "{EXTRACTED}"
        },
        {
            "test_result_json": {
                "numFailedTestSuites": 0,
                "numPassedTestSuites": 0,
                "numPendingTestSuites": 0,
                "numTotalTestSuites": 0,
                "numFailedTests": 0,
                "numPassedTests": 0,
                "numPendingTests": 0,
                "numTotalTests": 0,
                "testResults": [],
                "summary": "Test failed before it was executed."
            }
        },
        {
            "debug_log": [
                {
                    "count": "1",
                    "message": "PHP Notice: Function FeaturesController::get_compatible_plugins_for_feature was called incorrectly. FeaturesController::get_compatible_plugins_for_feature should not be called before the woocommerce_init action. Backtrace: include(\'phar:\\/\\/\\/usr\\/local\\/bin\\/wp\\/php\\/boot-phar.php\'), include(\'phar:\\/\\/\\/usr\\/local\\/bin\\/wp\\/vendor\\/wp-cli\\/wp-cli\\/php\\/wp-cli.php\'), WP_CLI\\\\bootstrap, WP_CLI\\\\Bootstrap\\\\LaunchRunner->process, WP_CLI\\\\Runner->start, WP_CLI\\\\Runner->run_command_and_exit, WP_CLI\\\\Runner->run_command, WP_CLI\\\\Dispatcher\\\\Subcommand->invoke, call_user_func, WP_CLI\\\\Dispatcher\\\\CommandFactory::WP_CLI\\\\Dispatcher\\\\{closure}, call_user_func, Plugin_Command->activate, activate_plugin, do_action(\'activate_woocommerce\\/woocommerce.php\'), WP_Hook->do_action, WP_Hook->apply_filters, WC_Install::install, WC_Install::create_options, WC_Settings_Page->get_settings, WC_Settings_Page->get_settings_for_section, apply_filters(\'woocommerce_get_settings_advanced\'), WP_Hook->apply_filters, Automattic\\\\WooCommerce\\\\Internal\\\\Fea in \\/var\\/www\\/html\\/wp-includes\\/functions.php on line 6031"
                }
            ]
        }
    ]
]';
