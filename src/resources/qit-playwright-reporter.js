/**
 * QIT JSONL Reporter
 * Emits all Playwright events as JSON Lines for flexible processing by QIT
 */
class QITReporter {
  constructor(options = {}) {
    this.options = options;
  }

  onBegin(config, suite) {
    this.emit('begin', {
      totalTests: suite.allTests().length,
      workers: config.workers,
      projects: config.projects.map(p => ({
        name: p.name,
        testDir: p.testDir
      })),
      version: config.version,
      rootDir: config.rootDir,
      configFile: config.configFile
    });
  }

  onTestBegin(test, result) {
    this.emit('testBegin', {
      title: test.title,
      titlePath: test.titlePath(),
      file: test.location.file,
      line: test.location.line,
      column: test.location.column,
      workerIndex: result.workerIndex,
      parallelIndex: result.parallelIndex,
      retry: result.retry,
      expectedDuration: test.expectedStatus === 'skipped' ? 0 : undefined,
      annotations: test.annotations,
      tags: test.tags
    });
  }

  onStepBegin(test, result, step) {
    this.emit('stepBegin', {
      title: step.title,
      category: step.category,
      testTitle: test.title,
      testFile: test.location.file,
      location: step.location
    });
  }

  onStepEnd(test, result, step) {
    this.emit('stepEnd', {
      title: step.title,
      category: step.category,
      duration: step.duration,
      error: step.error ? {
        message: step.error.message || String(step.error),
        stack: step.error.stack
      } : null
    });
  }

  onTestEnd(test, result) {
    this.emit('testEnd', {
      title: test.title,
      titlePath: test.titlePath(),
      file: test.location.file,
      line: test.location.line,
      status: result.status,
      duration: result.duration,
      errors: result.errors.map(e => ({
        message: e.message || String(e),
        stack: e.stack,
        value: e.value
      })),
      retry: result.retry,
      workerIndex: result.workerIndex,
      parallelIndex: result.parallelIndex,
      stdout: result.stdout,
      stderr: result.stderr,
      attachments: result.attachments.map(a => ({
        name: a.name,
        contentType: a.contentType,
        path: a.path,
        body: a.body
      }))
    });
  }

  onEnd(result) {
    this.emit('end', {
      status: result.status,
      startTime: result.startTime,
      duration: result.duration
    });
  }

  onError(error) {
    this.emit('error', {
      message: error.message || String(error),
      stack: error.stack,
      value: error.value
    });
  }

  onStdOut(chunk, test, result) {
    this.emit('stdout', {
      text: chunk.toString(),
      test: test ? {
        title: test.title,
        file: test.location.file
      } : null,
      workerIndex: result?.workerIndex
    });
  }

  onStdErr(chunk, test, result) {
    this.emit('stderr', {
      text: chunk.toString(),
      test: test ? {
        title: test.title,
        file: test.location.file
      } : null,
      workerIndex: result?.workerIndex
    });
  }

  // Simple emit function - just outputs JSONL
  emit(event, data) {
    const line = JSON.stringify({
      event,
      timestamp: Date.now(),
      data
    });
    process.stdout.write(`::QIT::${line}\n`);
  }
}

module.exports = QITReporter;