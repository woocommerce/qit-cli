<?php

namespace QIT\IntegrationTests;

use PHPUnit\Event\Event;
use PHPUnit\Event\EventFacadeIsSealedException;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\MarkedIncomplete;
use PHPUnit\Event\Test\Passed;
use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\TestRunner\ExecutionStarted;
use PHPUnit\Event\TestRunner\Finished as TestRunnerFinished;
use PHPUnit\Event\TestSuite\Started as TestSuiteStarted;
use PHPUnit\Event\TestSuite\Finished as TestSuiteFinished;
use PHPUnit\Event\UnknownSubscriberTypeException;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * Real-Time Test Reporter Extension for QIT Integration Tests
 * 
 * Provides real-time feedback during test execution with:
 * - Progress indicators
 * - Execution times
 * - Memory usage
 * - Color-coded results
 */
class RealTimeTestReporter implements Extension
{
    private array $testTimes = [];
    private array $testStatuses = [];
    private int $totalTests = 0;
    private int $executedTests = 0;
    private int $passedTests = 0;
    private int $failedTests = 0;
    private int $skippedTests = 0;
    private int $incompleteTests = 0;
    private int $erroredTests = 0;
    private float $suiteStartTime;
    private bool $useColors;
    private bool $verbose;
    private $output;
    private string $currentTestName = '';

    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $this->useColors = $configuration->colors();
        $this->verbose = $parameters->has('verbose') ? $parameters->get('verbose') === 'true' : false;
        $this->output = STDOUT;

