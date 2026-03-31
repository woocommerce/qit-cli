"use strict";
var __defProp = Object.defineProperty;
var __getOwnPropDesc = Object.getOwnPropertyDescriptor;
var __getOwnPropNames = Object.getOwnPropertyNames;
var __hasOwnProp = Object.prototype.hasOwnProperty;
var __export = (target, all) => {
  for (var name in all)
    __defProp(target, name, { get: all[name], enumerable: true });
};
var __copyProps = (to, from, except, desc) => {
  if (from && typeof from === "object" || typeof from === "function") {
    for (let key of __getOwnPropNames(from))
      if (!__hasOwnProp.call(to, key) && key !== except)
        __defProp(to, key, { get: () => from[key], enumerable: !(desc = __getOwnPropDesc(from, key)) || desc.enumerable });
  }
  return to;
};
var __toCommonJS = (mod) => __copyProps(__defProp({}, "__esModule", { value: true }), mod);

// src/index.ts
var index_exports = {};
__export(index_exports, {
  default: () => index_default,
  qit: () => qit
});
module.exports = __toCommonJS(index_exports);

// node_modules/tsup/assets/cjs_shims.js
var getImportMetaUrl = () => typeof document === "undefined" ? new URL(`file:${__filename}`).href : document.currentScript && document.currentScript.tagName.toUpperCase() === "SCRIPT" ? document.currentScript.src : new URL("main.js", document.baseURI).href;
var importMetaUrl = /* @__PURE__ */ getImportMetaUrl();

// src/env.ts
function requireEnv(name) {
  const value = process.env[name];
  if (value === void 0 || value === "") {
    throw new Error(
      `${name} is not set.
Set it manually, or run tests via: qit run:e2e / qit env:up + source $(qit env:source)`
    );
  }
  return value;
}
function optionalEnv(name) {
  return process.env[name] ?? "";
}
function commaSplit(name) {
  const raw = optionalEnv(name);
  return raw ? raw.split(",").map((s) => s.trim()).filter(Boolean) : [];
}
var env = {
  /** True when QIT=1 is set. Safe to call in any context. */
  get isQit() {
    return process.env.QIT === "1";
  },
  get id() {
    return requireEnv("QIT_ENV_ID");
  },
  get siteUrl() {
    return requireEnv("QIT_SITE_URL");
  },
  get adminUrl() {
    return requireEnv("QIT_WP_ADMIN");
  },
  get baseUrl() {
    return requireEnv("QIT_BASE_URL");
  },
  db: {
    get host() {
      return requireEnv("QIT_DB_HOST");
    },
    get name() {
      return requireEnv("QIT_DB_NAME");
    },
    get user() {
      return requireEnv("QIT_DB_USER");
    },
    get password() {
      return requireEnv("QIT_DB_PASSWORD");
    }
  },
  wp: {
    get username() {
      return requireEnv("QIT_WP_USERNAME");
    },
    get password() {
      return requireEnv("QIT_WP_PASSWORD");
    }
  },
  containers: {
    get php() {
      return requireEnv("QIT_PHP_CONTAINER");
    },
    get db() {
      return requireEnv("QIT_DB_CONTAINER");
    }
  },
  sut: {
    get slug() {
      return requireEnv("QIT_SUT_SLUG");
    },
    get type() {
      return requireEnv("QIT_SUT_TYPE");
    },
    get entrypoint() {
      return requireEnv("QIT_SUT_ENTRYPOINT");
    }
  },
  versions: {
    get wp() {
      return requireEnv("QIT_WP_VERSION");
    },
    get woo() {
      return requireEnv("QIT_WOO_VERSION");
    },
    get php() {
      return requireEnv("QIT_PHP_VERSION");
    }
  },
  plugins: {
    get active() {
      return commaSplit("QIT_ACTIVE_PLUGINS");
    },
    get additional() {
      return commaSplit("QIT_ADDITIONAL_PLUGINS");
    }
  },
  themes: {
    get additional() {
      return commaSplit("QIT_ADDITIONAL_THEMES");
    }
  },
  get testPackages() {
    return commaSplit("QIT_TEST_PACKAGES");
  }
};

