<?php

namespace QIT_CLI\Environment\Environments\E2E;

use QIT_CLI\Environment\Environments\EnvInfo;

class E2EEnvInfo extends EnvInfo {
	/** @var string */
	public string $environment = 'e2e';

	/** @var string The site URL, if any. */
	public string $site_url;

	/** @var array<string,string> */
	public array $runner_args = [];

	/** @var string */
	public string $wp_version = '';

	/** @var bool */
	public bool $object_cache = false;

	/** @var string */
	public string $php_version;

	/** @var string */
	public string $nginx_port;

	/** @var string The slug of the extension under test. */
	public string $sut_slug;

	/** @var string The type of the SUT, either "plugin" or "theme". */
	public string $sut_type;

	/** @var string The entrypoint of the extension under test. */
	public string $sut_entrypoint;

	/** @var string The path to the SUT on the host. */
	public string $sut_path;

	/** @var int The Woo ID of the extension under test. */
	public int $sut_id;

	/** @var string The domain being used. */
	public string $domain;

	public bool $skip_activating_plugins = false;

	public bool $skip_activating_themes = false;

	/** @var array<int,array{
	 *     slug:string,
	 *     test_tag:string,
	 *     type:string,
	 *     action:string,
	 *     path_in_php_container:string,
	 *     path_in_host:string
	 * }> $tests
	 */
	public array $tests = [];

	/** @var array<mixed>> */
	public array $playwright_config = [];

	/** @var string The playwright test tag to be executed */
	public string $pw_test_tag = '';

	/** @var string The WooCommerce version, if any. */
	public string $woo_version = 'none';

	/** @var bool Whether this test run is for a development build. */
	public bool $is_development_build;

	/** @var string Whether to notify the developer about the result of this test run. */
	public string $notify;

	/** @var array<string,array<string,array>> The test packages configuration. */
	public array $test_packages = [];
}
