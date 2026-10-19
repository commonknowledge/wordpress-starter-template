// Test-only main entry, built by tests/build/build.test.mjs in place of
// assets/main.ts. Each import and expression exercises a part of the build.
import "./modern.css";
import "./sass.scss";
import domReady from "@wordpress/dom-ready";

interface Config {
  label?: { text: string };
}

const config: Config = JSON.parse("{}");

domReady(() => {
  // Optional chaining and nullish coalescing, which the es2017 target lacks
  document.body.dataset.label = config.label?.text ?? "theme-build-test";
});
