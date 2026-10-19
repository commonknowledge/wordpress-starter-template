#!/bin/sh
# Activates every installed plugin, one at a time, and checks each activates
# and leaves the site serving pages and REST API JSON. Plugins stay active, so
# tests run afterwards cover them too. Wordfence is skipped in development,
# where a must-use plugin deactivates it: without its production setup, its
# firewall prints warnings that break the login page.
#
# Usage: scripts/test-plugins.sh [base-url]

cd "$(dirname "$0")/.." || exit 1

BASE_URL="${1:-http://localhost:8082}"
failures=0

pass() { echo "PASS  $1"; }
fail() { echo "FAIL  $1"; failures=$((failures + 1)); }

wp() {
    docker compose exec -T wordpress php -d display_errors=stderr /bin/wp "$@" 2>/dev/null
}

# check_page <path> <first-character>: returns 200 and starts with the
# character, so a PHP warning printed before the page or JSON fails
check_page() {
    body=$(curl -s -w '\n%{http_code}' "$BASE_URL$1")
    status=$(printf '%s' "$body" | tail -n 1)
    first=$(printf '%s' "$body" | head -c 1)
    [ "$status" = "200" ] && [ "$first" = "$2" ]
}

env=$(wp eval 'echo WP_ENV;')
plugins=$(wp plugin list --field=name --status=inactive --skip-update-check)
[ -n "$plugins" ] || echo "No inactive plugins to test"

for plugin in $plugins; do
    if [ "$plugin" = "wordfence" ] && [ "$env" = "development" ]; then
        echo "SKIP  wordfence, which is deactivated in development"
        continue
    fi
    if ! wp plugin activate "$plugin" >/dev/null; then
        fail "$plugin activates"
        continue
    fi
    if check_page / "<" && check_page /wp-json/ "{"; then
        pass "$plugin activates and the site still works"
    else
        fail "$plugin activates and the site still works"
    fi
done

echo
if [ "$failures" -eq 0 ]; then
    echo "All checks passed"
else
    echo "$failures check(s) failed"
    exit 1
fi
