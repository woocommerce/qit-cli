# Makefile for QIT Custom Tests

# Run all tests (including group tests)
test:
	./vendor/bin/paratest

# Run custom tests (excluding group tests)
test-custom:
	./vendor/bin/phpunit --exclude-group group-tests

# Run group tests only
test-group:
	./vendor/bin/phpunit --group group-tests

# Update snapshots
update-snapshots:
	UPDATE_SNAPSHOTS=true ./vendor/bin/paratest

.PHONY: test test-custom test-group update-snapshots
