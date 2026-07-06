import Database from "better-sqlite3";
import { describe, expect, it } from "vitest";
import { LyteCache, SerializationError } from "../src/index.js";
import { tempDbPath } from "./helpers.js";

/**
 * Guards the cross-language storage contract described in SPEC.md: a SQLite file built by hand
 * per the schema (standing in for one written by the Python or Java implementation) must be
 * readable by LyteCache exactly as documented -- byte for byte per type code, with no silent
 * misinterpretation. Mirrors the equivalent fixture tests in litecache-python and lytecache-java.
 */
describe("cross-compatibility fixture", () => {
  it("reads every portable type code from a hand-built fixture", () => {
    const path = tempDbPath();
    buildFixture(path);

    const cache = new LyteCache({ path, sweepInterval: null });
    try {
      expect(cache.get("greeting")).toBe("hello from another language");
      expect(cache.get<Buffer>("raw")).toEqual(Buffer.from([1, 2, 3, 4, 5]));
      expect(cache.get("count")).toBe(42);
      expect(cache.get<number>("ratio")).toBeCloseTo(3.14, 5);
      expect(cache.get("person")).toEqual({ name: "Ada", createdAt: "2024-01-15T10:30:00" });
      // Already expired in the fixture: must read as a miss, not stale data.
      expect(cache.get("expired")).toBeUndefined();
    } finally {
      cache.close();
    }
  });

  it("raises SerializationError for a foreign-language type code instead of returning raw bytes", () => {
    const path = tempDbPath();
    const db = new Database(path);
    db.exec(SCHEMA_SQL);
    insertRow(db, "pickled", 5, Buffer.from("would-be-pickle-bytes"));
    db.close();

    const cache = new LyteCache({ path, sweepInterval: null, strict: true });
    try {
      expect(() => cache.get("pickled")).toThrow(SerializationError);
    } finally {
      cache.close();
    }
  });

  it("writes the same wire format it reads: an int counter is plain decimal text, not binary", () => {
    const path = tempDbPath();
    const cache = new LyteCache({ path, sweepInterval: null });
    cache.set("counter", 42);
    cache.set("pi", 3.5);
    cache.close();

    const db = new Database(path, { readonly: true });
    try {
      const counterRow = db
        .prepare("SELECT value, value_type FROM cache WHERE key = 'counter'")
        .get() as { value: Buffer; value_type: number };
      expect(counterRow.value.toString("utf8")).toBe("42");
      expect(counterRow.value_type).toBe(2);

      const piRow = db.prepare("SELECT value, value_type FROM cache WHERE key = 'pi'").get() as {
        value: Buffer;
        value_type: number;
      };
      expect(piRow.value.toString("utf8")).toBe("3.5");
      expect(piRow.value_type).toBe(3);
    } finally {
      db.close();
    }
  });
});

const SCHEMA_SQL = `
CREATE TABLE cache (
  key            TEXT    NOT NULL,
  namespace      TEXT    NOT NULL DEFAULT 'default',
  value          BLOB    NOT NULL,
  value_type     INTEGER NOT NULL DEFAULT 0,
  created_at     INTEGER NOT NULL,
  expires_at     INTEGER,
  last_accessed  INTEGER NOT NULL,
  access_count   INTEGER NOT NULL DEFAULT 0,
  size_bytes     INTEGER NOT NULL,
  PRIMARY KEY (namespace, key)
) WITHOUT ROWID;
CREATE TABLE meta (k TEXT PRIMARY KEY, v TEXT NOT NULL);
INSERT INTO meta (k, v) VALUES ('schema_version', '1');
`;

function insertRow(
  db: InstanceType<typeof Database>,
  key: string,
  typeCode: number,
  value: Buffer,
  createdAt = Date.now(),
  expiresAt: number | null = null,
): void {
  db.prepare(
    `INSERT INTO cache (key, namespace, value, value_type, created_at, expires_at, last_accessed, access_count, size_bytes)
     VALUES (?, 'default', ?, ?, ?, ?, ?, 0, ?)`,
  ).run(key, value, typeCode, createdAt, expiresAt, createdAt, value.length);
}

/** Builds a SQLite file by hand, using nothing but raw better-sqlite3 and the schema/type-code
 * table from SPEC.md -- standing in for a file written by another language's implementation. */
function buildFixture(path: string): void {
  const db = new Database(path);
  db.exec(SCHEMA_SQL);
  const now = Date.now();
  insertRow(db, "greeting", 1, Buffer.from("hello from another language", "utf8"), now);
  insertRow(db, "raw", 0, Buffer.from([1, 2, 3, 4, 5]), now);
  insertRow(db, "count", 2, Buffer.from("42", "utf8"), now);
  insertRow(db, "ratio", 3, Buffer.from("3.14", "utf8"), now);
  insertRow(
    db,
    "person",
    4,
    Buffer.from(JSON.stringify({ name: "Ada", createdAt: "2024-01-15T10:30:00" }), "utf8"),
    now,
  );
  insertRow(db, "expired", 1, Buffer.from("gone", "utf8"), now - 10_000, now - 5_000);
  db.close();
}
