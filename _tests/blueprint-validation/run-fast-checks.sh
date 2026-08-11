#!/bin/bash
# Blueprint support — checks that need no Docker.
#
# Usage:
#   ./run-fast-checks.sh                 # uses ../../src/qit-cli.php
#   QIT_BIN="php /path/to/qit-cli.php" ./run-fast-checks.sh
#
# Requires a checkout that has Blueprint support (branch 26-08/blueprint-env-support).

set -uo pipefail

HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BP="$HERE/blueprints"
QIT_BIN="${QIT_BIN:-php $HERE/../../src/qit-cli.php}"
WORK="$(mktemp -d)"
trap 'rm -rf "$WORK"' EXIT

PASS=0
FAIL=0

# check <name> <expected-substring> <output>
check() {
	local name="$1" expected="$2" actual="$3"

	if grep -qF -- "$expected" <<<"$actual"; then
		printf '  \033[32m✓\033[0m %s\n' "$name"
		PASS=$((PASS + 1))
	else
		printf '  \033[31m✗\033[0m %s\n      expected to find: %s\n' "$name" "$expected"
		printf '      got: %s\n' "$(head -c 600 <<<"$actual" | tr '\n' '|')"
		FAIL=$((FAIL + 1))
	fi
}

# check_absent <name> <forbidden-substring> <output>
check_absent() {
	local name="$1" forbidden="$2" actual="$3"

	if grep -qF -- "$forbidden" <<<"$actual"; then
		printf '  \033[31m✗\033[0m %s\n      should NOT contain: %s\n' "$name" "$forbidden"
		FAIL=$((FAIL + 1))
	else
		printf '  \033[32m✓\033[0m %s\n' "$name"
		PASS=$((PASS + 1))
	fi
}

# Decodes the base64 payload of the first `printf %s '...' | base64 -d` command.
decode_payload() {
	php -r '$s = stream_get_contents( STDIN ); if ( preg_match( "#printf %s .([A-Za-z0-9+/=]+).#", $s, $m ) ) { echo base64_decode( $m[1] ); }'
}

section() { printf '\n\033[1m%s\033[0m\n' "$1"; }

section '1. Declarative half — versions'
out=$($QIT_BIN blueprint:import "$BP/versions.json" 2>&1)
check 'php version maps to php_version' '"php_version": "8.1"' "$out"
check 'wp version maps to wordpress_version' '"wordpress_version": "6.5"' "$out"

out=$($QIT_BIN blueprint:import "$BP/aliases.json" 2>&1)
check 'php "latest" resolves to a concrete version' '"php_version": "8.3"' "$out"
check 'wp "nightly" is kept' '"wordpress_version": "nightly"' "$out"
check 'alias resolution is reported' 'resolved to PHP 8.3' "$out"

section '2. Declarative half — plugins and themes'
out=$($QIT_BIN blueprint:import "$BP/extensions.json" -v 2>&1)
check 'wporg plugin becomes an entry' '"slug": "akismet"' "$out"
check 'url plugin keeps the zip URL' '"url": "https://example.com/my-plugin.zip"' "$out"
check 'theme becomes an entry' '"slug": "storefront"' "$out"
check 'WooCommerce is pinned, not listed' '"woocommerce_version": "9.5.0"' "$out"
check_absent 'WooCommerce is not also a plugin entry' '"slug": "woocommerce"' "$out"
check 'activate:false plugins are deactivated' 'Deactivate 2 plugins' "$out"
check 'deactivations are one command' "wp plugin deactivate 'my-plugin' 'classic-editor'" "$out"
check 'theme activation step is emitted' "wp theme activate 'storefront'" "$out"

section '3. Imperative half — options and constants'
out=$($QIT_BIN blueprint:import "$BP/options-constants.json" -v 2>&1)
check 'all site options run in one command' 'Set 3 site option(s)' "$out"
check_absent 'no per-option wp-cli calls' 'wp option update' "$out"
check 'constants use wp config set' "wp config set 'WP_DEBUG' 'true' --type=constant --raw" "$out"
check 'string constants are not raw' "wp config set 'WP_MEMORY_LIMIT' '512M' --type=constant" "$out"

payload=$(decode_payload <<<"$out")
check 'options payload uses update_option' 'update_option(' "$payload"
check 'options payload reports refusals' 'option not applied by WordPress' "$payload"

section '4. Imperative half — Playground paths are rewritten'
out=$($QIT_BIN blueprint:import "$BP/runphp-paths.json" -v 2>&1)
check 'writeFile path is rewritten' '/var/www/html/wp-content/mu-plugins/x.php' "$out"
check 'wp-cli argument path is rewritten' 'wp eval-file /var/www/html/wp-content/x.php' "$out"

payload=$(decode_payload <<<"$out")
check 'runPHP payload is rewritten' "require_once '/var/www/html/wp-load.php'" "$payload"
check_absent 'no Playground root survives in runPHP' '/wordpress/' "$payload"

section '5. Unsupported input is reported, not dropped'
out=$($QIT_BIN blueprint:import "$BP/unsupported.json" 2>&1)
check 'enableMultisite is reported' 'Skipped unsupported step "enableMultisite"' "$out"
check 'defineSiteUrl is reported' 'Skipped unsupported step "defineSiteUrl"' "$out"
check 'resetData is reported' 'Skipped unsupported step "resetData"' "$out"
check 'unknown steps are reported' 'Skipped unsupported step "notARealStep"' "$out"
check 'login is explained' 'login step ignored' "$out"
check 'phpExtensionBundles is reported' 'phpExtensionBundles (kitchen-sink) ignored' "$out"

