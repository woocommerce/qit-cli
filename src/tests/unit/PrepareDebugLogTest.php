<?php

namespace QIT_CLI_Tests;

use PHPUnit\Framework\TestCase;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Utils\PrepareDebugLog;

class PrepareDebugLogTest extends TestCase {
	/** @var string */
	protected $debug_log_file;

	/** @var string */
	protected $prepared_file;

	public function setUp(): void {
		parent::setUp();
		$this->debug_log_file = tempnam( sys_get_temp_dir(), 'qit-debug-log' );
		$this->prepared_file  = tempnam( sys_get_temp_dir(), 'qit-debug-log-prepared' );
	}

	public function tearDown(): void {
		@unlink( $this->debug_log_file );
		@unlink( $this->prepared_file );
		parent::tearDown();
	}

	protected function prepare( string $debug_log_contents, string $sut_slug ): array {
		file_put_contents( $this->debug_log_file, $debug_log_contents );

		$env_info      = new E2EEnvInfo();
		$env_info->sut = [ 'slug' => $sut_slug ];

		$prepare_debug_log = new PrepareDebugLog();
		$prepare_debug_log->set_sut_slug( $sut_slug );
		$prepare_debug_log->prepare_debug_log( $this->debug_log_file, $this->prepared_file, $env_info );

		return json_decode( (string) file_get_contents( $this->prepared_file ), true );
	}

	protected function messages( array $log_entries ): array {
		return array_column( $log_entries, 'message' );
	}

	public function test_woo_core_activation_table_db_errors_are_ignored_for_non_woo_sut() {
		$debug_log = <<<LOG
[28-Jul-2026 08:21:41 UTC] WordPress database error Table 'wordpress.wp_woocommerce_attribute_taxonomies' doesn't exist for query SELECT * FROM wp_woocommerce_attribute_taxonomies WHERE attribute_name != '' ORDER BY attribute_name ASC; made by activate_plugin, plugin_sandbox_scrape, include_once('/plugins/woocommerce/woocommerce.php'), WC, WooCommerce::instance, WooCommerce->__construct, WooCommerce->init_hooks, Automattic\WooCommerce\Internal\ProductAttributes\VisualAttributeTermAdmin->register, wc_get_attribute_taxonomies, QM_DB->query
[28-Jul-2026 08:21:42 UTC] WordPress database error Table 'wordpress.wp_wc_tax_rate_classes' doesn't exist for query SELECT * FROM wp_wc_tax_rate_classes; made by activate_plugin, plugin_sandbox_scrape, include_once('/plugins/woocommerce/woocommerce.php'), QM_DB->query
[28-Jul-2026 08:21:43 UTC] PHP Warning: Undefined variable \$foo in /var/www/html/wp-content/plugins/registration-form-fields/includes/form.php on line 10
LOG;

		$messages = $this->messages( $this->prepare( $debug_log, 'registration-form-fields' ) );

		$this->assertCount( 1, $messages );
		$this->assertStringContainsString( 'registration-form-fields', $messages[0] );
	}

	public function test_woo_core_activation_table_db_errors_are_kept_when_testing_woocommerce_core() {
		$debug_log = <<<LOG
[28-Jul-2026 08:21:41 UTC] WordPress database error Table 'wordpress.wp_woocommerce_attribute_taxonomies' doesn't exist for query SELECT * FROM wp_woocommerce_attribute_taxonomies; made by activate_plugin, plugin_sandbox_scrape, QM_DB->query
LOG;

		$messages = $this->messages( $this->prepare( $debug_log, 'woocommerce' ) );

		$this->assertCount( 1, $messages );
		$this->assertStringContainsString( 'wp_woocommerce_attribute_taxonomies', $messages[0] );
	}

	public function test_db_errors_on_other_tables_are_kept() {
		$debug_log = <<<LOG
[28-Jul-2026 08:21:41 UTC] WordPress database error Table 'wordpress.wp_my_plugin_table' doesn't exist for query SELECT * FROM wp_my_plugin_table; made by activate_plugin, plugin_sandbox_scrape, QM_DB->query
LOG;

		$messages = $this->messages( $this->prepare( $debug_log, 'registration-form-fields' ) );

		$this->assertCount( 1, $messages );
		$this->assertStringContainsString( 'wp_my_plugin_table', $messages[0] );
	}

	public function test_woo_core_activation_table_db_errors_use_prefix_agnostic_match() {
		$debug_log = <<<LOG
[28-Jul-2026 08:21:41 UTC] WordPress database error Table 'wordpress.custom_woocommerce_attribute_taxonomies' doesn't exist for query SELECT * FROM custom_woocommerce_attribute_taxonomies; made by activate_plugin, plugin_sandbox_scrape, QM_DB->query
LOG;

		$log_entries = $this->prepare( $debug_log, 'registration-form-fields' );

		$this->assertSame( [], $log_entries );
	}
}
