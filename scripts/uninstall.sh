#!/bin/sh
# Removes this project's local environment: its containers, network, database
# and built WordPress image. The project's files, .env, Composer packages and
# theme build stay, so ./start.sh can bring it back with a fresh database.
#
# With --all, also removes what start.sh set up for every project on this
# machine: the shared proxy and the *.test DNS setup. Those are left alone
# while other projects still use the proxy.
#
# Usage: scripts/uninstall.sh [--all] [--yes]
#   --all  Also remove the shared proxy and the *.test DNS setup
#   --yes  Don't ask before deleting the project's database

set -eu
cd "$(dirname "$0")/.."

ALL=
YES=
for arg in "$@"; do
    case "$arg" in
    --all) ALL=1 ;;
    --yes | -y) YES=1 ;;
    *)
        echo "Usage: scripts/uninstall.sh [--all] [--yes]"
        exit 2
        ;;
    esac
done

# Asks a yes/no question that defaults to no, since this deletes data
confirm() {
    [ -n "$YES" ] && return 0
    printf '%s [y/N] ' "$1"
    read -r answer || answer=
    case "$answer" in
    [yY]*) return 0 ;;
    *) return 1 ;;
    esac
}

project=$(basename "$PWD")

if ! command -v docker >/dev/null 2>&1 || ! docker info >/dev/null 2>&1; then
    echo "Docker isn't running, so no containers were removed."
    [ -n "$ALL" ] && scripts/setup-dns.sh --remove
    exit 0
fi

if ! confirm "Remove $project's containers and delete its database?"; then
    echo "Nothing removed."
    exit 1
fi

# Compose needs LOCAL_DOMAIN to read docker-compose.yml, even to remove things
. scripts/env.sh
LOCAL_DOMAIN="${LOCAL_DOMAIN:-unset.test}" docker compose down --volumes --rmi local --remove-orphans
echo "Removed $project's containers, network, database and WordPress image."

[ -z "$ALL" ] && exit 0

others=$(docker ps -a --filter network=local-proxy --format '{{.Names}}' | grep -v '^local-proxy-' || true)
if [ -n "$others" ]; then
    echo "Other projects still use the proxy, so it and the *.test DNS setup stay:"
    echo "$others" | sed 's/^/  /'
    exit 0
fi

scripts/proxy.sh down --rmi all
scripts/setup-dns.sh --remove
echo "Removed the shared proxy and the *.test DNS setup."
