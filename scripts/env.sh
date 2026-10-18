# Sourced by the other scripts, from the repository root. Reads LOCAL_DOMAIN
# from .env and sets DEFAULT_SITE_URL from it. As with Docker Compose, a
# LOCAL_DOMAIN already set in the environment wins over .env.

if [ -z "${LOCAL_DOMAIN:-}" ] && [ -f .env ]; then
    LOCAL_DOMAIN=$(tr -d '\r' < .env \
        | sed -n 's/^[[:space:]]*\(export[[:space:]]\{1,\}\)\{0,1\}LOCAL_DOMAIN[[:space:]]*=[[:space:]]*//p' \
        | tail -n 1 | sed 's/[[:space:]]#.*//; s/[[:space:]]*$//' | tr -d "\"'")
fi
# shellcheck disable=SC2034 # used by the scripts that source this file
DEFAULT_SITE_URL="${LOCAL_DOMAIN:+http://$LOCAL_DOMAIN}"
