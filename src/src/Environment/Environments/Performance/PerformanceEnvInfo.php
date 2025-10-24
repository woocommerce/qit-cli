<?php

namespace QIT_CLI\Environment\Environments\Performance;

use QIT_CLI\Environment\Environments\QITEnvInfo;

class PerformanceEnvInfo extends QITEnvInfo {
	/** @var string */
	public string $environment = 'performance';

	/** @var bool Whether to run baseline tests before main tests */
	public $run_baseline = true;
}
