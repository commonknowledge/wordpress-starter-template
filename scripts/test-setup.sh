#!/bin/sh
# Regression tests for scripts/setup.sh. Run against a fresh, disposable
# install, straight after setup.sh has run once: it deletes and recreates the
# /blocks/ fixture page and adds and removes test pages.
#
# Usage: scripts/test-setup.sh [site-url] [site-title]

cd "$(dirname "$0")/.."
. scripts/env.sh

SITE_URL="${1:-${DEFAULT_SITE_URL:?Pass a site URL or set LOCAL_DOMAIN in .env}}"
SITE_TITLE="${2:-WordPress Starter}"
MARKER="Nested page content that setup must not change"
failures=0

pass() { echo "PASS  $1"; }
fail() { echo "FAIL  $1"; failures=$((failures + 1)); }

wp() {
    docker compose exec -T wordpress php -d display_errors=stderr /bin/wp "$@" 2>/dev/null
}

setup() {
    scripts/setup.sh "$SITE_URL" "$SITE_TITLE" >/dev/null 2>&1
}

root_blocks_ids() {
    wp post list --post_type=page --name=blocks --post_parent=0 --field=ID
}

# On a fresh install the fixture page can get the ID a hard-coded navigation
# ref points to, so the header must not render the page's content
count=$(curl -s "$SITE_URL/blocks/" | grep -o "Hello from a custom block" | wc -l | tr -d ' ')
if [ "$count" = "1" ]; then
    pass "/blocks/ shows the example block once"
else
    fail "/blocks/ shows the example block once (found $count)"
fi

# Start without a root /blocks/ page, with a nested page using the same slug
for id in $(root_blocks_ids); do wp post delete "$id" --force >/dev/null; done
parent=$(wp post create --post_type=page --post_status=publish --post_title="Test parent" --post_name=test-parent --porcelain)
nested=$(wp post create --post_type=page --post_status=publish --post_title="Nested blocks" --post_name=blocks \
    --post_parent="$parent" --post_content="$MARKER" --porcelain)

setup
if [ "$(wp post get "$nested" --field=post_content)" = "$MARKER" ]; then
    pass "setup leaves a nested page with the fixture's slug unchanged"
else
    fail "setup leaves a nested page with the fixture's slug unchanged"
fi
if [ "$(root_blocks_ids | wc -l | tr -d ' ')" = "1" ]; then
    pass "setup creates the root fixture page"
else
    fail "setup creates the root fixture page"
fi

# Two root pages with the fixture's slug can only come from direct database
# edits, but setup must refuse to guess which one to update
original=$(root_blocks_ids)
duplicate=$(wp eval '
    global $wpdb;
    $wpdb->insert($wpdb->posts, [
        "post_type" => "page", "post_status" => "publish", "post_name" => "blocks",
        "post_title" => "Duplicate", "post_content" => "Duplicate content", "post_parent" => 0,
        "post_date" => current_time("mysql"), "post_date_gmt" => current_time("mysql", true),
        "post_modified" => current_time("mysql"), "post_modified_gmt" => current_time("mysql", true),
        "post_excerpt" => "", "to_ping" => "", "pinged" => "", "post_content_filtered" => "",
    ]);
    echo $wpdb->insert_id;
')
if setup; then
    fail "setup fails when several root pages have the fixture's slug"
else
    pass "setup fails when several root pages have the fixture's slug"
fi
if [ "$(wp post get "$duplicate" --field=post_content)" = "Duplicate content" ]; then
    pass "setup changes neither page when the slug is ambiguous"
else
    fail "setup changes neither page when the slug is ambiguous"
fi
wp post delete "$duplicate" --force >/dev/null

wp post delete "$nested" --force >/dev/null
wp post delete "$parent" --force >/dev/null
[ -n "$original" ] || setup

echo
if [ "$failures" -eq 0 ]; then
    echo "All checks passed"
else
    echo "$failures check(s) failed"
    exit 1
fi
