#!/bin/sh
# Smoke test for the Docker Compose development environment. Checks that
# WordPress is installed and serving pages through nginx and PHP-FPM.
#
# Usage: scripts/smoke-test.sh <site-title> [base-url] [path=text ...]
#   e.g. scripts/smoke-test.sh "WordPress Starter" http://wordpress-starter-template.test
#
# Each extra path=text argument checks that the page at path returns 200 and
# contains text, e.g. "/blocks/=wp-block-wordpress-starter-template-example".
# The base URL defaults to LOCAL_DOMAIN in .env.

cd "$(dirname "$0")/.."
. scripts/env.sh

SITE_TITLE="$1"
BASE_URL="${2:-${DEFAULT_SITE_URL:?Pass a base URL or set LOCAL_DOMAIN in .env}}"
failures=0

if [ -z "$SITE_TITLE" ]; then
    echo "Usage: $0 <site-title> [base-url] [path=text ...]"
    exit 2
fi
shift
[ $# -gt 0 ] && shift

pass() { echo "PASS  $1"; }
fail() { echo "FAIL  $1"; failures=$((failures + 1)); }

# check_status <path> <expected-status>
check_status() {
    status=$(curl -s -o /dev/null -w '%{http_code}' "$BASE_URL$1")
    if [ "$status" = "$2" ]; then
        pass "$1 returns $2"
    else
        fail "$1 returns $2 (got $status)"
    fi
}

# check_body <path> <text>
check_body() {
    if curl -s "$BASE_URL$1" | grep -qF "$2"; then
        pass "$1 contains '$2'"
    else
        fail "$1 contains '$2'"
    fi
}

check_status / 200
check_body / "<title>$SITE_TITLE"
check_body / "</html>"
check_status /wp/wp-login.php 200
check_status /wp-json/ 200

# PHP notices printed before the JSON break the REST API, and the block editor
if curl -s "$BASE_URL/wp-json/" | head -c 1 | grep -q '{'; then
    pass "/wp-json/ returns JSON with no output before it"
else
    fail "/wp-json/ returns JSON with no output before it"
fi

for check in "$@"; do
    path="${check%%=*}"
    text="${check#*=}"
    check_status "$path" 200
    check_body "$path" "$text"
done

echo
if [ "$failures" -eq 0 ]; then
    echo "All checks passed"
else
    echo "$failures check(s) failed"
    exit 1
fi
