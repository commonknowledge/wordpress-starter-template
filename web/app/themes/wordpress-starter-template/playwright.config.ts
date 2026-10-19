import { defineConfig, devices } from "@playwright/test";

// Browser tests against a running site, e.g. the Docker Compose one after
// scripts/setup.sh. Run with `npm run test:e2e` from the theme folder.
export default defineConfig({
  testDir: "./tests/e2e",
  forbidOnly: !!process.env.CI,
  retries: 0,
  workers: 1,
  reporter: process.env.CI ? [["list"], ["html", { open: "never" }]] : "list",
  use: {
    baseURL: process.env.SITE_URL ?? "http://localhost:8082",
    trace: "retain-on-failure",
  },
  projects: [
    // Logs in once and saves the session for the editor tests
    {
      name: "log-in",
      testMatch: /log-in\.setup\.ts/,
      use: {
        ...devices["Desktop Chrome"],
        launchOptions: { executablePath: process.env.CHROMIUM_PATH },
      },
    },
    {
      name: "chromium",
      dependencies: ["log-in"],
      use: {
        ...devices["Desktop Chrome"],
        // For a Chromium installed outside Playwright's own browser folder
        launchOptions: { executablePath: process.env.CHROMIUM_PATH },
      },
    },
  ],
  // In development the site loads main.js and main.css from the dev server,
  // and blocks load their files from the build/ it writes. Other environments
  // load the production build in build/, which the dev server would replace,
  // so set E2E_DEV_SERVER=false for them.
  webServer:
    process.env.E2E_DEV_SERVER === "false"
      ? undefined
      : {
          command: "npm run dev",
          url: "http://localhost:9000/main.js",
          reuseExistingServer: !process.env.CI,
          timeout: 120_000,
        },
});
