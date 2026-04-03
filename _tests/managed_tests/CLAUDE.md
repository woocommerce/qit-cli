# Self-Tests & Releases

## Self-Tests (Snapshot Updates)

Self-tests run real tests against QIT production and compare results against stored snapshots. When CLI output format changes (e.g., `--json` now decodes embedded fields), snapshots need updating.

### CI runtimes per test type

| Test Type | CI Runtime | Batch |
|-----------|-----------|-------|
| Validation | ~1 min | fast |
| PHPStan | ~1.5 min | fast |
| Security | ~1.5 min | fast |
| PHPCompatibility | ~1.5 min | fast |
| Malware | ~2 min | fast |
| Activation | ~7 min | medium (must run alone — uses custom tests) |
| Woo API | ~10 min | heavy |
| Woo E2E | ~22 min | heavy |

### Updating snapshots

From `/storage/qit/qit-cli/_tests/managed_tests`:

```bash
# 1. Fast batch first (~2 min locally, runs in parallel on CI)
php QITSelfTests.php update phpstan,malware,security,phpcompatibility,validation

# 2. Then medium/heavy individually as needed
php QITSelfTests.php update activation
php QITSelfTests.php update woo-api
php QITSelfTests.php update woo-e2e
```

**Do NOT run all test types at once** — activation uses custom tests and must run alone. Batch the fast ones, then run the rest individually.

### When to update which snapshots

Not every change requires updating all test types. Match the scope:

- **CLI output format change** (e.g., `--json` field decoding): all test types affected — update the fast batch first, verify, then heavy ones only if the parser fix doesn't cover it
- **Manager-side result format change**: only the test types whose results changed
- **New test scenario added**: only that test type
- **Environment/infrastructure change**: likely all, start with fast batch to confirm

### Verifying (without updating)

```bash
php QITSelfTests.php run phpstan,malware,security,phpcompatibility,validation
```

### Key files

- `QITSelfTests.php` — entry point
- `src/test-result-parser.php` — normalizes raw JSON before snapshot comparison (extracts stringified JSON fields, removes volatile data)
- `tests/__snapshots__/` — snapshot files (PHP files returning strings)
- `README.md` — full documentation of the self-test system

### Common scenario: JSON output format changed

If `--json` output changes shape (fields decoded, added, removed), the test-result-parser may need updating to handle the new format, AND snapshots need regenerating. The parser is the source of truth for how raw API responses are normalized before comparison.

## Releases

### Process

1. **Update self-test snapshots** (see above) and commit
2. **Build the phar**:
   ```bash
   make build VERSION=1.1.8
   ```
   This compiles `src/` into the `qit` phar binary, replacing `@QIT_CLI_VERSION@` with the version string.

3. **Write changelog** at `docs/changelogs/1.1.8.md` — follow the format of previous changelogs (see `docs/changelogs/1.1.7.md` for reference).

4. **Commit** the phar binary + changelog:
   ```bash
   git add qit docs/changelogs/1.1.8.md
   git commit -m "1.1.8 release"
   ```

5. **Tag and push**:
   ```bash
   git tag 1.1.8
   git push origin trunk --tags
   ```

6. GitHub Actions `release.yml` automatically creates a GitHub Release with the phar attached, using the changelog as the release body.

### Version flow

- `@QIT_CLI_VERSION@` placeholder in `src/src/bootstrap.php` line 78
- `_build/box.json.dist` maps `QIT_CLI_VERSION` → `QIT_VERSION_REPLACE`
- `make build VERSION=X.Y.Z` substitutes `QIT_VERSION_REPLACE` → the actual version in `_build/box.json`
- Box compiler bakes the version into the phar
- `release.yml` validates the binary doesn't contain `qit_dev_build` before creating the release
