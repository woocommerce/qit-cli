# API Fuzz Testing (Beta)

`qit run:api-fuzz` exercises the WordPress REST API routes owned by a plugin in a disposable QIT environment. It is an authenticated beta for plugins; themes, group runs, mass tests, scheduled runs, and release automation are not supported.

## Run a Test

Test the current published build by WooCommerce.com slug or product ID:

```bash
qit run:api-fuzz my-plugin
qit run:api-fuzz 123456
```

Test a development build using the standard QIT artifact inputs:

```bash
qit run:api-fuzz my-plugin --zip=my-plugin.zip
qit run:api-fuzz my-plugin --zip=./my-plugin
qit run:api-fuzz my-plugin --zip=https://example.test/my-plugin.zip
```

Runs wait for completion by default, with a 45-minute client timeout. The fuzz campaign itself is limited to 20 minutes or 2,500 generated requests. Use `--timeout=<seconds>` to change the client wait, or enqueue and follow the run separately:

```bash
qit run:api-fuzz my-plugin --async
qit get <test-run-id>
```

Pressing Ctrl+C stops the local wait but does not cancel the remote test. If the platform cancels a run, `qit get` reports lifecycle status `cancelled` and exits with code 1.

## Results and Exit Codes

API fuzzing discovers plugin-owned routes in anonymous and administrator contexts. A candidate finding is reported only after two clean-state confirmation replays; the database and persistent object cache are reset before each replay.

QIT reports two separate states:

- `Status` is the Manager lifecycle result: `pending`, `running`, `success`, `warning`, `failed`, or `cancelled`.
- `Campaign State` is the fuzz campaign result in `test_result_json.campaign.state`: `completed`, `partial`, `not_applicable`, or `unavailable`.

Exit codes are:

- `0`: completed without confirmed findings.
- `1`: confirmed plugin-attributed finding, unavailable campaign, failed run, or cancelled run.
- `3`: partial or not-applicable campaign, or findings attributed to shared/core code.

A shared-code warning is evidence about code reached through a plugin-owned route; it is not treated as a product failure. The report retains route ownership, fault origin, minimized requests, confirmation evidence, coverage, anomalies, and suppressed findings.

Use JSON output for the complete normalized result:

```bash
qit run:api-fuzz my-plugin --json
qit run:api-fuzz my-plugin --async --json
qit get <test-run-id> --json
```

## Report URLs

Report URLs contain a secret token and should not be written to public logs. Interactive synchronous runs and `qit get` display the URL, matching the established managed-test behavior. Async and non-interactive output require an explicit request:

```bash
qit run:api-fuzz my-plugin --async --print-report-url
qit run:api-fuzz my-plugin --print-report-url # non-interactive/CI output
```

JSON output retains the Manager response shape and therefore includes the report URL when the Manager returns one. Handle JSON output as sensitive data.
