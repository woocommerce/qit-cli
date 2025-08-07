# NPM Install Behavior in QIT CLI

## Automatic npm install

The QIT CLI **automatically runs `npm install`** when downloading test packages. This happens in the following scenarios:

1. **When using remote test packages**: Downloaded from the QIT Manager
2. **When using local test packages**: Specified via `--test-package` option

## How it works

The automatic npm install is handled by the `TestPackageDownloader` class:
- Location: `src/PreCommand/Download/TestPackageDownloader.php:218`
- Detects `package.json` in the test package directory
- Runs `npm ci` if `package-lock.json` exists (for reproducible builds)
- Falls back to `npm install` if no lock file exists

## Important implications

### DO NOT include npm install in your manifest.json

Since npm dependencies are installed automatically, you should NOT include `npm install` in your test package's `manifest.json` phases. 

**Incorrect** (redundant):
```json
{
  "test": {
    "phases": {
      "setup": ["npm install"],
      "run": ["npx playwright test"]
    }
  }
}
```

**Correct**:
```json
{
  "test": {
    "phases": {
      "run": ["npx playwright test"]
    }
  }
}
```

### Use setup phase for build steps

The `setup` phase should be used for:
- Building your test code (`npm run build`)
- Compiling TypeScript
- Other preparation steps
- NOT for installing dependencies

## Test Package Requirements

When creating test packages without a specific SUT (System Under Test):
- Tests should work with a basic WordPress/WooCommerce installation
- Don't assume specific plugins are activated unless using `--sut` option
- Use generic tests that validate the test environment itself

## Example Test Package Structure

```
my-test-package/
├── manifest.json       # No npm install in phases
├── package.json        # Dependencies defined here
├── package-lock.json   # Optional, for reproducible builds
├── playwright.config.js
└── tests/
    └── example.spec.js
```

The QIT CLI will automatically handle dependency installation before running your tests.