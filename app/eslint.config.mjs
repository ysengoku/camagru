import globals from "globals";
import tseslint from "@typescript-eslint/eslint-plugin";
import tsparser from "@typescript-eslint/parser";
import prettierPlugin from "eslint-plugin-prettier";
import prettierConfig from "eslint-config-prettier";

export default [
  {
    files: ["**/*.ts"],
    ignores: ["node_modules", "dist", "build"],

    languageOptions: {
      parser: tsparser,
      sourceType: "module",
      globals: {
        ...globals.browser,
        ...globals.node,
      },
    },

    plugins: {
      "@typescript-eslint": tseslint,
      prettier: prettierPlugin,
    },

    rules: {
      ...tseslint.configs.recommended.rules,

      "@typescript-eslint/no-unused-vars": "warn",
      "no-console": "warn",
      "max-len": ["error", { code: 80, ignoreTemplateLiterals: true }],
      "no-tabs": "off",
      "semi": ["error", "always"],
      "object-curly-spacing": ["error", "always"],
      "quotes": ["error", "single"],
      "indent": ["error", 2],
      "camelcase": ["error", { properties: "never" }],
      "no-undef": "off",
      "prettier/prettier": "error",
      'indent': 'off',
      '@typescript-eslint/indent': 'off',

      ...prettierConfig.rules,
    },
  },
];
