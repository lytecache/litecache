// Test helper (not a test itself): launched as a separate OS process by concurrency.test.ts to
// verify that CacheLock excludes concurrent holders across processes, not just within one Node
// process. Acquires the named lock, appends a START line, holds it for holdMs, appends an END
// line, then releases. fs.appendFileSync with the default "a" flag maps to O_APPEND, which POSIX
// guarantees is atomic for a single short write -- enough for these one-line markers not to
// interleave across processes.
const fs = require("node:fs");
const { LyteCache } = require("../../dist/index.cjs");

const [, , dbPath, lockName, logFile, holdMsRaw] = process.argv;
const holdMs = Number.parseInt(holdMsRaw, 10);
const marker = `${process.pid}-${process.hrtime.bigint()}`;

function sleepSyncMs(ms) {
  const sab = new Int32Array(new SharedArrayBuffer(4));
  Atomics.wait(sab, 0, 0, ms);
}

const cache = new LyteCache({ path: dbPath, sweepInterval: null });
const lock = cache.lock(lockName, { timeoutMs: 30000 });
try {
  fs.appendFileSync(logFile, `START ${marker} ${Date.now()}\n`);
  sleepSyncMs(holdMs);
  fs.appendFileSync(logFile, `END ${marker} ${Date.now()}\n`);
} finally {
  lock.release();
  cache.close();
}
