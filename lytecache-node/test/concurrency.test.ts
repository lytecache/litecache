import { fork } from "node:child_process";
import { fileURLToPath } from "node:url";
import { dirname, join } from "node:path";
import { writeFileSync } from "node:fs";
import { readFile } from "node:fs/promises";
import { describe, expect, it } from "vitest";
import { LyteCache } from "../src/index.js";
import { tempDbPath } from "./helpers.js";

const __dirname = dirname(fileURLToPath(import.meta.url));

function runWorker(script: string, args: string[]): Promise<number> {
  return new Promise((resolve, reject) => {
    const child = fork(join(__dirname, "workers", script), args, { stdio: "inherit" });
    child.on("error", reject);
    child.on("exit", (code) => resolve(code ?? -1));
  });
}

describe("cross-process atomicity", () => {
  it("incr is atomic across processes sharing one file", async () => {
    const path = tempDbPath();
    const processCount = 4;
    const iterationsPerProcess = 200;

    const exitCodes = await Promise.all(
      Array.from({ length: processCount }, () =>
        runWorker("incr-worker.cjs", [path, "counter", String(iterationsPerProcess)]),
      ),
    );
    for (const code of exitCodes) expect(code).toBe(0);

    const cache = new LyteCache({ path, sweepInterval: null });
    try {
      expect(cache.get("counter")).toBe(processCount * iterationsPerProcess);
    } finally {
      cache.close();
    }
  }, 30000);

  it("a distributed lock excludes concurrent holders across processes", async () => {
    const path = tempDbPath();
    const logFile = tempDbPath().replace(/\.db$/, ".log");
    writeFileSync(logFile, "");
    const processCount = 4;
    const holdMs = 150;

    const exitCodes = await Promise.all(
      Array.from({ length: processCount }, () =>
        runWorker("lock-worker.cjs", [path, "shared-lock", logFile, String(holdMs)]),
      ),
    );
    for (const code of exitCodes) expect(code).toBe(0);

    const content = await readFile(logFile, "utf8");
    const lines = content.trim().split("\n").filter(Boolean);
    expect(lines).toHaveLength(processCount * 2);

    const windows: { start: number; end: number }[] = [];
    for (let i = 0; i < lines.length; i += 2) {
      const startParts = lines[i]!.split(" ");
      const endParts = lines[i + 1]!.split(" ");
      expect(startParts[0]).toBe("START");
      expect(endParts[0]).toBe("END");
      expect(startParts[1]).toBe(endParts[1]); // same marker, in order
      windows.push({ start: Number(startParts[2]), end: Number(endParts[2]) });
    }

    windows.sort((a, b) => a.start - b.start);
    for (let i = 1; i < windows.length; i++) {
      expect(windows[i]!.start).toBeGreaterThanOrEqual(windows[i - 1]!.end);
    }
  }, 30000);
});
