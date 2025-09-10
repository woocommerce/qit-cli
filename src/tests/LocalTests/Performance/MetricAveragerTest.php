<?php

namespace QIT_CLI\LocalTests\Performance\Tests;

use PHPUnit\Framework\TestCase;
use QIT_CLI\LocalTests\Performance\MetricAverager;
use QIT_CLI\LocalTests\Performance\Environment\PerformanceEnvInfo;
use QIT_CLI\LocalTests\Performance\Result\PerformanceTestResult;

/**
 * Test class for MetricAverager to ensure proper averaging of median metrics
 * used in performance scoring calculations.
 */
class MetricAveragerTest extends TestCase {

    /** @var MetricAverager */
    private $metric_averager;

    /** @var PerformanceEnvInfo */
    private $env_info;

    protected function setUp(): void {
        $this->metric_averager = new MetricAverager();
        $this->env_info = $this->createMockEnvInfo();
    }

    /**
     * Test averaging of Core Web Vitals median values (critical for performance scoring).
     */
    public function test_averages_core_web_vitals_medians(): void {
        $results = $this->createTestResults([
            // Iteration 1: Good performance.
            [
                'browser_web_vital_ttfb' => ['med' => 100.5, 'avg' => 105.2, 'min' => 95.0, 'max' => 120.0, 'p95' => 115.0],
                'browser_web_vital_fcp'  => ['med' => 800.0, 'avg' => 820.5, 'min' => 750.0, 'max' => 900.0, 'p95' => 880.0],
                'browser_web_vital_lcp'  => ['med' => 1500.0, 'avg' => 1520.0, 'min' => 1400.0, 'max' => 1650.0, 'p95' => 1600.0],
                'browser_web_vital_inp'  => ['med' => 50.0, 'avg' => 52.5, 'min' => 45.0, 'max' => 60.0, 'p95' => 58.0],
                'browser_web_vital_cls'  => ['med' => 0.05, 'avg' => 0.06, 'min' => 0.02, 'max' => 0.10, 'p95' => 0.09],
            ],
            // Iteration 2: Average performance.
            [
                'browser_web_vital_ttfb' => ['med' => 120.0, 'avg' => 125.0, 'min' => 110.0, 'max' => 140.0, 'p95' => 135.0],
                'browser_web_vital_fcp'  => ['med' => 900.0, 'avg' => 910.0, 'min' => 850.0, 'max' => 1000.0, 'p95' => 980.0],
                'browser_web_vital_lcp'  => ['med' => 1600.0, 'avg' => 1620.0, 'min' => 1500.0, 'max' => 1750.0, 'p95' => 1700.0],
                'browser_web_vital_inp'  => ['med' => 60.0, 'avg' => 62.0, 'min' => 55.0, 'max' => 70.0, 'p95' => 68.0],
                'browser_web_vital_cls'  => ['med' => 0.08, 'avg' => 0.09, 'min' => 0.05, 'max' => 0.15, 'p95' => 0.12],
            ],
            // Iteration 3: Slower performance.
            [
                'browser_web_vital_ttfb' => ['med' => 139.5, 'avg' => 145.0, 'min' => 130.0, 'max' => 160.0, 'p95' => 155.0],
                'browser_web_vital_fcp'  => ['med' => 1000.0, 'avg' => 1020.0, 'min' => 950.0, 'max' => 1100.0, 'p95' => 1080.0],
                'browser_web_vital_lcp'  => ['med' => 1700.0, 'avg' => 1720.0, 'min' => 1600.0, 'max' => 1850.0, 'p95' => 1800.0],
                'browser_web_vital_inp'  => ['med' => 70.0, 'avg' => 72.0, 'min' => 65.0, 'max' => 80.0, 'p95' => 78.0],
                'browser_web_vital_cls'  => ['med' => 0.12, 'avg' => 0.13, 'min' => 0.08, 'max' => 0.18, 'p95' => 0.16],
            ],
        ]);

        $averaged_result = $this->metric_averager->average_test_results($results, $this->env_info);
        $metrics = $averaged_result->get_metrics();

        // Test TTFB median average: (100.5 + 120.0 + 139.5) / 3 = 120.0.
        $this->assertEqualsWithDelta(120.0, $metrics['browser_web_vital_ttfb']['med'], 0.01, 'TTFB median should be averaged correctly');

        // Test FCP median average: (800.0 + 900.0 + 1000.0) / 3 = 900.0.
        $this->assertEqualsWithDelta(900.0, $metrics['browser_web_vital_fcp']['med'], 0.01, 'FCP median should be averaged correctly');

        // Test LCP median average: (1500.0 + 1600.0 + 1700.0) / 3 = 1600.0.
        $this->assertEqualsWithDelta(1600.0, $metrics['browser_web_vital_lcp']['med'], 0.01, 'LCP median should be averaged correctly');

        // Test INP median average: (50.0 + 60.0 + 70.0) / 3 = 60.0.
        $this->assertEqualsWithDelta(60.0, $metrics['browser_web_vital_inp']['med'], 0.01, 'INP median should be averaged correctly');

        // Test CLS median average: (0.05 + 0.08 + 0.12) / 3 = 0.083...
        $this->assertEqualsWithDelta(0.0833, $metrics['browser_web_vital_cls']['med'], 0.001, 'CLS median should be averaged correctly');
    }

