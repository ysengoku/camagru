import { defineConfig } from 'vite';

const allowedHosts = ['client', 'localhost', '127.0.0.1'];
if (process.env.APP_HOST) {
  allowedHosts.push(process.env.APP_HOST);
}

export default defineConfig({
  root: '.',
  build: {
    outDir: '../public/assets',
    emptyOutDir: false,
    manifest : true,
    rollupOptions: {
      input: {
        main: './js/main.js',
        studio: './js/studio/entry.js',
        feed: './js/feed/entry.js',
        auth: './js/auth/entry.js',
        profile: './js/profile/entry.js',
        post: './js/post/entry.js',
        error: './js/error/entry.js',
      },
      output: {
        entryFileNames: '[name].js',
        assetFileNames: '[name]-[hash][extname]',
      },
    },
  },
  server: {
    host: '0.0.0.0',
    port: 5173,
    middlewareMode: false,
    allowedHosts: allowedHosts,
    hmr: false,
    watch: {
      usePolling: true,
    },
  },
});
