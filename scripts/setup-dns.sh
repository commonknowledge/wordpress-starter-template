#!/bin/sh
# Points the host's resolver at the local proxy's DNS server for *.test, so every
# name under .test reaches the proxy. Needs sudo, and only needs running once
# per machine. Run scripts/proxy.sh to start the proxy.
#
# Usage: scripts/setup-dns.sh [--remove]

set -eu
cd "$(dirname "$0")/.."
. scripts/env.sh

DNS_PORT=5354

case "$(uname -s)" in
Darwin)
    file=/etc/resolver/test
    if [ "${1:-}" = "--remove" ]; then
        sudo rm -f "$file"
    else
        sudo mkdir -p /etc/resolver
        printf 'nameserver 127.0.0.1\nport %s\n' "$DNS_PORT" | sudo tee "$file" >/dev/null
    fi
    ;;
Linux)
    if ! systemctl is-active --quiet systemd-resolved 2>/dev/null; then
        echo "systemd-resolved is not running, so add each site's LOCAL_DOMAIN to /etc/hosts instead:"
        echo "  127.0.0.1 ${LOCAL_DOMAIN:-example.test}"
        exit 1
    fi
    file=/etc/systemd/resolved.conf.d/test.conf
    if [ "${1:-}" = "--remove" ]; then
        sudo rm -f "$file"
    else
        sudo mkdir -p /etc/systemd/resolved.conf.d
        printf '[Resolve]\nDNS=127.0.0.1:%s\nDomains=~test\n' "$DNS_PORT" | sudo tee "$file" >/dev/null
    fi
    sudo systemctl restart systemd-resolved
    ;;
*)
    echo "Unsupported system: add each site to your hosts file instead:"
    echo "  127.0.0.1 ${LOCAL_DOMAIN:-example.test}"
    exit 1
    ;;
esac

if [ "${1:-}" = "--remove" ]; then
    echo "Removed $file"
else
    echo "Wrote $file: *.test now resolves through the local proxy"
fi
