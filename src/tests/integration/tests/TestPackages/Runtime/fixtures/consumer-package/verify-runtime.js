/**
 * Integration test: verifies @woocommerce/qit-runtime works end-to-end.
 *
 * Checks:
 * 1. Runtime imports correctly
 * 2. qit.env.isQit is true
 * 3. qit.actions('testGreet') returns the provider's function
 * 4. qit.package('test/provider') returns the provider's exports
 * 5. Writes CTRF results for QIT to collect
 */
const fs = require('fs');
const path = require('path');

// Debug: show relevant env vars and module resolution
console.log('=== QIT Runtime Debug ===');
console.log('QIT=' + process.env.QIT);
console.log('QIT_ACTIONS_MANIFEST=' + (process.env.QIT_ACTIONS_MANIFEST || '<not set>'));
console.log('QIT_PACKAGE_MAP=' + (process.env.QIT_PACKAGE_MAP || '<not set>'));
console.log('CWD=' + process.cwd());
try {
  const runtimePath = require.resolve('@woocommerce/qit-runtime');
  console.log('Runtime resolved to: ' + runtimePath);
} catch (e) {
  console.log('Runtime resolve FAILED: ' + e.message);
}
console.log('=========================');

const tests = [];
let allPassed = true;

function check(name, fn) {
  try {
    fn();
    tests.push({ name, status: 'passed', duration: 0 });
  } catch (e) {
    tests.push({ name, status: 'failed', duration: 0, message: e.message });
    allPassed = false;
    console.error(`FAIL: ${name} — ${e.message}`);
  }
}

// 1. Import the runtime
let qit;
check('runtime imports correctly', () => {
  qit = require('@woocommerce/qit-runtime');
  // Handle default export
  if (qit.default) qit = qit.default;
  if (!qit || typeof qit !== 'object') throw new Error('qit is not an object');
});

// 2. qit.env.isQit
check('qit.env.isQit is true', () => {
  if (qit.env.isQit !== true) throw new Error(`expected true, got ${qit.env.isQit}`);
});

// 3. qit.actions('testGreet')
check('qit.actions returns provider function', () => {
  const fns = qit.actions('testGreet');
  if (!Array.isArray(fns)) throw new Error(`expected array, got ${typeof fns}`);
  if (fns.length !== 1) throw new Error(`expected 1 action, got ${fns.length}`);
  if (typeof fns[0] !== 'function') throw new Error(`expected function, got ${typeof fns[0]}`);
});

check('action function is callable with correct result', () => {
  const [greet] = qit.actions('testGreet');
  const result = greet('world');
  if (result !== 'hello world') throw new Error(`expected "hello world", got "${result}"`);
});

check('action has .provider metadata', () => {
  const [greet] = qit.actions('testGreet');
  if (greet.provider !== 'test/provider') throw new Error(`expected "test/provider", got "${greet.provider}"`);
});

// 4. qit.actions for unknown returns empty array
check('qit.actions returns [] for unknown', () => {
  const fns = qit.actions('nonexistent');
  if (!Array.isArray(fns) || fns.length !== 0) throw new Error(`expected [], got ${JSON.stringify(fns)}`);
});

// 5. qit.hasAction
check('qit.hasAction returns true for registered action', () => {
  if (qit.hasAction('testGreet') !== true) throw new Error('expected true');
});

check('qit.hasAction returns false for unknown', () => {
  if (qit.hasAction('nonexistent') !== false) throw new Error('expected false');
});

// 6. qit.package
check('qit.package returns provider exports', () => {
  const pkg = qit.package('test/provider');
  if (typeof pkg.sayHi !== 'function') throw new Error(`expected function, got ${typeof pkg.sayHi}`);
  if (pkg.sayHi() !== 'hi') throw new Error(`expected "hi", got "${pkg.sayHi()}"`);
});

// 7. qit.wp() — async checks run before writing CTRF
async function asyncCheck(name, fn) {
  try {
    await fn();
    tests.push({ name, status: 'passed', duration: 0 });
  } catch (e) {
    tests.push({ name, status: 'failed', duration: 0, message: e.message });
    allPassed = false;
    console.error(`FAIL: ${name} — ${e.message}`);
  }
}

async function runAsyncChecks() {
  await asyncCheck('qit.wp returns WP-CLI output', async () => {
    const result = await qit.wp('option get blogname');
    if (typeof result !== 'string') throw new Error(`expected string, got ${typeof result}`);
    if (result.length === 0) throw new Error('expected non-empty string');
  });

  await asyncCheck('qit.exec runs command in container', async () => {
    const result = await qit.exec('php --version');
    if (!result.includes('PHP')) throw new Error(`expected PHP version string, got: ${result}`);
  });
}

runAsyncChecks().then(() => {
// Write CTRF results
const resultsDir = path.join(__dirname, 'results');
fs.mkdirSync(resultsDir, { recursive: true });

const now = Date.now();
const ctrf = {
  results: {
    tool: { name: 'qit-runtime-verify' },
    summary: {
      tests: tests.length,
      passed: tests.filter(t => t.status === 'passed').length,
      failed: tests.filter(t => t.status === 'failed').length,
      skipped: 0,
      pending: 0,
      other: 0,
      start: now,
      stop: now,
    },
    tests,
  },
};

fs.writeFileSync(path.join(resultsDir, 'ctrf.json'), JSON.stringify(ctrf, null, 2));

console.log(`\nQIT Runtime verification: ${tests.filter(t => t.status === 'passed').length}/${tests.length} passed`);
process.exit(allPassed ? 0 : 1);
});