    /**
     * Test that all statistical values are averaged correctly, not just medians.
     */
    public function test_averages_all_statistical_values(): void {
        $results = $this->createTestResults([
            [
                'browser_web_vital_ttfb' => ['med' => 100.0, 'avg' => 105.0, 'min' => 90.0, 'max' => 120.0, 'p95' => 115.0],
            ],
            [
                'browser_web_vital_ttfb' => ['med' => 110.0, 'avg' => 115.0, 'min' => 100.0, 'max' => 130.0, 'p95' => 125.0],
            ],
            [
                'browser_web_vital_ttfb' => ['med' => 120.0, 'avg' => 125.0, 'min' => 110.0, 'max' => 140.0, 'p95' => 135.0],
            ],
        ]);

        $averaged_result = $this->metric_averager->average_test_results($results, $this->env_info);
        $metrics = $averaged_result->get_metrics();

        $ttfb_stats = $metrics['browser_web_vital_ttfb'];

        // Test all statistical values are averaged
        $this->assertEqualsWithDelta(110.0, $ttfb_stats['med'], 0.01, 'Median should be averaged');
        $this->assertEqualsWithDelta(115.0, $ttfb_stats['avg'], 0.01, 'Average should be averaged');
        $this->assertEqualsWithDelta(100.0, $ttfb_stats['min'], 0.01, 'Min should be averaged');
        $this->assertEqualsWithDelta(130.0, $ttfb_stats['max'], 0.01, 'Max should be averaged');
        $this->assertEqualsWithDelta(125.0, $ttfb_stats['p95'], 0.01, 'P95 should be averaged');
    }

    /**
     * Test averaging of scalar metrics like exit codes.
     */
    public function test_averages_scalar_metrics(): void {
        $results = $this->createTestResults([
            ['k6_exit_code' => 0, 'test_duration' => 30.5],
            ['k6_exit_code' => 0, 'test_duration' => 32.0],
            ['k6_exit_code' => 0, 'test_duration' => 28.5],
        ]);

        $averaged_result = $this->metric_averager->average_test_results($results, $this->env_info);
        $metrics = $averaged_result->get_metrics();

        $this->assertEquals(0, $metrics['k6_exit_code'], 'Exit codes should be averaged');
        $this->assertEqualsWithDelta(30.33, $metrics['test_duration'], 0.01, 'Duration should be averaged');
    }

    /**
     * Test averaging of checks metrics (special case with passes/fails).
     */
    public function test_averages_checks_metrics(): void {
        $results = $this->createTestResults([
            ['checks' => ['passes' => 10, 'fails' => 2]],
            ['checks' => ['passes' => 12, 'fails' => 1]],
            ['checks' => ['passes' => 8, 'fails' => 3]],
        ]);

        $averaged_result = $this->metric_averager->average_test_results($results, $this->env_info);
        $metrics = $averaged_result->get_metrics();

        $checks = $metrics['checks'];
        $this->assertEqualsWithDelta(10.0, $checks['passes'], 0.01, 'Passes should be averaged');
        $this->assertEqualsWithDelta(2.0, $checks['fails'], 0.01, 'Fails should be averaged');
    }

    /**
     * Test handling of missing or null values in metrics.
     */
    public function test_handles_missing_values(): void {
        $results = $this->createTestResults([
            [
                'browser_web_vital_ttfb' => ['med' => 100.0, 'avg' => null, 'min' => 90.0],
                'missing_metric' => null,
            ],
            [
                'browser_web_vital_ttfb' => ['med' => 110.0, 'avg' => 115.0, 'min' => 100.0],
                // missing_metric not present
            ],
            [
                'browser_web_vital_ttfb' => ['med' => 120.0, 'avg' => 125.0, 'min' => null],
                'missing_metric' => 'test_value',
            ],
        ]);

        $averaged_result = $this->metric_averager->average_test_results($results, $this->env_info);
        $metrics = $averaged_result->get_metrics();

        $ttfb_stats = $metrics['browser_web_vital_ttfb'];
        $this->assertEqualsWithDelta(110.0, $ttfb_stats['med'], 0.01, 'Should average valid median values');
        $this->assertEqualsWithDelta(120.0, $ttfb_stats['avg'], 0.01, 'Should average non-null avg values');
        $this->assertEqualsWithDelta(95.0, $ttfb_stats['min'], 0.01, 'Should average non-null min values');

        // Missing metric should be handled gracefully.
        $this->assertEquals('test_value', $metrics['missing_metric'], 'Should return most common non-null value');
    }

