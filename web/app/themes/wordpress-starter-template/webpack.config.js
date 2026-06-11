const fs = require("fs");
const path = require("path");

const DependencyExtractionWebpackPlugin = require("@wordpress/dependency-extraction-webpack-plugin");
const BrowserSyncPlugin = require("browser-sync-webpack-plugin");
const CopyPlugin = require("copy-webpack-plugin");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

/**
 * Each block lives in blocks/<name>/ and contributes:
 *   - index.tsx   editor script (React)
 *   - view.ts     front-end script (Alpine.js), optional
 *   - block.json  block metadata, copied to build/<name>/
 *   - render.php  server-side render template, copied to build/<name>/
 * The built block directory is then registered by functions.php.
 */
const blocksDir = path.resolve(__dirname, "blocks");
const blockEntries = {};
for (const block of fs.readdirSync(blocksDir)) {
  for (const file of ["index.tsx", "view.ts"]) {
    const entryFile = path.join(blocksDir, block, file);
    if (fs.existsSync(entryFile)) {
      blockEntries[`${block}/${path.parse(file).name}`] = entryFile;
    }
  }
}

module.exports = (env, argv) => {
  const isProduction = argv.mode !== "development";

  return {
    context: __dirname,
    devtool: isProduction ? false : "eval-source-map",
    entry: {
      main: "./assets/main.ts",
      ...blockEntries,
    },
    output: {
      path: path.resolve(__dirname, "build"),
      filename: "[name].js",
      clean: true,
    },
    plugins: [
      new MiniCssExtractPlugin({
        filename: "[name].css",
      }),
      new CopyPlugin({
        patterns: [{ context: "blocks", from: "*/{block.json,render.php}" }],
      }),
      // Rewrites imports of react and @wordpress/* packages to the copies
      // WordPress itself ships (window.React, window.wp.*), and emits a
      // [name].asset.php file per entry listing the matching script handles,
      // which WordPress reads when enqueueing.
      new DependencyExtractionWebpackPlugin(),
      // In dev/watch mode, proxy the WordPress site (browse via
      // http://localhost:3000) and auto-reload the browser whenever webpack
      // emits new assets or theme PHP/template files change.
      !isProduction &&
        new BrowserSyncPlugin({
          proxy: process.env.WP_PROXY_TARGET || "http://localhost:8082",
          port: 3000,
          files: ["**/*.php", "templates/**", "parts/**", "theme.json"],
          open: false,
          ghostMode: false,
        }),
    ].filter(Boolean),
    module: {
      rules: [
        {
          test: /\.tsx?$/,
          use: "ts-loader",
          exclude: /node_modules/,
        },
        {
          test: /\.css$/i,
          use: [MiniCssExtractPlugin.loader, "css-loader"],
        },
        {
          test: /\.(woff(2)?|ttf|eot|svg)$/,
          type: "asset/resource",
          generator: {
            filename: "[name][ext]",
          },
        },
      ],
    },
    resolve: {
      extensions: [".ts", ".tsx", ".js"],
    },
    target: "web",
  };
};
