<?php return '{
    "run_id": 123456,
    "test_type": "activation",
    "wordpress_version": "6.0.0-normalized",
    "woocommerce_version": "6.0.0-normalized",
    "additional_woo_plugins": [],
    "additional_wp_plugins": [],
    "test_log": "",
    "test_result_json": "{\\"results_overview\\":{\\"total_extensions\\":\\"1\\",\\"extensions_with_errors\\":{\\"woocommerce-product-feeds\\":{\\"WP-CLI Plugin Activation\\":2,\\"\\\\\\/\\":2,\\"\\\\\\/cart\\\\\\/\\":1,\\"\\\\\\/my-account\\\\\\/\\":2}},\\"error_totals\\":{\\"fatal\\":1,\\"notice\\":4,\\"warning\\":2,\\"E_USER_NOTICE\\":4,\\"E_USER_WARNING\\":2,\\"E_USER_ERROR\\":1},\\"summary\\":\\"7 Errors Detected. (1 Fatal, 2 Warnings, 4 Notices)\\",\\"error_count\\":7,\\"count_extensions_with_errors\\":1},\\"0\\":{\\"activated_alongside\\":\\"woocommerce-product-feeds\\",\\"context\\":\\"WP-CLI Plugin Activation\\",\\"is_fatal\\":\\"No\\",\\"error_type\\":\\"E_USER_NOTICE\\",\\"error_message\\":\\"Notice on all requests\\",\\"error_file\\":\\"woocommerce-product-feeds\\\\\\/woocommerce-product-feeds.php\\",\\"error_line\\":15,\\"backtrace\\":[{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-content\\\\\\/plugins\\\\\\/woocommerce-product-feeds\\\\\\/woocommerce-product-feeds.php\\",\\"line\\":15,\\"function\\":\\"trigger_error\\",\\"args\\":[\\"Notice on all requests\\"]},{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-admin\\\\\\/includes\\\\\\/plugin.php\\",\\"line\\":2314,\\"args\\":[\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-content\\\\\\/plugins\\\\\\/woocommerce-product-feeds\\\\\\/woocommerce-product-feeds.php\\"],\\"function\\":\\"include_once\\"},{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-admin\\\\\\/includes\\\\\\/plugin.php\\",\\"line\\":661,\\"function\\":\\"plugin_sandbox_scrape\\",\\"args\\":[\\"woocommerce-product-feeds\\\\\\/woocommerce-product-feeds.php\\"]},{\\"file\\":\\"phar:\\\\\\/\\\\\\/\\\\\\/usr\\\\\\/bin\\\\\\/wp\\\\\\/vendor\\\\\\/wp-cli\\\\\\/extension-command\\\\\\/src\\\\\\/Plugin_Command.php\\",\\"line\\":333,\\"function\\":\\"activate_plugin\\",\\"args\\":[\\"woocommerce-product-feeds\\\\\\/woocommerce-product-feeds.php\\",\\"\\",null]},{\\"function\\":\\"activate\\",\\"class\\":\\"Plugin_Command\\",\\"type\\":\\"->\\",\\"args\\":[[\\"woocommerce-product-feeds\\"],[]]},{\\"file\\":\\"phar:\\\\\\/\\\\\\/\\\\\\/usr\\\\\\/bin\\\\\\/wp\\\\\\/vendor\\\\\\/wp-cli\\\\\\/wp-cli\\\\\\/php\\\\\\/WP_CLI\\\\\\/Dispatcher\\\\\\/CommandFactory.php\\",\\"line\\":100,\\"function\\":\\"call_user_func\\",\\"args\\":[[[],\\"activate\\"],[\\"woocommerce-product-feeds\\"],[]]},{\\"function\\":\\"WP_CLI\\\\\\\\Dispatcher\\\\\\\\{closure}\\",\\"class\\":\\"WP_CLI\\\\\\\\Dispatcher\\\\\\\\CommandFactory\\",\\"type\\":\\"::\\",\\"args\\":[[\\"woocommerce-product-feeds\\"],[]]}],\\"db_error\\":\\"\\"},\\"1\\":{\\"activated_alongside\\":\\"woocommerce-product-feeds\\",\\"context\\":\\"\\\\\\/\\",\\"is_fatal\\":\\"No\\",\\"error_type\\":\\"E_USER_NOTICE\\",\\"error_message\\":\\"Notice on all requests\\",\\"error_file\\":\\"woocommerce-product-feeds\\\\\\/woocommerce-product-feeds.php\\",\\"error_line\\":15,\\"backtrace\\":[{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-content\\\\\\/plugins\\\\\\/woocommerce-product-feeds\\\\\\/woocommerce-product-feeds.php\\",\\"line\\":15,\\"function\\":\\"trigger_error\\",\\"args\\":[\\"Notice on all requests\\"]},{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-settings.php\\",\\"line\\":447,\\"args\\":[\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-content\\\\\\/plugins\\\\\\/woocommerce-product-feeds\\\\\\/woocommerce-product-feeds.php\\"],\\"function\\":\\"include_once\\"},{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-config.php\\",\\"line\\":133,\\"args\\":[\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-settings.php\\"],\\"function\\":\\"require_once\\"},{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-load.php\\",\\"line\\":50,\\"args\\":[\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-config.php\\"],\\"function\\":\\"require_once\\"},{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-blog-header.php\\",\\"line\\":13,\\"args\\":[\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-load.php\\"],\\"function\\":\\"require_once\\"},{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/index.php\\",\\"line\\":17,\\"args\\":[\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-blog-header.php\\"],\\"function\\":\\"require\\"},{\\"file\\":\\"\\\\\\/home\\\\\\/runner\\\\\\/work\\\\\\/staging-compatibility-dashboard\\\\\\/staging-compatibility-dashboard\\\\\\/ci\\\\\\/tests\\\\\\/compatibility\\\\\\/go-to-frontpage.php\\",\\"line\\":59,\\"args\\":[\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/index.php\\"],\\"function\\":\\"require\\"}],\\"db_error\\":\\"\\"},\\"2\\":{\\"activated_alongside\\":\\"woocommerce-product-feeds\\",\\"context\\":\\"\\\\\\/\\",\\"is_fatal\\":\\"No\\",\\"error_type\\":\\"E_USER_WARNING\\",\\"error_message\\":\\"Warning on all requests\\",\\"error_file\\":\\"woocommerce-product-feeds\\\\\\/woocommerce-product-feeds.php\\",\\"error_line\\":12,\\"backtrace\\":[{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-content\\\\\\/plugins\\\\\\/woocommerce-product-feeds\\\\\\/woocommerce-product-feeds.php\\",\\"line\\":12,\\"function\\":\\"trigger_error\\",\\"args\\":[\\"Warning on all requests\\",512]},{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-includes\\\\\\/class-wp-hook.php\\",\\"line\\":308,\\"function\\":\\"{closure}\\",\\"args\\":[{\\"public_query_vars\\":[\\"rating_filter\\",\\"filter_stock_status\\",\\"min_price\\",\\"max_price\\",\\"m\\",\\"p\\",\\"posts\\",\\"w\\",\\"cat\\",\\"withcomments\\",\\"withoutcomments\\",\\"s\\",\\"search\\",\\"exact\\",\\"sentence\\",\\"calendar\\",\\"page\\",\\"paged\\",\\"more\\",\\"tb\\",\\"pb\\",\\"author\\",\\"order\\",\\"orderby\\",\\"year\\",\\"monthnum\\",\\"day\\",\\"hour\\",\\"minute\\",\\"second\\",\\"name\\",\\"category_name\\",\\"tag\\",\\"feed\\",\\"author_name\\",\\"pagename\\",\\"page_id\\",\\"error\\",\\"attachment\\",\\"attachment_id\\",\\"subpost\\",\\"subpost_id\\",\\"preview\\",\\"robots\\",\\"favicon\\",\\"taxonomy\\",\\"term\\",\\"cpage\\",\\"post_type\\",\\"embed\\",\\"post_format\\",\\"wc-api\\",\\"product_cat\\",\\"product_tag\\",\\"product\\",\\"rest_route\\",\\"sitemap\\",\\"sitemap-subtype\\",\\"sitemap-stylesheet\\",\\"order-pay\\",\\"order-received\\",\\"orders\\",\\"view-order\\",\\"downloads\\",\\"edit-account\\",\\"edit-address\\",\\"payment-methods\\",\\"lost-password\\",\\"customer-logout\\",\\"add-payment-method\\",\\"delete-payment-method\\",\\"set-default-payment-method\\",\\"wc-auth-version\\",\\"wc-auth-route\\",\\"order-pay\\",\\"order-received\\",\\"orders\\",\\"view-order\\",\\"downloads\\",\\"edit-account\\",\\"edit-address\\",\\"payment-methods\\",\\"lost-password\\",\\"customer-logout\\",\\"add-payment-method\\",\\"delete-payment-method\\",\\"set-default-payment-method\\",\\"wc-api-version\\",\\"wc-api-route\\",\\"wc-api\\"],\\"private_query_vars\\":[\\"offset\\",\\"posts_per_page\\",\\"posts_per_archive_page\\",\\"showposts\\",\\"nopaging\\",\\"post_type\\",\\"post_status\\",\\"category__in\\",\\"category__not_in\\",\\"category__and\\",\\"tag__in\\",\\"tag__not_in\\",\\"tag__and\\",\\"tag_slug__in\\",\\"tag_slug__and\\",\\"tag_id\\",\\"post_mime_type\\",\\"perm\\",\\"comments_per_page\\",\\"post__in\\",\\"post__not_in\\",\\"post_parent\\",\\"post_parent__in\\",\\"post_parent__not_in\\",\\"title\\",\\"fields\\"],\\"extra_query_vars\\":[],\\"query_vars\\":[],\\"query_string\\":\\"\\",\\"request\\":\\"\\",\\"matched_rule\\":\\"\\",\\"matched_query\\":\\"\\",\\"did_permalink\\":false}]},{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-includes\\\\\\/class-wp-hook.php\\",\\"line\\":332,\\"function\\":\\"apply_filters\\",\\"class\\":\\"WP_Hook\\",\\"type\\":\\"->\\",\\"args\\":[\\"\\",[{\\"public_query_vars\\":[\\"rating_filter\\",\\"filter_stock_status\\",\\"min_price\\",\\"max_price\\",\\"m\\",\\"p\\",\\"posts\\",\\"w\\",\\"cat\\",\\"withcomments\\",\\"withoutcomments\\",\\"s\\",\\"search\\",\\"exact\\",\\"sentence\\",\\"calendar\\",\\"page\\",\\"paged\\",\\"more\\",\\"tb\\",\\"pb\\",\\"author\\",\\"order\\",\\"orderby\\",\\"year\\",\\"monthnum\\",\\"day\\",\\"hour\\",\\"minute\\",\\"second\\",\\"name\\",\\"category_name\\",\\"tag\\",\\"feed\\",\\"author_name\\",\\"pagename\\",\\"page_id\\",\\"error\\",\\"attachment\\",\\"attachment_id\\",\\"subpost\\",\\"subpost_id\\",\\"preview\\",\\"robots\\",\\"favicon\\",\\"taxonomy\\",\\"term\\",\\"cpage\\",\\"post_type\\",\\"embed\\",\\"post_format\\",\\"wc-api\\",\\"product_cat\\",\\"product_tag\\",\\"product\\",\\"rest_route\\",\\"sitemap\\",\\"sitemap-subtype\\",\\"sitemap-stylesheet\\",\\"order-pay\\",\\"order-received\\",\\"orders\\",\\"view-order\\",\\"downloads\\",\\"edit-account\\",\\"edit-address\\",\\"payment-methods\\",\\"lost-password\\",\\"customer-logout\\",\\"add-payment-method\\",\\"delete-payment-method\\",\\"set-default-payment-method\\",\\"wc-auth-version\\",\\"wc-auth-route\\",\\"order-pay\\",\\"order-received\\",\\"orders\\",\\"view-order\\",\\"downloads\\",\\"edit-account\\",\\"edit-address\\",\\"payment-methods\\",\\"lost-password\\",\\"customer-logout\\",\\"add-payment-method\\",\\"delete-payment-method\\",\\"set-default-payment-method\\",\\"wc-api-version\\",\\"wc-api-route\\",\\"wc-api\\"],\\"private_query_vars\\":[\\"offset\\",\\"posts_per_page\\",\\"posts_per_archive_page\\",\\"showposts\\",\\"nopaging\\",\\"post_type\\",\\"post_status\\",\\"category__in\\",\\"category__not_in\\",\\"category__and\\",\\"tag__in\\",\\"tag__not_in\\",\\"tag__and\\",\\"tag_slug__in\\",\\"tag_slug__and\\",\\"tag_id\\",\\"post_mime_type\\",\\"perm\\",\\"comments_per_page\\",\\"post__in\\",\\"post__not_in\\",\\"post_parent\\",\\"post_parent__in\\",\\"post_parent__not_in\\",\\"title\\",\\"fields\\"],\\"extra_query_vars\\":[],\\"query_vars\\":[],\\"query_string\\":\\"\\",\\"request\\":\\"\\",\\"matched_rule\\":\\"\\",\\"matched_query\\":\\"\\",\\"did_permalink\\":false}]]},{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-includes\\\\\\/plugin.php\\",\\"line\\":565,\\"function\\":\\"do_action\\",\\"class\\":\\"WP_Hook\\",\\"type\\":\\"->\\",\\"args\\":[[{\\"public_query_vars\\":[\\"rating_filter\\",\\"filter_stock_status\\",\\"min_price\\",\\"max_price\\",\\"m\\",\\"p\\",\\"posts\\",\\"w\\",\\"cat\\",\\"withcomments\\",\\"withoutcomments\\",\\"s\\",\\"search\\",\\"exact\\",\\"sentence\\",\\"calendar\\",\\"page\\",\\"paged\\",\\"more\\",\\"tb\\",\\"pb\\",\\"author\\",\\"order\\",\\"orderby\\",\\"year\\",\\"monthnum\\",\\"day\\",\\"hour\\",\\"minute\\",\\"second\\",\\"name\\",\\"category_name\\",\\"tag\\",\\"feed\\",\\"author_name\\",\\"pagename\\",\\"page_id\\",\\"error\\",\\"attachment\\",\\"attachment_id\\",\\"subpost\\",\\"subpost_id\\",\\"preview\\",\\"robots\\",\\"favicon\\",\\"taxonomy\\",\\"term\\",\\"cpage\\",\\"post_type\\",\\"embed\\",\\"post_format\\",\\"wc-api\\",\\"product_cat\\",\\"product_tag\\",\\"product\\",\\"rest_route\\",\\"sitemap\\",\\"sitemap-subtype\\",\\"sitemap-stylesheet\\",\\"order-pay\\",\\"order-received\\",\\"orders\\",\\"view-order\\",\\"downloads\\",\\"edit-account\\",\\"edit-address\\",\\"payment-methods\\",\\"lost-password\\",\\"customer-logout\\",\\"add-payment-method\\",\\"delete-payment-method\\",\\"set-default-payment-method\\",\\"wc-auth-version\\",\\"wc-auth-route\\",\\"order-pay\\",\\"order-received\\",\\"orders\\",\\"view-order\\",\\"downloads\\",\\"edit-account\\",\\"edit-address\\",\\"payment-methods\\",\\"lost-password\\",\\"customer-logout\\",\\"add-payment-method\\",\\"delete-payment-method\\",\\"set-default-payment-method\\",\\"wc-api-version\\",\\"wc-api-route\\",\\"wc-api\\"],\\"private_query_vars\\":[\\"offset\\",\\"posts_per_page\\",\\"posts_per_archive_page\\",\\"showposts\\",\\"nopaging\\",\\"post_type\\",\\"post_status\\",\\"category__in\\",\\"category__not_in\\",\\"category__and\\",\\"tag__in\\",\\"tag__not_in\\",\\"tag__and\\",\\"tag_slug__in\\",\\"tag_slug__and\\",\\"tag_id\\",\\"post_mime_type\\",\\"perm\\",\\"comments_per_page\\",\\"post__in\\",\\"post__not_in\\",\\"post_parent\\",\\"post_parent__in\\",\\"post_parent__not_in\\",\\"title\\",\\"fields\\"],\\"extra_query_vars\\":[],\\"query_vars\\":[],\\"query_string\\":\\"\\",\\"request\\":\\"\\",\\"matched_rule\\":\\"\\",\\"matched_query\\":\\"\\",\\"did_permalink\\":false}]]},{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-includes\\\\\\/class-wp.php\\",\\"line\\":797,\\"function\\":\\"do_action_ref_array\\",\\"args\\":[\\"wp\\",[{\\"public_query_vars\\":[\\"rating_filter\\",\\"filter_stock_status\\",\\"min_price\\",\\"max_price\\",\\"m\\",\\"p\\",\\"posts\\",\\"w\\",\\"cat\\",\\"withcomments\\",\\"withoutcomments\\",\\"s\\",\\"search\\",\\"exact\\",\\"sentence\\",\\"calendar\\",\\"page\\",\\"paged\\",\\"more\\",\\"tb\\",\\"pb\\",\\"author\\",\\"order\\",\\"orderby\\",\\"year\\",\\"monthnum\\",\\"day\\",\\"hour\\",\\"minute\\",\\"second\\",\\"name\\",\\"category_name\\",\\"tag\\",\\"feed\\",\\"author_name\\",\\"pagename\\",\\"page_id\\",\\"error\\",\\"attachment\\",\\"attachment_id\\",\\"subpost\\",\\"subpost_id\\",\\"preview\\",\\"robots\\",\\"favicon\\",\\"taxonomy\\",\\"term\\",\\"cpage\\",\\"post_type\\",\\"embed\\",\\"post_format\\",\\"wc-api\\",\\"product_cat\\",\\"product_tag\\",\\"product\\",\\"rest_route\\",\\"sitemap\\",\\"sitemap-subtype\\",\\"sitemap-stylesheet\\",\\"order-pay\\",\\"order-received\\",\\"orders\\",\\"view-order\\",\\"downloads\\",\\"edit-account\\",\\"edit-address\\",\\"payment-methods\\",\\"lost-password\\",\\"customer-logout\\",\\"add-payment-method\\",\\"delete-payment-method\\",\\"set-default-payment-method\\",\\"wc-auth-version\\",\\"wc-auth-route\\",\\"order-pay\\",\\"order-received\\",\\"orders\\",\\"view-order\\",\\"downloads\\",\\"edit-account\\",\\"edit-address\\",\\"payment-methods\\",\\"lost-password\\",\\"customer-logout\\",\\"add-payment-method\\",\\"delete-payment-method\\",\\"set-default-payment-method\\",\\"wc-api-version\\",\\"wc-api-route\\",\\"wc-api\\"],\\"private_query_vars\\":[\\"offset\\",\\"posts_per_page\\",\\"posts_per_archive_page\\",\\"showposts\\",\\"nopaging\\",\\"post_type\\",\\"post_status\\",\\"category__in\\",\\"category__not_in\\",\\"category__and\\",\\"tag__in\\",\\"tag__not_in\\",\\"tag__and\\",\\"tag_slug__in\\",\\"tag_slug__and\\",\\"tag_id\\",\\"post_mime_type\\",\\"perm\\",\\"comments_per_page\\",\\"post__in\\",\\"post__not_in\\",\\"post_parent\\",\\"post_parent__in\\",\\"post_parent__not_in\\",\\"title\\",\\"fields\\"],\\"extra_query_vars\\":[],\\"query_vars\\":[],\\"query_string\\":\\"\\",\\"request\\":\\"\\",\\"matched_rule\\":\\"\\",\\"matched_query\\":\\"\\",\\"did_permalink\\":false}]]},{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-includes\\\\\\/functions.php\\",\\"line\\":1332,\\"function\\":\\"main\\",\\"class\\":\\"WP\\",\\"type\\":\\"->\\",\\"args\\":[\\"\\"]},{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-blog-header.php\\",\\"line\\":16,\\"function\\":\\"wp\\",\\"args\\":[]}],\\"db_error\\":\\"\\"},\\"3\\":{\\"activated_alongside\\":\\"woocommerce-product-feeds\\",\\"context\\":\\"\\\\\\/cart\\\\\\/\\",\\"is_fatal\\":\\"No\\",\\"error_type\\":\\"E_USER_NOTICE\\",\\"error_message\\":\\"Notice on all requests\\",\\"error_file\\":\\"woocommerce-product-feeds\\\\\\/woocommerce-product-feeds.php\\",\\"error_line\\":15,\\"backtrace\\":[{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-content\\\\\\/plugins\\\\\\/woocommerce-product-feeds\\\\\\/woocommerce-product-feeds.php\\",\\"line\\":15,\\"function\\":\\"trigger_error\\",\\"args\\":[\\"Notice on all requests\\"]},{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-settings.php\\",\\"line\\":447,\\"args\\":[\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-content\\\\\\/plugins\\\\\\/woocommerce-product-feeds\\\\\\/woocommerce-product-feeds.php\\"],\\"function\\":\\"include_once\\"},{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-config.php\\",\\"line\\":133,\\"args\\":[\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-settings.php\\"],\\"function\\":\\"require_once\\"},{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-load.php\\",\\"line\\":50,\\"args\\":[\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-config.php\\"],\\"function\\":\\"require_once\\"},{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-blog-header.php\\",\\"line\\":13,\\"args\\":[\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-load.php\\"],\\"function\\":\\"require_once\\"},{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/index.php\\",\\"line\\":17,\\"args\\":[\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-blog-header.php\\"],\\"function\\":\\"require\\"},{\\"file\\":\\"\\\\\\/home\\\\\\/runner\\\\\\/work\\\\\\/staging-compatibility-dashboard\\\\\\/staging-compatibility-dashboard\\\\\\/ci\\\\\\/tests\\\\\\/compatibility\\\\\\/go-to-cart.php\\",\\"line\\":59,\\"args\\":[\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/index.php\\"],\\"function\\":\\"require\\"}],\\"db_error\\":\\"\\"},\\"4\\":{\\"activated_alongside\\":\\"woocommerce-product-feeds\\",\\"context\\":\\"WP-CLI Plugin Activation\\",\\"is_fatal\\":\\"Yes\\",\\"error_type\\":\\"E_USER_ERROR\\",\\"error_message\\":\\"[TIMESTAMP] PHP Fatal error:  Uncaught Error: Call to undefined function call_to_undefined_function() in \\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-content\\\\\\/plugins\\\\\\/woocommerce-product-feeds\\\\\\/woocommerce-product-feeds.php:9\\\\nStack trace:\\\\n#0 \\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-includes\\\\\\/class-wp-hook.php(308): {closure}(Object(WP))\\\\n#1 \\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-includes\\\\\\/class-wp-hook.php(332): WP_Hook->apply_filters(\'\', Array)\\\\n#2 \\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-includes\\\\\\/plugin.php(565): WP_Hook->do_action(Array)\\\\n#3 \\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-includes\\\\\\/class-wp.php(797): do_action_ref_array(\'wp\', Array)\\\\n#4 \\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-includes\\\\\\/functions.php(1332): WP->main(\'\')\\\\n#5 \\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-blog-header.php(16): wp()\\\\n#6 \\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/index.php(17): require(\'\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/w...\')\\\\n#7 \\\\\\/home\\\\\\/runner\\\\\\/work\\\\\\/staging-compatibility-dashboard\\\\\\/staging-compatibility-dashboard\\\\\\/ci\\\\\\/tests\\\\\\/compatibility\\\\\\/go-to-cart.php(59): require(\'\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/i...\')\\\\n#8 {main}\\\\n  thrown in \\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-content\\\\\\/plugins\\\\\\/woocommerce-product-feeds\\\\\\/woocommerce-product-feeds.php on line 9\\",\\"error_file\\":\\"-\\",\\"error_line\\":\\"-\\",\\"backtrace\\":[{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/compatibility-stderr-logger.php\\",\\"line\\":44,\\"function\\":\\"trigger_error\\",\\"args\\":[\\"[TIMESTAMP] PHP Fatal error:  Uncaught Error: Call to undefined function call_to_undefined_function() in \\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-content\\\\\\/plugins\\\\\\/woocommerce-product-feeds\\\\\\/woocommerce-product-feeds.php:9\\\\nStack trace:\\\\n#0 \\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-includes\\\\\\/class-wp-hook.php(308): {closure}(Object(WP))\\\\n#1 \\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-includes\\\\\\/class-wp-hook.php(332): WP_Hook->apply_filters(\'\', Array)\\\\n#2 \\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-includes\\\\\\/plugin.php(565): WP_Hook->do_action(Array)\\\\n#3 \\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-includes\\\\\\/class-wp.php(797): do_action_ref_array(\'wp\', Array)\\\\n#4 \\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-includes\\\\\\/functions.php(1332): WP->main(\'\')\\\\n#5 \\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-blog-header.php(16): wp()\\\\n#6 \\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/index.php(17): require(\'\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/w...\')\\\\n#7 \\\\\\/home\\\\\\/runner\\\\\\/work\\\\\\/staging-compatibility-dashboard\\\\\\/staging-compatibility-dashboard\\\\\\/ci\\\\\\/tests\\\\\\/compatibility\\\\\\/go-to-cart.php(59): require(\'\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/i...\')\\\\n#8 {main}\\\\n  thrown in \\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-content\\\\\\/plugins\\\\\\/woocommerce-product-feeds\\\\\\/woocommerce-product-feeds.php on line 9\\",256]}],\\"db_error\\":\\"\\"},\\"5\\":{\\"activated_alongside\\":\\"woocommerce-product-feeds\\",\\"context\\":\\"\\\\\\/my-account\\\\\\/\\",\\"is_fatal\\":\\"No\\",\\"error_type\\":\\"E_USER_NOTICE\\",\\"error_message\\":\\"Notice on all requests\\",\\"error_file\\":\\"woocommerce-product-feeds\\\\\\/woocommerce-product-feeds.php\\",\\"error_line\\":15,\\"backtrace\\":[{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-content\\\\\\/plugins\\\\\\/woocommerce-product-feeds\\\\\\/woocommerce-product-feeds.php\\",\\"line\\":15,\\"function\\":\\"trigger_error\\",\\"args\\":[\\"Notice on all requests\\"]},{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-settings.php\\",\\"line\\":447,\\"args\\":[\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-content\\\\\\/plugins\\\\\\/woocommerce-product-feeds\\\\\\/woocommerce-product-feeds.php\\"],\\"function\\":\\"include_once\\"},{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-config.php\\",\\"line\\":133,\\"args\\":[\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-settings.php\\"],\\"function\\":\\"require_once\\"},{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-load.php\\",\\"line\\":50,\\"args\\":[\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-config.php\\"],\\"function\\":\\"require_once\\"},{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-blog-header.php\\",\\"line\\":13,\\"args\\":[\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-load.php\\"],\\"function\\":\\"require_once\\"},{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/index.php\\",\\"line\\":17,\\"args\\":[\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-blog-header.php\\"],\\"function\\":\\"require\\"},{\\"file\\":\\"\\\\\\/home\\\\\\/runner\\\\\\/work\\\\\\/staging-compatibility-dashboard\\\\\\/staging-compatibility-dashboard\\\\\\/ci\\\\\\/tests\\\\\\/compatibility\\\\\\/go-to-account.php\\",\\"line\\":59,\\"args\\":[\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/index.php\\"],\\"function\\":\\"require\\"}],\\"db_error\\":\\"\\"},\\"6\\":{\\"activated_alongside\\":\\"woocommerce-product-feeds\\",\\"context\\":\\"\\\\\\/my-account\\\\\\/\\",\\"is_fatal\\":\\"No\\",\\"error_type\\":\\"E_USER_WARNING\\",\\"error_message\\":\\"Warning on all requests\\",\\"error_file\\":\\"woocommerce-product-feeds\\\\\\/woocommerce-product-feeds.php\\",\\"error_line\\":12,\\"backtrace\\":[{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-content\\\\\\/plugins\\\\\\/woocommerce-product-feeds\\\\\\/woocommerce-product-feeds.php\\",\\"line\\":12,\\"function\\":\\"trigger_error\\",\\"args\\":[\\"Warning on all requests\\",512]},{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-includes\\\\\\/class-wp-hook.php\\",\\"line\\":308,\\"function\\":\\"{closure}\\",\\"args\\":[{\\"public_query_vars\\":[\\"rating_filter\\",\\"filter_stock_status\\",\\"min_price\\",\\"max_price\\",\\"m\\",\\"p\\",\\"posts\\",\\"w\\",\\"cat\\",\\"withcomments\\",\\"withoutcomments\\",\\"s\\",\\"search\\",\\"exact\\",\\"sentence\\",\\"calendar\\",\\"page\\",\\"paged\\",\\"more\\",\\"tb\\",\\"pb\\",\\"author\\",\\"order\\",\\"orderby\\",\\"year\\",\\"monthnum\\",\\"day\\",\\"hour\\",\\"minute\\",\\"second\\",\\"name\\",\\"category_name\\",\\"tag\\",\\"feed\\",\\"author_name\\",\\"pagename\\",\\"page_id\\",\\"error\\",\\"attachment\\",\\"attachment_id\\",\\"subpost\\",\\"subpost_id\\",\\"preview\\",\\"robots\\",\\"favicon\\",\\"taxonomy\\",\\"term\\",\\"cpage\\",\\"post_type\\",\\"embed\\",\\"post_format\\",\\"wc-api\\",\\"product_cat\\",\\"product_tag\\",\\"product\\",\\"rest_route\\",\\"sitemap\\",\\"sitemap-subtype\\",\\"sitemap-stylesheet\\",\\"order-pay\\",\\"order-received\\",\\"orders\\",\\"view-order\\",\\"downloads\\",\\"edit-account\\",\\"edit-address\\",\\"payment-methods\\",\\"lost-password\\",\\"customer-logout\\",\\"add-payment-method\\",\\"delete-payment-method\\",\\"set-default-payment-method\\",\\"wc-auth-version\\",\\"wc-auth-route\\",\\"order-pay\\",\\"order-received\\",\\"orders\\",\\"view-order\\",\\"downloads\\",\\"edit-account\\",\\"edit-address\\",\\"payment-methods\\",\\"lost-password\\",\\"customer-logout\\",\\"add-payment-method\\",\\"delete-payment-method\\",\\"set-default-payment-method\\",\\"wc-api-version\\",\\"wc-api-route\\",\\"wc-api\\"],\\"private_query_vars\\":[\\"offset\\",\\"posts_per_page\\",\\"posts_per_archive_page\\",\\"showposts\\",\\"nopaging\\",\\"post_type\\",\\"post_status\\",\\"category__in\\",\\"category__not_in\\",\\"category__and\\",\\"tag__in\\",\\"tag__not_in\\",\\"tag__and\\",\\"tag_slug__in\\",\\"tag_slug__and\\",\\"tag_id\\",\\"post_mime_type\\",\\"perm\\",\\"comments_per_page\\",\\"post__in\\",\\"post__not_in\\",\\"post_parent\\",\\"post_parent__in\\",\\"post_parent__not_in\\",\\"title\\",\\"fields\\"],\\"extra_query_vars\\":[],\\"query_vars\\":{\\"page\\":\\"\\",\\"pagename\\":\\"my-account\\"},\\"query_string\\":\\"pagename=my-account\\",\\"request\\":\\"my-account\\",\\"matched_rule\\":\\"(.?.+?)(?:\\\\\\/([0-9]+))?\\\\\\/?$\\",\\"matched_query\\":\\"pagename=my-account&page=\\",\\"did_permalink\\":true}]},{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-includes\\\\\\/class-wp-hook.php\\",\\"line\\":332,\\"function\\":\\"apply_filters\\",\\"class\\":\\"WP_Hook\\",\\"type\\":\\"->\\",\\"args\\":[\\"\\",[{\\"public_query_vars\\":[\\"rating_filter\\",\\"filter_stock_status\\",\\"min_price\\",\\"max_price\\",\\"m\\",\\"p\\",\\"posts\\",\\"w\\",\\"cat\\",\\"withcomments\\",\\"withoutcomments\\",\\"s\\",\\"search\\",\\"exact\\",\\"sentence\\",\\"calendar\\",\\"page\\",\\"paged\\",\\"more\\",\\"tb\\",\\"pb\\",\\"author\\",\\"order\\",\\"orderby\\",\\"year\\",\\"monthnum\\",\\"day\\",\\"hour\\",\\"minute\\",\\"second\\",\\"name\\",\\"category_name\\",\\"tag\\",\\"feed\\",\\"author_name\\",\\"pagename\\",\\"page_id\\",\\"error\\",\\"attachment\\",\\"attachment_id\\",\\"subpost\\",\\"subpost_id\\",\\"preview\\",\\"robots\\",\\"favicon\\",\\"taxonomy\\",\\"term\\",\\"cpage\\",\\"post_type\\",\\"embed\\",\\"post_format\\",\\"wc-api\\",\\"product_cat\\",\\"product_tag\\",\\"product\\",\\"rest_route\\",\\"sitemap\\",\\"sitemap-subtype\\",\\"sitemap-stylesheet\\",\\"order-pay\\",\\"order-received\\",\\"orders\\",\\"view-order\\",\\"downloads\\",\\"edit-account\\",\\"edit-address\\",\\"payment-methods\\",\\"lost-password\\",\\"customer-logout\\",\\"add-payment-method\\",\\"delete-payment-method\\",\\"set-default-payment-method\\",\\"wc-auth-version\\",\\"wc-auth-route\\",\\"order-pay\\",\\"order-received\\",\\"orders\\",\\"view-order\\",\\"downloads\\",\\"edit-account\\",\\"edit-address\\",\\"payment-methods\\",\\"lost-password\\",\\"customer-logout\\",\\"add-payment-method\\",\\"delete-payment-method\\",\\"set-default-payment-method\\",\\"wc-api-version\\",\\"wc-api-route\\",\\"wc-api\\"],\\"private_query_vars\\":[\\"offset\\",\\"posts_per_page\\",\\"posts_per_archive_page\\",\\"showposts\\",\\"nopaging\\",\\"post_type\\",\\"post_status\\",\\"category__in\\",\\"category__not_in\\",\\"category__and\\",\\"tag__in\\",\\"tag__not_in\\",\\"tag__and\\",\\"tag_slug__in\\",\\"tag_slug__and\\",\\"tag_id\\",\\"post_mime_type\\",\\"perm\\",\\"comments_per_page\\",\\"post__in\\",\\"post__not_in\\",\\"post_parent\\",\\"post_parent__in\\",\\"post_parent__not_in\\",\\"title\\",\\"fields\\"],\\"extra_query_vars\\":[],\\"query_vars\\":{\\"page\\":\\"\\",\\"pagename\\":\\"my-account\\"},\\"query_string\\":\\"pagename=my-account\\",\\"request\\":\\"my-account\\",\\"matched_rule\\":\\"(.?.+?)(?:\\\\\\/([0-9]+))?\\\\\\/?$\\",\\"matched_query\\":\\"pagename=my-account&page=\\",\\"did_permalink\\":true}]]},{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-includes\\\\\\/plugin.php\\",\\"line\\":565,\\"function\\":\\"do_action\\",\\"class\\":\\"WP_Hook\\",\\"type\\":\\"->\\",\\"args\\":[[{\\"public_query_vars\\":[\\"rating_filter\\",\\"filter_stock_status\\",\\"min_price\\",\\"max_price\\",\\"m\\",\\"p\\",\\"posts\\",\\"w\\",\\"cat\\",\\"withcomments\\",\\"withoutcomments\\",\\"s\\",\\"search\\",\\"exact\\",\\"sentence\\",\\"calendar\\",\\"page\\",\\"paged\\",\\"more\\",\\"tb\\",\\"pb\\",\\"author\\",\\"order\\",\\"orderby\\",\\"year\\",\\"monthnum\\",\\"day\\",\\"hour\\",\\"minute\\",\\"second\\",\\"name\\",\\"category_name\\",\\"tag\\",\\"feed\\",\\"author_name\\",\\"pagename\\",\\"page_id\\",\\"error\\",\\"attachment\\",\\"attachment_id\\",\\"subpost\\",\\"subpost_id\\",\\"preview\\",\\"robots\\",\\"favicon\\",\\"taxonomy\\",\\"term\\",\\"cpage\\",\\"post_type\\",\\"embed\\",\\"post_format\\",\\"wc-api\\",\\"product_cat\\",\\"product_tag\\",\\"product\\",\\"rest_route\\",\\"sitemap\\",\\"sitemap-subtype\\",\\"sitemap-stylesheet\\",\\"order-pay\\",\\"order-received\\",\\"orders\\",\\"view-order\\",\\"downloads\\",\\"edit-account\\",\\"edit-address\\",\\"payment-methods\\",\\"lost-password\\",\\"customer-logout\\",\\"add-payment-method\\",\\"delete-payment-method\\",\\"set-default-payment-method\\",\\"wc-auth-version\\",\\"wc-auth-route\\",\\"order-pay\\",\\"order-received\\",\\"orders\\",\\"view-order\\",\\"downloads\\",\\"edit-account\\",\\"edit-address\\",\\"payment-methods\\",\\"lost-password\\",\\"customer-logout\\",\\"add-payment-method\\",\\"delete-payment-method\\",\\"set-default-payment-method\\",\\"wc-api-version\\",\\"wc-api-route\\",\\"wc-api\\"],\\"private_query_vars\\":[\\"offset\\",\\"posts_per_page\\",\\"posts_per_archive_page\\",\\"showposts\\",\\"nopaging\\",\\"post_type\\",\\"post_status\\",\\"category__in\\",\\"category__not_in\\",\\"category__and\\",\\"tag__in\\",\\"tag__not_in\\",\\"tag__and\\",\\"tag_slug__in\\",\\"tag_slug__and\\",\\"tag_id\\",\\"post_mime_type\\",\\"perm\\",\\"comments_per_page\\",\\"post__in\\",\\"post__not_in\\",\\"post_parent\\",\\"post_parent__in\\",\\"post_parent__not_in\\",\\"title\\",\\"fields\\"],\\"extra_query_vars\\":[],\\"query_vars\\":{\\"page\\":\\"\\",\\"pagename\\":\\"my-account\\"},\\"query_string\\":\\"pagename=my-account\\",\\"request\\":\\"my-account\\",\\"matched_rule\\":\\"(.?.+?)(?:\\\\\\/([0-9]+))?\\\\\\/?$\\",\\"matched_query\\":\\"pagename=my-account&page=\\",\\"did_permalink\\":true}]]},{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-includes\\\\\\/class-wp.php\\",\\"line\\":797,\\"function\\":\\"do_action_ref_array\\",\\"args\\":[\\"wp\\",[{\\"public_query_vars\\":[\\"rating_filter\\",\\"filter_stock_status\\",\\"min_price\\",\\"max_price\\",\\"m\\",\\"p\\",\\"posts\\",\\"w\\",\\"cat\\",\\"withcomments\\",\\"withoutcomments\\",\\"s\\",\\"search\\",\\"exact\\",\\"sentence\\",\\"calendar\\",\\"page\\",\\"paged\\",\\"more\\",\\"tb\\",\\"pb\\",\\"author\\",\\"order\\",\\"orderby\\",\\"year\\",\\"monthnum\\",\\"day\\",\\"hour\\",\\"minute\\",\\"second\\",\\"name\\",\\"category_name\\",\\"tag\\",\\"feed\\",\\"author_name\\",\\"pagename\\",\\"page_id\\",\\"error\\",\\"attachment\\",\\"attachment_id\\",\\"subpost\\",\\"subpost_id\\",\\"preview\\",\\"robots\\",\\"favicon\\",\\"taxonomy\\",\\"term\\",\\"cpage\\",\\"post_type\\",\\"embed\\",\\"post_format\\",\\"wc-api\\",\\"product_cat\\",\\"product_tag\\",\\"product\\",\\"rest_route\\",\\"sitemap\\",\\"sitemap-subtype\\",\\"sitemap-stylesheet\\",\\"order-pay\\",\\"order-received\\",\\"orders\\",\\"view-order\\",\\"downloads\\",\\"edit-account\\",\\"edit-address\\",\\"payment-methods\\",\\"lost-password\\",\\"customer-logout\\",\\"add-payment-method\\",\\"delete-payment-method\\",\\"set-default-payment-method\\",\\"wc-auth-version\\",\\"wc-auth-route\\",\\"order-pay\\",\\"order-received\\",\\"orders\\",\\"view-order\\",\\"downloads\\",\\"edit-account\\",\\"edit-address\\",\\"payment-methods\\",\\"lost-password\\",\\"customer-logout\\",\\"add-payment-method\\",\\"delete-payment-method\\",\\"set-default-payment-method\\",\\"wc-api-version\\",\\"wc-api-route\\",\\"wc-api\\"],\\"private_query_vars\\":[\\"offset\\",\\"posts_per_page\\",\\"posts_per_archive_page\\",\\"showposts\\",\\"nopaging\\",\\"post_type\\",\\"post_status\\",\\"category__in\\",\\"category__not_in\\",\\"category__and\\",\\"tag__in\\",\\"tag__not_in\\",\\"tag__and\\",\\"tag_slug__in\\",\\"tag_slug__and\\",\\"tag_id\\",\\"post_mime_type\\",\\"perm\\",\\"comments_per_page\\",\\"post__in\\",\\"post__not_in\\",\\"post_parent\\",\\"post_parent__in\\",\\"post_parent__not_in\\",\\"title\\",\\"fields\\"],\\"extra_query_vars\\":[],\\"query_vars\\":{\\"page\\":\\"\\",\\"pagename\\":\\"my-account\\"},\\"query_string\\":\\"pagename=my-account\\",\\"request\\":\\"my-account\\",\\"matched_rule\\":\\"(.?.+?)(?:\\\\\\/([0-9]+))?\\\\\\/?$\\",\\"matched_query\\":\\"pagename=my-account&page=\\",\\"did_permalink\\":true}]]},{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-includes\\\\\\/functions.php\\",\\"line\\":1332,\\"function\\":\\"main\\",\\"class\\":\\"WP\\",\\"type\\":\\"->\\",\\"args\\":[\\"\\"]},{\\"file\\":\\"\\\\\\/var\\\\\\/www\\\\\\/html\\\\\\/wp-blog-header.php\\",\\"line\\":16,\\"function\\":\\"wp\\",\\"args\\":[]}],\\"db_error\\":\\"\\"}}",
    "status": "failed",
    "test_result_aws_url": "",
    "test_result_aws_expiration": 0,
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
    "version": "Zip"
}';
