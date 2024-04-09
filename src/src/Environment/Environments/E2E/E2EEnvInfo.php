<?php

namespace QIT_CLI\Environment\Environments\E2E;

use QIT_CLI\Environment\Environments\EnvInfo;

class E2EEnvInfo extends EnvInfo {
	/** @var string */
	public $environment = 'e2e';

	/** @var string The site URL, if any. */
	public $site_url;

	/** @var string */
	public $wordpress_version = '';

	/** @var bool */
	public $object_cache = false;

	/** @var string */
	public $php_version;

	/** @var string */
	public $nginx_port;

	/** @var string The domain being used. */
	public $domain;

	/** @var string */
	public $woocommerce_version;

	/** @var array<int,array{
	 *     slug:string,
	 *     test_tag:string,
	 *     type:string,
	 *     action:string,
	 *     path_in_container:string,
	 *     path_in_host:string
	 * }> $tests
	 */
	public $tests = [];
}
