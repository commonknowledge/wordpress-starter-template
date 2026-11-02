// Checks what the theme build writes, so a bump to webpack, its loaders and
// plugins, Sass, PostCSS or TypeScript that changes the output fails here
// rather than on a site. Builds into temporary folders and leaves build/ alone.
//
// Run with `npm test` from the theme folder.

import assert from "node:assert/strict";
import { spawnSync } from "node:child_process";
import fs from "node:fs";
import os from "node:os";
import path from "node:path";
import { after, before, describe, test } from "node:test";

const themeDir = path.resolve(import.meta.dirname, "../..");
const blocksDir = path.join(themeDir, "blocks");
const webpackBin = path.join(themeDir, "node_modules/.bin/webpack");
const tempDirs = [];

function build(env = {}) {
  const outputDir = fs.mkdtempSync(path.join(os.tmpdir(), "theme-build-"));
  tempDirs.push(outputDir);
  const result = spawnSync(webpackBin, ["--output-path", outputDir], {
    cwd: themeDir,
    env: { ...process.env, DEBUG: "false", ...env },
    encoding: "utf8",
  });
  assert.equal(
    result.status,
    0,
    `webpack failed:\n${result.stdout}\n${result.stderr}`,
  );
  return outputDir;
}

function read(file) {
  assert.ok(fs.existsSync(file), `${file} was not built`);
  return fs.readFileSync(file, "utf8");
}

// The script handles listed in a build's .asset.php file
function assetDependencies(assetFile) {
  const match = read(assetFile).match(/'dependencies'\s*=>\s*array\(([^)]*)\)/);
  assert.ok(match, `${assetFile} has no dependencies array`);
  return [...match[1].matchAll(/'([^']+)'/g)].map((m) => m[1]);
}

// Each block folder with its block.json
const blocks = fs
  .readdirSync(blocksDir, { withFileTypes: true })
  .filter((dirent) => dirent.isDirectory())
  .map((dirent) => {
    const dir = path.join(blocksDir, dirent.name);
    return {
      dir,
      folder: dirent.name,
      metadata: JSON.parse(read(path.join(dir, "block.json"))),
    };
  });

// The built files a block.json loads, mapped from build/ to outputDir
function blockFiles(block, outputDir) {
  const fields = [
    "editorScript",
    "script",
    "viewScript",
    "editorStyle",
    "style",
    "viewStyle",
  ];
  return fields.flatMap((field) =>
    [block.metadata[field] ?? []]
      .flat()
      .filter((value) => value.startsWith("file:"))
      .map((value) => {
        const file = path.resolve(block.dir, value.slice("file:".length));
        const fromBuild = path.relative(path.join(themeDir, "build"), file);
        assert.ok(
          !fromBuild.startsWith(".."),
          `${block.folder}/block.json ${field} points outside build/: ${value}`,
        );
        return { field, file: path.join(outputDir, fromBuild) };
      }),
  );
}

after(() => {
  for (const dir of tempDirs) {
    fs.rmSync(dir, { recursive: true, force: true });
  }
});

describe("theme build", () => {
  let outputDir;
  before(() => {
    outputDir = build();
  });

  test("writes main.js, main.css and main.asset.php", () => {
    for (const file of ["main.js", "main.css", "main.asset.php"]) {
      read(path.join(outputDir, file));
    }
  });

  test("finds at least one block", () => {
    assert.ok(blocks.length > 0, "no blocks found in blocks/");
  });

  for (const block of blocks) {
    describe(`block ${block.folder}`, () => {
      test("block.json loads files the build writes", () => {
        const files = blockFiles(block, outputDir);
        assert.ok(
          files.some(({ field }) => field === "editorScript"),
          "block.json has no editorScript",
        );
        for (const { field, file } of files) {
          read(file);
          if (field.endsWith("Script")) {
            read(file.replace(/\.js$/, ".asset.php"));
          }
        }
      });

      test("scripts use WordPress's copies of @wordpress packages and React", () => {
        for (const source of ["index.tsx", "view.ts"]) {
          const sourceFile = path.join(block.dir, source);
          if (!fs.existsSync(sourceFile)) continue;
          const name = path.parse(source).name;
          const builtDir = path.join(outputDir, "blocks", block.folder);
          const deps = assetDependencies(
            path.join(builtDir, `${name}.asset.php`),
          );
          const imports = [
            ...read(sourceFile).matchAll(/from\s+"@wordpress\/([\w-]+)"/g),
          ].map((m) => `wp-${m[1]}`);
          for (const handle of imports) {
            assert.ok(
              deps.includes(handle),
              `${name}.asset.php lacks ${handle} (has ${deps})`,
            );
          }
          // Bundling React or a @wordpress package makes a script hundreds of KB
          const size = fs.statSync(path.join(builtDir, `${name}.js`)).size;
          assert.ok(
            size < 50_000,
            `${name}.js is ${size} bytes, so it bundles a dependency`,
          );
        }
      });

      test("styles are compiled to plain CSS", () => {
        const styles = blockFiles(block, outputDir).filter(({ field }) =>
          field.endsWith("tyle"),
        );
        const className = `.wp-block-${block.metadata.name.replace("/", "-")}`;
        for (const { file } of styles) {
          const css = read(file);
          assert.ok(
            css.includes(className),
            `${file} has no ${className} rule`,
          );
          assert.ok(!css.includes("&"), `${file} has uncompiled nesting`);
        }
      });
    });
  }
});

// tests/build/entry.ts imports a WordPress package, modern CSS and TypeScript
// syntax newer than the build's target, to check each is handled
describe("theme build of the test entry", () => {
  let outputDir;
  before(() => {
    outputDir = build({ MAIN_ENTRY: "./tests/build/entry.ts" });
  });

  test("lists imported WordPress packages in main.asset.php", () => {
    const deps = assetDependencies(path.join(outputDir, "main.asset.php"));
    assert.ok(deps.includes("wp-dom-ready"), `main.asset.php lists ${deps}`);
  });

  test("compiles TypeScript down to the tsconfig target", () => {
    const js = read(path.join(outputDir, "main.js"));
    assert.ok(
      js.includes("theme-build-test"),
      "main.js lacks the entry's code",
    );
    assert.ok(!js.includes("?."), "main.js has optional chaining");
    assert.ok(!js.includes("??"), "main.js has nullish coalescing");
  });

  test("runs postcss-preset-env on CSS", () => {
    const css = read(path.join(outputDir, "main.css"));
    assert.match(
      css,
      /\.fixture-parent \.fixture-child\s*\{/,
      "nesting not flattened",
    );
    assert.match(
      css,
      /@media\s*\(max-width:\s*30em\)/,
      "custom media not resolved",
    );
    assert.match(
      css,
      /\.fixture-colour\s*\{\s*color:\s*rgb\(/,
      "no rgb() fallback for oklch()",
    );
  });

  test("compiles Sass", () => {
    const css = read(path.join(outputDir, "main.css"));
    assert.match(
      css,
      /\.fixture-sass \.fixture-sass-child\s*\{\s*width:\s*20px/,
      "Sass not compiled",
    );
  });
});
