<?php return '{
    "run_id": 123456,
    "test_type": "security",
    "wordpress_version": "6.0.0-normalized",
    "woocommerce_version": "6.0.0-normalized",
    "additional_woo_plugins": [],
    "additional_wp_plugins": [],
    "test_log": "",
    "test_result_json": "{\\"totals\\":{\\"errors\\":2,\\"warnings\\":0,\\"fixable\\":0},\\"files\\":{\\"\\\\\\/home\\\\\\/runner\\\\\\/work\\\\\\/compatibility-dashboard\\\\\\/compatibility-dashboard\\\\\\/ci\\\\\\/plugins\\\\\\/automatewoo\\\\\\/automatewoo.php\\":{\\"errors\\":2,\\"warnings\\":0,\\"messages\\":[{\\"message\\":\\"Detected usage of a non-sanitized input variable: $_POST[\'foo\']\\",\\"source\\":\\"WordPress.Security.ValidatedSanitizedInput.InputNotSanitized\\",\\"severity\\":5,\\"fixable\\":false,\\"type\\":\\"ERROR\\",\\"line\\":9,\\"column\\":10},{\\"message\\":\\"All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found \'\\\\\\"Unescaped output! $foo\\\\\\"\'.\\",\\"source\\":\\"WordPress.Security.EscapeOutput.OutputNotEscaped\\",\\"severity\\":5,\\"fixable\\":false,\\"type\\":\\"ERROR\\",\\"line\\":12,\\"column\\":8}]}}}\\n\\n",
    "status": "failed",
    "test_result_aws_url": "",
    "test_result_aws_expiration": 0,
    "is_development": true,
    "woo_extension": {
        "id": 4652610,
        "host": "wccom",
        "name": "AutomateWoo"
    },
    "client": "qit_cli",
    "event": "cli_development_extension_test",
    "optional_features": {
        "hpos": false,
        "cc_blocks": false
    },
    "test_results_manager_url": "https:\\/\\/test-results-manager.com",
    "test_results_manager_expiration": 1234567890,
    "version": "Zip"
}';
