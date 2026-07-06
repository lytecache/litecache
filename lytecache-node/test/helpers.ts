import { mkdtempSync } from "node:fs";
import { tmpdir } from "node:os";
import { join } from "node:path";
import { randomUUID } from "node:crypto";

/** A fresh temp directory per call, so tests never share a database file. */
export function tempDir(): string {
  return mkdtempSync(join(tmpdir(), "lytecache-test-"));
}

/** A fresh, non-existent `.db` path inside a fresh temp directory. */
export function tempDbPath(): string {
  return join(tempDir(), `${randomUUID()}.db`);
}
