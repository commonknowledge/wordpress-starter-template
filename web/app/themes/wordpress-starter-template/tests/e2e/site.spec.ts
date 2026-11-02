// Browser tests of the site as people use it: the front end, the block editor
// and the Site Editor. They fail on JavaScript errors, failed requests and
// blocks the editor can't load, which a WordPress, plugin or theme build
// dependency bump can cause without breaking server-rendered HTML.

import fs from "node:fs";
import path from "node:path";
import { type Page, expect, test } from "@playwright/test";
import { adminSession } from "./session";

const blocksDir = path.resolve(__dirname, "../../blocks");

// Every custom block, by the name in its block.json
const blockNames: string[] = fs
  .readdirSync(blocksDir, { withFileTypes: true })
  .filter((dirent) => dirent.isDirectory())
  .map(
    (dirent) =>
      JSON.parse(
        fs.readFileSync(
          path.join(blocksDir, dirent.name, "block.json"),
          "utf8",
        ),
      ).name,
  );

const blockClass = (name: string) => `.wp-block-${name.replace("/", "-")}`;

// The site and the webpack dev server it loads theme assets from. Requests
// elsewhere, such as Gravatar or WordPress.org news, depend on the network.
const ownOrigins = [
  new URL(process.env.SITE_URL ?? "http://localhost:8082").origin,
  "http://localhost:9000",
];

function isOwnUrl(url: string): boolean {
  return ownOrigins.includes(new URL(url).origin);
}

// PHP's own error output, which development shows in pages and responses
const phpError =
  /(Fatal error|Parse error|Warning|Notice|Deprecated)<\/b>:|^(PHP )?(Fatal error|Warning|Notice|Deprecated): /m;

// Collects JavaScript errors, console errors, failed requests and PHP error
// output on a page. Call check() to fail the test if there were any.
function watchForErrors(page: Page) {
  const errors: string[] = [];
  const pending: Promise<void>[] = [];
  page.on("pageerror", (error) => errors.push(`page error: ${error.message}`));
  page.on("console", (message) => {
    const url = message.location().url;
    if (message.type() === "error" && (!url || isOwnUrl(url))) {
      errors.push(`console error: ${message.text()} (${url})`);
    }
  });
  page.on("requestfailed", (request) => {
    // Navigating away cancels requests still in flight
    if (
      isOwnUrl(request.url()) &&
      request.failure()?.errorText !== "net::ERR_ABORTED"
    ) {
      errors.push(
        `request failed: ${request.url()} ${request.failure()?.errorText}`,
      );
    }
  });
  page.on("response", (response) => {
    if (!isOwnUrl(response.url())) return;
    if (response.status() >= 400) {
      errors.push(`HTTP ${response.status()}: ${response.url()}`);
    }
    const type = response.headers()["content-type"] ?? "";
    if (/html|json/.test(type)) {
      // A response that never finishes, such as a long poll, is skipped
      const timeout = new Promise<string>((resolve) =>
        setTimeout(() => resolve(""), 5_000),
      );
      pending.push(
        Promise.race([response.text(), timeout])
          .then((body) => {
            const match = body.match(phpError);
            if (match) {
              const at = body.indexOf(match[0]);
              const excerpt = body.slice(at, at + 300).replace(/<[^>]+>/g, "");
              errors.push(`PHP error in ${response.url()}: ${excerpt}`);
            }
          })
          // Redirects and responses to navigated-away pages have no body
          .catch(() => undefined),
      );
    }
  });
  return {
    async check() {
      await Promise.all(pending);
      expect(errors).toEqual([]);
    },
  };
}

// The editors keep making requests, so they never reach "networkidle". Give
// scripts that run after load time to report errors.
async function settle(page: Page) {
  await page.waitForLoadState("load");
  await page.waitForTimeout(2_000);
}

test.describe("front end", () => {
  for (const url of ["/", "/blocks/"]) {
    test(`${url} loads with no errors`, async ({ page }) => {
      const errors = watchForErrors(page);
      const responses: string[] = [];
      page.on("response", (response) => responses.push(response.url()));

      await page.goto(url);
      await settle(page);

      expect(
        responses.some((u) => /\/main\.css(\?|$)/.test(u)),
        "main.css loaded",
      ).toBe(true);
      expect(
        responses.some((u) => /\/main\.js(\?|$)/.test(u)),
        "main.js loaded",
      ).toBe(true);
      await errors.check();
    });
  }

  test("/blocks/ shows every custom block with its styles", async ({
    page,
  }) => {
    await page.goto("/blocks/");
    for (const name of blockNames) {
      const block = page.locator(blockClass(name)).first();
      await expect(block, `${name} is on /blocks/`).toBeVisible();
      // Each block's style.scss sets a style its wrapper wouldn't otherwise have
      const css = await block.evaluate((el) => {
        const style = getComputedStyle(el);
        return `${style.borderTopStyle} ${style.paddingTop}`;
      });
      expect(css, `${name} has its stylesheet applied`).not.toBe("none 0px");
    }
  });
});

test.describe("editor", () => {
  test.use({ storageState: adminSession });

  test("the dashboard loads with no errors", async ({ page }) => {
    const errors = watchForErrors(page);
    await page.goto("/wp/wp-admin/");
    await expect(page.locator("#wpbody-content")).toBeVisible();
    await settle(page);
    await errors.check();
  });

  test("the block editor registers and renders every custom block", async ({
    page,
  }) => {
    // The /blocks/ fixture page holds every custom block
    await page.goto("/blocks/");
    const bodyClass = (await page.locator("body").getAttribute("class")) ?? "";
    const id = bodyClass.match(/\bpage-id-(\d+)\b/)?.[1];
    expect(id, "/blocks/ page ID").toBeTruthy();

    const errors = watchForErrors(page);
    await page.goto(`/wp/wp-admin/post.php?post=${id}&action=edit`);
    const canvas = page.frameLocator('iframe[name="editor-canvas"]');
    await expect(
      canvas.locator(".block-editor-block-list__layout").first(),
    ).toBeVisible({
      timeout: 30_000,
    });

    for (const name of blockNames) {
      const registered = await page.evaluate(
        (blockName) =>
          // @ts-expect-error wp is WordPress's global in the editor
          Boolean(window.wp.blocks.getBlockType(blockName)),
        name,
      );
      expect(registered, `${name} is registered in the editor`).toBe(true);
      await expect(
        canvas.locator(blockClass(name)).first(),
        `${name} renders`,
      ).toBeVisible();
    }
    // Shown for blocks the editor can't find or whose saved markup is invalid
    await expect(canvas.locator(".block-editor-warning")).toHaveCount(0);
    await expect(canvas.locator(".wp-block-missing")).toHaveCount(0);

    await settle(page);
    await errors.check();
  });

  test("the Site Editor loads the theme with no errors", async ({ page }) => {
    const errors = watchForErrors(page);
    await page.goto("/wp/wp-admin/site-editor.php");
    await expect(page.locator(".edit-site")).toBeVisible({ timeout: 30_000 });
    const canvas = page.frameLocator("iframe");
    await expect(canvas.locator("body").first()).toBeVisible({
      timeout: 30_000,
    });
    await settle(page);
    await errors.check();
  });
});
