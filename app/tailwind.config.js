/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './src/**/*.ts',
    './src/**/**/*.ts',
  ],
  safelist: [
    'flex', 'flex-row', 'flex-col', 'min-h-screen', 'bg-cyan-900',
    'text-white', 'p-4', 'h-24', 'w-full',
    'hover:bg-cyan-700',
  ],
  theme: {
    extend: {},
  },
  plugins: [],
};
