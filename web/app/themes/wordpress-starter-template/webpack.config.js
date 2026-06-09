const path = require("path");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const { DefinePlugin } = require("webpack");

const DEBUG = process.env.DEBUG === "true" ? true : false;

module.exports = [
  {
    context: __dirname,
    devServer: {
      compress: true,
      headers: {
        "Access-Control-Allow-Origin": "*",
      },
      port: 9000,
    },
    devtool: DEBUG ? "eval-source-map" : false,
    entry: {
      main: "./assets/main.tsx",
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
    ],
    module: {
      rules: [
        {
          test: /\.tsx?$/,
          use: "ts-loader",
          exclude: /node_modules/,
        },
        {
          test: /\.css$/i,
          use: [
            MiniCssExtractPlugin.loader,
            {
              loader: "css-loader",
            },
            {
              loader: "postcss-loader",
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
      extensions: [".ts", ".tsx", ".js"],
    },
    target: "web",
    watchOptions: {
      aggregateTimeout: 200,
      poll: 1000,
      ignored: ["build/**/*"],
    },
  },
];
