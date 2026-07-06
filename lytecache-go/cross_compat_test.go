package lytecache_test

import (
	"database/sql"
	"errors"
	"testing"
	"time"

	lytecache "github.com/YOURUSERNAME/lytecache-go"
)

// TestCrossCompatibilityRawRows inserts rows via raw SQL exactly as SPEC.md
// describes, mirroring the fixture used by the Python, Java, and Node.js
// test suites: type codes 0-4 must read back correctly, and a fake code-5
// (Python-pickle-only) row must produce ErrSerialization rather than
// silently returning garbage.
func TestCrossCompatibilityRawRows(t *testing.T) {
	path := tempDBPath(t)

	c, err := lytecache.New(lytecache.WithPath(path))
	if err != nil {
		t.Fatal(err)
	}
	defer func() { _ = c.Close() }()

	// Touch the cache once so the file and schema definitely exist before a
	// second, raw connection opens it.
	if err := c.Set("bootstrap", "x"); err != nil {
		t.Fatal(err)
	}
	if _, err := c.Delete("bootstrap"); err != nil {
		t.Fatal(err)
	}

	raw, err := sql.Open("sqlite", path)
	if err != nil {
		t.Fatal(err)
	}
	defer func() { _ = raw.Close() }()

	now := time.Now().UnixMilli()
	insert := func(key string, value []byte, valueType int) {
		t.Helper()
		_, err := raw.Exec(`
INSERT INTO cache (key, namespace, value, value_type, created_at, expires_at, last_accessed, access_count, size_bytes)
VALUES (?, 'default', ?, ?, ?, NULL, ?, 0, ?)`, key, value, valueType, now, now, len(value))
		if err != nil {
			t.Fatalf("inserting raw row for %q: %v", key, err)
		}
	}

	insert("bytes", []byte{1, 2, 3}, 0)
	insert("string", []byte("hello"), 1)
	insert("int", []byte("42"), 2)
	insert("float", []byte("3.14"), 3)
	insert("json", []byte(`{"a":1}`), 4)
	insert("pickle", []byte("whatever a Python pickle looks like"), 5)

	if v, found, err := c.GetBytes("bytes"); err != nil || !found || string(v) != "\x01\x02\x03" {
		t.Fatalf("bytes: v=%v found=%v err=%v", v, found, err)
	}
	if v, found, err := c.GetString("string"); err != nil || !found || v != "hello" {
		t.Fatalf("string: v=%q found=%v err=%v", v, found, err)
	}
	if v, found, err := c.GetInt64("int"); err != nil || !found || v != 42 {
		t.Fatalf("int: v=%d found=%v err=%v", v, found, err)
	}
	if v, found, err := c.GetFloat64("float"); err != nil || !found || v != 3.14 {
		t.Fatalf("float: v=%v found=%v err=%v", v, found, err)
	}
	var m map[string]any
	if found, err := c.Get("json", &m); err != nil || !found || m["a"] != float64(1) {
		t.Fatalf("json: m=%v found=%v err=%v", m, found, err)
	}

	strictCache, err := lytecache.New(lytecache.WithPath(path), lytecache.WithStrict(true))
	if err != nil {
		t.Fatal(err)
	}
	defer func() { _ = strictCache.Close() }()

	var s string
	_, err = strictCache.Get("pickle", &s)
	if !errors.Is(err, lytecache.ErrSerialization) {
		t.Fatalf("expected ErrSerialization reading a value_type=5 row, got %v", err)
	}
}
