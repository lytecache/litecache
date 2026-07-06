// Test helper (not a test itself): launched as a separate OS process by concurrency.test.ts to
// verify that LyteCache#incr is atomic across processes sharing one SQLite file, not just
// synchronous calls within a single Node process. Requires the built package (dist/index.cjs)
// rather than the TS source, since child_process.fork() runs plain Node modules.
const { LyteCache } = require("../../dist/index.cjs");

const [, , dbPath, key, iterationsRaw] = process.argv;
const iterations = Number.parseInt(iterationsRaw, 10);

const cache = new LyteCache({ path: dbPath, sweepInterval: null });
for (let i = 0; i < iterations; i++) {
  cache.incr(key);
}
cache.close();
