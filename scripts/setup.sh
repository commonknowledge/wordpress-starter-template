#!/bin/sh
# Local setup for the Docker Compose environment. Installs WordPress if needed,
# activates the theme, sets pretty permalinks and loads every page fixture in
# scripts/fixtures as a page with the fixture's file name as its slug. Safe to
# re-run: existing fixture pages are updated.
#
# Usage: scripts/setup.sh [site-url] [site-title]
#   e.g. scripts/setup.sh http://localhost:8082 "WordPress Starter"
# Run `docker compose up -d` first. The admin login is admin / admin (local only).

set -eu
cd "$(dirname "$0")/.."

SITE_URL="${1:-http://localhost:8082}"
SITE_TITLE="${2:-WordPress Starter}"

# Send PHP notices to stderr so they don't end up in captured output
wp() {
    docker compose exec -T wordpress php -d display_errors=stderr /bin/wp "$@"
}

echo "Waiting for MariaDB"
for i in $(seq 1 30); do
    docker compose exec -T mysql mariadb -uroot -ppassword -e 'SELECT 1' >/dev/null 2>&1 && break
    [ "$i" -eq 30 ] && { echo "MariaDB did not start"; exit 1; }
    sleep 2
done

if ! wp core is-installed 2>/dev/null; then
    wp core install --url="$SITE_URL" --title="$SITE_TITLE" \
        --admin_user=admin --admin_password=admin --admin_email=admin@example.test --skip-email
fi

wp theme activate wordpress-starter-template
wp rewrite structure '/%postname%/'

for fixture in scripts/fixtures/*.html; do
    [ -e "$fixture" ] || continue
    slug=$(basename "$fixture" .html)
    # The repository is mounted at /var/www/html in the container
    path="/var/www/html/$fixture"
    id=$(wp post list --post_type=page --name="$slug" --field=ID 2>/dev/null)
    if [ -n "$id" ]; then
        wp post update "$id" "$path"
    else
        wp post create "$path" --post_type=page --post_status=publish \
            --post_title="$slug" --post_name="$slug"
    fi
done
