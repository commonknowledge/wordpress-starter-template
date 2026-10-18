#!/bin/sh
# Gets this project running locally, from a fresh clone or at any point after.
# Each step checks whether it has already been done, so it is safe to run
# again. It asks before doing anything that changes the project or needs sudo.
#
# Steps, each of which can also be run by hand (see README.md):
#   1. Rename a new project made from the template (scripts/rename-project.php)
#   2. Create .env from .env.example
#   3. Start the local proxy, shared by every project (scripts/proxy.sh)
#   4. Point *.test at the proxy, once per machine (scripts/setup-dns.sh)
#   5. Install Composer dependencies
#   6. Build the theme
#   7. Start the containers, building the WordPress image if it has changed
#   8. Install WordPress and load the page fixtures (scripts/setup.sh)
#
# Usage: ./start.sh [--yes] [--name <project-name>]
#   --yes   Accept every default without asking. The project is only renamed
#           when --name is given too.
#   --name  The new project's name, in kebab-case, e.g. tenants-union

set -eu
cd "$(dirname "$0")"

TEMPLATE_NAME=wordpress-starter-template
YES=
NAME=

while [ $# -gt 0 ]; do
    case "$1" in
    --yes | -y) YES=1 ;;
    --name)
        NAME="${2:-}"
        shift
        ;;
    -h | --help)
        sed -n '2,20p' "$0" | sed 's/^# \{0,1\}//'
        exit 0
        ;;
    *)
        echo "Unknown option: $1 (see ./start.sh --help)"
        exit 2
        ;;
    esac
    shift
done

step() { printf '\n==> %s\n' "$1"; }
done_already() { echo "    $1"; }

# Asks a yes/no question that defaults to yes
confirm() {
    [ -n "$YES" ] && return 0
    printf '%s [Y/n] ' "$1"
    read -r answer || answer=
    case "$answer" in
    [nN]*) return 1 ;;
    *) return 0 ;;
    esac
}

is_kebab_case() {
    printf '%s' "$1" | grep -Eq '^[a-z][a-z0-9]*(-[a-z0-9]+)*$'
}

# Runs PHP locally if installed, otherwise in Composer's container
php_run() {
    if command -v php >/dev/null 2>&1; then
        php "$@"
    else
        docker run --rm -e RENAME_FROM_START_SH -v "$PWD":/app -w /app composer:2 php "$@"
    fi
}

if ! docker info >/dev/null 2>&1; then
    echo "Docker isn't running. Start Docker, then run ./start.sh again."
    exit 1
fi

step "Project name"
renamed=
if [ -d "web/app/themes/$TEMPLATE_NAME" ]; then
    if [ -n "$NAME" ] || { [ -z "$YES" ] && confirm "This is still called $TEMPLATE_NAME. Rename it for a new project?"; }; then
        while ! is_kebab_case "$NAME"; do
            [ -n "$NAME" ] && echo "    '$NAME' isn't kebab-case."
            [ -n "$YES" ] && exit 1
            printf 'Project name, in kebab-case: lowercase words joined by hyphens, e.g. tenants-union: '
            read -r NAME || exit 1
        done
        RENAME_FROM_START_SH=1 php_run scripts/rename-project.php "$NAME"
        renamed=1
    else
        done_already "Keeping the template's name"
    fi
else
    done_already "Already renamed"
fi

# The theme folder carries the project's name, as composer.json's autoload path does
THEME=$(sed -n 's#.*"web/app/themes/\([^/"]*\)/src/".*#\1#p' composer.json | head -n 1)

step "Environment file"
if [ -f .env ]; then
    done_already ".env already exists"
else
    cp .env.example .env
    echo "    Created .env from .env.example"
fi
. scripts/env.sh

step "Local proxy"
if [ -n "$(docker ps -q --filter name='^local-proxy-traefik$' --filter status=running)" ]; then
    done_already "The proxy is already running"
else
    scripts/proxy.sh
fi

step "Local domains"
if [ -f /etc/resolver/test ] || [ -f /etc/systemd/resolved.conf.d/test.conf ]; then
    done_already "*.test already resolves through the proxy"
elif grep -Eq "^[^#]*[[:space:]]$LOCAL_DOMAIN([[:space:]]|$)" /etc/hosts 2>/dev/null; then
    done_already "$LOCAL_DOMAIN is in /etc/hosts"
elif confirm "Make every *.test domain resolve to the proxy? This needs sudo, once per machine."; then
    scripts/setup-dns.sh || true
else
    echo "    Skipped: add '127.0.0.1 $LOCAL_DOMAIN' to /etc/hosts to reach the site."
fi

step "Composer dependencies"
if [ -f vendor/autoload.php ] && [ -z "$renamed" ]; then
    done_already "Composer dependencies are already installed"
elif command -v composer >/dev/null 2>&1; then
    composer install
else
    docker compose run --rm composer install
fi

step "Theme"
if [ -f "web/app/themes/$THEME/build/main.js" ] && [ -z "$renamed" ]; then
    done_already "The theme is already built"
elif ! command -v npm >/dev/null 2>&1; then
    echo "    npm isn't installed, so the theme wasn't built. Install Node (see web/app/themes/$THEME/.nvmrc), then run ./start.sh again."
elif confirm "Build the theme with npm?"; then
    (cd "web/app/themes/$THEME" && npm ci && npm run build)
fi

step "Containers"
docker compose up -d --build

step "WordPress"
echo "    Waiting for MariaDB"
for i in $(seq 1 30); do
    docker compose exec -T mysql mariadb -uroot -ppassword -e 'SELECT 1' >/dev/null 2>&1 && break
    [ "$i" -eq 30 ] && { echo "MariaDB did not start"; exit 1; }
    sleep 2
done
if docker compose exec -T wordpress wp core is-installed >/dev/null 2>&1; then
    done_already "WordPress is already installed"
    scripts/setup.sh
elif confirm "Install WordPress, activate the theme and load the page fixtures?"; then
    scripts/setup.sh
fi

printf '\nThe site is at %s, and the admin login is admin / admin.\n' "$DEFAULT_SITE_URL"
