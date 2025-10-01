<?php

namespace QIT_CLI\Environment\Environments\E2E;

use QIT_CLI\Environment\Environments\EnvInfo;

class E2EEnvInfo extends EnvInfo {
	/** @var string */
	public string $environment = 'e2e';

	/** @var string */
	public string $site_url = '';

	/** @var array<string,string> */
	public array $passthrough_args = [];

	/** @var string */
	public string $wp = '';

	/** @var bool */
	public bool $object_cache = false;

	/** @var string */
	public string $php = '';

	/** @var bool */
	public bool $network_restriction = true;

	/** @var string */
	public string $nginx_port = '';

	/** @var int */
	public int $db_port = 0;

	/** @var string */
	public string $php_container = '';

	/** @var string */
	public string $db_container = '';

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

	/** @var array<string,array<string,array<mixed>>> */
	public array $test_packages = [];

	/** @var array<string,array{path:string,source:string,container_path:string,package_id?:string,manifest?:array<string,mixed>}> */
	public array $test_packages_for_setup = [];

	/** @var bool */
	public bool $skip_test_phases = false;

	/** @var string|null */
	public ?string $artifacts_dir = null;

	/** @var array<string,string> Resource constraints for all Docker containers (only applied if set) */
	public array $resource_constraints = [];
}
