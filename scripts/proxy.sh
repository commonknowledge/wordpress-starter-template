#!/bin/sh
# Starts the local proxy in docker/proxy, shared by every project on this
# machine. It serves projects at http://<name>.test. Run it once, from any
# project; it restarts with Docker. Run it before `docker compose` in any
# project, which joins the proxy's network. Any arguments go to `docker compose`
# instead, e.g. `scripts/proxy.sh down` or `scripts/proxy.sh logs -f`.
#
# Usage: scripts/proxy.sh [docker compose arguments]

set -eu
cd "$(dirname "$0")/../docker/proxy"

if [ "$#" -eq 0 ]; then
    set -- up -d
fi

docker compose -f compose.yml "$@"