// src/docker.ts
var import_child_process = require("child_process");
function getPhpContainer() {
  const container = process.env.QIT_PHP_CONTAINER;
  if (!container) {
    throw new Error(
      "QIT_PHP_CONTAINER is not set.\nDocker commands require a running QIT environment.\nRun: qit env:up + source $(qit env:source)"
    );
  }
  return container;
}
async function wp(command) {
  const container = getPhpContainer();
  const result = (0, import_child_process.execSync)(
    `docker exec ${container} wp ${command} --allow-root`,
    { encoding: "utf-8", timeout: 6e4 }
  );
  return result.trim();
}
async function exec(command) {
  const container = getPhpContainer();
  const result = (0, import_child_process.execSync)(
    `docker exec ${container} ${command}`,
    { encoding: "utf-8", timeout: 6e4 }
  );
  return result.trim();
}

// src/actions.ts
var import_fs = require("fs");
var import_module = require("module");
var _cache = null;
var _require = (0, import_module.createRequire)(importMetaUrl);
function loadManifest() {
  if (_cache) return _cache;
  _cache = /* @__PURE__ */ new Map();
  const manifestPath = process.env.QIT_ACTIONS_MANIFEST;
  if (!manifestPath) {
    return _cache;
  }
  let manifest;
  try {
    manifest = JSON.parse((0, import_fs.readFileSync)(manifestPath, "utf-8"));
  } catch {
    return _cache;
  }
  for (const [name, entries] of Object.entries(manifest)) {
    const fns = [];
    for (const entry of entries) {
      const mod = _require(entry.path);
      const fn = mod.default ?? mod;
      fn.provider = entry.provider;
      fns.push(fn);
    }
    _cache.set(name, fns);
  }
  return _cache;
}
function actions(name) {
  return loadManifest().get(name) ?? [];
}
function hasAction(name) {
  return actions(name).length > 0;
}

// src/packages.ts
var import_fs2 = require("fs");
var import_module2 = require("module");
var _map = null;
var _packageCache = /* @__PURE__ */ new Map();
var _require2 = (0, import_module2.createRequire)(importMetaUrl);
function getMap() {
  if (_map) return _map;
  const mapPath = process.env.QIT_PACKAGE_MAP;
  if (!mapPath) {
    _map = {};
    return _map;
  }
  try {
    _map = JSON.parse((0, import_fs2.readFileSync)(mapPath, "utf-8"));
  } catch {
    _map = {};
  }
  return _map;
}
function loadPackage(name) {
  const cached = _packageCache.get(name);
  if (cached) return cached;
  const map = getMap();
  const packagePath = map[name];
  if (!packagePath) {
    const available = Object.keys(map);
    const availableStr = available.length > 0 ? `Available packages: ${available.join(", ")}` : "No packages are loaded in this environment.";
    throw new Error(
      `Package '${name}' is not available.
${availableStr}
Make sure it is included via --test-package ${name}`
    );
  }
  const mod = _require2(packagePath);
  _packageCache.set(name, mod);
  return mod;
}

// src/wait-for.ts
async function waitFor(condition, timeout = 3e4, interval = 1e3) {
  const start = Date.now();
  while (Date.now() - start < timeout) {
    if (await condition()) {
      return;
    }
    await new Promise((resolve) => setTimeout(resolve, interval));
  }
  throw new Error(`waitFor timed out after ${timeout}ms`);
}

// src/index.ts
var qit = {
  env,
  wp,
  exec,
  actions,
  hasAction,
  package: loadPackage,
  waitFor,
  version: "0.1.1"
};
var index_default = qit;
// Annotate the CommonJS export names for ESM import in node:
0 && (module.exports = {
  qit
});
//# sourceMappingURL=index.cjs.map