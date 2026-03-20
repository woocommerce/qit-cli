# Makefile for QIT Integration Tests

PARATEST = ./vendor/bin/paratest
PHPUNIT  = ./vendor/bin/phpunit
PROCS   ?= 16

# ── Fast tests (no Docker, no Manager network calls) ──────────────────────

test: test-env-up test-run-e2e test-remote
	@echo "All fast tests passed."

test-env-up:
	@echo "── env:up config resolution tests ──"
	QIT_SELF_TEST=env_up $(PARATEST) -p $(PROCS) --functional --stop-on-defect --group env-up

test-run-e2e:
	@echo "── run:e2e config resolution tests ──"
	QIT_SELF_TEST=run_e2e $(PARATEST) -p $(PROCS) --functional --stop-on-defect --group run-e2e

test-remote:
	@echo "── remote test config resolution tests ──"
	QIT_SELF_TEST=remote_test $(PARATEST) -p $(PROCS) --functional --stop-on-defect --group remote-test

# ── Slow tests (need Manager, network, packages) ──────────────────────────

test-full:
	$(PARATEST) -p $(PROCS)

# ── Docker tests (need running Docker) ────────────────────────────────────

test-docker:
	$(PHPUNIT) --group docker

# ── Snapshots ─────────────────────────────────────────────────────────────

update-snapshots:
	@echo "── Updating env:up snapshots ──"
	QIT_SELF_TEST=env_up UPDATE_SNAPSHOTS=true $(PARATEST) -p $(PROCS) --group env-up
	@echo "── Updating run:e2e snapshots ──"
	QIT_SELF_TEST=run_e2e UPDATE_SNAPSHOTS=true $(PARATEST) -p $(PROCS) --group run-e2e
	@echo "── Updating remote test snapshots ──"
	QIT_SELF_TEST=remote_test UPDATE_SNAPSHOTS=true $(PARATEST) -p $(PROCS) --group remote-test

.PHONY: test test-env-up test-run-e2e test-remote test-full test-docker update-snapshots
