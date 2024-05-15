### How to run tests?

Run `make tests` to run all tests.

Or, run any of the commands below to run tests:

```bash
make phpcs
make phpstan
make phpunit
make phan
```

### How to run tests with Xdebug:

To run tests with Xdebug, you can use the following commands:

```bash
make phpunit DEBUG=1
```

### How to update snapshots:

To update snapshots, run the following command:

```bash
make phpunit ARGS='-d --update-snapshots'
```

You can also combine both:

```bash
make phpunit ARGS='-d --update-snapshots' DEBUG=1
```