section '6. Rejected input'
out=$($QIT_BIN blueprint:import 'https://playground.wordpress.net/blueprint.json' 2>&1)
check 'remote Blueprints are refused' 'Remote Blueprints are not supported' "$out"

out=$($QIT_BIN blueprint:import "$BP/v2.json" 2>&1)
check 'Blueprint v2 is refused' 'Only version 1' "$out"

out=$($QIT_BIN blueprint:import "$BP/invalid.json" 2>&1)
check 'malformed JSON is refused' 'Invalid JSON' "$out"

out=$($QIT_BIN blueprint:import "$BP/does-not-exist.json" 2>&1)
check 'missing file is refused' 'not found' "$out"

section '7. Blueprints without steps'
out=$($QIT_BIN blueprint:import "$BP/no-steps.json" 2>&1)
check 'plugins still land in the env block' '"slug": "akismet"' "$out"
check_absent 'no setup commands are announced' 'setup command(s)' "$out"

section '8. Export (QIT → Blueprint)'
cat > "$WORK/qit.json" <<'JSON'
{
	"environments": {
		"default": {
			"php": "8.3",
			"wp": "6.7",
			"woo": "9.4.0",
			"plugins": [ "akismet", { "slug": "my-plugin", "from": "local", "path": "./my-plugin" } ],
			"themes": [ "storefront", "twentytwentyfour" ],
			"php_extensions": [ "imagick" ]
		}
	}
}
JSON
out=$(cd "$WORK" && $QIT_BIN blueprint:export 2>&1)
check 'short keys are understood' '"php": "8.3"' "$out"
check 'wp version maps to a Playground alias' '"wp": "6.7"' "$out"
check 'pinned Woo becomes a plugin step' '"slug": "woocommerce"' "$out"
check 'wporg plugins become resources' '"resource": "wordpress.org/plugins"' "$out"
check 'local plugins are reported as lost' 'cannot be expressed in a Blueprint' "$out"
check 'docker-only settings are reported' 'Dropped custom PHP extensions' "$out"

# Round trip: export, then import what was exported.
(cd "$WORK" && $QIT_BIN blueprint:export --output="$WORK/roundtrip.json" >/dev/null 2>&1)
out=$($QIT_BIN blueprint:import "$WORK/roundtrip.json" 2>&1)
check 'round trip keeps the PHP version' '"php_version": "8.3"' "$out"
check 'round trip keeps the plugins' '"slug": "akismet"' "$out"

section '9. Precedence (env:up, no Docker)'
out=$(cd "$WORK" && rm -f qit.json && QIT_SELF_TEST=env_up $QIT_BIN env:up --blueprint="$BP/versions.json" 2>&1)
check 'blueprint sets the PHP version' '"php_version":"8.1"' "$out"

out=$(cd "$WORK" && QIT_SELF_TEST=env_up $QIT_BIN env:up --blueprint="$BP/versions.json" --php=8.4 2>&1)
check 'CLI beats the blueprint' '"php_version":"8.4"' "$out"

out=$(cd "$WORK" && QIT_SELF_TEST=env_up $QIT_BIN env:up --blueprint="$BP/store.json" 2>&1)
check 'steps are mounted as a utility package' '/qit/packages/blueprint-steps' "$out"
check 'blueprint plugins reach the environment' '"slug":"classic-editor"' "$out"

section '10. Bundled files shipped next to the Blueprint'
mkdir -p "$WORK/bundle"
printf 'PK' > "$WORK/bundle/mytheme.zip"
printf '<rss/>' > "$WORK/bundle/content.xml"
cat > "$WORK/bundle/blueprint.json" <<'JSON'
{
	"steps": [
		{ "step": "installTheme", "themeData": { "resource": "bundled", "path": "./mytheme.zip" } },
		{ "step": "importWxr", "file": { "resource": "bundled", "path": "./content.xml" } },
		{ "step": "importWxr", "file": { "resource": "bundled", "path": "../../../../../../../../../../../../etc/hosts" } }
	]
}
JSON
out=$($QIT_BIN blueprint:import "$WORK/bundle/blueprint.json" -v 2>&1)
check 'bundled theme installs as a local extension' '"from": "local"' "$out"
check 'bundled theme resolves next to the Blueprint' "$WORK/bundle/mytheme.zip" "$out"
check 'bundled WXR uses the mounted package path' '/qit/packages/blueprint-steps/content.xml' "$out"
check_absent 'bundled files are not inlined as base64' "$(printf '<rss/>' | base64)" "$out"
check 'paths escaping the Blueprint directory are refused' 'outside the Blueprint directory' "$out"

section '11. Environments that cannot run steps refuse them'
out=$(cd "$WORK" && $QIT_BIN env:up --environment_type=performance --blueprint="$BP/store.json" 2>&1)
check 'performance env refuses a Blueprint with steps' 'performance environments do not run Blueprint steps' "$out"

out=$(cd "$WORK" && QIT_SELF_TEST=env_up $QIT_BIN env:up --environment_type=performance --blueprint="$BP/no-steps.json" 2>&1)
check 'performance env accepts a step-less Blueprint' '"php_version":"8.3"' "$out"

out=$($QIT_BIN run:performance woocommerce --blueprint="$BP/store.json" 2>&1)
check 'remote performance runs refuse --blueprint' 'only applies to local runs' "$out"

section '12. Option is exposed by every command that can use it'
for cmd in env:up run:e2e run:performance run:woo-e2e run:woo-api; do
	out=$($QIT_BIN "$cmd" --help 2>&1)
	check "$cmd accepts --blueprint" '--blueprint[=BLUEPRINT]' "$out"
done

printf '\n\033[1mResult: %d passed, %d failed\033[0m\n' "$PASS" "$FAIL"
[ "$FAIL" -eq 0 ]
