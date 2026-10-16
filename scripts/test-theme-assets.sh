#!/bin/sh
# Regression test for loading main.js's WordPress dependencies. Builds the
# theme from a test entry that imports @wordpress/dom-ready, then checks the
# build lists wp-dom-ready in build/main.asset.php and that WordPress loads it
# before main.js. Rebuilds the normal theme when it finishes, pass or fail.
#
# Needs Node and the theme's npm dependencies installed, and a running site
# with the theme active.
#
# Usage: scripts/test-theme-assets.sh [base-url]

cd "$(dirname "$0")/.."

BASE_URL="${1:-http://localhost:8082}"
THEME_DIR=web/app/themes/wordpress-starter-template
ASSET_FILE="/var/www/html/$THEME_DIR/build/main.asset.php"
TEST_ENTRY=./tests/main-with-wp-dependency.ts
failures=0

pass() { echo "PASS  $1"; }
fail() { echo "FAIL  $1"; failures=$((failures + 1)); }
abort() { echo "ERROR $1"; exit 1; }

build() {
    (cd "$THEME_DIR" && MAIN_ENTRY="$1" npm run --silent build >/dev/null 2>&1)
}

restore() {
    status=$?
    build "" || { echo "ERROR could not rebuild the normal theme"; status=1; }
    exit "$status"
}
trap restore EXIT
trap 'exit 1' INT TERM

build "$TEST_ENTRY" || abort "could not build the theme from $TEST_ENTRY"

deps=$(docker compose exec -T wordpress php -r "
    \$asset = require '$ASSET_FILE';
    if (!is_array(\$asset) || !isset(\$asset['dependencies'])) {
        fwrite(STDERR, 'no dependencies in asset file');
        exit(1);
    }
    echo implode(' ', \$asset['dependencies']);
") || abort "could not read $ASSET_FILE"

case " $deps " in
*" wp-dom-ready "*) pass "build/main.asset.php lists wp-dom-ready" ;;
*) abort "build/main.asset.php does not list wp-dom-ready (got: '$deps')" ;;
esac

page=$(curl -fsS "$BASE_URL/") || abort "could not fetch $BASE_URL/"

# Byte offset of a script tag in the page, or nothing if it isn't there
offset() {
    printf '%s' "$page" | grep -bo "id=\"$1-js\"" | head -n 1 | cut -d: -f1
}

main=$(offset theme-custom-script)
[ -n "$main" ] || abort "main.js is not loaded on the homepage"

for handle in $deps; do
    at=$(offset "$handle")
    if [ -z "$at" ]; then
        fail "$handle is loaded"
    elif [ "$at" -lt "$main" ]; then
        pass "$handle is loaded before main.js"
    else
        fail "$handle is loaded before main.js"
    fi
done

echo
if [ "$failures" -eq 0 ]; then
    echo "All checks passed"
else
    echo "$failures check(s) failed"
    exit 1
fi
