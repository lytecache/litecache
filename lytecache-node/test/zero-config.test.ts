import { existsSync, mkdtempSync, rmSync } from "node:fs";
import { tmpdir } from "node:os";
import { join } from "node:path";
import { fileURLToPath } from "node:url";
import { afterEach, beforeEach, describe, expect, it } from "vitest";
import { LyteCache } from "../src/index.js";
import { defaultPath } from "../src/paths.js";
import { tempDbPath, tempDir } from "./helpers.js";

describe("zero-config path resolution", () => {
  let originalEnv: string | undefined;
  beforeEach(() => {
    originalEnv = process.env.LYTECACHE_PATH;
  });
  afterEach(() => {
    if (originalEnv === undefined) delete process.env.LYTECACHE_PATH;
    else process.env.LYTECACHE_PATH = originalEnv;
  });

  it("creates the file and missing parent directories on first use", () => {
    const dir = tempDir();
    const nested = join(dir, "does", "not", "exist", "yet", "cache.db");
    expect(existsSync(join(dir, "does"))).toBe(false);

    const cache = new LyteCache({ path: nested, sweepInterval: null });
    cache.set("k", "v");
    cache.close();

    expect(existsSync(nested)).toBe(true);
  });

  it("LYTECACHE_PATH overrides the default path", () => {
    const overridePath = tempDbPath();
    process.env.LYTECACHE_PATH = overridePath;
    expect(defaultPath()).toBe(overridePath);

    const cache = new LyteCache({ sweepInterval: null });
    try {
      expect(cache.path).toBe(overridePath);
      cache.set("k", "v");
      expect(existsSync(overridePath)).toBe(true);
    } finally {
      cache.close();
    }
  });

  it("two different working directories resolve to two different default paths", () => {
    delete process.env.LYTECACHE_PATH;
    const dirA = mkdtempSync(join(tmpdir(), "lytecache-cwd-a-"));
    const dirB = mkdtempSync(join(tmpdir(), "lytecache-cwd-b-"));
    try {
      const pathA = defaultPath(dirA);
      const pathB = defaultPath(dirB);
      expect(pathA).not.toBe(pathB);
      // Same directory queried twice must be stable.
      expect(defaultPath(dirA)).toBe(pathA);
    } finally {
      rmSync(dirA, { recursive: true, force: true });
      rmSync(dirB, { recursive: true, force: true });
    }
  });

  it("LyteCache.defaultPath() matches the module-level defaultPath()", () => {
    delete process.env.LYTECACHE_PATH;
    expect(LyteCache.defaultPath()).toBe(defaultPath());
  });
});

describe("persistence", () => {
  it("data survives close and reopen", () => {
    const path = tempDbPath();
    const first = new LyteCache({ path, sweepInterval: null });
    first.set("persistent", "value");
    first.close();

    const second = new LyteCache({ path, sweepInterval: null });
    try {
      expect(second.get("persistent")).toBe("value");
    } finally {
      second.close();
    }
  });
});

describe("sweeper does not keep the process alive", () => {
  it("a script using the cache with a sweeper exits promptly without close()", async () => {
    const { spawnSync } = await import("node:child_process");
    const path = tempDbPath();
    const script = `
      const { LyteCache } = require(${JSON.stringify(
        // fileURLToPath, not .pathname -- .pathname leaves a leading slash before a Windows
        // drive letter (e.g. "/D:/..."), which isn't a valid path for require() to resolve.
        fileURLToPath(new URL("../dist/index.cjs", import.meta.url)),
      )});
      const cache = new LyteCache({ path: ${JSON.stringify(path)}, sweepInterval: 60 });
      cache.set("k", "v");
      // Deliberately not calling cache.close() -- the sweeper's unref()'d timer must not keep
      // the event loop alive.
    `;
    const start = Date.now();
    const result = spawnSync(process.execPath, ["-e", script], { timeout: 5000 });
    const elapsedMs = Date.now() - start;
    expect(result.status).toBe(0);
    expect(elapsedMs).toBeLessThan(4000);
  });
});
