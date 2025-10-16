<?php

namespace QIT_CLI\Environment\Environments\Performance;

use QIT_CLI\Environment\Environments\QITEnvInfo;

class PerformanceEnvInfo extends QITEnvInfo {
	/** @var string */
	public string $environment = 'performance';

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

	/** @var array<string,array{manifest:mixed,container_path:string}> */
	public array $test_packages_metadata = [];
}
