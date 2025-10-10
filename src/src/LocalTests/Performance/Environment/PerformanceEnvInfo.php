<?php

namespace QIT_CLI\LocalTests\Performance\Environment;

use QIT_CLI\Environment\Environments\EnvInfo;

class PerformanceEnvInfo extends EnvInfo {
	/** @var string */
	public string $environment = 'performance';

	/** @var string */
	public $wp = '';

	/** @var string */
	public $woo = '';

	/** @var bool */
	public $object_cache = false;

	/** @var string */
	public $php = '';

	/** @var bool */
	public bool $network_restriction = true;

	/** @var string */
	public $nginx_port;

	/** @var string PHP container name */
	public string $php_container = '';

	/** @var string Database container name */
	public string $db_container = '';

	/** @var bool */
	public bool $skip_test_phases = false;

	/** @var array<string,string> */
	public array $additional_vars = [];

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

	/** @var bool Whether to run baseline tests before main tests */
	public $run_baseline = true;

	/** @var array<string,array<string,array<mixed>>> */
	public array $test_packages = [];

	/** @var array<string,array{path:string,source:string,container_path:string,package_id?:string,manifest?:array<string,mixed>}> */
	public array $test_packages_for_setup = [];

	/** @var array<string,array{manifest:mixed,container_path:string}> */
	public array $test_packages_metadata = [];
}