        try {
            $facade->registerSubscriber(new TestRunnerStartedSubscriber($this));
            $facade->registerSubscriber(new TestRunnerFinishedSubscriber($this));
            $facade->registerSubscriber(new TestSuiteStartedSubscriber($this));
            $facade->registerSubscriber(new TestPreparationStartedSubscriber($this));
            $facade->registerSubscriber(new TestPassedSubscriber($this));
            $facade->registerSubscriber(new TestFailedSubscriber($this));
            $facade->registerSubscriber(new TestErroredSubscriber($this));
            $facade->registerSubscriber(new TestSkippedSubscriber($this));
            $facade->registerSubscriber(new TestIncompleteSubscriber($this));
            $facade->registerSubscriber(new TestFinishedSubscriber($this));
        } catch (EventFacadeIsSealedException | UnknownSubscriberTypeException $e) {
            // Silent failure - fall back to default PHPUnit output
        }
    }

    public function handleTestRunnerStarted(ExecutionStarted $event): void
    {
        $this->suiteStartTime = microtime(true);
        $this->totalTests = $event->testSuite()->count();
        
        fwrite(STDOUT, "\n🚀 QIT Integration Test Runner\n");
        fwrite(STDOUT, str_repeat('═', 80) . "\n");
        fwrite(STDOUT, sprintf("📊 Total tests to run: %d\n", $this->totalTests));
        fwrite(STDOUT, str_repeat('─', 80) . "\n\n");
        flush();
    }

    public function handleTestSuiteStarted(TestSuiteStarted $event): void
    {
        $suite = $event->testSuite();
        if ($suite->isForTestClass()) {
            $className = $this->getShortClassName($suite->name());
            if ($className && $className !== 'IntegrationTests') {
                $this->write($this->color('suite', "\n📁 " . $className) . "\n");
                $this->write(str_repeat('─', 60) . "\n");
            }
        }
    }

    public function handleTestPreparationStarted(PreparationStarted $event): void
    {
        $test = $event->test();
        $this->currentTestName = $this->formatTestName($test->name());
        
        $this->testTimes[$test->id()] = microtime(true);
        
        // Show test starting
        $progress = sprintf("[%3d/%3d]", $this->executedTests + 1, $this->totalTests);
        $this->write(sprintf("%s ⏳ %s ... ", 
            $this->color('progress', $progress),
            $this->currentTestName
        ));
    }

    public function handleTestPassed(Passed $event): void
    {
        $test = $event->test();
        $this->passedTests++;
        $this->testStatuses[$test->id()] = 'passed';
        $this->updateTestLine($test->id(), '✅', 'success');
    }

    public function handleTestFailed(Failed $event): void
    {
        $test = $event->test();
        $this->failedTests++;
        $this->testStatuses[$test->id()] = 'failed';
        $this->updateTestLine($test->id(), '❌', 'failure');
        
        // Always show failure reason
        $throwable = $event->throwable();
        $message = $throwable->message();
        
        // Truncate very long messages
        if (strlen($message) > 200 && !$this->verbose) {
            $message = substr($message, 0, 197) . '...';
        }
        
        $this->write($this->color('failure', '   └─ ' . $message) . "\n");
        
        // Note: PHPUnit's Throwable doesn't have file()/line() methods for location info
    }

    public function handleTestErrored(Errored $event): void
    {
        $test = $event->test();
        $this->erroredTests++;
        $this->testStatuses[$test->id()] = 'errored';
        $this->updateTestLine($test->id(), '💥', 'error');
        
        // Always show error details for errors (not just in verbose mode)
        $throwable = $event->throwable();
        $this->write($this->color('error', '   └─ Error: ' . $throwable->message()) . "\n");
        
        // Show the file and line where it happened (if available)
        // Note: PHPUnit's Throwable doesn't have file()/line() methods
    }

    public function handleTestSkipped(Skipped $event): void
    {
        $test = $event->test();
        $this->skippedTests++;
        $this->testStatuses[$test->id()] = 'skipped';
        $this->updateTestLine($test->id(), '⏭️', 'skip');
        
        if ($this->verbose && $event->message() !== '') {
            $this->write($this->color('skip', '   └─ ' . $event->message()) . "\n");
        }
    }

    public function handleTestIncomplete(MarkedIncomplete $event): void
    {
        $test = $event->test();
        $this->incompleteTests++;
        $this->testStatuses[$test->id()] = 'incomplete';
        $this->updateTestLine($test->id(), '🚧', 'warning');
    }

    public function handleTestFinished(Finished $event): void
    {
        $this->executedTests++;
    }

    public function handleTestRunnerFinished(TestRunnerFinished $event): void
    {
        $totalTime = microtime(true) - $this->suiteStartTime;
        
        $this->write("\n" . str_repeat('═', 80) . "\n");
        $this->write($this->color('bold', "📈 Test Summary\n"));
        $this->write(str_repeat('─', 80) . "\n");
        
        $summary = [];
        if ($this->passedTests > 0) {
            $summary[] = $this->color('success', sprintf("✅ Passed: %d", $this->passedTests));
        }
        if ($this->failedTests > 0) {
            $summary[] = $this->color('failure', sprintf("❌ Failed: %d", $this->failedTests));
        }
        if ($this->erroredTests > 0) {
            $summary[] = $this->color('error', sprintf("💥 Errors: %d", $this->erroredTests));
        }
        if ($this->skippedTests > 0) {
            $summary[] = $this->color('skip', sprintf("⏭️  Skipped: %d", $this->skippedTests));
        }
        if ($this->incompleteTests > 0) {
            $summary[] = $this->color('warning', sprintf("🚧 Incomplete: %d", $this->incompleteTests));
        }
        
        if (!empty($summary)) {
            $this->write(implode(' | ', $summary) . "\n");
            $this->write(str_repeat('─', 80) . "\n");
        }
        
        $this->write(sprintf("⏱️  Total time: %s\n", $this->formatTime($totalTime)));
        $this->write(sprintf("💾 Memory peak: %s\n", $this->formatMemory(memory_get_peak_usage(true))));
        
        $successRate = $this->totalTests > 0 
            ? ($this->passedTests / $this->totalTests) * 100 
            : 0;
        
        $rateColor = $successRate === 100 ? 'success' : ($successRate >= 75 ? 'warning' : 'failure');
        $this->write(sprintf("📊 Success rate: %s\n", 
            $this->color($rateColor, sprintf("%.1f%%", $successRate))
        ));
        
        $this->write(str_repeat('═', 80) . "\n\n");
    }

    private function updateTestLine(string $testId, string $icon, string $colorType): void
    {
        $elapsed = isset($this->testTimes[$testId]) 
            ? microtime(true) - $this->testTimes[$testId] 
            : 0;
        
        $timeStr = $this->formatTime($elapsed);
        $timeColor = $this->getTimeColor($elapsed);
        
        // Clear the line and show result
        $this->write("\r" . str_repeat(' ', 100) . "\r");
        $this->write(sprintf("[%3d/%3d] %s %s %s\n",
            $this->executedTests + 1,
            $this->totalTests,
            $icon,
            $this->currentTestName,
            $this->color($timeColor, sprintf("[%s]", $timeStr))
        ));
    }

    private function formatTestName(string $name): string
    {
        // Remove namespace and class name if present
        if (strpos($name, '::') !== false) {
            $parts = explode('::', $name);
            $name = end($parts);
        }
        
        // Handle data providers
        $name = preg_replace('/ with data set (.+)$/', ' [$1]', $name);
        
        // Convert test_method_name to readable format
        if (strpos($name, 'test_') === 0) {
            $name = substr($name, 5);
        }
        
        // Replace underscores with spaces
        $name = str_replace('_', ' ', $name);
        
        // Handle camelCase
        $name = preg_replace('/(?<!^)(?=[A-Z])/', ' ', $name);
        
        // Capitalize words
        $name = ucwords(strtolower(trim($name)));
        
        return $name;
    }

    private function getShortClassName(string $className): string
    {
        $parts = explode('\\', $className);
        return end($parts);
    }

    private function formatTime(float $seconds): string
    {
        if ($seconds < 0.001) {
            return sprintf('%.0fμs', $seconds * 1000000);
        } elseif ($seconds < 0.01) {
            return sprintf('%.1fms', $seconds * 1000);
        } elseif ($seconds < 1) {
            return sprintf('%.0fms', $seconds * 1000);
        } elseif ($seconds < 10) {
            return sprintf('%.2fs', $seconds);
        } elseif ($seconds < 60) {
            return sprintf('%.1fs', $seconds);
        } else {
            return sprintf('%dm %02ds', floor($seconds / 60), $seconds % 60);
        }
    }

    private function formatMemory(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        $power = min($power, count($units) - 1);
        return sprintf('%.2f %s', $bytes / pow(1024, $power), $units[$power]);
    }

    private function getTimeColor(float $seconds): string
    {
        // Adjusted for integration tests which tend to be slower
        if ($seconds < 1) return 'success';    // Fast
        if ($seconds < 5) return 'dim';        // Normal
        if ($seconds < 10) return 'warning';   // Slow
        return 'failure';                       // Very slow
    }

    private function color(string $type, string $text): string
    {
        if (!$this->useColors) {
            return $text;
        }

        $colors = [
            'success'  => "\033[32m",
            'failure'  => "\033[31m",
            'warning'  => "\033[33m",
            'skip'     => "\033[36m",
            'error'    => "\033[35m",
            'bold'     => "\033[1m",
            'dim'      => "\033[90m",
            'suite'    => "\033[34;1m",
            'progress' => "\033[90m",
            'reset'    => "\033[0m",
        ];

        $color = $colors[$type] ?? '';
        return $color . $text . $colors['reset'];
    }

    private function write(string $text): void
    {
        fwrite($this->output, $text);
    }
}

