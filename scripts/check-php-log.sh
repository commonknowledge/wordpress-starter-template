#!/bin/sh
# Fails if PHP logged any errors, warnings or notices, and prints them. Checks
# WordPress's debug log, which development writes to, and the wordpress
# container's output, where PHP logs when the debug log is off, as in
# production. Run after the other tests, so it covers every page they loaded.
# Deprecations are not logged (see web/app/mu-plugins/hide-deprecation-notices.php).
#
# Usage: scripts/check-php-log.sh [log-file]

cd "$(dirname "$0")/.." || exit 1

LOG="${1:-web/app/debug.log}"
failures=0

if [ -s "$LOG" ]; then
    echo "FAIL  PHP logged errors in $LOG:"
    cat "$LOG"
    failures=1
fi

container=$(docker compose logs --no-color wordpress 2>/dev/null |
    grep -E 'PHP (Fatal error|Parse error|Warning|Notice|Deprecated)')
if [ -n "$container" ]; then
    echo "FAIL  PHP logged errors in the wordpress container's output:"
    printf '%s\n' "$container"
    failures=1
fi

[ "$failures" -eq 0 ] || exit 1
echo "PASS  PHP logged no errors"
