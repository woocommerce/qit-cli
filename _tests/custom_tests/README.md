### Running tests

1. Copy `.env.example` to `.env` and update the values
2. Run `./vendor/bin/paratest`

### Updating snapshots

1. Append "UPDATE_SNAPSHOTS" env var, eg: `UPDATE_SNAPSHOTS=true ./vendor/bin/paratest`

### How it works

1. The tests are regular PHPUnit tests
2. The tests run in parallel using [ParaTest](https://github.com/paratestphp/paratest)
3. This is because each test might spin up a QIT environment, which takes some time
4. If the tests were run sequentially, it would take a long time to run all the tests
5. For each test, we:
  5.1 Assign a uniquely generated QIT_HOME
  5.2 The first test that runs will acquire a exclusive file lock on a file
  5.3 All other tests when seeing that there is a file lock on a file will wait for that lock to be released
  5.4 The first test will authenticate to QIT and release the lock
  5.5 All other tests will then reuse the same QIT baseline authentication JSON
  5.6 Then all tests starts in parallel, with a shared, persistent cache. Ideally, all operations would be offline, without touching the internet
  5.7 This allows us to write more and more tests without increasing the time linearly
  5.8 After the test ends, each QIT_HOME that was generated is deleted