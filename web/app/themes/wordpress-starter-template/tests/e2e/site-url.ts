// The site's URL: SITE_URL if set, otherwise its local domain from
// LOCAL_DOMAIN, as start.sh sets it in .env
export const siteUrl =
  process.env.SITE_URL ??
  `http://${process.env.LOCAL_DOMAIN ?? "wordpress-starter-template.test"}`;
