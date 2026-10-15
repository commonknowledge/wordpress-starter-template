#!/bin/sh
# Smoke test for the Docker Compose development environment. Checks that
# WordPress is installed and serving pages through nginx and PHP-FPM.
#
# Usage: scripts/smoke-test.sh <site-title> [base-url]
#   e.g. scripts/smoke-test.sh "WordPress Starter" http://localhost:8082

SITE_TITLE="$1"
BASE_URL="${2:-http://localhost:8082}"
failures=0

if [ -z "$SITE_TITLE" ]; then
    echo "Usage: $0 <site-title> [base-url]"
    exit 2
fi

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

echo
if [ "$failures" -eq 0 ]; then
    echo "All checks passed"
else
    echo "$failures check(s) failed"
    exit 1
fi
