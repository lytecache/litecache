import { execFileSync } from "node:child_process";
import { fileURLToPath, pathToFileURL } from "node:url";
import { dirname, join } from "node:path";
import { describe, expect, it } from "vitest";
import { tempDbPath } from "./helpers.js";

const __dirname = dirname(fileURLToPath(import.meta.url));
const distDir = join(__dirname, "..", "dist");

describe("dual package (require + import)", () => {
  it("works via require() (CommonJS)", () => {
    const path = tempDbPath();
    const script = `
      const { LyteCache } = require(${JSON.stringify(join(distDir, "index.cjs"))});
      const c = new LyteCache({ path: ${JSON.stringify(path)}, sweepInterval: null });
      c.set("k", { name: "Ada" });
      console.log(JSON.stringify(c.get("k")));
      c.close();
    `;
    const output = execFileSync(process.execPath, ["-e", script], { encoding: "utf8" });
    expect(JSON.parse(output.trim())).toEqual({ name: "Ada" });
  });

  it("works via import (ESM)", () => {
    const path = tempDbPath();
    // A bare OS path (e.g. Windows' "D:\...") is not a valid ESM module specifier -- Node's
    // ESM loader parses "D:" as a URL scheme and rejects it. Absolute paths must be file://
    // URLs, unlike require() below, which accepts a plain filesystem path directly.
    const script = `
      import { LyteCache } from ${JSON.stringify(pathToFileURL(join(distDir, "index.js")).href)};
      const c = new LyteCache({ path: ${JSON.stringify(path)}, sweepInterval: null });
      c.set("k", { name: "Ada" });
      console.log(JSON.stringify(c.get("k")));
      c.close();
    `;
    const output = execFileSync(process.execPath, ["--input-type=module", "-e", script], {
      encoding: "utf8",
    });
    expect(JSON.parse(output.trim())).toEqual({ name: "Ada" });
  });
});
