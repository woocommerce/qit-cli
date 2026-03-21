# Problem Statement: Profiles Should Be the Single Source of Test Config

## The User's Mental Model (What Should Be True)

A test profile is where you configure a test. Everything a test needs goes in the profile:

```json
{
  "test_types": {
    "e2e": {
      "default": {
        "php": "8.3",
        "wp": "6.4",
        "test_packages": ["./tests"]
      }
    },
    "security": {
      "default": {
        "severity": "medium"
      }
    }
  }
}
```

The user doesn't need to know whether `run:e2e` runs locally with Docker or `run:security` runs remotely on the Manager. That's an implementation detail. They put their config in the profile and it works.

**Environments are an optional power feature for reuse** — when you find yourself duplicating version combos across profiles, you extract them:

```json
{
  "environments": {
    "staging": { "php": "8.2", "wp": "6.4", "woo": "8.5" }
  },
  "test_types": {
    "e2e": {
      "default": { "environment": "staging", "test_packages": ["./tests"] }
    },
    "activation": {
      "default": { "environment": "staging" }
    }
  }
}
```

Environments are a refactoring tool, not a prerequisite.

## What Actually Happens Today

The two execution paths handle profile config completely differently:

**Remote tests** (`run:security`, `run:compatibility`, `run:phpstan`, etc.):
- `CreateRunCommands` loads the profile, merges CLI overrides, sends everything to the Manager API
- Inline values in profiles **work** (the profile IS the API request)
- But only long-form keys work (`wordpress_version`, not `wp`) — short-form silently fails

**Local tests** (`run:e2e`, `run:activation`):
- `RunE2ECommand` loads the profile but **only reads `environment`, `test_packages`, and `sut`**
- Inline environment values (`php`, `wp`, `woo`, `plugins`, etc.) are **silently ignored**
- The user must put these in an `environments` block and reference it — no inline alternative

| What the user puts in a profile | Remote test | Local test |
|---|---|---|
| `"severity": "medium"` | ✅ Sent to API | N/A (not relevant) |
| `"wordpress_version": "6.4"` | ✅ Sent to API | ❌ Silently ignored |
| `"wp": "6.4"` | ❌ Silently ignored (API wants long-form) | ❌ Silently ignored |
| `"environment": "staging"` | ❌ Ignored (not implemented) | ✅ Resolved |
| `"test_packages": [...]` | N/A | ✅ Used |

Nothing works consistently across both paths.

## Root Causes

1. **Remote tests treat profiles as flat API param bags.** `CreateRunCommands` dumps the profile directly into the HTTP request. This accidentally made inline values work for remote tests, but only with long-form keys and without environment resolution.

2. **Local tests only extract specific fields from profiles.** `QITInput.get_environment_options()` reads CLI flags, not profile values. The profile's `environment` reference works, but inline values are ignored.

3. **Short-form aliases are expanded in two places, neither of which covers profiles.** CLI argv expansion happens in `qit-cli.php`. Environment config expansion happens in `EnvironmentConfigResolver`. Profile config gets no alias expansion.

4. **`additionalProperties: true` on the test profile schema** means the schema accepts any key without error — inline values pass validation but have no effect.

## The Progressive Complexity Model (Design Goal)

Users should be able to adopt concepts incrementally:

| Complexity | What the user needs | Concept introduced |
|---|---|---|
| "Just try it" | `qit run:e2e my-plugin --php=8.3` | CLI flags only |
| "Save my defaults" | Profile with inline values | Profiles |
| "Test against multiple stacks" | Shared version combos | Environments (optional) |
| "Batch my test runs" | Run everything at once | Groups (optional) |

Each layer is additive. Nobody is forced to learn environments for a simple use case. Environments become relevant only when the user has duplication to eliminate.

## Proposed Solution

### Principle

**Profiles are the single source of test config. Environments are optional sugar for reuse.**

Precedence: CLI flags > inline profile values > referenced environment > framework defaults.

### Precedence Rule: Profile Inline Values Override Referenced Environment

When a profile has both an `"environment"` reference AND inline values, the inline values win
for the keys they specify. The environment provides the base, the profile refines it:

```json
{
  "environments": {
    "staging": { "php": "8.2", "wp": "6.4", "woo": "8.5" }
  },
  "test_types": {
    "e2e": {
      "default": {
        "environment": "staging",
        "php": "8.3",
        "test_packages": ["./tests"]
      }
    }
  }
}
```

Resolved config: `php: "8.3"` (from profile), `wp: "6.4"` (from environment), `woo: "8.5"` (from environment).

Full precedence chain:

```
CLI flags  >  inline profile values  >  referenced environment  >  framework defaults
```

This is the same mental model as CSS specificity or environment `extends` — specific beats general.
The user is saying "use staging, but override PHP." No new concept needed.

### What Needs to Change

