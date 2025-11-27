<?php

namespace QIT_CLI\Environment\Environments\E2E;

use QIT_CLI\Environment\Environments\QITEnvInfo;

class E2EEnvInfo extends QITEnvInfo {
	/** @var string */
	public string $environment = 'e2e';

	/** @var array<string,string> */
	public array $passthrough_args = [];

	/** @var int */
	public int $db_port = 0;

	public bool $skip_activating_plugins = false;

	public bool $skip_activating_themes = false;

	/** @var bool Whether to run only globalSetup and setup phases without test execution */
	public bool $global_setup_only = false;

	/** @var \QIT_CLI\Environment\EnvironmentManager|null */
	public ?\QIT_CLI\Environment\EnvironmentManager $environment_manager = null;

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

	/** @var bool */
	public bool $is_development_build = false;

	/** @var string */
	public string $notify = '';

	/** @var string|null */
	public ?string $artifacts_dir = null;

	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		$vars = get_object_vars( $this );
		// Exclude environment_manager from serialization (runtime-only object)
		unset( $vars['environment_manager'] );
		return $vars;
	}
}
