<?php

namespace QIT_CLI\Performance\Runner;

use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Performance\Result\PerformanceTestResult;
use Symfony\Component\Console\Output\OutputInterface;

abstract class PerformanceRunner {
	/** @var OutputInterface */
	protected $output;

	public function __construct( OutputInterface $output ) {
		$this->output = $output;
	}

	/**
	 * Run performance tests against the environment.
	 *
	 * @param E2EEnvInfo $env_info The environment information
	 * @return int The exit status code of the test process
	 */
	abstract public function run_performance_test( E2EEnvInfo $env_info ): int;

	/**
	 * Get the performance test result after running tests.
	 *
	 * @return PerformanceTestResult
	 */
	abstract public function get_performance_test_result(): PerformanceTestResult;
} 