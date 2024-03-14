<?php return '[
    [
        {
            "test_run_id": 123456,
            "run_id": 123456,
            "test_type": "security",
            "wordpress_version": "6.0.0-normalized",
            "woocommerce_version": "6.0.0-normalized",
            "php_version": "7.4",
            "max_php_version": "",
            "min_php_version": "",
            "additional_woo_plugins": [],
            "additional_wp_plugins": [],
            "test_log": "",
            "status": "success",
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
            "test_summary": "",
            "debug_log": "",
            "version": "0.1-test-version",
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
                            "errors": 304,
                            "warnings": 164,
                            "fixable": 0
                        },
                        "files": {
                            "\\/home\\/runner\\/work\\/qit-runner\\/qit-runner\\/ci\\/plugins\\/woocommerce-product-feeds\\/woocommerce-product-feeds.php": {
                                "errors": 304,
                                "warnings": 164,
                                "messages": [
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'\\" . $_GET[\'title\'] . \\"\';\\" ); \\/\\/ Bad.\\n",
                                        "line": 9,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'\\" . $_GET[\'title\'] . \\"\';\\" ); \\/\\/ Bad.\\n",
                                        "line": 9,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found $_GET",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'\\" . $_GET[\'title\'] . \\"\';\\" ); \\/\\/ Bad.\\n",
                                        "line": 9,
                                        "column": 70
                                    },
                                    {
                                        "message": "Detected usage of a non-sanitized input variable: $_GET[\'title\']",
                                        "source": "WordPress.Security.ValidatedSanitizedInput.InputNotSanitized",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'\\" . $_GET[\'title\'] . \\"\';\\" ); \\/\\/ Bad.\\n",
                                        "line": 9,
                                        "column": 70
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'{$_GET[\'title\']}\';\\" ); \\/\\/ Bad.\\n",
                                        "line": 10,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'{$_GET[\'title\']}\';\\" ); \\/\\/ Bad.\\n",
                                        "line": 10,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable {$_GET[\'title\']} at \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'{$_GET[\'title\']}\';\\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'{$_GET[\'title\']}\';\\" ); \\/\\/ Bad.\\n",
                                        "line": 10,
                                        "column": 15
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'$var\';\\" ); \\/\\/ Bad.\\n",
                                        "line": 11,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'$var\';\\" ); \\/\\/ Bad.\\n",
                                        "line": 11,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $var at \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'$var\';\\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'$var\';\\" ); \\/\\/ Bad.\\n",
                                        "line": 11,
                                        "column": 15
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'Hello World!\';\\" ); \\/\\/ Ok.\\n",
                                        "line": 12,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'Hello World!\';\\" ); \\/\\/ Ok.\\n",
                                        "line": 12,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'{$_GET[\'title\']}\';\\" ) ); \\/\\/ Bad.\\n",
                                        "line": 13,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'{$_GET[\'title\']}\';\\" ) ); \\/\\/ Bad.\\n",
                                        "line": 13,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable {$_GET[\'title\']} at \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'{$_GET[\'title\']}\';\\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'{$_GET[\'title\']}\';\\" ) ); \\/\\/ Bad.\\n",
                                        "line": 13,
                                        "column": 31
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'$var\';\\" ) ); \\/\\/ Bad.\\n",
                                        "line": 14,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'$var\';\\" ) ); \\/\\/ Bad.\\n",
                                        "line": 14,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $var at \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'$var\';\\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'$var\';\\" ) ); \\/\\/ Bad.\\n",
                                        "line": 14,
                                        "column": 31
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE %s;\\", $_GET[\'title\'] ) ); \\/\\/ Ok.\\n",
                                        "line": 15,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE %s;\\", $_GET[\'title\'] ) ); \\/\\/ Ok.\\n",
                                        "line": 15,
                                        "column": 1
                                    },
                                    {
                                        "message": "Detected usage of a non-sanitized input variable: $_GET[\'title\']",
                                        "source": "WordPress.Security.ValidatedSanitizedInput.InputNotSanitized",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE %s;\\", $_GET[\'title\'] ) ); \\/\\/ Ok.\\n",
                                        "line": 15,
                                        "column": 87
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'\\" . $escaped_var . \\"\';\\" ); \\/\\/ Bad: old-style ignore comment. WPCS: unprepared SQL OK.\\n",
                                        "line": 17,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'\\" . $escaped_var . \\"\';\\" ); \\/\\/ Bad: old-style ignore comment. WPCS: unprepared SQL OK.\\n",
                                        "line": 17,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found $escaped_var",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'\\" . $escaped_var . \\"\';\\" ); \\/\\/ Bad: old-style ignore comment. WPCS: unprepared SQL OK.\\n",
                                        "line": 17,
                                        "column": 70
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'{$escaped_var}\';\\" ); \\/\\/  Bad: old-style ignore comment. WPCS: unprepared SQL OK.\\n",
                                        "line": 18,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'{$escaped_var}\';\\" ); \\/\\/  Bad: old-style ignore comment. WPCS: unprepared SQL OK.\\n",
                                        "line": 18,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable {$escaped_var} at \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'{$escaped_var}\';\\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'{$escaped_var}\';\\" ); \\/\\/  Bad: old-style ignore comment. WPCS: unprepared SQL OK.\\n",
                                        "line": 18,
                                        "column": 15
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"SELECT SUBSTRING( post_name, %d + 1 ) REGEXP \'^[0-9]+$\'\\", array( 123 ) ) ); \\/\\/ Ok.\\n",
                                        "line": 20,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"SELECT SUBSTRING( post_name, %d + 1 ) REGEXP \'^[0-9]+$\'\\", array( 123 ) ) ); \\/\\/ Ok.\\n",
                                        "line": 20,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"SELECT * FROM $wpdb->posts WHERE post_title = \'The \\\\$_GET var can be evil.\' AND ID = %s\\", array( 123 ) ) ); \\/\\/ Ok.\\n",
                                        "line": 21,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"SELECT * FROM $wpdb->posts WHERE post_title = \'The \\\\$_GET var can be evil.\' AND ID = %s\\", array( 123 ) ) ); \\/\\/ Ok.\\n",
                                        "line": 21,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"SELECT * FROM $wpdb->posts WHERE post_title = \'The $_GET[foo] var is evil.\' AND ID = %s\\", array( 123 ) ) ); \\/\\/ Bad.\\n",
                                        "line": 22,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"SELECT * FROM $wpdb->posts WHERE post_title = \'The $_GET[foo] var is evil.\' AND ID = %s\\", array( 123 ) ) ); \\/\\/ Bad.\\n",
                                        "line": 22,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $_GET[foo] at \\"SELECT * FROM $wpdb->posts WHERE post_title = \'The $_GET[foo] var is evil.\' AND ID = %s\\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"SELECT * FROM $wpdb->posts WHERE post_title = \'The $_GET[foo] var is evil.\' AND ID = %s\\", array( 123 ) ) ); \\/\\/ Bad.\\n",
                                        "line": 22,
                                        "column": 31
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"SELECT * FROM $wpdb->posts WHERE post_title = \'The \\\\\\\\$_GET[foo]\\/\\/ var is evil again.\' AND ID = %s\\", array( 123 ) ) ); \\/\\/ Bad.\\n",
                                        "line": 23,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"SELECT * FROM $wpdb->posts WHERE post_title = \'The \\\\\\\\$_GET[foo]\\/\\/ var is evil again.\' AND ID = %s\\", array( 123 ) ) ); \\/\\/ Bad.\\n",
                                        "line": 23,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $_GET[foo] at \\"SELECT * FROM $wpdb->posts WHERE post_title = \'The \\\\\\\\$_GET[foo]\\/\\/ var is evil again.\' AND ID = %s\\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"SELECT * FROM $wpdb->posts WHERE post_title = \'The \\\\\\\\$_GET[foo]\\/\\/ var is evil again.\' AND ID = %s\\", array( 123 ) ) ); \\/\\/ Bad.\\n",
                                        "line": 23,
                                        "column": 31
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"SELECT * FROM $wpdb->posts WHERE post_title = \'The \\\\$_GET var can be evil, but $_GET[foo] var is evil.\' AND ID = %s\\", array( 123 ) ) ); \\/\\/ Bad.\\n",
                                        "line": 24,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"SELECT * FROM $wpdb->posts WHERE post_title = \'The \\\\$_GET var can be evil, but $_GET[foo] var is evil.\' AND ID = %s\\", array( 123 ) ) ); \\/\\/ Bad.\\n",
                                        "line": 24,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $_GET[foo] at \\"SELECT * FROM $wpdb->posts WHERE post_title = \'The \\\\$_GET var can be evil, but $_GET[foo] var is evil.\' AND ID = %s\\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"SELECT * FROM $wpdb->posts WHERE post_title = \'The \\\\$_GET var can be evil, but $_GET[foo] var is evil.\' AND ID = %s\\", array( 123 ) ) ); \\/\\/ Bad.\\n",
                                        "line": 24,
                                        "column": 31
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'\\" . foo() . \\"\';\\" ); \\/\\/ Bad.\\n",
                                        "line": 26,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'\\" . foo() . \\"\';\\" ); \\/\\/ Bad.\\n",
                                        "line": 26,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found foo",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'\\" . foo() . \\"\';\\" ); \\/\\/ Bad.\\n",
                                        "line": 26,
                                        "column": 70
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'\\" . foo() . \\"\';\\" ) ); \\/\\/ Bad.\\n",
                                        "line": 27,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'\\" . foo() . \\"\';\\" ) ); \\/\\/ Bad.\\n",
                                        "line": 27,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found foo",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'\\" . foo() . \\"\';\\" ) ); \\/\\/ Bad.\\n",
                                        "line": 27,
                                        "column": 86
                                    },
                                    {
                                        "message": "It is not necessary to prepare a query which doesn\'t use variable replacement.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnnecessaryPrepare",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'\\" . foo() . \\"\';\\" ) ); \\/\\/ Bad.\\n",
                                        "line": 27,
                                        "column": 99
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM \\" . $wpdb->posts . \\" WHERE post_title LIKE \'foo\';\\" ); \\/\\/ Ok.\\n",
                                        "line": 29,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM \\" . $wpdb->posts . \\" WHERE post_title LIKE \'foo\';\\" ); \\/\\/ Ok.\\n",
                                        "line": 29,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$all_post_meta = $wpdb->get_results( $wpdb->prepare( sprintf(\\n",
                                        "line": 32,
                                        "column": 18
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$all_post_meta = $wpdb->get_results( $wpdb->prepare( sprintf(\\n",
                                        "line": 32,
                                        "column": 18
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'\\" . esc_sql( $foo ) . \\"\';\\" ); \\/\\/ Ok.\\n",
                                        "line": 39,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'\\" . esc_sql( $foo ) . \\"\';\\" ); \\/\\/ Ok.\\n",
                                        "line": 39,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE ID = \\" . absint( $foo ) . \\";\\" ); \\/\\/ Ok.\\n",
                                        "line": 40,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE ID = \\" . absint( $foo ) . \\";\\" ); \\/\\/ Ok.\\n",
                                        "line": 40,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$all_post_meta = $wpdb->get_results( $wpdb->prepare( sprintf(\\n",
                                        "line": 43,
                                        "column": 18
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$all_post_meta = $wpdb->get_results( $wpdb->prepare( sprintf(\\n",
                                        "line": 43,
                                        "column": 18
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"\\n",
                                        "line": 53,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"\\n",
                                        "line": 53,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"\\n",
                                        "line": 59,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"\\n",
                                        "line": 59,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $_GET[foo] at \\\\tWHERE post_title = \'The \\\\\\\\$_GET[foo]\\/\\/ var is evil again.\'\\\\n",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\tWHERE post_title = \'The \\\\\\\\$_GET[foo]\\/\\/ var is evil again.\'\\n",
                                        "line": 62,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( <<<EOT\\n",
                                        "line": 69,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( <<<EOT\\n",
                                        "line": 69,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable {$foo} at \\\\tWHERE ID = {$foo};\\\\n",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\tWHERE ID = {$foo};\\n",
                                        "line": 72,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( <<<\\"HD\\"\\n",
                                        "line": 76,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( <<<\\"HD\\"\\n",
                                        "line": 76,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable {$var} at \\\\tWHERE post_title LIKE \'{$var}\';\\\\n",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\tWHERE post_title LIKE \'{$var}\';\\n",
                                        "line": 79,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$all_post_meta = $wpdb->get_results( $wpdb->prepare( sprintf( <<<\'ND\'\\n",
                                        "line": 83,
                                        "column": 18
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$all_post_meta = $wpdb->get_results( $wpdb->prepare( sprintf( <<<\'ND\'\\n",
                                        "line": 83,
                                        "column": 18
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found foo",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "wpdb::prepare( \\"SELECT * FROM $wpdb?->posts WHERE post_title LIKE \'\\" . foo() . \\"\';\\" ); \\/\\/ Bad.\\n",
                                        "line": 95,
                                        "column": 72
                                    },
                                    {
                                        "message": "It is not necessary to prepare a query which doesn\'t use variable replacement.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnnecessaryPrepare",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "wpdb::prepare( \\"SELECT * FROM $wpdb?->posts WHERE post_title LIKE \'\\" . foo() . \\"\';\\" ); \\/\\/ Bad.\\n",
                                        "line": 95,
                                        "column": 85
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\/\\/ Some arbitrary comment.\\n",
                                        "line": 97,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\/\\/ Some arbitrary comment.\\n",
                                        "line": 97,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found $escaped_var",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\tWHERE post_title LIKE \'\\" . $escaped_var . \\"\';\\"\\n",
                                        "line": 100,
                                        "column": 30
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE ID = \\" . (int) $foo . \\";\\" ); \\/\\/ Ok.\\n",
                                        "line": 103,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE ID = \\" . (int) $foo . \\";\\" ); \\/\\/ Ok.\\n",
                                        "line": 103,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE value = \\" . (float) $foo . \\";\\" ); \\/\\/ Ok.\\n",
                                        "line": 105,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE value = \\" . (float) $foo . \\";\\" ); \\/\\/ Ok.\\n",
                                        "line": 105,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query(\\n",
                                        "line": 107,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query(\\n",
                                        "line": 107,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM {$wpdb->bar()} WHERE post_title LIKE \'{$title->sub()}\';\\" ); \\/\\/ Bad x 1.\\n",
                                        "line": 116,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM {$wpdb->bar()} WHERE post_title LIKE \'{$title->sub()}\';\\" ); \\/\\/ Bad x 1.\\n",
                                        "line": 116,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable {$title->sub()} at \\"SELECT * FROM {$wpdb->bar()} WHERE post_title LIKE \'{$title->sub()}\';\\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM {$wpdb->bar()} WHERE post_title LIKE \'{$title->sub()}\';\\" ); \\/\\/ Bad x 1.\\n",
                                        "line": 116,
                                        "column": 15
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM ${wpdb->bar} WHERE post_title LIKE \'${title->sub}\';\\" ); \\/\\/ Bad x 1.\\n",
                                        "line": 117,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM ${wpdb->bar} WHERE post_title LIKE \'${title->sub}\';\\" ); \\/\\/ Bad x 1.\\n",
                                        "line": 117,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable ${title->sub} at \\"SELECT * FROM ${wpdb->bar} WHERE post_title LIKE \'${title->sub}\';\\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM ${wpdb->bar} WHERE post_title LIKE \'${title->sub}\';\\" ); \\/\\/ Bad x 1.\\n",
                                        "line": 117,
                                        "column": 15
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM ${wpdb->{$baz}} WHERE post_title LIKE \'${title->{$sub}}\';\\" ); \\/\\/ Bad x 1.\\n",
                                        "line": 118,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM ${wpdb->{$baz}} WHERE post_title LIKE \'${title->{$sub}}\';\\" ); \\/\\/ Bad x 1.\\n",
                                        "line": 118,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable ${title->{$sub}} at \\"SELECT * FROM ${wpdb->{$baz}} WHERE post_title LIKE \'${title->{$sub}}\';\\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM ${wpdb->{$baz}} WHERE post_title LIKE \'${title->{$sub}}\';\\" ); \\/\\/ Bad x 1.\\n",
                                        "line": 118,
                                        "column": 15
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM ${wpdb->{${\'a\'}}} WHERE post_title LIKE \'${title->{${\'sub\'}}}\';\\" ); \\/\\/ Bad x 1.\\n",
                                        "line": 119,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM ${wpdb->{${\'a\'}}} WHERE post_title LIKE \'${title->{${\'sub\'}}}\';\\" ); \\/\\/ Bad x 1.\\n",
                                        "line": 119,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable ${title->{${\'sub\'}}} at \\"SELECT * FROM ${wpdb->{${\'a\'}}} WHERE post_title LIKE \'${title->{${\'sub\'}}}\';\\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM ${wpdb->{${\'a\'}}} WHERE post_title LIKE \'${title->{${\'sub\'}}}\';\\" ); \\/\\/ Bad x 1.\\n",
                                        "line": 119,
                                        "column": 15
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb\\" ); \\/\\/ Bad x 1, $wpdb on its own is not valid.\\n",
                                        "line": 122,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb\\" ); \\/\\/ Bad x 1, $wpdb on its own is not valid.\\n",
                                        "line": 122,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $wpdb at \\"SELECT * FROM $wpdb\\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb\\" ); \\/\\/ Bad x 1, $wpdb on its own is not valid.\\n",
                                        "line": 122,
                                        "column": 15
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb\\n",
                                        "line": 124,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb\\n",
                                        "line": 124,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found $_GET",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t-> \\/*comment*\\/ query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'\\" . $_GET[\'title\'] . \\"\';\\" ); \\/\\/ Bad.\\n",
                                        "line": 125,
                                        "column": 79
                                    },
                                    {
                                        "message": "Detected usage of a non-sanitized input variable: $_GET[\'title\']",
                                        "source": "WordPress.Security.ValidatedSanitizedInput.InputNotSanitized",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t-> \\/*comment*\\/ query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'\\" . $_GET[\'title\'] . \\"\';\\" ); \\/\\/ Bad.\\n",
                                        "line": 125,
                                        "column": 79
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb?->query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'\\" . (int) $foo . \\"\';\\" ); \\/\\/ OK.\\n",
                                        "line": 127,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb?->query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'\\" . (int) $foo . \\"\';\\" ); \\/\\/ OK.\\n",
                                        "line": 127,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb?->query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'\\" . foo() . \\"\';\\" ); \\/\\/ Bad.\\n",
                                        "line": 128,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb?->query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'\\" . foo() . \\"\';\\" ); \\/\\/ Bad.\\n",
                                        "line": 128,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found foo",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb?->query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'\\" . foo() . \\"\';\\" ); \\/\\/ Bad.\\n",
                                        "line": 128,
                                        "column": 71
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found foo",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "WPDB::prepare( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'\\" . foo() . \\"\';\\" ); \\/\\/ Bad.\\n",
                                        "line": 130,
                                        "column": 71
                                    },
                                    {
                                        "message": "It is not necessary to prepare a query which doesn\'t use variable replacement.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnnecessaryPrepare",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "WPDB::prepare( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'\\" . foo() . \\"\';\\" ); \\/\\/ Bad.\\n",
                                        "line": 130,
                                        "column": 84
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->Query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'\\" . foo() . \\"\';\\" ); \\/\\/ Bad.\\n",
                                        "line": 131,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->Query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'\\" . foo() . \\"\';\\" ); \\/\\/ Bad.\\n",
                                        "line": 131,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found foo",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->Query( \\"SELECT * FROM $wpdb->posts WHERE post_title LIKE \'\\" . foo() . \\"\';\\" ); \\/\\/ Bad.\\n",
                                        "line": 131,
                                        "column": 70
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE value = \\" . {$foo} . \\";\\" ); \\/\\/ Bad - on $foo, not on the {}.\\n",
                                        "line": 133,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE value = \\" . {$foo} . \\";\\" ); \\/\\/ Bad - on $foo, not on the {}.\\n",
                                        "line": 133,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found $foo",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE value = \\" . {$foo} . \\";\\" ); \\/\\/ Bad - on $foo, not on the {}.\\n",
                                        "line": 133,
                                        "column": 62
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE value = \\" . (array) $foo . \\";\\" ); \\/\\/ Bad - on $foo, not on the (array).\\n",
                                        "line": 134,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE value = \\" . (array) $foo . \\";\\" ); \\/\\/ Bad - on $foo, not on the (array).\\n",
                                        "line": 134,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found $foo",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE value = \\" . (array) $foo . \\";\\" ); \\/\\/ Bad - on $foo, not on the (array).\\n",
                                        "line": 134,
                                        "column": 69
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE value = \\" . - 10 . \\";\\" ); \\/\\/ OK.\\n",
                                        "line": 135,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE value = \\" . - 10 . \\";\\" ); \\/\\/ OK.\\n",
                                        "line": 135,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE value = \\" . + 1.0 . \\";\\" ); \\/\\/ OK.\\n",
                                        "line": 136,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE value = \\" . + 1.0 . \\";\\" ); \\/\\/ OK.\\n",
                                        "line": 136,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE value = \\" . 10 \\/ 2.5 . \\";\\" ); \\/\\/ OK.\\n",
                                        "line": 137,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE value = \\" . 10 \\/ 2.5 . \\";\\" ); \\/\\/ OK.\\n",
                                        "line": 137,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE value = \\" . ++ $foo . \\";\\" ); \\/\\/ Bad - on $foo, not on the ++.\\n",
                                        "line": 138,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE value = \\" . ++ $foo . \\";\\" ); \\/\\/ Bad - on $foo, not on the ++.\\n",
                                        "line": 138,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found $foo",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE value = \\" . ++ $foo . \\";\\" ); \\/\\/ Bad - on $foo, not on the ++.\\n",
                                        "line": 138,
                                        "column": 64
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \'SELECT * FROM \' . $wpdb::TABLE_NAME . \\" WHERE post_title LIKE \'%s\';\\", \'%something\' ) ); \\/\\/ OK.\\n",
                                        "line": 141,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \'SELECT * FROM \' . $wpdb::TABLE_NAME . \\" WHERE post_title LIKE \'%s\';\\", \'%something\' ) ); \\/\\/ OK.\\n",
                                        "line": 141,
                                        "column": 1
                                    },
                                    {
                                        "message": "Simple placeholders should not be quoted in the query string in $wpdb->prepare(). Found: \'%s\'.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedSimplePlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \'SELECT * FROM \' . $wpdb::TABLE_NAME . \\" WHERE post_title LIKE \'%s\';\\", \'%something\' ) ); \\/\\/ OK.\\n",
                                        "line": 141,
                                        "column": 70
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \'SELECT * FROM \' . $notwpdb?->posts . \\" WHERE post_title LIKE \'%s\';\\", \'%something\' ) ); \\/\\/ Bad.\\n",
                                        "line": 142,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \'SELECT * FROM \' . $notwpdb?->posts . \\" WHERE post_title LIKE \'%s\';\\", \'%something\' ) ); \\/\\/ Bad.\\n",
                                        "line": 142,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found $notwpdb",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \'SELECT * FROM \' . $notwpdb?->posts . \\" WHERE post_title LIKE \'%s\';\\", \'%something\' ) ); \\/\\/ Bad.\\n",
                                        "line": 142,
                                        "column": 50
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found posts",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \'SELECT * FROM \' . $notwpdb?->posts . \\" WHERE post_title LIKE \'%s\';\\", \'%something\' ) ); \\/\\/ Bad.\\n",
                                        "line": 142,
                                        "column": 61
                                    },
                                    {
                                        "message": "Simple placeholders should not be quoted in the query string in $wpdb->prepare(). Found: \'%s\'.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedSimplePlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \'SELECT * FROM \' . $notwpdb?->posts . \\" WHERE post_title LIKE \'%s\';\\", \'%something\' ) ); \\/\\/ Bad.\\n",
                                        "line": 142,
                                        "column": 69
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE value = \\" . 10_000 . \\";\\" ); \\/\\/ OK.\\n",
                                        "line": 145,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE value = \\" . 10_000 . \\";\\" ); \\/\\/ OK.\\n",
                                        "line": 145,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE value = \\" . 0o34 . \\";\\" ); \\/\\/ OK.\\n",
                                        "line": 146,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( \\"SELECT * FROM $wpdb->posts WHERE value = \\" . 0o34 . \\";\\" ); \\/\\/ OK.\\n",
                                        "line": 146,
                                        "column": 1
                                    },
                                    {
                                        "message": "All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found \'$wpdb\'.",
                                        "source": "WordPress.Security.EscapeOutput.OutputNotEscaped",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "echo $wpdb::CONSTANT_NAME;\\n",
                                        "line": 151,
                                        "column": 6
                                    },
                                    {
                                        "message": "Incorrect number of replacements passed to $wpdb->prepare(). Found 2 replacement parameters, expected 6.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "wpdb::prepare( \\"SELECT * FROM $wpdb->posts\\n",
                                        "line": 157,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $sql at $sql = $wpdb->prepare( $sql, $replacements ); \\/\\/ OK - no query available to examine - this will be handled by the PreparedSQL sniff.\\\\n",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( $sql, $replacements ); \\/\\/ OK - no query available to examine - this will be handled by the PreparedSQL sniff.\\n",
                                        "line": 161,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $sql at $sql = $wpdb->prepare( $sql, $replacements ); \\/\\/ OK - no query available to examine - this will be handled by the PreparedSQL sniff.\\\\n",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( $sql, $replacements ); \\/\\/ OK - no query available to examine - this will be handled by the PreparedSQL sniff.\\n",
                                        "line": 161,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $replacements at $sql = $wpdb->prepare( $sql, $replacements ); \\/\\/ OK - no query available to examine - this will be handled by the PreparedSQL sniff.\\\\n",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( $sql, $replacements ); \\/\\/ OK - no query available to examine - this will be handled by the PreparedSQL sniff.\\n",
                                        "line": 161,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $sql at $sql = $wpdb->prepare( \\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %d and user_login = %s\\", 1, \\"admin\\" ); \\/\\/ OK.\\n",
                                        "line": 162,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found SELECT",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %d and user_login = %s\\", 1, \\"admin\\" ); \\/\\/ OK.\\n",
                                        "line": 162,
                                        "column": 25
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found FROM",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %d and user_login = %s\\", 1, \\"admin\\" ); \\/\\/ OK.\\n",
                                        "line": 162,
                                        "column": 34
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found id",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %d and user_login = %s\\", 1, \\"admin\\" ); \\/\\/ OK.\\n",
                                        "line": 162,
                                        "column": 58
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found =",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %d and user_login = %s\\", 1, \\"admin\\" ); \\/\\/ OK.\\n",
                                        "line": 162,
                                        "column": 61
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found d",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %d and user_login = %s\\", 1, \\"admin\\" ); \\/\\/ OK.\\n",
                                        "line": 162,
                                        "column": 64
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found and",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %d and user_login = %s\\", 1, \\"admin\\" ); \\/\\/ OK.\\n",
                                        "line": 162,
                                        "column": 66
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found user_login",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %d and user_login = %s\\", 1, \\"admin\\" ); \\/\\/ OK.\\n",
                                        "line": 162,
                                        "column": 70
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found =",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %d and user_login = %s\\", 1, \\"admin\\" ); \\/\\/ OK.\\n",
                                        "line": 162,
                                        "column": 81
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found s",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %d and user_login = %s\\", 1, \\"admin\\" ); \\/\\/ OK.\\n",
                                        "line": 162,
                                        "column": 84
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found admin",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %d and user_login = %s\\", 1, \\"admin\\" ); \\/\\/ OK.\\n",
                                        "line": 162,
                                        "column": 92
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $sql at $sql = $wpdb?->prepare( \\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb?->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %d and user_login = %s\\", array( 1, \\"admin\\" ) ); \\/\\/ OK.\\n",
                                        "line": 163,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found SELECT",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb?->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %d and user_login = %s\\", array( 1, \\"admin\\" ) ); \\/\\/ OK.\\n",
                                        "line": 163,
                                        "column": 26
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found FROM",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb?->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %d and user_login = %s\\", array( 1, \\"admin\\" ) ); \\/\\/ OK.\\n",
                                        "line": 163,
                                        "column": 35
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found id",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb?->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %d and user_login = %s\\", array( 1, \\"admin\\" ) ); \\/\\/ OK.\\n",
                                        "line": 163,
                                        "column": 59
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found =",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb?->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %d and user_login = %s\\", array( 1, \\"admin\\" ) ); \\/\\/ OK.\\n",
                                        "line": 163,
                                        "column": 62
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found d",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb?->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %d and user_login = %s\\", array( 1, \\"admin\\" ) ); \\/\\/ OK.\\n",
                                        "line": 163,
                                        "column": 65
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found and",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb?->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %d and user_login = %s\\", array( 1, \\"admin\\" ) ); \\/\\/ OK.\\n",
                                        "line": 163,
                                        "column": 67
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found user_login",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb?->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %d and user_login = %s\\", array( 1, \\"admin\\" ) ); \\/\\/ OK.\\n",
                                        "line": 163,
                                        "column": 71
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found =",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb?->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %d and user_login = %s\\", array( 1, \\"admin\\" ) ); \\/\\/ OK.\\n",
                                        "line": 163,
                                        "column": 82
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found s",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb?->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %d and user_login = %s\\", array( 1, \\"admin\\" ) ); \\/\\/ OK.\\n",
                                        "line": 163,
                                        "column": 85
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found admin",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb?->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %d and user_login = %s\\", array( 1, \\"admin\\" ) ); \\/\\/ OK.\\n",
                                        "line": 163,
                                        "column": 100
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $sql at $sql = $wpdb->prepare( \'SELECT * FROM `table` WHERE `column` = %s AND `field` = %d\', \'foo\', 1337 ); \\/\\/ OK.\\\\n",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \'SELECT * FROM `table` WHERE `column` = %s AND `field` = %d\', \'foo\', 1337 ); \\/\\/ OK.\\n",
                                        "line": 164,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $sql at $sql = $wpdb->prepare( \'SELECT DATE_FORMAT(`field`, \\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \'SELECT DATE_FORMAT(`field`, \\" %%c\\") FROM `table` WHERE `column` = %s\', \'foo\' ); \\/\\/ OK.\\n",
                                        "line": 165,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found c",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \'SELECT DATE_FORMAT(`field`, \\" %%c\\") FROM `table` WHERE `column` = %s\', \'foo\' ); \\/\\/ OK.\\n",
                                        "line": 165,
                                        "column": 57
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $sql at $sql = $wpdb->prepare( \'SELECT * FROM `table`\' ); \\/\\/ Warning.\\\\n",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \'SELECT * FROM `table`\' ); \\/\\/ Warning.\\n",
                                        "line": 170,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $sql at $sql = $wpdb->prepare( \'SELECT * FROM `table` WHERE id = \' . $id ); \\/\\/ OK - this will be handled by the PreparedSQL sniff.\\\\n",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \'SELECT * FROM `table` WHERE id = \' . $id ); \\/\\/ OK - this will be handled by the PreparedSQL sniff.\\n",
                                        "line": 171,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $id at $sql = $wpdb->prepare( \'SELECT * FROM `table` WHERE id = \' . $id ); \\/\\/ OK - this will be handled by the PreparedSQL sniff.\\\\n",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \'SELECT * FROM `table` WHERE id = \' . $id ); \\/\\/ OK - this will be handled by the PreparedSQL sniff.\\n",
                                        "line": 171,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $sql at $sql = $wpdb->prepare( \\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `table` WHERE id = $id\\" ); \\/\\/ OK - this will be handled by the PreparedSQL sniff.\\n",
                                        "line": 172,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found SELECT",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `table` WHERE id = $id\\" ); \\/\\/ OK - this will be handled by the PreparedSQL sniff.\\n",
                                        "line": 172,
                                        "column": 25
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found FROM",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `table` WHERE id = $id\\" ); \\/\\/ OK - this will be handled by the PreparedSQL sniff.\\n",
                                        "line": 172,
                                        "column": 34
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found `",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `table` WHERE id = $id\\" ); \\/\\/ OK - this will be handled by the PreparedSQL sniff.\\n",
                                        "line": 172,
                                        "column": 39
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found table",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `table` WHERE id = $id\\" ); \\/\\/ OK - this will be handled by the PreparedSQL sniff.\\n",
                                        "line": 172,
                                        "column": 40
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found `",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `table` WHERE id = $id\\" ); \\/\\/ OK - this will be handled by the PreparedSQL sniff.\\n",
                                        "line": 172,
                                        "column": 45
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found WHERE",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `table` WHERE id = $id\\" ); \\/\\/ OK - this will be handled by the PreparedSQL sniff.\\n",
                                        "line": 172,
                                        "column": 47
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found id",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `table` WHERE id = $id\\" ); \\/\\/ OK - this will be handled by the PreparedSQL sniff.\\n",
                                        "line": 172,
                                        "column": 53
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found =",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `table` WHERE id = $id\\" ); \\/\\/ OK - this will be handled by the PreparedSQL sniff.\\n",
                                        "line": 172,
                                        "column": 56
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found $id",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `table` WHERE id = $id\\" ); \\/\\/ OK - this will be handled by the PreparedSQL sniff.\\n",
                                        "line": 172,
                                        "column": 58
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $sql at $sql = $wpdb->prepare( \\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `table` WHERE id = {\\n",
                                        "line": 173,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found SELECT",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `table` WHERE id = {\\n",
                                        "line": 173,
                                        "column": 25
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found FROM",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `table` WHERE id = {\\n",
                                        "line": 173,
                                        "column": 34
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found `",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `table` WHERE id = {\\n",
                                        "line": 173,
                                        "column": 39
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found table",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `table` WHERE id = {\\n",
                                        "line": 173,
                                        "column": 40
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found `",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `table` WHERE id = {\\n",
                                        "line": 173,
                                        "column": 45
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found WHERE",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `table` WHERE id = {\\n",
                                        "line": 173,
                                        "column": 47
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found id",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `table` WHERE id = {\\n",
                                        "line": 173,
                                        "column": 53
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found =",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `table` WHERE id = {\\n",
                                        "line": 173,
                                        "column": 56
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found $id",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t$id[\'some%sing\']}\\" ); \\/\\/ OK - this will be handled by the PreparedSQL sniff.\\n",
                                        "line": 174,
                                        "column": 2
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $sql at $sql = $wpdb?->prepare( \'SELECT * FROM \' . $wpdb->users ); \\/\\/ Warning.\\\\n",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb?->prepare( \'SELECT * FROM \' . $wpdb->users ); \\/\\/ Warning.\\n",
                                        "line": 175,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $sql at $sql = $wpdb->prepare( \\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `{$wpdb->users}`\\" );  \\/\\/ Warning.\\n",
                                        "line": 176,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found SELECT",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `{$wpdb->users}`\\" );  \\/\\/ Warning.\\n",
                                        "line": 176,
                                        "column": 25
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found FROM",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `{$wpdb->users}`\\" );  \\/\\/ Warning.\\n",
                                        "line": 176,
                                        "column": 34
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found `",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `{$wpdb->users}`\\" );  \\/\\/ Warning.\\n",
                                        "line": 176,
                                        "column": 39
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found `",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `{$wpdb->users}`\\" );  \\/\\/ Warning.\\n",
                                        "line": 176,
                                        "column": 54
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $sql at $sql = $wpdb->prepare( \\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `{$wpdb->users}` WHERE id = $id\\" ); \\/\\/ OK - this will be handled by the PreparedSQL sniff.\\n",
                                        "line": 177,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found SELECT",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `{$wpdb->users}` WHERE id = $id\\" ); \\/\\/ OK - this will be handled by the PreparedSQL sniff.\\n",
                                        "line": 177,
                                        "column": 25
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found FROM",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `{$wpdb->users}` WHERE id = $id\\" ); \\/\\/ OK - this will be handled by the PreparedSQL sniff.\\n",
                                        "line": 177,
                                        "column": 34
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found `",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `{$wpdb->users}` WHERE id = $id\\" ); \\/\\/ OK - this will be handled by the PreparedSQL sniff.\\n",
                                        "line": 177,
                                        "column": 39
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found `",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `{$wpdb->users}` WHERE id = $id\\" ); \\/\\/ OK - this will be handled by the PreparedSQL sniff.\\n",
                                        "line": 177,
                                        "column": 54
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found WHERE",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `{$wpdb->users}` WHERE id = $id\\" ); \\/\\/ OK - this will be handled by the PreparedSQL sniff.\\n",
                                        "line": 177,
                                        "column": 56
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found id",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `{$wpdb->users}` WHERE id = $id\\" ); \\/\\/ OK - this will be handled by the PreparedSQL sniff.\\n",
                                        "line": 177,
                                        "column": 62
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found =",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `{$wpdb->users}` WHERE id = $id\\" ); \\/\\/ OK - this will be handled by the PreparedSQL sniff.\\n",
                                        "line": 177,
                                        "column": 65
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found $id",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `{$wpdb->users}` WHERE id = $id\\" ); \\/\\/ OK - this will be handled by the PreparedSQL sniff.\\n",
                                        "line": 177,
                                        "column": 67
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $sql at $sql = $wpdb->prepare( \'SELECT * FROM `table`\', $something ); \\/\\/ Warning.\\\\n",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \'SELECT * FROM `table`\', $something ); \\/\\/ Warning.\\n",
                                        "line": 182,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $something at $sql = $wpdb->prepare( \'SELECT * FROM `table`\', $something ); \\/\\/ Warning.\\\\n",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \'SELECT * FROM `table`\', $something ); \\/\\/ Warning.\\n",
                                        "line": 182,
                                        "column": 1
                                    },
                                    {
                                        "message": "Unsupported placeholder used in $wpdb->prepare(). Found: \\"%1\\".",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \'%d %1$e %%% % %A %h\', 1 ); \\/\\/ Bad x 5.\\n",
                                        "line": 187,
                                        "column": 1
                                    },
                                    {
                                        "message": "Unsupported placeholder used in $wpdb->prepare(). Found: \\"%%%\\".",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \'%d %1$e %%% % %A %h\', 1 ); \\/\\/ Bad x 5.\\n",
                                        "line": 187,
                                        "column": 1
                                    },
                                    {
                                        "message": "Found unescaped literal \\"%\\" character.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnescapedLiteral",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \'%d %1$e %%% % %A %h\', 1 ); \\/\\/ Bad x 5.\\n",
                                        "line": 187,
                                        "column": 1
                                    },
                                    {
                                        "message": "Unsupported placeholder used in $wpdb->prepare(). Found: \\"%A\\".",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \'%d %1$e %%% % %A %h\', 1 ); \\/\\/ Bad x 5.\\n",
                                        "line": 187,
                                        "column": 1
                                    },
                                    {
                                        "message": "Unsupported placeholder used in $wpdb->prepare(). Found: \\"%h\\".",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \'%d %1$e %%% % %A %h\', 1 ); \\/\\/ Bad x 5.\\n",
                                        "line": 187,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $sql at $sql = $wpdb->prepare( \'%d %1$e %%% % %A %h\', 1 ); \\/\\/ Bad x 5.\\\\n",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \'%d %1$e %%% % %A %h\', 1 ); \\/\\/ Bad x 5.\\n",
                                        "line": 187,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $e at $sql = $wpdb->prepare( \'%d %1$e %%% % %A %h\', 1 ); \\/\\/ Bad x 5.\\\\n",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \'%d %1$e %%% % %A %h\', 1 ); \\/\\/ Bad x 5.\\n",
                                        "line": 187,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $sql at $sql = $wpdb->prepare( \'%%%s\', 1 ); \\/\\/ OK.\\\\n",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \'%%%s\', 1 ); \\/\\/ OK.\\n",
                                        "line": 188,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $sql at $sql = $wpdb->prepare( \\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb?->users WHERE id = %1\\\\$d and user_login = %2\\\\$s\\", 1, \\"admin\\" ); \\/\\/ OK. 2 x warning for unquoted complex placeholders.\\n",
                                        "line": 189,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found SELECT",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb?->users WHERE id = %1\\\\$d and user_login = %2\\\\$s\\", 1, \\"admin\\" ); \\/\\/ OK. 2 x warning for unquoted complex placeholders.\\n",
                                        "line": 189,
                                        "column": 25
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found FROM",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb?->users WHERE id = %1\\\\$d and user_login = %2\\\\$s\\", 1, \\"admin\\" ); \\/\\/ OK. 2 x warning for unquoted complex placeholders.\\n",
                                        "line": 189,
                                        "column": 34
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found id",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb?->users WHERE id = %1\\\\$d and user_login = %2\\\\$s\\", 1, \\"admin\\" ); \\/\\/ OK. 2 x warning for unquoted complex placeholders.\\n",
                                        "line": 189,
                                        "column": 59
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found =",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb?->users WHERE id = %1\\\\$d and user_login = %2\\\\$s\\", 1, \\"admin\\" ); \\/\\/ OK. 2 x warning for unquoted complex placeholders.\\n",
                                        "line": 189,
                                        "column": 62
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found $d",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb?->users WHERE id = %1\\\\$d and user_login = %2\\\\$s\\", 1, \\"admin\\" ); \\/\\/ OK. 2 x warning for unquoted complex placeholders.\\n",
                                        "line": 189,
                                        "column": 67
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found and",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb?->users WHERE id = %1\\\\$d and user_login = %2\\\\$s\\", 1, \\"admin\\" ); \\/\\/ OK. 2 x warning for unquoted complex placeholders.\\n",
                                        "line": 189,
                                        "column": 70
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found user_login",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb?->users WHERE id = %1\\\\$d and user_login = %2\\\\$s\\", 1, \\"admin\\" ); \\/\\/ OK. 2 x warning for unquoted complex placeholders.\\n",
                                        "line": 189,
                                        "column": 74
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found =",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb?->users WHERE id = %1\\\\$d and user_login = %2\\\\$s\\", 1, \\"admin\\" ); \\/\\/ OK. 2 x warning for unquoted complex placeholders.\\n",
                                        "line": 189,
                                        "column": 85
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found $s",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb?->users WHERE id = %1\\\\$d and user_login = %2\\\\$s\\", 1, \\"admin\\" ); \\/\\/ OK. 2 x warning for unquoted complex placeholders.\\n",
                                        "line": 189,
                                        "column": 90
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found admin",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb?->users WHERE id = %1\\\\$d and user_login = %2\\\\$s\\", 1, \\"admin\\" ); \\/\\/ OK. 2 x warning for unquoted complex placeholders.\\n",
                                        "line": 189,
                                        "column": 99
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $sql at $sql = $wpdb->prepare( \\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb?->users WHERE id = %01.2f and user_login = %10.10X\\", 1, \\"admin\\" ); \\/\\/ Bad x 1 + 1 warning unquoted complex placeholders + 1 warning nr of replacements.\\n",
                                        "line": 190,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found SELECT",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb?->users WHERE id = %01.2f and user_login = %10.10X\\", 1, \\"admin\\" ); \\/\\/ Bad x 1 + 1 warning unquoted complex placeholders + 1 warning nr of replacements.\\n",
                                        "line": 190,
                                        "column": 25
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found FROM",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb?->users WHERE id = %01.2f and user_login = %10.10X\\", 1, \\"admin\\" ); \\/\\/ Bad x 1 + 1 warning unquoted complex placeholders + 1 warning nr of replacements.\\n",
                                        "line": 190,
                                        "column": 34
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found id",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb?->users WHERE id = %01.2f and user_login = %10.10X\\", 1, \\"admin\\" ); \\/\\/ Bad x 1 + 1 warning unquoted complex placeholders + 1 warning nr of replacements.\\n",
                                        "line": 190,
                                        "column": 59
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found =",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb?->users WHERE id = %01.2f and user_login = %10.10X\\", 1, \\"admin\\" ); \\/\\/ Bad x 1 + 1 warning unquoted complex placeholders + 1 warning nr of replacements.\\n",
                                        "line": 190,
                                        "column": 62
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found f",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb?->users WHERE id = %01.2f and user_login = %10.10X\\", 1, \\"admin\\" ); \\/\\/ Bad x 1 + 1 warning unquoted complex placeholders + 1 warning nr of replacements.\\n",
                                        "line": 190,
                                        "column": 69
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found and",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb?->users WHERE id = %01.2f and user_login = %10.10X\\", 1, \\"admin\\" ); \\/\\/ Bad x 1 + 1 warning unquoted complex placeholders + 1 warning nr of replacements.\\n",
                                        "line": 190,
                                        "column": 71
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found user_login",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb?->users WHERE id = %01.2f and user_login = %10.10X\\", 1, \\"admin\\" ); \\/\\/ Bad x 1 + 1 warning unquoted complex placeholders + 1 warning nr of replacements.\\n",
                                        "line": 190,
                                        "column": 75
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found =",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb?->users WHERE id = %01.2f and user_login = %10.10X\\", 1, \\"admin\\" ); \\/\\/ Bad x 1 + 1 warning unquoted complex placeholders + 1 warning nr of replacements.\\n",
                                        "line": 190,
                                        "column": 86
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found X",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb?->users WHERE id = %01.2f and user_login = %10.10X\\", 1, \\"admin\\" ); \\/\\/ Bad x 1 + 1 warning unquoted complex placeholders + 1 warning nr of replacements.\\n",
                                        "line": 190,
                                        "column": 94
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found admin",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb?->users WHERE id = %01.2f and user_login = %10.10X\\", 1, \\"admin\\" ); \\/\\/ Bad x 1 + 1 warning unquoted complex placeholders + 1 warning nr of replacements.\\n",
                                        "line": 190,
                                        "column": 102
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $sql at $sql = $wpdb->prepare( \\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %\'.09F AND user_login = %1\\\\$04x\\", 1, \\"admin\\" ); \\/\\/ Bad x 1 + 1 warning unquoted complex placeholders + 1 warning nr of replacements.\\n",
                                        "line": 191,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found SELECT",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %\'.09F AND user_login = %1\\\\$04x\\", 1, \\"admin\\" ); \\/\\/ Bad x 1 + 1 warning unquoted complex placeholders + 1 warning nr of replacements.\\n",
                                        "line": 191,
                                        "column": 25
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found FROM",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %\'.09F AND user_login = %1\\\\$04x\\", 1, \\"admin\\" ); \\/\\/ Bad x 1 + 1 warning unquoted complex placeholders + 1 warning nr of replacements.\\n",
                                        "line": 191,
                                        "column": 34
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found id",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %\'.09F AND user_login = %1\\\\$04x\\", 1, \\"admin\\" ); \\/\\/ Bad x 1 + 1 warning unquoted complex placeholders + 1 warning nr of replacements.\\n",
                                        "line": 191,
                                        "column": 58
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found =",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %\'.09F AND user_login = %1\\\\$04x\\", 1, \\"admin\\" ); \\/\\/ Bad x 1 + 1 warning unquoted complex placeholders + 1 warning nr of replacements.\\n",
                                        "line": 191,
                                        "column": 61
                                    },
                                    {
                                        "message": "Unsupported placeholder used in $wpdb->prepare(). Found: \\"%1\\\\$04x\\".",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %\'.09F AND user_login = %1\\\\$04x\\", 1, \\"admin\\" ); \\/\\/ Bad x 1 + 1 warning unquoted complex placeholders + 1 warning nr of replacements.\\n",
                                        "line": 191,
                                        "column": 64
                                    },
                                    {
                                        "message": "Unsupported placeholder used in $wpdb->prepare(). Found: \\"%1\\\\$c\\\\\\".",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb->users WHERE id = \\\\\\"%1\\\\$c\\\\\\" AND user_login = \' % 2\\\\$e\'\\", 1, \\"admin\\" ); \\/\\/ Bad x 2 + 1 warning.\\n",
                                        "line": 192,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found $e",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb->users WHERE id = \\\\\\"%1\\\\$c\\\\\\" AND user_login = \' % 2\\\\$e\'\\", 1, \\"admin\\" ); \\/\\/ Bad x 2 + 1 warning.\\n",
                                        "line": 192,
                                        "column": 96
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found SELECT",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \'SELECT * FROM \' . $wpdb?->users . \' WHERE id = \\\\\'%1\\\\$b\\\\\' AND user_login = \\"%2\\\\$o\\"\', 1, \\"admin\\" ); \\/\\/ Bad x 2 + 1 warning.\\n",
                                        "line": 193,
                                        "column": 25
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found FROM",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \'SELECT * FROM \' . $wpdb?->users . \' WHERE id = \\\\\'%1\\\\$b\\\\\' AND user_login = \\"%2\\\\$o\\"\', 1, \\"admin\\" ); \\/\\/ Bad x 2 + 1 warning.\\n",
                                        "line": 193,
                                        "column": 34
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found WHERE",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \'SELECT * FROM \' . $wpdb?->users . \' WHERE id = \\\\\'%1\\\\$b\\\\\' AND user_login = \\"%2\\\\$o\\"\', 1, \\"admin\\" ); \\/\\/ Bad x 2 + 1 warning.\\n",
                                        "line": 193,
                                        "column": 61
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found id",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \'SELECT * FROM \' . $wpdb?->users . \' WHERE id = \\\\\'%1\\\\$b\\\\\' AND user_login = \\"%2\\\\$o\\"\', 1, \\"admin\\" ); \\/\\/ Bad x 2 + 1 warning.\\n",
                                        "line": 193,
                                        "column": 67
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found =",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \'SELECT * FROM \' . $wpdb?->users . \' WHERE id = \\\\\'%1\\\\$b\\\\\' AND user_login = \\"%2\\\\$o\\"\', 1, \\"admin\\" ); \\/\\/ Bad x 2 + 1 warning.\\n",
                                        "line": 193,
                                        "column": 70
                                    },
                                    {
                                        "message": "Unsupported placeholder used in $wpdb->prepare(). Found: \\"%1\\\\$b\\\\\\".",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \'SELECT * FROM \' . $wpdb?->users . \' WHERE id = \\\\\'%1\\\\$b\\\\\' AND user_login = \\"%2\\\\$o\\"\', 1, \\"admin\\" ); \\/\\/ Bad x 2 + 1 warning.\\n",
                                        "line": 193,
                                        "column": 73
                                    },
                                    {
                                        "message": "Unsupported placeholder used in $wpdb->prepare(). Found: \\"%2\\\\$o\\".",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \'SELECT * FROM \' . $wpdb?->users . \' WHERE id = \\\\\'%1\\\\$b\\\\\' AND user_login = \\"%2\\\\$o\\"\', 1, \\"admin\\" ); \\/\\/ Bad x 2 + 1 warning.\\n",
                                        "line": 193,
                                        "column": 73
                                    },
                                    {
                                        "message": "Simple placeholders should not be quoted in the query string in $wpdb->prepare(). Found: \\"%f\\".",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedSimplePlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \'\\"%f\\"\', 1.1 ); \\/\\/ Bad.\\n",
                                        "line": 198,
                                        "column": 24
                                    },
                                    {
                                        "message": "Simple placeholders should not be quoted in the query string in $wpdb->prepare(). Found: \\\\\'%s\\\\\'.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedSimplePlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \'SELECT * FROM `table` WHERE `field` = \\\\\'%s\\\\\'\', \'string\' ); \\/\\/ Bad.\\n",
                                        "line": 199,
                                        "column": 24
                                    },
                                    {
                                        "message": "Simple placeholders should not be quoted in the query string in $wpdb->prepare(). Found: \\\\\\"%d\\\\\\".",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedSimplePlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM `table` WHERE `id` = \\\\\\"%d\\\\\\"\\", 1 ); \\/\\/ Bad.\\n",
                                        "line": 200,
                                        "column": 24
                                    },
                                    {
                                        "message": "Complex placeholders used for values in the query string in $wpdb->prepare() will NOT be quoted automagically. Found: %1\\\\$s.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "\\tFROM `%1\\\\$s`\\n",
                                        "line": 203,
                                        "column": 1
                                    },
                                    {
                                        "message": "Complex placeholders used for values in the query string in $wpdb->prepare() will NOT be quoted automagically. Found: %2\\\\$d.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "\\tWHERE id = %2\\\\$d\\n",
                                        "line": 204,
                                        "column": 1
                                    },
                                    {
                                        "message": "Complex placeholders used for values in the query string in $wpdb->prepare() will NOT be quoted automagically. Found: %3\\\\$s.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "\\t\\tAND `%3\\\\$s` = \\"%4\\\\$s\\"\\n",
                                        "line": 205,
                                        "column": 1
                                    },
                                    {
                                        "message": "Placeholders found in the query passed to $wpdb->prepare(), but no replacements found. Expected 2 replacement(s) parameters.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.MissingReplacements",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %d AND user_login = %s\\" ); \\/\\/ Bad.\\n",
                                        "line": 217,
                                        "column": 8
                                    },
                                    {
                                        "message": "Incorrect number of replacements passed to $wpdb->prepare(). Found 1 replacement parameters, expected 2.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %d AND user_login = %s\\", 1 ); \\/\\/ Bad.\\n",
                                        "line": 218,
                                        "column": 8
                                    },
                                    {
                                        "message": "Incorrect number of replacements passed to $wpdb->prepare(). Found 3 replacement parameters, expected 2.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %d AND user_login = %s\\", 1, \\"admin\\", $variable ); \\/\\/ Bad.\\n",
                                        "line": 219,
                                        "column": 8
                                    },
                                    {
                                        "message": "Incorrect number of replacements passed to $wpdb->prepare(). Found 1 replacement parameters, expected 2.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %d AND user_login = %s\\", array( 1 ) ); \\/\\/ Bad.\\n",
                                        "line": 220,
                                        "column": 8
                                    },
                                    {
                                        "message": "Incorrect number of replacements passed to $wpdb->prepare(). Found 3 replacement parameters, expected 2.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %d AND user_login = %s\\", [ 1, \\"admin\\", $variable ] ); \\/\\/ Bad.\\n",
                                        "line": 221,
                                        "column": 8
                                    },
                                    {
                                        "message": "Incorrect number of replacements passed to $wpdb->prepare(). Found 1 replacement parameters, expected 2.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$sql          = $wpdb->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %d AND user_login = %s\\", $replacements ); \\/\\/ Bad.\\n",
                                        "line": 224,
                                        "column": 17
                                    },
                                    {
                                        "message": "Incorrect number of replacements passed to $wpdb->prepare(). Found 1 replacement parameters, expected 2.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$sql          = $wpdb->prepare( \\"SELECT * FROM $wpdb->users WHERE id = %d AND user_login = %s\\", $replacements ); \\/\\/ Bad - old-style ignore comment. WPCS: PreparedSQLPlaceholders replacement count OK.\\n",
                                        "line": 225,
                                        "column": 17
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found $esses",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$where = $wpdb->prepare( \\"{$wpdb->posts}.post_type IN (\\" . implode( \',\', $esses ) . \')\', $post_types ); \\/\\/ Warning.\\n",
                                        "line": 229,
                                        "column": 74
                                    },
                                    {
                                        "message": "Replacement variables found, but no valid placeholders found in the query.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$where = $wpdb->prepare( \\"{$wpdb->posts}.post_type IN (\\" . implode( \',\', $esses ) . \')\', $post_types ); \\/\\/ Warning.\\n",
                                        "line": 229,
                                        "column": 88
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found $esses",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$where = $wpdb->prepare( \\"{$wpdb->posts}.post_type IN (\\" . implode( \',\', $esses ) . \')\', $post_types ); \\/\\/ Bad - old-style ignore comment. WPCS: PreparedSQLPlaceholders replacement count OK.\\n",
                                        "line": 231,
                                        "column": 74
                                    },
                                    {
                                        "message": "Replacement variables found, but no valid placeholders found in the query.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$where = $wpdb->prepare( \\"{$wpdb->posts}.post_type IN (\\" . implode( \',\', $esses ) . \')\', $post_types ); \\/\\/ Bad - old-style ignore comment. WPCS: PreparedSQLPlaceholders replacement count OK.\\n",
                                        "line": 231,
                                        "column": 88
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$results = $wpdb->get_results(\\n",
                                        "line": 276,
                                        "column": 12
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$results = $wpdb->get_results(\\n",
                                        "line": 276,
                                        "column": 12
                                    },
                                    {
                                        "message": "Incorrect number of replacements passed to $wpdb->prepare(). Found 2 replacement parameters, expected 1.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$query = $wpdb->prepare(\\n",
                                        "line": 289,
                                        "column": 10
                                    },
                                    {
                                        "message": "Incorrect number of replacements passed to $wpdb->prepare(). Found 1 replacement parameters, expected 0.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$where = $wpdb->prepare(\\n",
                                        "line": 302,
                                        "column": 10
                                    },
                                    {
                                        "message": "Simple placeholders should not be quoted in the query string in $wpdb->prepare(). Found: \'%s\'.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedSimplePlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\t\\"{$wpdb->posts}.post_type IN (\'%s\')\\",\\n",
                                        "line": 304,
                                        "column": 3
                                    },
                                    {
                                        "message": "Dynamic placeholder generation should not have surrounding quotes.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedDynamicPlaceholderGeneration",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\"{$wpdb->posts}.post_type IN (\\\\\\"\\"\\n",
                                        "line": 311,
                                        "column": 2
                                    },
                                    {
                                        "message": "Dynamic placeholder generation should not have surrounding quotes.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedDynamicPlaceholderGeneration",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t. \\"\\\\\\") AND {$wpdb->posts}.post_status IN (\'\\"\\n",
                                        "line": 313,
                                        "column": 4
                                    },
                                    {
                                        "message": "Unless you are using SQL wildcards, using LIKE is inefficient. Use a straight compare instead. Found:  LIKE \\\\\'a string\\\\\'.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.LikeWithoutWildcards",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$sql = $wpdb->prepare( \'SELECT * FROM \' . $wpdb->posts . \' WHERE post_content LIKE \\\\\'a string\\\\\'\' ); \\/\\/ Warning x 2. Like without wildcard, no need for prepare.\\n",
                                        "line": 323,
                                        "column": 58
                                    },
                                    {
                                        "message": "It is not necessary to prepare a query which doesn\'t use variable replacement.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnnecessaryPrepare",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$sql = $wpdb->prepare( \'SELECT * FROM \' . $wpdb->posts . \' WHERE post_content LIKE \\\\\'a string\\\\\'\' ); \\/\\/ Warning x 2. Like without wildcard, no need for prepare.\\n",
                                        "line": 323,
                                        "column": 98
                                    },
                                    {
                                        "message": "Unless you are using SQL wildcards, using LIKE is inefficient. Use a straight compare instead. Found:  LIKE \'a string\'.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.LikeWithoutWildcards",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb->posts WHERE post_content LIKE \'a string\'\\" ); \\/\\/ Warning x 2. Like without wildcard, no need for prepare.\\n",
                                        "line": 324,
                                        "column": 24
                                    },
                                    {
                                        "message": "It is not necessary to prepare a query which doesn\'t use variable replacement.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnnecessaryPrepare",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb->posts WHERE post_content LIKE \'a string\'\\" ); \\/\\/ Warning x 2. Like without wildcard, no need for prepare.\\n",
                                        "line": 324,
                                        "column": 88
                                    },
                                    {
                                        "message": "SQL wildcards for a LIKE query should be passed in through a replacement parameter. Found:  LIKE \'%a string\'.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb->posts WHERE post_content LIKE \'%a string\' AND post_status = %s\\", $status ); \\/\\/ Bad.\\n",
                                        "line": 325,
                                        "column": 24
                                    },
                                    {
                                        "message": "SQL wildcards for a LIKE query should be passed in through a replacement parameter. Found:  LIKE \'a string%\'.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb->posts WHERE post_content LIKE \'a string%\' AND post_status = %s\\", $status ); \\/\\/ Bad.\\n",
                                        "line": 326,
                                        "column": 24
                                    },
                                    {
                                        "message": "SQL wildcards for a LIKE query should be passed in through a replacement parameter. Found:  LIKE \'%a string%\'.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb->posts WHERE post_content LIKE \'%a string%\' AND post_status = %s\\", $status ); \\/\\/ Bad.\\n",
                                        "line": 327,
                                        "column": 24
                                    },
                                    {
                                        "message": "SQL wildcards for a LIKE query should be passed in through a replacement parameter. Found:  LIKE \'a_string\'.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT * FROM $wpdb->posts WHERE post_content LIKE \'a_string\' AND post_status = %s\\", $status ); \\/\\/ Bad.\\n",
                                        "line": 328,
                                        "column": 24
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$comment_id = $wpdb->get_var( $wpdb->prepare( \'SELECT comment_ID FROM \' . $wpdb->comments . \' WHERE comment_post_ID = %d AND comment_agent LIKE %s\', intval( $post->ID ), \'Disqus\\/1.0:\' . $comment_id ) ); \\/\\/ OK.\\n",
                                        "line": 331,
                                        "column": 15
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$comment_id = $wpdb->get_var( $wpdb->prepare( \'SELECT comment_ID FROM \' . $wpdb->comments . \' WHERE comment_post_ID = %d AND comment_agent LIKE %s\', intval( $post->ID ), \'Disqus\\/1.0:\' . $comment_id ) ); \\/\\/ OK.\\n",
                                        "line": 331,
                                        "column": 15
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \'UPDATE \' . $wpdb->prefix . \'posts SET post_content = REPLACE(post_content, %s, %s) WHERE post_type = \\"page\\" AND post_content LIKE %s\', $meta_before, $meta_after, \'%\' . $wpdb->esc_like( $meta_before ) . \'%\' ) ); \\/\\/ OK.\\n",
                                        "line": 335,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \'UPDATE \' . $wpdb->prefix . \'posts SET post_content = REPLACE(post_content, %s, %s) WHERE post_type = \\"page\\" AND post_content LIKE %s\', $meta_before, $meta_after, \'%\' . $wpdb->esc_like( $meta_before ) . \'%\' ) ); \\/\\/ OK.\\n",
                                        "line": 335,
                                        "column": 1
                                    },
                                    {
                                        "message": "Unless you are using SQL wildcards, using LIKE is inefficient. Use a straight compare instead. Found:  LIKE \'product\'.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.LikeWithoutWildcards",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->prepare( \\"UPDATE $wpdb->posts SET post_status = \'pending\' WHERE (post_type LIKE \'product_variation\' or post_type LIKE \'product\') AND NOT ID IN (\\" . implode( \\",\\", $imported_ids ) . \\")\\" ); \\/\\/ Error x 1 for `product_variation`; warning x 1 for wrong use of LIKE with `product`; the PreparedSQL sniff will also kick in and throw an error about `$imported_ids`.\\n",
                                        "line": 340,
                                        "column": 17
                                    },
                                    {
                                        "message": "SQL wildcards for a LIKE query should be passed in through a replacement parameter. Found:  LIKE \'product_variation\'.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \\"UPDATE $wpdb->posts SET post_status = \'pending\' WHERE (post_type LIKE \'product_variation\' or post_type LIKE \'product\') AND NOT ID IN (\\" . implode( \\",\\", $imported_ids ) . \\")\\" ); \\/\\/ Error x 1 for `product_variation`; warning x 1 for wrong use of LIKE with `product`; the PreparedSQL sniff will also kick in and throw an error about `$imported_ids`.\\n",
                                        "line": 340,
                                        "column": 17
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found $imported_ids",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \\"UPDATE $wpdb->posts SET post_status = \'pending\' WHERE (post_type LIKE \'product_variation\' or post_type LIKE \'product\') AND NOT ID IN (\\" . implode( \\",\\", $imported_ids ) . \\")\\" ); \\/\\/ Error x 1 for `product_variation`; warning x 1 for wrong use of LIKE with `product`; the PreparedSQL sniff will also kick in and throw an error about `$imported_ids`.\\n",
                                        "line": 340,
                                        "column": 170
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$attachment = $wpdb->get_col( $wpdb->prepare( \\"SELECT ID FROM $wpdb->posts WHERE guid LIKE \'%%%s%%\' LIMIT 1;\\", $img_url ) ); \\/\\/ Bad.\\n",
                                        "line": 342,
                                        "column": 15
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$attachment = $wpdb->get_col( $wpdb->prepare( \\"SELECT ID FROM $wpdb->posts WHERE guid LIKE \'%%%s%%\' LIMIT 1;\\", $img_url ) ); \\/\\/ Bad.\\n",
                                        "line": 342,
                                        "column": 15
                                    },
                                    {
                                        "message": "SQL wildcards for a LIKE query should be passed in through a replacement parameter and the variable part of the replacement should be escaped using \\"esc_like()\\". Found:  LIKE \'%%%s%%\'.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQueryWithPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$attachment = $wpdb->get_col( $wpdb->prepare( \\"SELECT ID FROM $wpdb->posts WHERE guid LIKE \'%%%s%%\' LIMIT 1;\\", $img_url ) ); \\/\\/ Bad.\\n",
                                        "line": 342,
                                        "column": 47
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$result = $wpdb->get_col( $wpdb->prepare( \\"SELECT guid FROM $wpdb->posts WHERE guid LIKE \'%%%s\' and post_parent=%d;\\", $atts[\'model\'], $post->ID ) ); \\/\\/ Bad.\\n",
                                        "line": 344,
                                        "column": 11
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$result = $wpdb->get_col( $wpdb->prepare( \\"SELECT guid FROM $wpdb->posts WHERE guid LIKE \'%%%s\' and post_parent=%d;\\", $atts[\'model\'], $post->ID ) ); \\/\\/ Bad.\\n",
                                        "line": 344,
                                        "column": 11
                                    },
                                    {
                                        "message": "SQL wildcards for a LIKE query should be passed in through a replacement parameter and the variable part of the replacement should be escaped using \\"esc_like()\\". Found:  LIKE \'%%%s\'.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQueryWithPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$result = $wpdb->get_col( $wpdb->prepare( \\"SELECT guid FROM $wpdb->posts WHERE guid LIKE \'%%%s\' and post_parent=%d;\\", $atts[\'model\'], $post->ID ) ); \\/\\/ Bad.\\n",
                                        "line": 344,
                                        "column": 43
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$comments = $wpdb->get_results( $wpdb->prepare( \\"SELECT * FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_agent NOT LIKE \'Disqus\\/%%\'\\", $post->ID ) ); \\/\\/ Bad.\\n",
                                        "line": 346,
                                        "column": 13
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$comments = $wpdb->get_results( $wpdb->prepare( \\"SELECT * FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_agent NOT LIKE \'Disqus\\/%%\'\\", $post->ID ) ); \\/\\/ Bad.\\n",
                                        "line": 346,
                                        "column": 13
                                    },
                                    {
                                        "message": "SQL wildcards for a LIKE query should be passed in through a replacement parameter. Found:  LIKE \'Disqus\\/%%\'.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$comments = $wpdb->get_results( $wpdb->prepare( \\"SELECT * FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_agent NOT LIKE \'Disqus\\/%%\'\\", $post->ID ) ); \\/\\/ Bad.\\n",
                                        "line": 346,
                                        "column": 49
                                    },
                                    {
                                        "message": "SQL wildcards for a LIKE query should be passed in through a replacement parameter and the variable part of the replacement should be escaped using \\"esc_like()\\". Found:  LIKE \'%%%s%%\'.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQueryWithPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT count(*) FROM $this->fontsTable WHERE name LIKE \'%%%s%%\' OR status LIKE \'%%%s%%\' OR metadata LIKE \'%%%s%%\'\\", $search, $search, $search ); \\/\\/ Bad x 3, the PreparedSQL sniff will also kick in and throw an error about `$this`.\\n",
                                        "line": 348,
                                        "column": 24
                                    },
                                    {
                                        "message": "SQL wildcards for a LIKE query should be passed in through a replacement parameter and the variable part of the replacement should be escaped using \\"esc_like()\\". Found:  LIKE \'%%%s%%\'.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQueryWithPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT count(*) FROM $this->fontsTable WHERE name LIKE \'%%%s%%\' OR status LIKE \'%%%s%%\' OR metadata LIKE \'%%%s%%\'\\", $search, $search, $search ); \\/\\/ Bad x 3, the PreparedSQL sniff will also kick in and throw an error about `$this`.\\n",
                                        "line": 348,
                                        "column": 24
                                    },
                                    {
                                        "message": "SQL wildcards for a LIKE query should be passed in through a replacement parameter and the variable part of the replacement should be escaped using \\"esc_like()\\". Found:  LIKE \'%%%s%%\'.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQueryWithPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT count(*) FROM $this->fontsTable WHERE name LIKE \'%%%s%%\' OR status LIKE \'%%%s%%\' OR metadata LIKE \'%%%s%%\'\\", $search, $search, $search ); \\/\\/ Bad x 3, the PreparedSQL sniff will also kick in and throw an error about `$this`.\\n",
                                        "line": 348,
                                        "column": 24
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $this->fontsTable at \\"SELECT count(*) FROM $this->fontsTable WHERE name LIKE \'%%%s%%\' OR status LIKE \'%%%s%%\' OR metadata LIKE \'%%%s%%\'\\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT count(*) FROM $this->fontsTable WHERE name LIKE \'%%%s%%\' OR status LIKE \'%%%s%%\' OR metadata LIKE \'%%%s%%\'\\", $search, $search, $search ); \\/\\/ Bad x 3, the PreparedSQL sniff will also kick in and throw an error about `$this`.\\n",
                                        "line": 348,
                                        "column": 24
                                    },
                                    {
                                        "message": "SQL wildcards for a LIKE query should be passed in through a replacement parameter and the variable part of the replacement should be escaped using \\"esc_like()\\". Found:  like \\"%%%s%%\\".",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQueryWithPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$additional_where = $wpdb->prepare( \' AND (network like \\"%%%s%%\\" OR ProgramTitle like \\"%%%s%%\\" OR TransactionStatus like \\"%%%s%%\\" ) \', $search, $search, $search ); \\/\\/ Bad x 3.\\n",
                                        "line": 350,
                                        "column": 37
                                    },
                                    {
                                        "message": "SQL wildcards for a LIKE query should be passed in through a replacement parameter and the variable part of the replacement should be escaped using \\"esc_like()\\". Found:  like \\"%%%s%%\\".",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQueryWithPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$additional_where = $wpdb->prepare( \' AND (network like \\"%%%s%%\\" OR ProgramTitle like \\"%%%s%%\\" OR TransactionStatus like \\"%%%s%%\\" ) \', $search, $search, $search ); \\/\\/ Bad x 3.\\n",
                                        "line": 350,
                                        "column": 37
                                    },
                                    {
                                        "message": "SQL wildcards for a LIKE query should be passed in through a replacement parameter and the variable part of the replacement should be escaped using \\"esc_like()\\". Found:  like \\"%%%s%%\\".",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQueryWithPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$additional_where = $wpdb->prepare( \' AND (network like \\"%%%s%%\\" OR ProgramTitle like \\"%%%s%%\\" OR TransactionStatus like \\"%%%s%%\\" ) \', $search, $search, $search ); \\/\\/ Bad x 3.\\n",
                                        "line": 350,
                                        "column": 37
                                    },
                                    {
                                        "message": "SQL wildcards for a LIKE query should be passed in through a replacement parameter. Found:  LIKE concat(\'%%\',name,\'%%\').",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$robots_query = $wpdb->prepare( \\"SELECT name FROM $robots_table WHERE %s LIKE concat(\'%%\',name,\'%%\')\\", $http_user_agent ); \\/\\/ Bad, the PreparedSQL sniff will also kick in.\\n",
                                        "line": 352,
                                        "column": 33
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $robots_table at \\"SELECT name FROM $robots_table WHERE %s LIKE concat(\'%%\',name,\'%%\')\\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$robots_query = $wpdb->prepare( \\"SELECT name FROM $robots_table WHERE %s LIKE concat(\'%%\',name,\'%%\')\\", $http_user_agent ); \\/\\/ Bad, the PreparedSQL sniff will also kick in.\\n",
                                        "line": 352,
                                        "column": 33
                                    },
                                    {
                                        "message": "Simple placeholders should not be quoted in the query string in $wpdb->prepare(). Found: \\"%s\\".",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedSimplePlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \'SELECT * FROM \' . $wpdb->avatar_privacy . \' WHERE email LIKE \\"%s\\"\', $email ); \\/\\/ Bad (quotes).\\n",
                                        "line": 354,
                                        "column": 67
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$res = $wpdb->query( $wpdb->prepare( \'UPDATE \' . $wpdb->posts . \' SET post_name=\\"feed\\" WHERE post_name LIKE \\"feed-%\\" AND LENGTH(post_name)=6 AND post_type=%s\', BAWAS_POST_TYPE ) ); \\/\\/ Bad.\\n",
                                        "line": 356,
                                        "column": 8
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$res = $wpdb->query( $wpdb->prepare( \'UPDATE \' . $wpdb->posts . \' SET post_name=\\"feed\\" WHERE post_name LIKE \\"feed-%\\" AND LENGTH(post_name)=6 AND post_type=%s\', BAWAS_POST_TYPE ) ); \\/\\/ Bad.\\n",
                                        "line": 356,
                                        "column": 8
                                    },
                                    {
                                        "message": "SQL wildcards for a LIKE query should be passed in through a replacement parameter. Found:  LIKE \\"feed-%\\".",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$res = $wpdb->query( $wpdb->prepare( \'UPDATE \' . $wpdb->posts . \' SET post_name=\\"feed\\" WHERE post_name LIKE \\"feed-%\\" AND LENGTH(post_name)=6 AND post_type=%s\', BAWAS_POST_TYPE ) ); \\/\\/ Bad.\\n",
                                        "line": 356,
                                        "column": 65
                                    },
                                    {
                                        "message": "SQL wildcards for a LIKE query should be passed in through a replacement parameter. Found:  LIKE \'%%bbp_user%%\'.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$sql = $wpdb->prepare( \\"SELECT ID FROM $wpdb->users AS us INNER JOIN $wpdb->usermeta AS mt ON ( us.ID = mt.user_id ) WHERE ( mt.meta_key = \'bbp_last_login\' AND mt.meta_value < %s ) AND user_id IN ( SELECT user_id FROM $wpdb->usermeta AS mt WHERE (mt.meta_key = \'{$wpdb->prefix}capabilities\' AND mt.meta_value LIKE \'%%bbp_user%%\' ))\\", $beforegmdate ); \\/\\/ Bad.\\n",
                                        "line": 358,
                                        "column": 24
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$paged_events = $wpdb->get_results( $wpdb->prepare( \\"SELECT id as event_id FROM {$bp->events->table_name} WHERE ( name LIKE \'{$filter}%%\' OR description LIKE \'{$filter}%%\' ) AND id IN ({$gids}) \\" . $oldevents . \\" {$pag_sql}\\" ) ); \\/\\/ Bad x 2, the PreparedSQL sniff will also kick in and throw six errors.\\n",
                                        "line": 360,
                                        "column": 17
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$paged_events = $wpdb->get_results( $wpdb->prepare( \\"SELECT id as event_id FROM {$bp->events->table_name} WHERE ( name LIKE \'{$filter}%%\' OR description LIKE \'{$filter}%%\' ) AND id IN ({$gids}) \\" . $oldevents . \\" {$pag_sql}\\" ) ); \\/\\/ Bad x 2, the PreparedSQL sniff will also kick in and throw six errors.\\n",
                                        "line": 360,
                                        "column": 17
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable {$bp->events->table_name} at \\"SELECT id as event_id FROM {$bp->events->table_name} WHERE ( name LIKE \'{$filter}%%\' OR description LIKE \'{$filter}%%\' ) AND id IN ({$gids}) \\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$paged_events = $wpdb->get_results( $wpdb->prepare( \\"SELECT id as event_id FROM {$bp->events->table_name} WHERE ( name LIKE \'{$filter}%%\' OR description LIKE \'{$filter}%%\' ) AND id IN ({$gids}) \\" . $oldevents . \\" {$pag_sql}\\" ) ); \\/\\/ Bad x 2, the PreparedSQL sniff will also kick in and throw six errors.\\n",
                                        "line": 360,
                                        "column": 53
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable {$filter} at \\"SELECT id as event_id FROM {$bp->events->table_name} WHERE ( name LIKE \'{$filter}%%\' OR description LIKE \'{$filter}%%\' ) AND id IN ({$gids}) \\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$paged_events = $wpdb->get_results( $wpdb->prepare( \\"SELECT id as event_id FROM {$bp->events->table_name} WHERE ( name LIKE \'{$filter}%%\' OR description LIKE \'{$filter}%%\' ) AND id IN ({$gids}) \\" . $oldevents . \\" {$pag_sql}\\" ) ); \\/\\/ Bad x 2, the PreparedSQL sniff will also kick in and throw six errors.\\n",
                                        "line": 360,
                                        "column": 53
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable {$filter} at \\"SELECT id as event_id FROM {$bp->events->table_name} WHERE ( name LIKE \'{$filter}%%\' OR description LIKE \'{$filter}%%\' ) AND id IN ({$gids}) \\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$paged_events = $wpdb->get_results( $wpdb->prepare( \\"SELECT id as event_id FROM {$bp->events->table_name} WHERE ( name LIKE \'{$filter}%%\' OR description LIKE \'{$filter}%%\' ) AND id IN ({$gids}) \\" . $oldevents . \\" {$pag_sql}\\" ) ); \\/\\/ Bad x 2, the PreparedSQL sniff will also kick in and throw six errors.\\n",
                                        "line": 360,
                                        "column": 53
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable {$gids} at \\"SELECT id as event_id FROM {$bp->events->table_name} WHERE ( name LIKE \'{$filter}%%\' OR description LIKE \'{$filter}%%\' ) AND id IN ({$gids}) \\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$paged_events = $wpdb->get_results( $wpdb->prepare( \\"SELECT id as event_id FROM {$bp->events->table_name} WHERE ( name LIKE \'{$filter}%%\' OR description LIKE \'{$filter}%%\' ) AND id IN ({$gids}) \\" . $oldevents . \\" {$pag_sql}\\" ) ); \\/\\/ Bad x 2, the PreparedSQL sniff will also kick in and throw six errors.\\n",
                                        "line": 360,
                                        "column": 53
                                    },
                                    {
                                        "message": "SQL wildcards for a LIKE query should be passed in through a replacement parameter. Found:  LIKE \'%%\'.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$paged_events = $wpdb->get_results( $wpdb->prepare( \\"SELECT id as event_id FROM {$bp->events->table_name} WHERE ( name LIKE \'{$filter}%%\' OR description LIKE \'{$filter}%%\' ) AND id IN ({$gids}) \\" . $oldevents . \\" {$pag_sql}\\" ) ); \\/\\/ Bad x 2, the PreparedSQL sniff will also kick in and throw six errors.\\n",
                                        "line": 360,
                                        "column": 53
                                    },
                                    {
                                        "message": "SQL wildcards for a LIKE query should be passed in through a replacement parameter. Found:  LIKE \'%%\'.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$paged_events = $wpdb->get_results( $wpdb->prepare( \\"SELECT id as event_id FROM {$bp->events->table_name} WHERE ( name LIKE \'{$filter}%%\' OR description LIKE \'{$filter}%%\' ) AND id IN ({$gids}) \\" . $oldevents . \\" {$pag_sql}\\" ) ); \\/\\/ Bad x 2, the PreparedSQL sniff will also kick in and throw six errors.\\n",
                                        "line": 360,
                                        "column": 53
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found $oldevents",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$paged_events = $wpdb->get_results( $wpdb->prepare( \\"SELECT id as event_id FROM {$bp->events->table_name} WHERE ( name LIKE \'{$filter}%%\' OR description LIKE \'{$filter}%%\' ) AND id IN ({$gids}) \\" . $oldevents . \\" {$pag_sql}\\" ) ); \\/\\/ Bad x 2, the PreparedSQL sniff will also kick in and throw six errors.\\n",
                                        "line": 360,
                                        "column": 199
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable {$pag_sql} at \\" {$pag_sql}\\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$paged_events = $wpdb->get_results( $wpdb->prepare( \\"SELECT id as event_id FROM {$bp->events->table_name} WHERE ( name LIKE \'{$filter}%%\' OR description LIKE \'{$filter}%%\' ) AND id IN ({$gids}) \\" . $oldevents . \\" {$pag_sql}\\" ) ); \\/\\/ Bad x 2, the PreparedSQL sniff will also kick in and throw six errors.\\n",
                                        "line": 360,
                                        "column": 212
                                    },
                                    {
                                        "message": "SQL wildcards for a LIKE query should be passed in through a replacement parameter. Found:  LIKE \'%%post_%%\'.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$query = $wpdb->prepare( \\"SELECT language_code FROM {$wpdb->prefix}icl_translations WHERE element_type LIKE \'%%post_%%\' AND element_id = %d\\", $post_ID ); \\/\\/ Bad.\\n",
                                        "line": 362,
                                        "column": 26
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$postID = $wpdb->get_var( $wpdb->prepare( \\"SELECT `postID` FROM `\\" . EPDataBase::$table_name . \\"` WHERE `path` like \'\\" . $filePath . \\"\';\\" ) ); \\/\\/ OK, the PreparedSQL sniff will kick in and throw four errors.\\n",
                                        "line": 364,
                                        "column": 11
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$postID = $wpdb->get_var( $wpdb->prepare( \\"SELECT `postID` FROM `\\" . EPDataBase::$table_name . \\"` WHERE `path` like \'\\" . $filePath . \\"\';\\" ) ); \\/\\/ OK, the PreparedSQL sniff will kick in and throw four errors.\\n",
                                        "line": 364,
                                        "column": 11
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found EPDataBase",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$postID = $wpdb->get_var( $wpdb->prepare( \\"SELECT `postID` FROM `\\" . EPDataBase::$table_name . \\"` WHERE `path` like \'\\" . $filePath . \\"\';\\" ) ); \\/\\/ OK, the PreparedSQL sniff will kick in and throw four errors.\\n",
                                        "line": 364,
                                        "column": 70
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found $table_name",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$postID = $wpdb->get_var( $wpdb->prepare( \\"SELECT `postID` FROM `\\" . EPDataBase::$table_name . \\"` WHERE `path` like \'\\" . $filePath . \\"\';\\" ) ); \\/\\/ OK, the PreparedSQL sniff will kick in and throw four errors.\\n",
                                        "line": 364,
                                        "column": 82
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found $filePath",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$postID = $wpdb->get_var( $wpdb->prepare( \\"SELECT `postID` FROM `\\" . EPDataBase::$table_name . \\"` WHERE `path` like \'\\" . $filePath . \\"\';\\" ) ); \\/\\/ OK, the PreparedSQL sniff will kick in and throw four errors.\\n",
                                        "line": 364,
                                        "column": 122
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"show tables like \'$this->table_name\'\\" ) ) > 0; \\/\\/ OK, the PreparedSQL sniff will kick in.\\n",
                                        "line": 366,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"show tables like \'$this->table_name\'\\" ) ) > 0; \\/\\/ OK, the PreparedSQL sniff will kick in.\\n",
                                        "line": 366,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $this->table_name at \\"show tables like \'$this->table_name\'\\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"show tables like \'$this->table_name\'\\" ) ) > 0; \\/\\/ OK, the PreparedSQL sniff will kick in.\\n",
                                        "line": 366,
                                        "column": 31
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"DELETE FROM $wpdb->wp_options WHERE option_name LIKE \'%widget_gigya%\'\\" ) ); \\/\\/ Bad.\\n",
                                        "line": 368,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"DELETE FROM $wpdb->wp_options WHERE option_name LIKE \'%widget_gigya%\'\\" ) ); \\/\\/ Bad.\\n",
                                        "line": 368,
                                        "column": 1
                                    },
                                    {
                                        "message": "SQL wildcards for a LIKE query should be passed in through a replacement parameter. Found:  LIKE \'%widget_gigya%\'.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"DELETE FROM $wpdb->wp_options WHERE option_name LIKE \'%widget_gigya%\'\\" ) ); \\/\\/ Bad.\\n",
                                        "line": 368,
                                        "column": 31
                                    },
                                    {
                                        "message": "SQL wildcards for a LIKE query should be passed in through a replacement parameter. Found:  LIKE \'%%%%.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$where .= $wpdb->prepare( \\" AND `name` LIKE \'%%%%\\" . \'%s\' . \\"%%%%\' \\", $args[\'name\'] ); \\/\\/ Bad x 2.\\n",
                                        "line": 370,
                                        "column": 27
                                    },
                                    {
                                        "message": "Unsupported placeholder used in $wpdb->prepare(). Found: \\"%%%%\\".",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$where .= $wpdb->prepare( \\" AND `name` LIKE \'%%%%\\" . \'%s\' . \\"%%%%\' \\", $args[\'name\'] ); \\/\\/ Bad x 2.\\n",
                                        "line": 370,
                                        "column": 61
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"delete from wp_postmeta where post_id = $target_postId AND meta_key like \'google_snippets\'\\" ) ); \\/\\/ Bad, the PreparedSQL sniff will also kick in and throw an error about `$target_postId`.\\n",
                                        "line": 372,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"delete from wp_postmeta where post_id = $target_postId AND meta_key like \'google_snippets\'\\" ) ); \\/\\/ Bad, the PreparedSQL sniff will also kick in and throw an error about `$target_postId`.\\n",
                                        "line": 372,
                                        "column": 1
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found interpolated variable $target_postId at \\"delete from wp_postmeta where post_id = $target_postId AND meta_key like \'google_snippets\'\\"",
                                        "source": "WordPress.DB.PreparedSQL.InterpolatedNotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"delete from wp_postmeta where post_id = $target_postId AND meta_key like \'google_snippets\'\\" ) ); \\/\\/ Bad, the PreparedSQL sniff will also kick in and throw an error about `$target_postId`.\\n",
                                        "line": 372,
                                        "column": 31
                                    },
                                    {
                                        "message": "SQL wildcards for a LIKE query should be passed in through a replacement parameter. Found:  like \'google_snippets\'.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->query( $wpdb->prepare( \\"delete from wp_postmeta where post_id = $target_postId AND meta_key like \'google_snippets\'\\" ) ); \\/\\/ Bad, the PreparedSQL sniff will also kick in and throw an error about `$target_postId`.\\n",
                                        "line": 372,
                                        "column": 31
                                    },
                                    {
                                        "message": "Incorrect number of replacements passed to $wpdb->prepare(). Found 1 replacement parameters, expected 2.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->prepare( \'WHERE %i = %s\', $value ); \\/\\/ ReplacementsWrongNumber.\\n",
                                        "line": 377,
                                        "column": 1
                                    },
                                    {
                                        "message": "Incorrect number of replacements passed to $wpdb->prepare(). Found 2 replacement parameters, expected 1.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->prepare( \'WHERE %i = %x\', $field, $value ); \\/\\/ UnsupportedPlaceholder & ReplacementsWrongNumber.\\n",
                                        "line": 378,
                                        "column": 1
                                    },
                                    {
                                        "message": "Unsupported placeholder used in $wpdb->prepare(). Found: \\"%x\\".",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \'WHERE %i = %x\', $field, $value ); \\/\\/ UnsupportedPlaceholder & ReplacementsWrongNumber.\\n",
                                        "line": 378,
                                        "column": 17
                                    },
                                    {
                                        "message": "Complex placeholders used for values in the query string in $wpdb->prepare() will NOT be quoted automagically. Found: %2$s.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->prepare( \'WHERE %i = %2$s\', $field, $value ); \\/\\/ UnquotedComplexPlaceholder.\\n",
                                        "line": 379,
                                        "column": 17
                                    },
                                    {
                                        "message": "Complex placeholders used for values in the query string in $wpdb->prepare() will NOT be quoted automagically. Found: %10s.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->prepare( \'WHERE %i = %10s\', $field, $value ); \\/\\/ UnquotedComplexPlaceholder.\\n",
                                        "line": 380,
                                        "column": 17
                                    },
                                    {
                                        "message": "Complex placeholders used for values in the query string in $wpdb->prepare() will NOT be quoted automagically. Found: %2$-10s.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->prepare( \'WHERE %i = %2$-10s\', $field, $value ); \\/\\/ UnquotedComplexPlaceholder.\\n",
                                        "line": 381,
                                        "column": 17
                                    },
                                    {
                                        "message": "Simple placeholders should not be quoted in the query string in $wpdb->prepare(). Found: \\"%s\\".",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedSimplePlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \'WHERE %i = \\"%s\\"\', $field, $value ); \\/\\/ QuotedSimplePlaceholder.\\n",
                                        "line": 383,
                                        "column": 17
                                    },
                                    {
                                        "message": "Placeholders used for identifiers (%i) in the query string in $wpdb->prepare() are always quoted automagically. Please remove the surrounding quotes. Found: \\"%i\\"",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedIdentifierPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \'WHERE \\"%i\\" = %s\', $field, $value ); \\/\\/ QuotedIdentifierPlaceholder.\\n",
                                        "line": 384,
                                        "column": 17
                                    },
                                    {
                                        "message": "Placeholders used for identifiers (%i) in the query string in $wpdb->prepare() are always quoted automagically. Please remove the surrounding quotes. Found: \\\\\\"%i\\\\\\"",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedIdentifierPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \\"WHERE \\\\\\"%i\\\\\\" = %s\\", $field, $value ); \\/\\/ QuotedIdentifierPlaceholder.\\n",
                                        "line": 385,
                                        "column": 17
                                    },
                                    {
                                        "message": "Placeholders used for identifiers (%i) in the query string in $wpdb->prepare() are always quoted automagically. Please remove the surrounding quotes. Found: \'%i\'",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedIdentifierPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \\"WHERE \'%i\' = %s\\", $field, $value ); \\/\\/ QuotedIdentifierPlaceholder.\\n",
                                        "line": 386,
                                        "column": 17
                                    },
                                    {
                                        "message": "Placeholders used for identifiers (%i) in the query string in $wpdb->prepare() are always quoted automagically. Please remove the surrounding quotes. Found: \\\\\'%i\\\\\'",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedIdentifierPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \'WHERE \\\\\'%i\\\\\' = %s\', $field, $value ); \\/\\/ QuotedIdentifierPlaceholder.\\n",
                                        "line": 387,
                                        "column": 17
                                    },
                                    {
                                        "message": "Placeholders used for identifiers (%i) in the query string in $wpdb->prepare() are always quoted automagically. Please remove the surrounding quotes. Found: `%i`",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedIdentifierPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \'WHERE `%i` = %s\', $field, $value ); \\/\\/ QuotedIdentifierPlaceholder.\\n",
                                        "line": 388,
                                        "column": 17
                                    },
                                    {
                                        "message": "Placeholders used for identifiers (%i) in the query string in $wpdb->prepare() are always quoted automagically. Please remove the surrounding quotes. Found: \'%10i\'",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedIdentifierPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \\"WHERE \'%10i\' IS NULL\\", $field ); \\/\\/ QuotedIdentifierPlaceholder.\\n",
                                        "line": 390,
                                        "column": 17
                                    },
                                    {
                                        "message": "Placeholders used for identifiers (%i) in the query string in $wpdb->prepare() are always quoted automagically. Please remove the surrounding quotes. Found: \\"%10i\\"",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedIdentifierPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \'WHERE \\"%10i\\" IS NULL\', $field ); \\/\\/ QuotedIdentifierPlaceholder.\\n",
                                        "line": 391,
                                        "column": 17
                                    },
                                    {
                                        "message": "Placeholders used for identifiers (%i) in the query string in $wpdb->prepare() are always quoted automagically. Please remove the surrounding quotes. Found: \\\\\'%1$i\\\\\'",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedIdentifierPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \'WHERE \\\\\'%1$i\\\\\' IS NULL\', $field ); \\/\\/ QuotedIdentifierPlaceholder.\\n",
                                        "line": 392,
                                        "column": 17
                                    },
                                    {
                                        "message": "Placeholders used for identifiers (%i) in the query string in $wpdb->prepare() are always quoted automagically. Please remove the surrounding quotes. Found: \\\\\\"%10i\\\\\\"",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedIdentifierPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \\"WHERE \\\\\\"%10i\\\\\\" IS NULL\\", $field ); \\/\\/ QuotedIdentifierPlaceholder.\\n",
                                        "line": 393,
                                        "column": 17
                                    },
                                    {
                                        "message": "Found unescaped literal \\"%\\" character.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnescapedLiteral",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \'WHERE % i IS NULL\', $field ); \\/\\/ UnescapedLiteral & UnfinishedPrepare (while this is valid, should avoid).\\n",
                                        "line": 397,
                                        "column": 17
                                    },
                                    {
                                        "message": "Replacement variables found, but no valid placeholders found in the query.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->prepare( \'WHERE % i IS NULL\', $field ); \\/\\/ UnescapedLiteral & UnfinishedPrepare (while this is valid, should avoid).\\n",
                                        "line": 397,
                                        "column": 36
                                    },
                                    {
                                        "message": "Unsupported placeholder used in $wpdb->prepare(). Found: \\"%1$\\".",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \'WHERE %1$ 10.3i IS NULL\', $field ); \\/\\/ UnsupportedPlaceholder (parsed as \\"%1$\\", which is valid, but should avoid).\\n",
                                        "line": 401,
                                        "column": 17
                                    },
                                    {
                                        "message": "Placeholders used for identifiers (%i) in the query string in $wpdb->prepare() are always quoted automagically. Please remove the surrounding quotes. Found: `%1$i`",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedIdentifierPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \'WHERE `%1$i` IS NULL\', $field ); \\/\\/ QuotedIdentifierPlaceholder.\\n",
                                        "line": 407,
                                        "column": 17
                                    },
                                    {
                                        "message": "Placeholders used for identifiers (%i) in the query string in $wpdb->prepare() are always quoted automagically. Please remove the surrounding quotes. Found: `%10i`",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedIdentifierPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \'WHERE `%10i` IS NULL\', $field ); \\/\\/ QuotedIdentifierPlaceholder.\\n",
                                        "line": 408,
                                        "column": 17
                                    },
                                    {
                                        "message": "Found unescaped literal \\"%\\" character.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnescapedLiteral",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \'WHERE `% i` IS NULL\', $field ); \\/\\/ UnescapedLiteral & UnfinishedPrepare (if RegEx matched, then it should be QuotedIdentifierPlaceholder).\\n",
                                        "line": 409,
                                        "column": 17
                                    },
                                    {
                                        "message": "Replacement variables found, but no valid placeholders found in the query.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->prepare( \'WHERE `% i` IS NULL\', $field ); \\/\\/ UnescapedLiteral & UnfinishedPrepare (if RegEx matched, then it should be QuotedIdentifierPlaceholder).\\n",
                                        "line": 409,
                                        "column": 38
                                    },
                                    {
                                        "message": "Placeholders used for identifiers (%i) in the query string in $wpdb->prepare() are always quoted automagically. Please remove the surrounding quotes. Found: `%1$-10i`",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedIdentifierPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \'WHERE `%1$-10i` IS NULL\', $field ); \\/\\/ QuotedIdentifierPlaceholder.\\n",
                                        "line": 410,
                                        "column": 17
                                    },
                                    {
                                        "message": "Placeholders used for identifiers (%i) in the query string in $wpdb->prepare() are always quoted automagically. Please remove the surrounding quotes. Found: `%1$-10.3i`",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedIdentifierPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \'WHERE `%1$-10.3i` IS NULL\', $field ); \\/\\/ QuotedIdentifierPlaceholder.\\n",
                                        "line": 411,
                                        "column": 17
                                    },
                                    {
                                        "message": "Placeholders used for identifiers (%i) in the query string in $wpdb->prepare() are always quoted automagically. Please remove the surrounding quotes. Found: `%1$+10.3i`",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedIdentifierPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \'WHERE `%1$+10.3i` IS NULL\', $field ); \\/\\/ QuotedIdentifierPlaceholder.\\n",
                                        "line": 412,
                                        "column": 17
                                    },
                                    {
                                        "message": "Unsupported placeholder used in $wpdb->prepare(). Found: \\"%1$\\".",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \'WHERE `%1$ 10.3i` IS NULL\', $field ); \\/\\/ QuotedIdentifierPlaceholder, and UnsupportedPlaceholder (parsed as \\"%1$\\", which is valid, but should avoid).\\n",
                                        "line": 413,
                                        "column": 17
                                    },
                                    {
                                        "message": "Placeholders used for identifiers (%i) in the query string in $wpdb->prepare() are always quoted automagically. Please remove the surrounding quotes. Found: `%1$ 10.3i`",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedIdentifierPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \'WHERE `%1$ 10.3i` IS NULL\', $field ); \\/\\/ QuotedIdentifierPlaceholder, and UnsupportedPlaceholder (parsed as \\"%1$\\", which is valid, but should avoid).\\n",
                                        "line": 413,
                                        "column": 17
                                    },
                                    {
                                        "message": "Placeholders used for identifiers (%i) in the query string in $wpdb->prepare() are always quoted automagically. Please remove the surrounding quotes. Found: `%1$010.3i`",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedIdentifierPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \'WHERE `%1$010.3i` IS NULL\', $field ); \\/\\/ QuotedIdentifierPlaceholder.\\n",
                                        "line": 414,
                                        "column": 17
                                    },
                                    {
                                        "message": "Placeholders used for identifiers (%i) in the query string in $wpdb->prepare() are always quoted automagically. Please remove the surrounding quotes. Found: `%1$\'x10.3i`",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedIdentifierPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \\"WHERE `%1$\'x10.3i` IS NULL\\", $field ); \\/\\/ QuotedIdentifierPlaceholder.\\n",
                                        "line": 415,
                                        "column": 17
                                    },
                                    {
                                        "message": "Placeholders used for identifiers (%i) in the query string in $wpdb->prepare() are always quoted automagically. Please remove the surrounding quotes. Found: `%2$i`",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedIdentifierPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \'SELECT ID FROM `%2$i` WHERE `%1$i` = \\"%3$s\\"\', $field, $wpdb->posts, $value ); \\/\\/ QuotedIdentifierPlaceholder (x2).\\n",
                                        "line": 417,
                                        "column": 17
                                    },
                                    {
                                        "message": "Placeholders used for identifiers (%i) in the query string in $wpdb->prepare() are always quoted automagically. Please remove the surrounding quotes. Found: `%1$i`",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedIdentifierPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \'SELECT ID FROM `%2$i` WHERE `%1$i` = \\"%3$s\\"\', $field, $wpdb->posts, $value ); \\/\\/ QuotedIdentifierPlaceholder (x2).\\n",
                                        "line": 417,
                                        "column": 17
                                    },
                                    {
                                        "message": "Incorrect number of replacements passed to $wpdb->prepare(). Found 1 replacement parameters, expected 0.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->prepare(\\n",
                                        "line": 421,
                                        "column": 1
                                    },
                                    {
                                        "message": "The %i placeholder cannot be used within SQL `IN()` clauses.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.IdentifierWithinIN",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( \',\', array_fill( 0, count( $fields ), \'%i\' ) )\\n",
                                        "line": 424,
                                        "column": 50
                                    },
                                    {
                                        "message": "The %i placeholder cannot be used within SQL `IN()` clauses.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.IdentifierWithinIN",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\t\\t\'%i\' ) ) . \' )\',\\n",
                                        "line": 432,
                                        "column": 4
                                    },
                                    {
                                        "message": "The %i placeholder cannot be used within SQL `IN()` clauses.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.IdentifierWithinIN",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\txxx IN ( \' . implode( \',\', array_fill( 0, count( $post_types ), \\"%i\\" ) ) . \' )\',\\n",
                                        "line": 437,
                                        "column": 66
                                    },
                                    {
                                        "message": "The %i modifier is only supported in WP 6.2 or higher. Found: \\"%1$+10.3i\\".",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \'WHERE %1$+10.3i = %s\', $field, $value ); \\/\\/ UnsupportedIdentifierPlaceholder.\\n",
                                        "line": 443,
                                        "column": 17
                                    },
                                    {
                                        "message": "The %i modifier is only supported in WP 6.2 or higher. Found: \\"%10i\\".",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \\"WHERE \'%10i\' IS NULL\\", $field ); \\/\\/ UnsupportedIdentifierPlaceholder + QuotedIdentifierPlaceholder.\\n",
                                        "line": 444,
                                        "column": 17
                                    },
                                    {
                                        "message": "Placeholders used for identifiers (%i) in the query string in $wpdb->prepare() are always quoted automagically. Please remove the surrounding quotes. Found: \'%10i\'",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedIdentifierPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \\"WHERE \'%10i\' IS NULL\\", $field ); \\/\\/ UnsupportedIdentifierPlaceholder + QuotedIdentifierPlaceholder.\\n",
                                        "line": 444,
                                        "column": 17
                                    },
                                    {
                                        "message": "The %i placeholder cannot be used within SQL `IN()` clauses.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.IdentifierWithinIN",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \'xxx IN ( \' . implode( \',\', array_fill( 0, count( $post_types ), \'%i\' ) ) . \' )\', $fields ); \\/\\/ UnsupportedIdentifierPlaceholder + IdentifierWithinIN.\\n",
                                        "line": 445,
                                        "column": 82
                                    },
                                    {
                                        "message": "The %i modifier is only supported in WP 6.2 or higher. Found: \\"%i\\".",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare( \'xxx IN ( \' . implode( \',\', array_fill( 0, count( $post_types ), \'%i\' ) ) . \' )\', $fields ); \\/\\/ UnsupportedIdentifierPlaceholder + IdentifierWithinIN.\\n",
                                        "line": 445,
                                        "column": 82
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found ;",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$wpdb->prepare(); \\/\\/ Ignore.\\n",
                                        "line": 449,
                                        "column": 17
                                    },
                                    {
                                        "message": "Dynamic placeholder generation should not have surrounding quotes.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedDynamicPlaceholderGeneration",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\"{$wpdb->posts}.post_type IN (\\\\\\"\\"\\n",
                                        "line": 452,
                                        "column": 2
                                    },
                                    {
                                        "message": "Dynamic placeholder generation should not have surrounding quotes.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedDynamicPlaceholderGeneration",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\"{$wpdb->posts}.post_status IN (\'\\"\\n",
                                        "line": 459,
                                        "column": 2
                                    },
                                    {
                                        "message": "Incorrect number of replacements passed to $wpdb->prepare(). Found 1 replacement parameters, expected 2.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$where = $wpdb->prepare(\\n",
                                        "line": 465,
                                        "column": 10
                                    },
                                    {
                                        "message": "Dynamic placeholder generation should not have surrounding quotes.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedDynamicPlaceholderGeneration",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\"{$wpdb->posts}.post_status IN (\'\\"\\n",
                                        "line": 466,
                                        "column": 2
                                    },
                                    {
                                        "message": "Dynamic placeholder generation should not have surrounding quotes.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedDynamicPlaceholderGeneration",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\"{$wpdb->posts}.post_type IN (\\\\\\"\\"\\n",
                                        "line": 473,
                                        "column": 2
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found $array_fill",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t. implode( \',\', $array_fill )\\n",
                                        "line": 474,
                                        "column": 18
                                    },
                                    {
                                        "message": "Dynamic placeholder generation should not have surrounding quotes.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedDynamicPlaceholderGeneration",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\"{$wpdb->posts}.post_type IN (\\\\\\"\\"\\n",
                                        "line": 480,
                                        "column": 2
                                    },
                                    {
                                        "message": "Dynamic placeholder generation should not have surrounding quotes.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.QuotedDynamicPlaceholderGeneration",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\"{$wpdb->posts}.post_type IN (\\\\\\"\\"\\n",
                                        "line": 487,
                                        "column": 2
                                    },
                                    {
                                        "message": "Use of a direct database call is discouraged.",
                                        "source": "WordPress.DB.DirectDatabaseQuery.DirectQuery",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->get_col(\\n",
                                        "line": 494,
                                        "column": 1
                                    },
                                    {
                                        "message": "Direct database call without caching detected. Consider using wp_cache_get() \\/ wp_cache_set() or wp_cache_delete().",
                                        "source": "WordPress.DB.DirectDatabaseQuery.NoCaching",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$wpdb->get_col(\\n",
                                        "line": 494,
                                        "column": 1
                                    },
                                    {
                                        "message": "The %i placeholder cannot be used within SQL `IN()` clauses.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.IdentifierWithinIN",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\txxx IN ( \' . implode( \',\', array_fill( 0, count( $post_types ), \'%i\' \\/*comment*\\/ ) ) . \' )\',\\n",
                                        "line": 512,
                                        "column": 66
                                    },
                                    {
                                        "message": "The %i modifier is only supported in WP 6.2 or higher. Found: \\"%i\\".",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\txxx IN ( \' . implode( \',\', array_fill( 0, count( $post_types ), \'%i\' \\/*comment*\\/ ) ) . \' )\',\\n",
                                        "line": 512,
                                        "column": 66
                                    },
                                    {
                                        "message": "Unsupported placeholder used in $wpdb->prepare(). Found: \\"%C\\".",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t. implode( \',\', array_fill( 0, count( $post_types ), \'%C\' ) )\\n",
                                        "line": 518,
                                        "column": 55
                                    },
                                    {
                                        "message": "Replacement variables found, but no valid placeholders found in the query.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "\\t. \')\',\\n",
                                        "line": 519,
                                        "column": 7
                                    },
                                    {
                                        "message": "Incorrect number of replacements passed to $wpdb->prepare(). Found 1 replacement parameters, expected 0.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$where = $wpdb->prepare(\\n",
                                        "line": 523,
                                        "column": 10
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found args",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\targs: $replacements,\\n",
                                        "line": 577,
                                        "column": 2
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found :",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\targs: $replacements,\\n",
                                        "line": 577,
                                        "column": 6
                                    },
                                    {
                                        "message": "Placeholders found in the query passed to $wpdb->prepare(), but no replacements found. Expected 1 replacement(s) parameters.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.MissingReplacements",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$query = $wpdb->prepare(\\n",
                                        "line": 583,
                                        "column": 10
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found query",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\tquery: \'SELECT ID\\n",
                                        "line": 584,
                                        "column": 2
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found :",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\tquery: \'SELECT ID\\n",
                                        "line": 584,
                                        "column": 7
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found args",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\targs: $replacements,\\n",
                                        "line": 590,
                                        "column": 2
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found :",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\targs: $replacements,\\n",
                                        "line": 590,
                                        "column": 6
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found array",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( array: array_fill( 0, count( $post_types ), \'%s\' ), separator: \',\', ),\\n",
                                        "line": 600,
                                        "column": 12
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found :",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( array: array_fill( 0, count( $post_types ), \'%s\' ), separator: \',\', ),\\n",
                                        "line": 600,
                                        "column": 17
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found separator",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( array: array_fill( 0, count( $post_types ), \'%s\' ), separator: \',\', ),\\n",
                                        "line": 600,
                                        "column": 64
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found :",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( array: array_fill( 0, count( $post_types ), \'%s\' ), separator: \',\', ),\\n",
                                        "line": 600,
                                        "column": 73
                                    },
                                    {
                                        "message": "Incorrect number of replacements passed to $wpdb->prepare(). Found 1 replacement parameters, expected 0.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$where = $wpdb->prepare(\\n",
                                        "line": 605,
                                        "column": 10
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found array",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( array: $something, separator: \',\', ),\\n",
                                        "line": 608,
                                        "column": 12
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found :",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( array: $something, separator: \',\', ),\\n",
                                        "line": 608,
                                        "column": 17
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found $something",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( array: $something, separator: \',\', ),\\n",
                                        "line": 608,
                                        "column": 19
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found separator",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( array: $something, separator: \',\', ),\\n",
                                        "line": 608,
                                        "column": 31
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found :",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( array: $something, separator: \',\', ),\\n",
                                        "line": 608,
                                        "column": 40
                                    },
                                    {
                                        "message": "Incorrect number of replacements passed to $wpdb->prepare(). Found 1 replacement parameters, expected 0.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$where = $wpdb->prepare(\\n",
                                        "line": 613,
                                        "column": 10
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found arrays",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( arrays: array_fill( 0, count( $post_types ), \'%s\' ), separator: \',\', ),\\n",
                                        "line": 616,
                                        "column": 12
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found :",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( arrays: array_fill( 0, count( $post_types ), \'%s\' ), separator: \',\', ),\\n",
                                        "line": 616,
                                        "column": 18
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found separator",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( arrays: array_fill( 0, count( $post_types ), \'%s\' ), separator: \',\', ),\\n",
                                        "line": 616,
                                        "column": 65
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found :",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( arrays: array_fill( 0, count( $post_types ), \'%s\' ), separator: \',\', ),\\n",
                                        "line": 616,
                                        "column": 74
                                    },
                                    {
                                        "message": "Incorrect number of replacements passed to $wpdb->prepare(). Found 1 replacement parameters, expected 0.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$where = $wpdb->prepare(\\n",
                                        "line": 621,
                                        "column": 10
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found separator",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( separator: \',\', ),\\n",
                                        "line": 624,
                                        "column": 12
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found :",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( separator: \',\', ),\\n",
                                        "line": 624,
                                        "column": 21
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found start_index",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t. implode( \',\', array_fill( start_index: 0, count: count( $post_types ), value: \'%s\' ) ) \\/\\/ Expected order.\\n",
                                        "line": 632,
                                        "column": 30
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found :",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t. implode( \',\', array_fill( start_index: 0, count: count( $post_types ), value: \'%s\' ) ) \\/\\/ Expected order.\\n",
                                        "line": 632,
                                        "column": 41
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found count",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t. implode( \',\', array_fill( start_index: 0, count: count( $post_types ), value: \'%s\' ) ) \\/\\/ Expected order.\\n",
                                        "line": 632,
                                        "column": 46
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found :",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t. implode( \',\', array_fill( start_index: 0, count: count( $post_types ), value: \'%s\' ) ) \\/\\/ Expected order.\\n",
                                        "line": 632,
                                        "column": 51
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found value",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t. implode( \',\', array_fill( start_index: 0, count: count( $post_types ), value: \'%s\' ) ) \\/\\/ Expected order.\\n",
                                        "line": 632,
                                        "column": 75
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found :",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t. implode( \',\', array_fill( start_index: 0, count: count( $post_types ), value: \'%s\' ) ) \\/\\/ Expected order.\\n",
                                        "line": 632,
                                        "column": 80
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found value",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t. implode( \',\', array_fill( value: \'%s\', start_index: 0, count: count( $post_statusses ), ) ) \\/\\/ Unconventional order.\\n",
                                        "line": 634,
                                        "column": 30
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found :",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t. implode( \',\', array_fill( value: \'%s\', start_index: 0, count: count( $post_statusses ), ) ) \\/\\/ Unconventional order.\\n",
                                        "line": 634,
                                        "column": 35
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found start_index",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t. implode( \',\', array_fill( value: \'%s\', start_index: 0, count: count( $post_statusses ), ) ) \\/\\/ Unconventional order.\\n",
                                        "line": 634,
                                        "column": 43
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found :",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t. implode( \',\', array_fill( value: \'%s\', start_index: 0, count: count( $post_statusses ), ) ) \\/\\/ Unconventional order.\\n",
                                        "line": 634,
                                        "column": 54
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found count",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t. implode( \',\', array_fill( value: \'%s\', start_index: 0, count: count( $post_statusses ), ) ) \\/\\/ Unconventional order.\\n",
                                        "line": 634,
                                        "column": 59
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found :",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t. implode( \',\', array_fill( value: \'%s\', start_index: 0, count: count( $post_statusses ), ) ) \\/\\/ Unconventional order.\\n",
                                        "line": 634,
                                        "column": 64
                                    },
                                    {
                                        "message": "Incorrect number of replacements passed to $wpdb->prepare(). Found 1 replacement parameters, expected 0.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$where = $wpdb->prepare(\\n",
                                        "line": 639,
                                        "column": 10
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found start_index",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( \',\', array_fill( start_index: 0, count: count( $post_types ) ) ),\\n",
                                        "line": 642,
                                        "column": 29
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found :",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( \',\', array_fill( start_index: 0, count: count( $post_types ) ) ),\\n",
                                        "line": 642,
                                        "column": 40
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found count",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( \',\', array_fill( start_index: 0, count: count( $post_types ) ) ),\\n",
                                        "line": 642,
                                        "column": 45
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found :",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( \',\', array_fill( start_index: 0, count: count( $post_types ) ) ),\\n",
                                        "line": 642,
                                        "column": 50
                                    },
                                    {
                                        "message": "Incorrect number of replacements passed to $wpdb->prepare(). Found 1 replacement parameters, expected 0.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$where = $wpdb->prepare(\\n",
                                        "line": 647,
                                        "column": 10
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found start_index",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( \',\', array_fill( start_index: 0, values: \'%s\', count: count( $post_types ) ) ),\\n",
                                        "line": 650,
                                        "column": 29
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found :",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( \',\', array_fill( start_index: 0, values: \'%s\', count: count( $post_types ) ) ),\\n",
                                        "line": 650,
                                        "column": 40
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found values",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( \',\', array_fill( start_index: 0, values: \'%s\', count: count( $post_types ) ) ),\\n",
                                        "line": 650,
                                        "column": 45
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found :",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( \',\', array_fill( start_index: 0, values: \'%s\', count: count( $post_types ) ) ),\\n",
                                        "line": 650,
                                        "column": 51
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found count",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( \',\', array_fill( start_index: 0, values: \'%s\', count: count( $post_types ) ) ),\\n",
                                        "line": 650,
                                        "column": 59
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found :",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( \',\', array_fill( start_index: 0, values: \'%s\', count: count( $post_types ) ) ),\\n",
                                        "line": 650,
                                        "column": 64
                                    },
                                    {
                                        "message": "Incorrect number of replacements passed to $wpdb->prepare(). Found 1 replacement parameters, expected 0.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$where = $wpdb->prepare(\\n",
                                        "line": 655,
                                        "column": 10
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found value",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( \',\', array_fill( value: \'s\', start_index: 0, count: count( $post_types ) ) ),\\n",
                                        "line": 658,
                                        "column": 29
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found :",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( \',\', array_fill( value: \'s\', start_index: 0, count: count( $post_types ) ) ),\\n",
                                        "line": 658,
                                        "column": 34
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found start_index",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( \',\', array_fill( value: \'s\', start_index: 0, count: count( $post_types ) ) ),\\n",
                                        "line": 658,
                                        "column": 41
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found :",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( \',\', array_fill( value: \'s\', start_index: 0, count: count( $post_types ) ) ),\\n",
                                        "line": 658,
                                        "column": 52
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found count",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( \',\', array_fill( value: \'s\', start_index: 0, count: count( $post_types ) ) ),\\n",
                                        "line": 658,
                                        "column": 57
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found :",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( \',\', array_fill( value: \'s\', start_index: 0, count: count( $post_types ) ) ),\\n",
                                        "line": 658,
                                        "column": 62
                                    },
                                    {
                                        "message": "Incorrect number of replacements passed to $wpdb->prepare(). Found 1 replacement parameters, expected 0.",
                                        "source": "WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "WARNING",
                                        "codeFragment": "$where = $wpdb->prepare(\\n",
                                        "line": 663,
                                        "column": 10
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found value",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( \',\', array_fill( value: $s, start_index: 0, count: count( $post_types ) ) ),\\n",
                                        "line": 666,
                                        "column": 29
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found :",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( \',\', array_fill( value: $s, start_index: 0, count: count( $post_types ) ) ),\\n",
                                        "line": 666,
                                        "column": 34
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found $s",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( \',\', array_fill( value: $s, start_index: 0, count: count( $post_types ) ) ),\\n",
                                        "line": 666,
                                        "column": 36
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found start_index",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( \',\', array_fill( value: $s, start_index: 0, count: count( $post_types ) ) ),\\n",
                                        "line": 666,
                                        "column": 40
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found :",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( \',\', array_fill( value: $s, start_index: 0, count: count( $post_types ) ) ),\\n",
                                        "line": 666,
                                        "column": 51
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found count",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( \',\', array_fill( value: $s, start_index: 0, count: count( $post_types ) ) ),\\n",
                                        "line": 666,
                                        "column": 56
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found :",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\timplode( \',\', array_fill( value: $s, start_index: 0, count: count( $post_types ) ) ),\\n",
                                        "line": 666,
                                        "column": 61
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found values",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\tvalues: implode( \',\', array_fill( 0, count( $post_types ), \'%s\' ) ),\\n",
                                        "line": 675,
                                        "column": 3
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found :",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\tvalues: implode( \',\', array_fill( 0, count( $post_types ), \'%s\' ) ),\\n",
                                        "line": 675,
                                        "column": 9
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found format",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\tformat: \\"{$wpdb->posts}.post_type IN (\'%s\')\\",\\n",
                                        "line": 676,
                                        "column": 3
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found :",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "\\t\\tformat: \\"{$wpdb->posts}.post_type IN (\'%s\')\\",\\n",
                                        "line": 676,
                                        "column": 9
                                    },
                                    {
                                        "message": "Use placeholders and $wpdb->prepare(); found ...",
                                        "source": "WordPress.DB.PreparedSQL.NotPrepared",
                                        "severity": 5,
                                        "fixable": false,
                                        "type": "ERROR",
                                        "codeFragment": "$callback = $wpdb->prepare( ... ); \\/\\/ OK.",
                                        "line": 684,
                                        "column": 29
                                    }
                                ]
                            }
                        }
                    },
                    "semgrep": []
                }
            }
        }
    ]
]';
