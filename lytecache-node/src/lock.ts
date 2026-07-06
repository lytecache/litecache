import { randomUUID } from "node:crypto";
import "./dispose-polyfill.js";
import { LockTimeoutError } from "./errors.js";
import { sleepSyncMs } from "./sleep-sync.js";

/** The minimal surface {@link CacheLock} needs from {@link LyteCache}, to avoid a circular import. */
export interface LockHost {
  tryAcquireLock(lockKey: string, token: string, ttlSeconds: number): boolean;
  releaseLock(lockKey: string, token: string): boolean;
}

export interface LockOptions {
  /** How long to wait to acquire before throwing {@link LockTimeoutError}. Default 30000. */
  timeoutMs?: number;
  /** How often to retry while waiting. Default 50. */
  pollMs?: number;
}

/**
 * A process-safe distributed lock built on the cache's atomic {@link LyteCache.add} semantics:
 * acquiring is a `SET NX` on a `"lock:" + name` key, so at most one holder -- across threads and
 * processes -- can hold it at a time.
 *
 * The lock's TTL equals the acquisition timeout: if the holder crashes without releasing, the
 * lock expires and becomes available again rather than being stuck forever. Each acquisition
 * writes a unique random token as the lock's value, so release only ever removes a lock this
 * instance still holds -- never one that already expired and was re-acquired by someone else.
 *
 * Supports `using` (TC39 explicit resource management) via `Symbol.dispose`.
 */
export class CacheLock {
  private readonly host: LockHost;
  private readonly lockKey: string;
  private readonly token = randomUUID();
  private readonly ttlSeconds: number;
  private released = false;

  constructor(host: LockHost, name: string, options: LockOptions = {}) {
    this.host = host;
    this.lockKey = `lock:${name}`;
    const timeoutMs = options.timeoutMs ?? 30_000;
    const pollMs = options.pollMs ?? 50;
    this.ttlSeconds = timeoutMs / 1000;
    this.acquire(timeoutMs, pollMs);
  }

  private acquire(timeoutMs: number, pollMs: number): void {
    const deadline = Date.now() + timeoutMs;
    for (;;) {
      if (this.host.tryAcquireLock(this.lockKey, this.token, this.ttlSeconds)) {
        return;
      }
      if (Date.now() >= deadline) {
        throw new LockTimeoutError(
          `could not acquire lock "${this.lockKey.slice(5)}" within ${timeoutMs}ms`,
        );
      }
      sleepSyncMs(pollMs);
    }
  }

  /** Releases the lock, if this instance still holds it. Safe to call more than once. */
  release(): void {
    if (!this.released) {
      this.released = true;
      this.host.releaseLock(this.lockKey, this.token);
    }
  }

  [Symbol.dispose](): void {
    this.release();
  }
}
