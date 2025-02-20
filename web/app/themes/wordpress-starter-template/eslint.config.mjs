import eslint from "@eslint/js";
import prettier from "eslint-config-prettier";
import importPlugin from "eslint-plugin-import";
import globals from "globals";
import tseslint from "typescript-eslint";

export default tseslint.config(
  {
    files: ["**/*.{js,mjs,ts}"],
    extends: [
      eslint.configs.recommended,
      importPlugin.flatConfigs.recommended,
      importPlugin.flatConfigs.typescript,
      tseslint.configs.strict,
      tseslint.configs.stylistic,
      prettier,
    ],
    languageOptions: {
      globals: globals.browser,
    },
    rules: {
      "import/order": [
        "error",
        {
          alphabetize: {
            order: "asc",
            caseInsensitive: true,
          },
          groups: [
            "builtin",
            "external",
            "internal",
            "parent",
            "sibling",
            "index",
            "object",
            "type",
          ],
        },
      ],
      // Disable resolve checking as this is handled by typescript
      "import/no-unresolved": "off",
      // Import sorting is handled by the import/order rule.
      // However, sort-imports also sorts symbols inside a multi-import
      //     e.g. import { a, b, c } from "d"
      // Setting `ignoreDeclarationSort: true` restricts it to only this behavior.
      "sort-imports": [
        "error",
        {
          ignoreDeclarationSort: true,
        },
      ],
    },
  },
  {
    files: ["postcss.config.js", "tailwind.config.js", "webpack.config.js"],
    languageOptions: {
      globals: { ...globals.node },
      sourceType: "commonjs",
    },
    rules: {
      "@typescript-eslint/no-require-imports": "off",
    },
  },
  {
    ignores: ["build"],
  },
);