    /**
     * Test averaging with different number of iterations (flexibility).
     */
    public function test_averages_different_iteration_counts(): void {
        // Test with 2 iterations.
        $results_2 = $this->createTestResults([
            ['browser_web_vital_ttfb' => ['med' => 100.0]],
            ['browser_web_vital_ttfb' => ['med' => 200.0]],
        ]);

        $averaged_result_2 = $this->metric_averager->average_test_results($results_2, $this->env_info);
        $metrics_2 = $averaged_result_2->get_metrics();
        $this->assertEqualsWithDelta(150.0, $metrics_2['browser_web_vital_ttfb']['med'], 0.01, '2 iterations should average correctly');

        // Test with 5 iterations.
        $results_5 = $this->createTestResults([
            ['browser_web_vital_ttfb' => ['med' => 100.0]],
            ['browser_web_vital_ttfb' => ['med' => 110.0]],
            ['browser_web_vital_ttfb' => ['med' => 120.0]],
            ['browser_web_vital_ttfb' => ['med' => 130.0]],
            ['browser_web_vital_ttfb' => ['med' => 140.0]],
        ]);

        $averaged_result_5 = $this->metric_averager->average_test_results($results_5, $this->env_info);
        $metrics_5 = $averaged_result_5->get_metrics();
        $this->assertEqualsWithDelta(120.0, $metrics_5['browser_web_vital_ttfb']['med'], 0.01, '5 iterations should average correctly');
    }

    /**
     * Test that baseline flag is preserved from source results.
     */
    public function test_preserves_baseline_flag(): void {
        $results_baseline = $this->createTestResults([
            ['browser_web_vital_ttfb' => ['med' => 100.0]],
        ]);

        // Set baseline flag on source results.
        foreach ($results_baseline as $result) {
            $result->set_baseline(true);
        }

        $averaged_result = $this->metric_averager->average_test_results($results_baseline, $this->env_info);
        $this->assertTrue($averaged_result->is_baseline(), 'Should preserve baseline flag');

        // Test with non-baseline results
        $results_extension = $this->createTestResults([
            ['browser_web_vital_ttfb' => ['med' => 100.0]],
        ]);

        $averaged_result_ext = $this->metric_averager->average_test_results($results_extension, $this->env_info);
        $this->assertFalse($averaged_result_ext->is_baseline(), 'Should preserve non-baseline flag');
    }

    /**
     * Test that averaged result.json file is created with correct structure.
     */
    public function test_creates_averaged_result_file(): void {
        $results = $this->createTestResults([
            ['browser_web_vital_ttfb' => ['med' => 100.0]],
        ]);

        $averaged_result = $this->metric_averager->average_test_results($results, $this->env_info);

        // Check that result directory exists.
        $results_dir = $averaged_result->get_results_dir();
        $this->assertTrue(is_dir($results_dir), 'Results directory should be created');

        // Check that result.json exists and has correct structure.
        $result_file = $results_dir . '/result.json';
        $this->assertTrue(file_exists($result_file), 'result.json should be created');

        $result_data = json_decode(file_get_contents($result_file), true);
        $this->assertTrue($result_data['averaged'], 'Result should be marked as averaged');
        $this->assertArrayHasKey('metrics', $result_data, 'Should contain metrics');
        $this->assertEquals('averaged-performance-test', $result_data['root_group']['name'], 'Should have correct group name');

        // Check that summary file exists.
        $summary_file = $results_dir . '/averaged-summary.txt';
        $this->assertTrue(file_exists($summary_file), 'Summary file should be created');
        
        $summary_content = file_get_contents($summary_file);
        $this->assertStringContainsString('averaged performance test results', $summary_content, 'Summary should describe averaged results');
        $this->assertStringContainsString('iter1/', $summary_content, 'Summary should mention iteration directories');
    }

    /**
     * Test error handling for empty results array.
     */
    public function test_throws_exception_for_empty_results(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot average empty test results array');

        $this->metric_averager->average_test_results([]);
    }

