# Blueprints (experimental)

QIT can use a [WordPress Playground Blueprint](https://developer.wordpress.org/playground/blueprints/)
as the source for a local environment. No Playground is involved: the Blueprint is
translated into a normal QIT Docker environment.

```bash
qit env:up --blueprint=./blueprint.json
```

Blueprints also work when running tests, not only when starting an environment:

```bash
qit run:e2e my-plugin --blueprint=./blueprint.json --test-package=./my-tests
qit run:woo-e2e my-plugin --blueprint=./blueprint.json
qit run:woo-api my-plugin --blueprint=./blueprint.json
```

`run:performance` does not accept `--blueprint`; see below.

The Blueprint sets the site up before the test packages run, so tests see the
state it describes. Where both set the same option, the test package wins — its
own setup runs after the Blueprint's.

Mind what the Blueprint changes underneath a suite that has expectations baked
in. Pointing a Blueprint that sets `woocommerce_currency: EUR` at
`woocommerce/core-api-tests` fails two of its tests, because they assert `USD` —
the Blueprint applied correctly, and the suite disagrees with the store it
describes. `blueprint:import` / `blueprint:export` cover the conversion
on its own.

## How the translation works

A Blueprint has two halves, and each maps to a different part of QIT:

| Blueprint | QIT |
|---|---|
| `preferredVersions.php` / `.wp` | `php_version` / `wordpress_version` |
| `plugins`, `installPlugin`, `installTheme` | `plugins[]` / `themes[]` (`from: wporg`, `from: url`) |
| `features.networking: false` | `network_mode: offline` |
| every other step | commands run in the container after boot |

The declarative half becomes a qit.json environment block. The imperative half is
written out as a generated **utility package** (`package_type: utility`) whose
`globalSetup` phase runs the commands with WP-CLI inside the WordPress container,
before any other utility or test package.

Precedence is **Blueprint → qit.json → CLI flags**, so a Blueprint can be pinned
to a different PHP version with `--php` without editing it.

To see the translation without booting anything:

```bash
qit blueprint:import ./blueprint.json            # prints the qit.json fragment + planned commands
qit blueprint:import ./blueprint.json -v         # also prints the generated commands
qit blueprint:import ./blueprint.json --output=qit.json
```

## Step support

Translated: `installPlugin`, `installTheme`, `activatePlugin`, `activateTheme`,
`defineWpConfigConsts`, `setSiteOptions`, `updateUserMeta`, `setSiteLanguage`,
`runPHP`, `runSql`, `wp-cli`, `writeFile`, `mkdir`, `mv`, `cp`, `rm`, `rmdir`,
`unzip`, `importWxr`, `request`.

Reported and skipped, because they only make sense inside Playground or conflict
with how QIT builds environments: `defineSiteUrl` (QIT assigns the site URL),
`enableMultisite`, `resetData`, `importWordPressFiles`, `runWpInstallationWizard`,
`importThemeStarterContent`, `runPHPWithOptions`, `writeFiles`.

`login` is a no-op: QIT environments already have a logged-in `admin` user.

`setSiteOptions` and `updateUserMeta` are best-effort: WP-CLI exits non-zero when
WordPress reports a value as unchanged (WooCommerce does this for a few options),
and in Playground that is not a failure, so those commands never abort the rest of
the Blueprint. Every other step is fatal.

Notes and caveats:

- Paths under Playground's `/wordpress` are rewritten to `/var/www/html`, including
  inside `runPHP`, `runSql` and `wp-cli` payloads (Blueprints often `require_once
  '/wordpress/wp-load.php'`).
- `phpExtensionBundles` is ignored; QIT images bundle their own extensions. Use
  `--php_extension` if one is missing.
- WooCommerce is pinned via `woocommerce_version` rather than added as a plugin.
- `runSql` runs against MySQL. Blueprints written for Playground target SQLite and
  may not be portable.
- `bundled` resources — files shipped next to `blueprint.json` — are supported.
  Plugins and themes install from the host as ordinary local extensions; other
  files (WXR, SQL, zips) are copied into the generated package, which is mounted
  at `/qit/packages/blueprint-steps`, so an 8 MB uploads archive costs nothing
  extra. Paths are confined to the Blueprint's own directory.
- `git:directory` resources (a plugin or theme from a git ref) and `vfs:`
  resources are not supported; those entries are skipped with a warning.
- Blueprints v2 (the declarative format) is rejected; only v1 (`steps`) is handled.
- Remote Blueprint URLs are rejected on purpose. Playground sandboxes Blueprints in
  WASM; QIT executes them in a container with host volumes mounted, so download and
  review the file first.

## Where the steps run

`env:up` executes the generated utility package itself. Under `run:e2e` (and its
`run:woo-e2e` / `run:woo-api` subclasses), `env:up` is invoked with
`--skip-test-phases`, so the run command owns phase execution and runs the
Blueprint package first, ahead of every other utility and test package.

## Performance environments

Blueprints apply to e2e environments only. Performance environments run no test
package phases, so a Blueprint's steps would be mounted and never executed — and
honouring the versions and plugins while quietly dropping the steps is worse than
refusing:

```
Blueprints are not supported in performance environments — they only apply to
e2e environments.
```

`run:performance` refuses `--blueprint` before it does anything, local or remote.

Lifting this means teaching `PerformanceEnvironment` to run package phases; it has
no phase runner at all today.

## Exporting a QIT environment as a Blueprint

The reverse direction turns an environment into something shareable as a
Playground link:

```bash
qit blueprint:export                                       # prints a Blueprint for the "default" environment
qit blueprint:export --environment=legacy --output=bp.json
```

The export is lossy and says so: local plugin/theme paths, Docker volumes, PHP
extensions, env vars, the object cache and Xdebug have no Playground equivalent
and are reported as warnings.
