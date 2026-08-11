#!/bin/bash
# Asserts, from inside the container, that blueprints/store.json was applied
# before this test package ran. Emits CTRF so run:e2e can collect a result.

mkdir -p results blob-report

BLOGNAME=$(wp option get blogname --allow-root 2>/dev/null)
CURRENCY=$(wp option get woocommerce_currency --allow-root 2>/dev/null)
POSTS=$(wp post list --post_status=publish --field=post_title --allow-root 2>/dev/null | grep -c 'From the Blueprint')
CLASSIC=$(wp plugin get classic-editor --field=status --allow-root 2>/dev/null)

echo "blogname=${BLOGNAME} currency=${CURRENCY} blueprint_posts=${POSTS} classic-editor=${CLASSIC}"

FAILURES=""
[ "$BLOGNAME" = "Blueprint Store" ] || FAILURES="${FAILURES} blogname"
[ "$CURRENCY" = "EUR" ] || FAILURES="${FAILURES} currency"
[ "$POSTS" = "1" ] || FAILURES="${FAILURES} runPHP-post"
[ "$CLASSIC" = "inactive" ] || FAILURES="${FAILURES} activate-false"

if [ -z "$FAILURES" ]; then
	STATUS=passed
	PASSED=1
	FAILED=0
	MESSAGE="blueprint state applied before tests"
else
	STATUS=failed
	PASSED=0
	FAILED=1
	MESSAGE="blueprint state missing:${FAILURES}"
fi

cat > results/ctrf.json <<JSON
{
  "reportFormat": "CTRF",
  "specVersion": "0.1.0",
  "results": {
    "tool": { "name": "blueprint-assert" },
    "summary": { "tests": 1, "passed": ${PASSED}, "failed": ${FAILED}, "skipped": 0, "pending": 0, "other": 0, "start": 0, "stop": 0 },
    "tests": [ { "name": "${MESSAGE}", "status": "${STATUS}", "duration": 0 } ]
  }
}
JSON

echo "$MESSAGE"

[ "$STATUS" = "passed" ]
