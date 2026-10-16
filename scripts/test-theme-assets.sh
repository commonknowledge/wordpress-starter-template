#!/bin/sh
# Checks that every script the theme's main.js depends on, as listed by the
# build in build/main.asset.php, is loaded on the homepage. Run after building
# the theme, against a site with the theme active.
#
# Usage: scripts/test-theme-assets.sh [base-url]

cd "$(dirname "$0")/.."

BASE_URL="${1:-http://localhost:8082}"
ASSET_FILE=/var/www/html/web/app/themes/wordpress-starter-template/build/main.asset.php
failures=0

deps=$(docker compose exec -T wordpress php -r "echo implode(' ', (require '$ASSET_FILE')['dependencies']);")
page=$(curl -s "$BASE_URL/")

for handle in $deps; do
    if printf '%s' "$page" | grep -q "id=\"$handle-js\""; then
        echo "PASS  main.js dependency $handle is loaded"
    else
        echo "FAIL  main.js dependency $handle is loaded"
        failures=$((failures + 1))
    fi
done

if printf '%s' "$page" | grep -q 'id="theme-custom-script-js"'; then
    echo "PASS  main.js is loaded"
else
    echo "FAIL  main.js is loaded"
    failures=$((failures + 1))
fi

echo
if [ "$failures" -eq 0 ]; then
    echo "All checks passed"
else
    echo "$failures check(s) failed"
    exit 1
fi
