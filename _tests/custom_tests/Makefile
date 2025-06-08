# Makefile for QIT Custom Tests

# Run all tests (including group tests)
test:
	./vendor/bin/paratest

# Run custom tests (excluding group tests)
test-custom:
	./vendor/bin/phpunit --exclude-group group tests/ --exclude tests/RunGroupTest.php

# Run group tests only
test-group:
	./vendor/bin/phpunit tests/RunGroupTest.php

# Update snapshots
update-snapshots:
	UPDATE_SNAPSHOTS=true ./vendor/bin/paratest

.PHONY: test test-custom test-group update-snapshots