1. **`run:e2e` and local tests must read environment params from profiles.** When a profile has `"php": "8.3"`, that value should reach `env:up` the same way a CLI `--php=8.3` would. The profile values should be merged into the environment config with lower priority than CLI flags but higher priority than the referenced environment's values.

2. **Short-form aliases must be expanded in profiles.** `get_current_test_profile()` (or wherever profile config enters the system) should normalize `wp` → `wordpress_version`, `woo` → `woocommerce_version`, `php` → `php_version`. One alias map, used everywhere.

3. **Remote tests should resolve `"environment"` references.** If a remote test profile says `"environment": "staging"`, the environment's values should be merged into the API request params. This makes environments work consistently across local and remote tests.

4. **The alias map should live in one place.** Currently duplicated in `qit-cli.php` (argv) and `EnvironmentConfigResolver` (environment configs). Extract to a shared constant or utility method.

### What This Enables

After the fix, all of these work:

```json
// Simple — inline in profile (works for both local and remote tests)
{
  "test_types": {
    "e2e":      { "default": { "php": "8.3", "test_packages": ["./tests"] } },
    "activation": { "default": { "php": "8.3" } },
    "security": { "default": { "severity": "medium" } }
  }
}

// Reuse — extract to environment when you have duplication
{
  "environments": {
    "staging": { "php": "8.2", "wp": "6.4" }
  },
  "test_types": {
    "e2e":        { "default": { "environment": "staging", "test_packages": ["./tests"] } },
    "activation": { "default": { "environment": "staging" } },
    "security":   { "default": { "environment": "staging", "severity": "medium" } }
  }
}

// Environment + inline override — profile refines the referenced environment
{
  "environments": {
    "staging": { "php": "8.2", "wp": "6.4", "woo": "8.5" }
  },
  "test_types": {
    "e2e": {
      "default": {
        "environment": "staging",
        "php": "8.3",
        "test_packages": ["./tests"]
      }
    }
  }
}
// Result: php=8.3 (profile wins), wp=6.4 (from env), woo=8.5 (from env)

// CLI always overrides everything
// qit run:e2e --php=7.4   ← uses 7.4 regardless of profile or environment
```

### Where to Implement

- `QITCommand::get_current_test_profile()` — normalize aliases before returning
- `QITInput::get_environment_options()` — also read from test profile, not just CLI
- `CreateRunCommands::doExecute()` — resolve `"environment"` reference and merge before sending to API
- Shared alias map — extract from `EnvironmentConfigResolver` to a reusable location

### Immediate Fixes (Before Full Implementation)

The failing tests can be fixed now without the full solution:
- `RunActivationPrecedenceTest`: change fixtures to use long-form keys (matches current behavior)
- `UtilityPackageValidationTest`: decode JSON before asserting (already written)

These are correct interim fixes — the tests will still be valid after the full implementation because long-form keys will always work.

## Follow-up: Tighten the Test Profile Schema

`additionalProperties: true` on the test profile schema is a separate problem that should be
addressed after inline values work. Today it causes two issues:

1. **Typos are silent.** `"phpp": "8.3"` passes validation and does nothing. The user gets no
   feedback that their config is wrong. This is true regardless of whether inline values work.

2. **After inline values work, the blast radius grows.** More keys will be meaningful in profiles,
   so more typos become possible. The schema should validate that only recognized keys are accepted.

### Recognized profile keys (after the full implementation)

**Profile-specific:** `environment`, `extends`, `test_packages`, `tweaks`, `sut`

**Environment params (inline):** `php` / `php_version`, `wp` / `wordpress_version`,
`woo` / `woocommerce_version`, `plugins`, `themes`, `volumes`, `php_extensions`,
`envs`, `object_cache`

**Test-type-specific (remote tests):** `severity`, `phpstan_level`, `optional_features`,
`additional_woo_plugins`, etc. (defined by the Manager API schema per test type)

### Migration path

1. First: make inline values work (the main fix)
2. Then: change `additionalProperties: true` to `additionalProperties: false` with all recognized
   keys listed explicitly
3. Consider: a transitional phase where unrecognized keys emit a warning instead of hard-failing,
   to avoid breaking existing configs

## Where This Lives in Code

- Schema: `src/src/PreCommand/Schemas/qit-schema.json` (testProfile definition, line 153)
- Profile loading: `src/src/Commands/QITCommand.php` → `get_current_test_profile()` (line 134)
- Local test profile reading: `src/src/QITInput.php` → `get_environment_options()` (line 212)
- Remote test execution: `src/src/Commands/CreateRunCommands.php` → `doExecute()` (line 121)
- Environment resolution: `src/src/PreCommand/Configuration/EnvironmentConfigResolver.php`
- Alias maps: `src/src/PreCommand/Configuration/EnvironmentConfigResolver.php` line 44, `src/qit-cli.php` line 20
