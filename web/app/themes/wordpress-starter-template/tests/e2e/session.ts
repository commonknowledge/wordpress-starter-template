import path from "node:path";

// Where tests/e2e/log-in.setup.ts saves the admin user's logged-in session
export const adminSession = path.resolve(
  __dirname,
  "../../test-results/.auth/admin.json",
);
