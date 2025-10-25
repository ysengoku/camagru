export default {
  parser: '@typescript-eslint/parser',       // TypeScript parser
  parserOptions: {       
    ecmaVersion: 'latest',                   // Latest ECMAScript features
    sourceType: 'module',                    // Use ES modules
  },
  plugins: ['@typescript-eslint'],           // TypeScript ESLint plugin
  extends: [
    'eslint:recommended',                    // ESLint recommended rules
    'plugin:@typescript-eslint/recommended', // TypeScript recommended rules
  ] as const,
  rules: {
    '@typescript-eslint/no-unused-vars': ['warn'],                     // Warn on unused variables
    'no-console': 'off',                                               // Allow console.log
    'max-len': ['error', { code: 120, ignoreTemplateLiterals: true }], // Max line length
    'no-tabs': 'off',                                                  // Allow tabs
    semi: ['error', 'always'],                                         // Require semicolons
    'object-curly-spacing': ['error', 'always'],                       // Enforce spacing in object braces
    quotes: ['error', 'single'],                                       // Enforce single quotes
    indent: ['error', 2],                                              // Indent with 2 spaces
    camelcase: ['error', { properties: 'never' }],                     // Ignore camelCase for object properties
    'no-undef': 'off',                                                 // Disable no-undef for TypeScript
  } as const,
} as const;
