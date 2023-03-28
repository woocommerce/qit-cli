## Quality Insights Toolkit self-tests

This directory contains self-tests for the Quality Insights Toolkit.

### Self-tests
- Each directory is a test-type self-test, except for "vendor" and "tests" folders, eg: `activation`, `security`, `e2e`, etc.
- Each test-type self-test can contain multiple tests, eg: `activation/test-1`, `activation/test-2`, etc.
- Each test has a `env.php` file, with parameters used for the test, such as PHP, WooCommerce and WordPress versions, eg: `activation/test-1/env.php`.
- The `QITSelfTests.php` file is the runner for the self-tests.

### QITSelfTests.php 

- For each test type, it will create the zip file of the plugin of each test.
- For each test type, it will dispatch a QIT test with `--wait` and `--json` parameters, which will wait for the test to finish, and get the result of the test as a JSON.
- All tests are dispatched in parallel, and the results are processed as they come in.
- When a result is available, it will compare the JSON result with a previously saved snapshot.

### Regenerating snapshots
To re-generate the Snapshots, run `php QitSelfTests.php update`

### Running only a single test type
To run only a single test type, run `php QitSelfTests.php run TEST_TYPE`. For example, to run only the `activation` tests, run `php QitSelfTests.php run activation`. You can also `update` instead of `run`.