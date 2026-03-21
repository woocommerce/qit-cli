# Makefile for QIT Integration Tests

PARATEST = ./vendor/bin/paratest
PHPUNIT  = ./vendor/bin/phpunit
PROCS   ?= 16

# ── Fast tests (no Docker, no Manager network calls) ──────────────────────

test: test-env-up test-remote
	@echo "All fast tests passed."

test-env-up:
	@echo "── env:up config resolution tests ──"
	QIT_SELF_TEST=env_up $(PARATEST) -p $(PROCS) --functional --stop-on-defect --group env-up

test-remote:
	@echo "── remote test config resolution tests ──"
	QIT_SELF_TEST=remote_test $(PARATEST) -p $(PROCS) --functional --stop-on-defect --group remote-test

# ── Docker tests (need running Docker + Manager) ─────────────────────────

test-docker:
	$(PHPUNIT) --group docker

# ── Snapshots ─────────────────────────────────────────────────────────────

update-snapshots:
	@echo "── Updating env:up snapshots ──"
	QIT_SELF_TEST=env_up UPDATE_SNAPSHOTS=true $(PARATEST) -p $(PROCS) --functional --group env-up
	@echo "── Updating remote test snapshots ──"
	QIT_SELF_TEST=remote_test UPDATE_SNAPSHOTS=true $(PARATEST) -p $(PROCS) --functional --group remote-test

# ── Full suite (everything, slow) ─────────────────────────────────────────

test-full:
	$(PARATEST) -p $(PROCS)

.PHONY: test test-env-up test-remote test-docker update-snapshots test-full
