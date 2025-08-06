/**
 * QIT Playwright Reporter
 * Outputs structured JSON Lines format for real-time parsing by QIT
 */
class QITReporter {
  constructor(options = {}) {
    this.options = options;
    this.totalTests = 0;
    this.testResults = new Map();
    this.startTime = Date.now();
  }

  onBegin(config, suite) {
    this.totalTests = suite.allTests().length;
    this.emit({
      type: 'session:start',
      timestamp: new Date().toISOString(),
      data: {
        totalTests: this.totalTests,
        workers: config.workers,
        projects: config.projects.map(p => ({ name: p.name }))
      }
    });
  }

  onTestBegin(test, result) {
    // Don't emit test:start to avoid line overwrites
    // We'll show the test info when it completes
  }

  onTestEnd(test, result) {
    const testId = this.getTestId(test);
    this.testResults.set(testId, result);

    this.emit({
      type: 'test:end',
      timestamp: new Date().toISOString(),
      data: {
        id: testId,
        title: test.title,
        status: result.status,
        duration: result.duration,
        errors: result.errors.map(e => ({
          message: e.message || String(e),
          stack: e.stack
        })),
        retry: result.retry,
        workerIndex: result.workerIndex
      }
    });

    // Don't emit progress updates - they cause line overwrite issues
  }

  onEnd(result) {
    const duration = Date.now() - this.startTime;
    const summary = this.calculateSummary();

    this.emit({
      type: 'session:end',
      timestamp: new Date().toISOString(),
      data: {
        status: result.status,
        duration: duration,
        summary: summary
      }
    });
  }

  onError(error) {
    this.emit({
      type: 'error',
      timestamp: new Date().toISOString(),
      data: {
        message: error.message || String(error),
        stack: error.stack
      }
    });
  }

  // Helper methods
  emit(event) {
    // Output as JSONL with QIT marker
    const line = `::QIT::${JSON.stringify(event)}\n`;
    process.stdout.write(line);
  }

  getTestId(test) {
    const file = test.location.file;
    const line = test.location.line;
    return `${file}:${line}:${test.title}`;
  }

  emitProgress() {
    const completed = this.testResults.size;
    const passed = Array.from(this.testResults.values()).filter(r => r.status === 'passed').length;
    const failed = Array.from(this.testResults.values()).filter(r => r.status === 'failed').length;
    const skipped = Array.from(this.testResults.values()).filter(r => r.status === 'skipped').length;

    this.emit({
      type: 'progress',
      timestamp: new Date().toISOString(),
      data: {
        completed,
        total: this.totalTests,
        passed,
        failed,
        skipped,
        percentage: Math.round((completed / this.totalTests) * 100)
      }
    });
  }

  calculateSummary() {
    const results = Array.from(this.testResults.values());
    return {
      total: this.totalTests,
      passed: results.filter(r => r.status === 'passed').length,
      failed: results.filter(r => r.status === 'failed' || r.status === 'timedOut').length,
      skipped: results.filter(r => r.status === 'skipped').length,
      flaky: results.filter(r => r.status === 'passed' && r.retry > 0).length
    };
  }
}

module.exports = QITReporter;