#!/bin/sh
# Fails if WordPress logged any PHP errors, warnings or notices, and prints
# them. Run after the other tests, so it covers every page they loaded.
# Deprecations are not logged (see web/app/mu-plugins/hide-deprecation-notices.php).
#
# Usage: scripts/check-php-log.sh [log-file]

cd "$(dirname "$0")/.." || exit 1

LOG="${1:-web/app/debug.log}"

if [ -s "$LOG" ]; then
    echo "FAIL  PHP logged errors in $LOG:"
    cat "$LOG"
    exit 1
fi
echo "PASS  PHP logged no errors"
