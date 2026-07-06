import { defineConfig } from "tsup";

export default defineConfig({
  entry: ["src/index.ts"],
  format: ["esm", "cjs"],
  dts: true,
  sourcemap: true,
  clean: true,
  target: "node18",
  // better-sqlite3 ships a native binding; it must be required at runtime by
  // the consumer's own node_modules, never bundled.
  external: ["better-sqlite3"],
  skipNodeModulesBundle: true,
});
