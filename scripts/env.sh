# Sourced by the other scripts, from the repository root. Reads LOCAL_DOMAIN
# from .env and sets DEFAULT_SITE_URL from it.

LOCAL_DOMAIN=$(grep '^LOCAL_DOMAIN=' .env 2>/dev/null | tail -n 1 | cut -d= -f2- | tr -d "\"'")
DEFAULT_SITE_URL="${LOCAL_DOMAIN:+http://$LOCAL_DOMAIN}"
