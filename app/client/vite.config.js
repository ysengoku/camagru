import { defineConfig } from "vite";

export default defineConfig({
  root: ".",
  build: {
    outDir: "../public/assets",
    emptyOutDir: false,
    rollupOptions: {
      input: {
        main: "./js/main.js",
      },
      output: {
        entryFileNames: "[name].js",
      },
    },
  },
  server: {
    host: "0.0.0.0",
    port: 5173,
    middlewareMode: false,
    allowedHosts: ["client", "localhost", "127.0.0.1"],
    hmr: false,
  },
});
