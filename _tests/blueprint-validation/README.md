# Blueprint support — validation battery

Manual validation for `--blueprint` (branch `26-08/blueprint-env-support`).

Two parts: a scripted set that needs no Docker, and a Docker checklist you drive
by hand. Run the fast set first — if it fails, the Docker cases will too.

```
_tests/blueprint-validation/
├── run-fast-checks.sh              55 assertions, no Docker, ~40s
├── blueprints/                     fixtures, one per behaviour
└── packages/assert-blueprint/      test package that asserts Blueprint state from inside the container
```

## Part 1 — Fast checks (no Docker)

```bash
git checkout 26-08/blueprint-env-support
./_tests/blueprint-validation/run-fast-checks.sh
```

Expected: `Result: 55 passed, 0 failed`, exit 0.

To point it at another checkout (a worktree, say):

```bash
QIT_BIN="php /path/to/qit-cli/src/qit-cli.php" ./_tests/blueprint-validation/run-fast-checks.sh
```

Sanity check on the harness itself: run it against `trunk` and it should report
roughly `4 passed, 51 failed`. If `trunk` passes, the script is not testing what
it claims.

What it covers: version mapping and aliases · plugin/theme resolution (wporg,
url, activate:false, WooCommerce pinning) · command batching · Playground path
rewriting inside payloads · unsupported steps being reported · rejected input
(remote URLs, v2, malformed JSON, missing file) · export and round trip ·
Blueprint → qit.json → CLI precedence · environments that cannot run steps
refusing them · the option being present on all five commands.

## Part 2 — Docker checklist

Each case lists what to run and what to look for. Tear down with
`qit env:down <env_id>` when you are done with one.

Shorthand used below:

```bash
QIT="php src/qit-cli.php"
BP=_tests/blueprint-validation/blueprints
```

### D1 — Minimal boot

```bash
$QIT env:up --blueprint=$BP/store.json
```

- [ ] Summary prints `Landing: http://localhost:<port>/shop/`
- [ ] `Stack: WordPress stable, PHP 8.3`
- [ ] Plugins list shows WooCommerce and Classic Editor
- [ ] Warning for `enableMultisite`, and nothing else unexpected

Then, against the container (`docker ps` for the name):

```bash
docker exec qit_env_php_<env_id> bash -c '
  wp option get blogname --allow-root
  wp option get woocommerce_currency --allow-root
  wp post list --post_status=publish --field=post_title --allow-root
  wp plugin get classic-editor --field=status --allow-root
  grep WP_DEBUG /var/www/html/wp-config.php'
```

- [ ] `Blueprint Store`
- [ ] `EUR`
- [ ] `From the Blueprint` is present
- [ ] classic-editor is `inactive` (Blueprint said `activate: false`, QIT activates by default)
- [ ] `define( 'WP_DEBUG', true )`

### D2 — A refused option does not abort the run

`store.json` deliberately sets `woocommerce_api_enabled`, which modern
WooCommerce will not store.

- [ ] Output contains `Warning: option not applied by WordPress: woocommerce_api_enabled`
- [ ] Every option after it still reports `set:` / `unchanged:`
- [ ] The `runPHP` step still ran (the post from D1 exists)

### D3 — Trailing output is not swallowed

`store.json`'s `runPHP` ends with `echo 'seeded-no-newline'` — no trailing newline.

- [ ] `seeded-no-newline` appears in the globalSetup frame

### D4 — CLI overrides the Blueprint

```bash
$QIT env:up --blueprint=$BP/store.json --php=8.4 --wp=6.7
```

- [ ] Summary reads `WordPress 6.7, PHP 8.4`
- [ ] Blueprint plugins and steps still applied

### D5 — qit.json sits between Blueprint and CLI

Create a `qit.json` with `environments.default.php = "8.2"`, then:

```bash
$QIT env:up --blueprint=$BP/store.json          # expect PHP 8.2 (qit.json beats Blueprint)
$QIT env:up --blueprint=$BP/store.json --php=8.4 # expect PHP 8.4 (CLI beats both)
```

- [ ] Both match the expectation above

### D6 — Tests observe the Blueprint state

```bash
$QIT run:e2e woocommerce \
  --blueprint=$BP/store.json \
  --test-package=./_tests/blueprint-validation/packages/assert-blueprint
```

- [ ] `Status: ✓ PASSED`
- [ ] Log line reads `blogname=Blueprint Store currency=EUR blueprint_posts=1 classic-editor=inactive`
- [ ] `blueprint/steps` runs **before** the assert package

Negative control — the same package without a Blueprint must fail:

```bash
$QIT run:e2e woocommerce --test-package=./_tests/blueprint-validation/packages/assert-blueprint
```

- [ ] `Status: ✗ FAILED`, message names the missing state

### D7 — woo-api / woo-e2e

Needs `woocommerce/core-api-tests:latest` published to the current backend.

```bash
$QIT run:woo-api woocommerce --blueprint=$BP/store.json
```

- [ ] Environment boots with the Blueprint applied
- [ ] Woo core API tests execute
- [ ] Note which options the test package overwrites afterwards — it runs its own
      globalSetup after the Blueprint, so on overlap the package wins

Same shape for `run:woo-e2e`.

### D8 — Performance

Performance environments have no phase runner, so Blueprint **steps** cannot run
there. Both refusals are asserted by the fast checks; these are the Docker-level
confirmations.

```bash
$QIT run:performance woocommerce --blueprint=$BP/store.json
# expect: --blueprint only applies to local runs. Add --local…

$QIT run:performance woocommerce --local --blueprint=$BP/store.json
# expect: This Blueprint has N step(s), and performance environments do not run…

$QIT run:performance woocommerce --local --blueprint=$BP/no-steps.json
# expect: boots, with the Blueprint's versions/plugins applied
```

- [ ] Remote run refuses the flag, nothing enqueued
- [ ] Local run with steps refuses before booting
- [ ] Step-less Blueprint boots and its plugins/versions are present

Note: a local performance run needs a **published** performance test package —
`--test-package` there only accepts registry references, not local paths.

### D9 — Remote runs refuse the flag

```bash
$QIT run:woo-api woocommerce --extension_set=<a-real-set> --blueprint=$BP/store.json
```

- [ ] Fails with `--extension_set … cannot be combined with local-only option(s): --blueprint`
- [ ] Nothing is uploaded

### D10 — Idempotency

Run D1 twice.

- [ ] Both runs print the same `blueprints/<hash>` package path
- [ ] The package is mounted once, not twice (`Test packages prepared:` lists it once)
- [ ] Second run behaves identically to the first

### D11 — Real-world Blueprint

```bash
$QIT env:up --blueprint=/path/to/woocommerce-test-store-hello-elementor/blueprint.json
```

- [ ] `VERIFIED products=3 shop_page=5` and `ACTIVATED 15 non-woo (active_total=16)`
      appear — those are the Blueprint's own assertions, and they `exit` non-zero
      when they fail
- [ ] Theme is Hello Elementor, 3 products exist, landing URL is `/shop/`

### D12 — env:reset keeps the Blueprint state

After D1:

```bash
$QIT env:reset
```

- [ ] Site returns to the post-Blueprint state (products/options present), not to
      a bare WordPress install

## Known gaps — expected failures, not bugs

- Managed/remote tests (`run:activation`, `run:security`, `run:phpstan`, …) do not
  accept `--blueprint`; their environments are built server-side.
- Performance environments run no test package phases, so Blueprint steps are
  refused there (versions/plugins/themes still apply).
- `vfs:` resources and Blueprint bundles (zipped Blueprints) are unsupported.
- Blueprints v2 is rejected outright.
- Remote Blueprint URLs are rejected by design.
