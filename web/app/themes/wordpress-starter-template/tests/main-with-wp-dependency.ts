// Test-only main entry, built by scripts/test-theme-assets.sh in place of
// assets/main.ts. Importing a WordPress package makes the build list
// wp-dom-ready in build/main.asset.php, which functions.php must enqueue.
import domReady from "@wordpress/dom-ready";

domReady(() => {
  document.documentElement.dataset.themeAssetsTest = "ready";
});
