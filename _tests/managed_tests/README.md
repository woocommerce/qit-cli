## Quality Insights Toolkit self-tests

This directory contains self-tests for the Quality Insights Toolkit.

The self-tests uses Snapshot Testing to validate that the QIT flags the expected issues given a certain input.

### Self-tests
- Each directory is a test-type self-test, except for "vendor" and "tests" folders, eg: `activation`, `security`, `e2e`, etc.
- Each test-type self-test can contain multiple tests, eg: `activation/test-1`, `activation/test-2`, etc.
- Each test has a `env.php` file, with parameters used for the test, such as PHP, WooCommerce and WordPress versions, eg: `activation/test-1/env.php`.

### Running the self-tests

This is the runner for the self-tests. When you run `php QitSelfTests.php run`, it will:

- For each test type directory (eg: `activation`)
  - For each test (eg: `activation/test-1`)
    - Create the zip of the SUT
    - Create a PHPUnit test file
    - Dispatch a QIT test with `--wait` and `--json` parameters
    - Compare the JSON result with a previously saved snapshot

If the JSON result is different from the snapshot, the test will fail. All tests are dispatched in parallel, and the results are processed as they come in. When a result is available, it will compare the JSON result with a previously saved snapshot.

### Regenerating snapshots
To re-generate the Snapshots, run `php QitSelfTests.php update`

### Running only a single test type
To run only a single test type, run `php QitSelfTests.php run TEST_TYPE`. For example, to run only the `activation` tests, run `php QitSelfTests.php run activation`. You can also `update` instead of `run`.

### How to create a new test
- Create a new directory with the test type, eg: `performance`.
- Create a new directory for the test, eg: `performance/test-1`.
- Create a new `env.php` file with the parameters for the test, eg: `performance/test-1/env.php`. Copy the file format from existing tests.
- Create the SUT plugin directory with the plugin files to test, eg: `performance/test-1/{SLUG}`, where `{SLUG}` follows the same convention as existing tests.

That's it, now you can run `php QitSelfTests.php run performance` to run the test. Validate that the snapshot is correct, and commit the snapshot.