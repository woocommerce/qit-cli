<?php

namespace QIT_CLI\Environment\Environments\E2E;

use QIT_CLI\Environment\Environments\EnvInfo;

class E2EEnvInfo extends EnvInfo {
	/** @var string */
	public string $environment = 'e2e';

	/** @var string */
	public string $site_url = '';

	/** @var array<string,string> */
	public array $runner_args = [];

	/** @var string */
	public string $wp = '';

	/** @var bool */
	public bool $object_cache = false;

	/** @var string */
	public string $php = '';

	/** @var string */
	public string $nginx_port = '';

	/** @var array<string,mixed> */
	public array $sut = [];

	public bool $skip_activating_plugins = false;

	public bool $skip_activating_themes = false;

	/** @var array<int,array{
	 *     slug:string,
	 *     test_tag:string,
	 *     type:string,
	 *     action:string,
	 *     path_in_php_container:string,
	 *     path_in_host:string
	 * }>
	 */
	public array $tests = [];

	/** @var array<mixed> */
	public array $playwright_config = [];

	/** @var string */
	public string $pw_test_tag = '';

	/** @var string */
	public string $woo = '';

	/** @var bool */
	public bool $is_development_build = false;

	/** @var string */
	public string $notify = '';

	/** @var array<string,array<string,array>> */
	public array $test_packages = [];
	
	/** @var string|null */
	public ?string $artifacts_dir = null;
}
