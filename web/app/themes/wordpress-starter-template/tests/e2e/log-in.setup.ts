// Logs in as the admin user that scripts/setup.sh creates, once for every
// test that needs it, and saves the session to a file they load
import { expect, test as setup } from "@playwright/test";
import { adminSession } from "./session";

setup("log in as admin", async ({ page }) => {
  await page.goto("/wp/wp-login.php");
  await page.fill("#user_login", "admin");
  await page.fill("#user_pass", "admin");
  await page.click("#wp-submit");
  // The first login on a fresh site can take a while
  await expect(page.locator("#wpadminbar"), "logged in").toBeVisible({
    timeout: 20_000,
  });
  await page.context().storageState({ path: adminSession });
});