// Event Subscribers
class TestRunnerStartedSubscriber implements \PHPUnit\Event\TestRunner\ExecutionStartedSubscriber
{
    public function __construct(private RealTimeTestReporter $extension) {}
    
    public function notify(ExecutionStarted $event): void
    {
        $this->extension->handleTestRunnerStarted($event);
    }
}

class TestRunnerFinishedSubscriber implements \PHPUnit\Event\TestRunner\FinishedSubscriber
{
    public function __construct(private RealTimeTestReporter $extension) {}
    
    public function notify(TestRunnerFinished $event): void
    {
        $this->extension->handleTestRunnerFinished($event);
    }
}

class TestSuiteStartedSubscriber implements \PHPUnit\Event\TestSuite\StartedSubscriber
{
    public function __construct(private RealTimeTestReporter $extension) {}
    
    public function notify(TestSuiteStarted $event): void
    {
        $this->extension->handleTestSuiteStarted($event);
    }
}

class TestPreparationStartedSubscriber implements \PHPUnit\Event\Test\PreparationStartedSubscriber
{
    public function __construct(private RealTimeTestReporter $extension) {}
    
    public function notify(PreparationStarted $event): void
    {
        $this->extension->handleTestPreparationStarted($event);
    }
}

class TestPassedSubscriber implements \PHPUnit\Event\Test\PassedSubscriber
{
    public function __construct(private RealTimeTestReporter $extension) {}
    
    public function notify(Passed $event): void
    {
        $this->extension->handleTestPassed($event);
    }
}

class TestFailedSubscriber implements \PHPUnit\Event\Test\FailedSubscriber
{
    public function __construct(private RealTimeTestReporter $extension) {}
    
    public function notify(Failed $event): void
    {
        $this->extension->handleTestFailed($event);
    }
}

class TestErroredSubscriber implements \PHPUnit\Event\Test\ErroredSubscriber
{
    public function __construct(private RealTimeTestReporter $extension) {}
    
    public function notify(Errored $event): void
    {
        $this->extension->handleTestErrored($event);
    }
}

class TestSkippedSubscriber implements \PHPUnit\Event\Test\SkippedSubscriber
{
    public function __construct(private RealTimeTestReporter $extension) {}
    
    public function notify(Skipped $event): void
    {
        $this->extension->handleTestSkipped($event);
    }
}

class TestIncompleteSubscriber implements \PHPUnit\Event\Test\MarkedIncompleteSubscriber
{
    public function __construct(private RealTimeTestReporter $extension) {}
    
    public function notify(MarkedIncomplete $event): void
    {
        $this->extension->handleTestIncomplete($event);
    }
}

class TestFinishedSubscriber implements \PHPUnit\Event\Test\FinishedSubscriber
{
    public function __construct(private RealTimeTestReporter $extension) {}
    
    public function notify(Finished $event): void
    {
        $this->extension->handleTestFinished($event);
    }
}