    /**
     * Test realistic performance scoring scenario with fluctuating metrics.
     */
    public function test_realistic_performance_scoring_scenario(): void {
        // Simulate real-world performance test results with natural fluctuation.
        $results = $this->createTestResults([
            // Good run.
            [
                'browser_web_vital_ttfb' => ['med' => 89.2, 'avg' => 95.1, 'min' => 78.5, 'max' => 112.3, 'p95' => 108.7],
                'browser_web_vital_fcp'  => ['med' => 782.1, 'avg' => 801.5, 'min' => 721.2, 'max' => 891.7, 'p95' => 856.3],
                'browser_web_vital_lcp'  => ['med' => 1456.7, 'avg' => 1489.2, 'min' => 1321.8, 'max' => 1678.9, 'p95' => 1601.4],
                'browser_web_vital_inp'  => ['med' => 48.3, 'avg' => 52.1, 'min' => 41.2, 'max' => 67.8, 'p95' => 61.5],
                'browser_web_vital_cls'  => ['med' => 0.043, 'avg' => 0.051, 'min' => 0.021, 'max' => 0.089, 'p95' => 0.076],
            ],
            // Average run with some variation.
            [
                'browser_web_vital_ttfb' => ['med' => 102.7, 'avg' => 108.3, 'min' => 89.1, 'max' => 134.5, 'p95' => 127.2],
                'browser_web_vital_fcp'  => ['med' => 845.6, 'avg' => 867.2, 'min' => 789.4, 'max' => 952.8, 'p95' => 921.7],
                'browser_web_vital_lcp'  => ['med' => 1521.3, 'avg' => 1551.8, 'min' => 1434.7, 'max' => 1712.4, 'p95' => 1672.9],
                'browser_web_vital_inp'  => ['med' => 55.7, 'avg' => 59.4, 'min' => 47.8, 'max' => 73.2, 'p95' => 68.9],
                'browser_web_vital_cls'  => ['med' => 0.061, 'avg' => 0.068, 'min' => 0.034, 'max' => 0.104, 'p95' => 0.092],
            ],
            // Slower run (network issues, etc.)
            [
                'browser_web_vital_ttfb' => ['med' => 118.4, 'avg' => 125.7, 'min' => 98.6, 'max' => 159.2, 'p95' => 146.8],
                'browser_web_vital_fcp'  => ['med' => 923.8, 'avg' => 948.1, 'min' => 856.3, 'max' => 1089.5, 'p95' => 1032.7],
                'browser_web_vital_lcp'  => ['med' => 1634.9, 'avg' => 1667.4, 'min' => 1523.6, 'max' => 1823.7, 'p95' => 1789.2],
                'browser_web_vital_inp'  => ['med' => 63.2, 'avg' => 67.8, 'min' => 54.1, 'max' => 84.7, 'p95' => 79.3],
                'browser_web_vital_cls'  => ['med' => 0.079, 'avg' => 0.085, 'min' => 0.052, 'max' => 0.127, 'p95' => 0.114],
            ],
        ]);

        $averaged_result = $this->metric_averager->average_test_results($results, $this->env_info);
        $metrics = $averaged_result->get_metrics();

        // Verify averages provide stable baseline for performance scoring.
        // TTFB: (89.2 + 102.7 + 118.4) / 3 = 103.43.
        $this->assertEqualsWithDelta(103.43, $metrics['browser_web_vital_ttfb']['med'], 0.01, 'TTFB should be stable');

        // FCP: (782.1 + 845.6 + 923.8) / 3 = 850.5.
        $this->assertEqualsWithDelta(850.5, $metrics['browser_web_vital_fcp']['med'], 0.01, 'FCP should be stable');

        // LCP: (1456.7 + 1521.3 + 1634.9) / 3 = 1537.63.
        $this->assertEqualsWithDelta(1537.63, $metrics['browser_web_vital_lcp']['med'], 0.01, 'LCP should be stable');

        // INP: (48.3 + 55.7 + 63.2) / 3 = 55.73.
        $this->assertEqualsWithDelta(55.73, $metrics['browser_web_vital_inp']['med'], 0.01, 'INP should be stable');

        // CLS: (0.043 + 0.061 + 0.079) / 3 = 0.061.
        $this->assertEqualsWithDelta(0.061, $metrics['browser_web_vital_cls']['med'], 0.001, 'CLS should be stable');
    }

    /**
     * Helper method to create mock PerformanceEnvInfo.
     */
    private function createMockEnvInfo(): PerformanceEnvInfo {
        $env_info = new PerformanceEnvInfo();
        $env_info->env_id = 'test_env_123';
        $env_info->sut_slug = 'test-plugin';
        $env_info->sut_type = 'plugin';
        return $env_info;
    }

    /**
     * Helper method to create test results with given metrics.
     */
    private function createTestResults(array $metrics_data): array {
        $results = [];
        
        foreach ($metrics_data as $i => $metrics) {
            // Create environment info for this iteration.
            $iteration_env_info = clone $this->env_info;
            $iteration_env_info->env_id = $this->env_info->env_id . "/iter" . ($i + 1);
            
            $result = new PerformanceTestResult($iteration_env_info);
            $result->set_status('completed');
            $result->set_baseline(false);
            
            // Add metrics to the result.
            foreach ($metrics as $metric_name => $metric_value) {
                $result->add_metric($metric_name, $metric_value);
            }
            
            $results[] = $result;
        }
        
        return $results;
    }
}