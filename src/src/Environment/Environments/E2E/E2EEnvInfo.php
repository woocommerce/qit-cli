<?php

namespace QIT_CLI\Environment\Environments\E2E;

use QIT_CLI\Environment\Environments\EnvInfo;

class E2EEnvInfo extends EnvInfo {
	/** @var string */
	public $environment = 'e2e';

	/** @var string The site URL, if any. */
	public $site_url;

	/** @var string */
	public $wp = '';

	/** @var bool */
	public $object_cache = false;

	/** @var string */
	public $php_version;

	/** @var string */
	public $nginx_port;

	/** @var string The slug of the extension under test. */
	public $sut_slug;

	/** @var string The type of the SUT, either "plugin" or "theme". */
	public $sut_type;

	/** @var string The entrypoint of the extension under test. */
	public $sut_entrypoint;

	/** @var string The path to the SUT on the host. */
	public $sut_path;

	/** @var int The Woo ID of the extension under test. */
	public $sut_id;

	/** @var string The domain being used. */
	public $domain;

	/** @var array<int,array{
	 *     slug:string,
	 *     test_tag:string,
	 *     type:string,
	 *     action:string,
	 *     path_in_php_container:string,
	 *     path_in_playwright_container:string,
	 *     path_in_host:string
	 * }> $tests
	 */
	public $tests = [];

	/** @var array<mixed>> */
	public $playwright_config = [];
}
