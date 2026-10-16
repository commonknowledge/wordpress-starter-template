const fs = require("fs");
const path = require("path");
const DependencyExtractionWebpackPlugin = require("@wordpress/dependency-extraction-webpack-plugin");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const { DefinePlugin } = require("webpack");

const DEBUG = process.env.DEBUG === "true" ? true : false;

// Each blocks/<name>/index.tsx is built to build/blocks/<name>/index.js, which
// the block's block.json loads as its editorScript. An optional view.ts is
// built to view.js, for front-end JavaScript loaded as its viewScript.
const blocksDir = path.resolve(__dirname, "blocks");
const blockEntries = Object.fromEntries(
  (fs.existsSync(blocksDir)
    ? fs.readdirSync(blocksDir, { withFileTypes: true })
    : []
  )
    .filter((dirent) => dirent.isDirectory())
    .flatMap((dirent) =>
      ["index.tsx", "view.ts"]
        .filter((file) =>
          fs.existsSync(path.resolve(blocksDir, dirent.name, file)),
        )
        .map((file) => [
          `blocks/${dirent.name}/${path.parse(file).name}`,
          `./blocks/${dirent.name}/${file}`,
        ]),
    ),
);

module.exports = [
  {
    context: __dirname,
    devServer: {
      compress: true,
      // block.json loads block scripts and styles from build/, so they must
      // be on disk rather than only in the dev server's memory
      devMiddleware: {
        writeToDisk: true,
      },
      headers: {
        "Access-Control-Allow-Origin": "*",
      },
      port: 9000,
    },
    devtool: DEBUG ? "eval-source-map" : false,
    entry: {
      main: "./assets/main.ts",
      ...blockEntries,
    },
    mode: DEBUG ? "development" : "production",
    output: {
      path: path.resolve("./build"),
      filename: "[name].js",
      clean: true,
    },
    plugins: [
      new DefinePlugin({
        //"process.env.VAR": "value",
      }),
      new MiniCssExtractPlugin({
        filename: "[name].css",
        chunkFilename: "[id].css",
      }),
      // Uses WordPress's own copies of @wordpress/* packages and React, and
      // writes an index.asset.php listing the scripts each entry depends on
      new DependencyExtractionWebpackPlugin(),
    ],
    module: {
      rules: [
        {
          test: /\.tsx?$/,
          use: "ts-loader",
          exclude: /node_modules/,
        },
        {
          test: /\.s?css$/i,
          use: [
            MiniCssExtractPlugin.loader,
            {
              loader: "css-loader",
            },
            {
              loader: "postcss-loader",
            },
            {
              loader: "sass-loader",
            },
          ],
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
      alias: {
        "@": path.resolve("./assets"),
      },
      extensions: [".ts", ".tsx", ".js", ".json"],
    },
    target: "web",
    watchOptions: {
      aggregateTimeout: 200,
      poll: 1000,
      ignored: ["build/**/*"],
    },
  },
];
