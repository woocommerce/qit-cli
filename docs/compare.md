# Comparing two test runs

`qit compare <run-a> <run-b>` tells you what changed between two test runs that already
happened, without re-running anything.

```
qit compare 12345 12346
qit compare 12345 12346 --format=json
```

The first ID is the baseline (**A**), the second is the run being judged against it (**B**).

## What it reports

* **Introduced failures** — tests that passed in A and fail in B
* **Resolved failures** — tests that failed in A and pass in B
* **Still failing** — tests that fail in both
* **Other status changes** — any other transition, e.g. `passed -> skipped`
* **Added / removed tests** — tests present in only one of the two runs, with their status
* **Summary counts** — the CTRF counters side by side, with a delta
* **Annotation changes** — added and removed `extra.annotations` entries, per test

Tests are matched by suite and name. CTRF does not guarantee unique test names, so runs
that repeat a name keep each occurrence distinct (`my test`, `my test #2`).

## Which test types can be compared

**Both runs must be of the same test type.** Two test types are two different
populations of tests, so the diff would degenerate to "every test was removed, every
other test was added" — and, because a new failing test counts as a failure introduced
by run B, it would report a regression that says nothing about either run. Comparing
across test types is refused outright:

```
$ qit compare 12345 12346
Cannot compare a "activation" run against a "woo-api" run. Only two runs of the same
test type share a population of tests. Test run 12345 is "activation" and test run
12346 is "woo-api".
```

One wrinkle worth knowing: run records for the Woo E2E suite carry two different
`test_type` spellings. The CLI has changed which value it submits for `run:woo-e2e` more
than once, and today the two paths of that command still disagree — `--extension-set`
submits `woo-e2e`, while without one it falls through to the generic E2E command and
submits `e2e`. `sync` advertises both as test types.

Two runs of that suite recorded under the different spellings are therefore refused by
this check. That is deliberate rather than an oversight: the message names both types, so
the case reads as what it is instead of producing a silent wrong answer.

Both runs must also report results in CTRF, which covers:

```
activation       CTRF
compatibility    CTRF
woo-api          CTRF
woo-e2e          CTRF
```

`qit get <id> --json-results` returns that CTRF verbatim, so anything you can see there is
what the comparison works from. Comparing a run of a test type that does not emit CTRF
fails with an explanation rather than a partial diff.

## What survives the round trip

The comparison only sees what makes it back from the machine that ran the tests:

**Does survive:** test names, statuses, durations, `extra.annotations`, `qitPackageMetadata`,
and the run's own environment metadata (WordPress / WooCommerce / PHP version).

**Does not survive:** attachment contents. CTRF `attachments[]` carries local filesystem
paths from the runner:

```json
{"name": "screenshot", "contentType": "image/png",
 "path": "/Users/.../results/artifacts/.../test-failed-1.png"}
```

On a CI runner those paths are gone once the job ends.

**Anything you want compared must be emitted as an annotation, not as an attachment.** A
test package that emits structured, already-normalised findings as `extra.annotations` gets
compared for free, without `qit compare` knowing what the data means.

## The comparability guard

A comparison is only meaningful when the two runs differ in the one variable you are
testing. `qit compare` checks the WordPress / WooCommerce / PHP versions, the extension
and its version, and the test packages recorded in `qitPackageMetadata`.

* No differences, or exactly one — the runs are comparable, and the differing dimension is
  shown as `differs`.
* Two or more differences — the comparison is still printed, but flagged, because a change
  in results cannot be attributed to any single one of them.

A differing test type is not part of this guard: it is refused before a comparison is
built at all, so every difference the guard reports is one the runs can actually be
judged on.

In `--format=json` this is the `guard` object: `comparable`, `differences` and `warnings`.

## Exit codes

| Code | Meaning |
|------|---------|
| `0`  | Run B introduced no failures |
| `1`  | Run B introduced failures (a regression on a shared test, or a new test that fails) |
| `2`  | The runs could not be fetched, are of different test types, or could not be compared |

This makes the command usable as a CI gate:

```
qit compare "$BASELINE_RUN" "$CANDIDATE_RUN" || echo "New failures against the candidate"
```

## Options

| Option | Description |
|--------|-------------|
| `--format` | `human` (default) or `json` |
| `--limit`  | Maximum entries printed per section in human output. `0` shows all. Defaults to 25. |

## Relationship to `compatibility-regression`

`compatibility-regression` (the PHPStan sweep) runs both passes inside one job and emits a
single result that already contains the diff. That works for a pipeline you control, but it
cannot serve two runs someone already made separately, on different days. `qit compare` is
for that case. The two are complementary.
