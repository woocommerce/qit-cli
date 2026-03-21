# QIT JSON Schemas

## Test Package Manifest (`qit-test.json`)

Schema URL: `https://qit.woo.com/json-schema/test-package`

### Minimal Test Package

```json
{
  "$schema": "https://qit.woo.com/json-schema/test-package",
  "package": "namespace/name",
  "test": {
    "phases": {
      "run": ["npx playwright test"]
    },
    "results": {
      "ctrf-json": "./test-results/ctrf-report.json",
      "blob-dir": "./test-results/blob"
    }
  }
}
```

### Minimal Utility Package

```json
{
  "$schema": "https://qit.woo.com/json-schema/test-package",
  "package": "namespace/name",
  "package_type": "utility",
  "test": {
    "phases": {
      "globalSetup": ["setup.sh"]
    }
  }
}
```

### Complete Structure

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `$schema` | string | No | `"https://qit.woo.com/json-schema/test-package"` |
| `package` | string | Yes | Identifier in `namespace/name` format |
| `package_type` | `"test"` or `"utility"` | No | Defaults to `"test"` |
| `test_type` | `"e2e"` or `"performance"` | No | Type of test |
| `test_dir` | string | No | Directory containing test files (default: `"./"`) |
| `description` | string | No | Human-readable description (max 500 chars) |
| `tags` | string[] | No | Tags for categorizing/searching |
| `timeout` | integer | No | Test timeout in seconds (1-3600) |

### `test` Object

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `test.phases` | object | Yes | Execution phases |
| `test.phases.globalSetup` | command[] | No | Runs once before all packages |
| `test.phases.setup` | command[] | No | Per-package setup |
| `test.phases.run` | command[] | No | Test execution (required for test packages) |
| `test.phases.teardown` | command[] | No | Per-package cleanup |
| `test.phases.globalTeardown` | command[] | No | Final cleanup |
| `test.results` | object | Conditional | Required when `run` phase exists |
| `test.results.ctrf-json` | string | Yes (e2e) | Path to CTRF JSON output |
| `test.results.blob-dir` | string | Yes (e2e) | Path to artifacts directory |

### Commands

Commands can be strings or objects:

```json
"run": [
  "npx playwright test",
  {
    "command": "npm install",
    "runs_on": "host",
    "timeout": 300,
    "continue_on_error": false
  }
]
```

### `requires` Object

| Field | Type | Description |
|-------|------|-------------|
| `requires.plugins` | string[] | Required plugins (installed and activated) |
| `requires.themes` | string[] | Required themes (installed but not activated) |
| `requires.secrets` | string[] | Required environment secrets (e.g., `STRIPE_API_KEY`) |
| `requires.network` | boolean | Whether network access is needed (default: false) |
| `requires.tunnel` | boolean | Whether a tunnel for external access is needed |
| `requires.wordpress` | string | WordPress version constraint |
| `requires.php` | string | PHP version constraint |

### Subpackages

Subpackages are focused test subsets from a larger suite:

```json
{
  "subpackages": {
    "namespace/checkout": {
      "description": "Just checkout tests",
      "test": {
        "phases": {
          "run": ["npx playwright test tests/checkout/"]
        }
      }
    }
  }
}
```

### Validation Rules

- Test packages MUST have a `run` phase AND `results`
- Utility packages MUST NOT have a `run` phase or `results`
- Exit code 0 = success, 1 = test failure, 3 = infrastructure failure
- Secrets are validated before execution (fail fast if missing)

---

## QIT Configuration (`qit.json`)

Schema URL: `https://qit.woo.com/json-schema/qit`

### Example

```json
{
  "$schema": "https://qit.woo.com/json-schema/qit",
  "sut": {
    "type": "plugin",
    "slug": "my-plugin",
    "source": {
      "type": "local",
      "path": "./",
      "build": "composer install --no-dev"
    }
  },
  "environments": {
    "default": {
      "wp": "stable",
      "woo": "stable",
      "php": "8.2"
    },
    "latest": {
      "wp": "rc",
      "woo": "rc",
      "php": "8.3"
    }
  },
  "test_types": {
    "e2e": {
      "smoke": {
        "environment": "default",
        "test_packages": ["./tests/qit"]
      },
      "full": {
        "environment": "default",
        "test_packages": [
          "./tests/qit",
          "woocommerce/checkout:latest"
        ]
      }
    },
    "security": {
      "scan": {
        "environment": "default"
      }
    }
  },
  "groups": {
    "ci": {
      "e2e": ["smoke"],
      "security": ["scan"]
    },
    "release": {
      "e2e": ["full"],
      "security": ["scan"]
    }
  }
}
```

### Top-Level Fields

| Field | Type | Description |
|-------|------|-------------|
| `sut` | object | System Under Test definition |
| `environments` | object | Named version combinations |
| `test_types` | object | Test type configurations with profiles |
| `groups` | object | Batch execution groups |
| `extends` | string | Path/URL to base config to extend |

### SUT Source Types

| Type | Required Fields | Description |
|------|----------------|-------------|
| `local` | `path` | Local directory or zip, optional `build` command |
| `url` | `url` | URL to zip file |
| `wccom` | - | WooCommerce.com marketplace (optional `version`) |
| `wporg` | - | WordPress.org (optional `version`) |

### Environment Fields

`php`, `wp`, `woo` (or their long forms `php_version`, `wordpress_version`, `woocommerce_version`), `plugins`, `themes`, `object_cache`, `envs`, `volumes`, `php_extensions`, `utilities`, `global_setup`.

### Profile Resolution

Precedence: CLI flags > inline profile values > referenced environment > defaults.
