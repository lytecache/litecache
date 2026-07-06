/**
 * Blocks the current thread for `ms` milliseconds. better-sqlite3 is synchronous by design, and a
 * couple of spots (lock polling, retrying the cold-start WAL race below) need to actually pause
 * rather than await something -- Atomics.wait on a throwaway SharedArrayBuffer is the standard way
 * to sleep synchronously in Node.
 */
export function sleepSyncMs(ms: number): void {
  const sab = new Int32Array(new SharedArrayBuffer(4));
  Atomics.wait(sab, 0, 0, ms);
}
