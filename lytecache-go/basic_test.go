package lytecache_test

import (
	"errors"
	"testing"

	lytecache "github.com/YOURUSERNAME/lytecache-go"
)

func TestSetGetString(t *testing.T) {
	c := newTestCache(t)
	if err := c.Set("k", "value"); err != nil {
		t.Fatal(err)
	}
	got, found, err := c.GetString("k")
	if err != nil || !found || got != "value" {
		t.Fatalf("got=%q found=%v err=%v", got, found, err)
	}
}

func TestSetGetBytes(t *testing.T) {
	c := newTestCache(t)
	want := []byte{0x00, 0x01, 0xff, 0x10}
	if err := c.Set("k", want); err != nil {
		t.Fatal(err)
	}
	got, found, err := c.GetBytes("k")
	if err != nil || !found || string(got) != string(want) {
		t.Fatalf("got=%v found=%v err=%v", got, found, err)
	}
}

func TestSetGetInt64(t *testing.T) {
	c := newTestCache(t)
	if err := c.Set("k", int64(42)); err != nil {
		t.Fatal(err)
	}
	got, found, err := c.GetInt64("k")
	if err != nil || !found || got != 42 {
		t.Fatalf("got=%d found=%v err=%v", got, found, err)
	}
}

func TestSetGetIntoIntVariants(t *testing.T) {
	c := newTestCache(t)
	if err := c.Set("k", 42); err != nil {
		t.Fatal(err)
	}
	var i int
	if found, err := c.Get("k", &i); err != nil || !found || i != 42 {
		t.Fatalf("int: got=%d found=%v err=%v", i, found, err)
	}
	var i32 int32
	if found, err := c.Get("k", &i32); err != nil || !found || i32 != 42 {
		t.Fatalf("int32: got=%d found=%v err=%v", i32, found, err)
	}
	var u uint
	if found, err := c.Get("k", &u); err != nil || !found || u != 42 {
		t.Fatalf("uint: got=%d found=%v err=%v", u, found, err)
	}
	var f float64
	if found, err := c.Get("k", &f); err != nil || !found || f != 42 {
		t.Fatalf("float64: got=%v found=%v err=%v", f, found, err)
	}
	var asAny any
	if found, err := c.Get("k", &asAny); err != nil || !found {
		t.Fatalf("any: found=%v err=%v", found, err)
	}
}

func TestSetGetFloat64(t *testing.T) {
	c := newTestCache(t)
	if err := c.Set("k", 3.14); err != nil {
		t.Fatal(err)
	}
	got, found, err := c.GetFloat64("k")
	if err != nil || !found || got != 3.14 {
		t.Fatalf("got=%v found=%v err=%v", got, found, err)
	}
}

func TestSetGetMap(t *testing.T) {
	c := newTestCache(t)
	if err := c.Set("k", map[string]any{"name": "Ada", "age": float64(30)}); err != nil {
		t.Fatal(err)
	}
	var v map[string]any
	found, err := c.Get("k", &v)
	if err != nil || !found {
		t.Fatalf("found=%v err=%v", found, err)
	}
	if v["name"] != "Ada" || v["age"] != float64(30) {
		t.Fatalf("got %#v", v)
	}
}

func TestGetMissingKey(t *testing.T) {
	c := newTestCache(t)
	var v string
	found, err := c.Get("nope", &v)
	if err != nil {
		t.Fatalf("expected nil error on miss, got %v", err)
	}
	if found {
		t.Fatal("expected found=false for missing key")
	}
}

func TestDelete(t *testing.T) {
	c := newTestCache(t)
	if err := c.Set("a", "1"); err != nil {
		t.Fatal(err)
	}
	if err := c.Set("b", "2"); err != nil {
		t.Fatal(err)
	}
	n, err := c.Delete("a", "b", "missing")
	if err != nil {
		t.Fatal(err)
	}
	if n != 2 {
		t.Fatalf("expected 2 deleted, got %d", n)
	}
	if exists, _ := c.Exists("a"); exists {
		t.Fatal("expected a to be deleted")
	}
}

func TestExists(t *testing.T) {
	c := newTestCache(t)
	if exists, err := c.Exists("k"); err != nil || exists {
		t.Fatalf("expected false, got %v err=%v", exists, err)
	}
	if err := c.Set("k", "v"); err != nil {
		t.Fatal(err)
	}
	if exists, err := c.Exists("k"); err != nil || !exists {
		t.Fatalf("expected true, got %v err=%v", exists, err)
	}
}

func TestOverwrite(t *testing.T) {
	c := newTestCache(t)
	if err := c.Set("k", "first"); err != nil {
		t.Fatal(err)
	}
	if err := c.Set("k", "second"); err != nil {
		t.Fatal(err)
	}
	got, found, err := c.GetString("k")
	if err != nil || !found || got != "second" {
		t.Fatalf("got=%q found=%v err=%v", got, found, err)
	}
}

func TestAddSetsOnlyIfAbsent(t *testing.T) {
	c := newTestCache(t)
	ok, err := c.Add("k", "first")
	if err != nil || !ok {
		t.Fatalf("expected first add to succeed: ok=%v err=%v", ok, err)
	}
	ok, err = c.Add("k", "second")
	if err != nil || ok {
		t.Fatalf("expected second add to fail: ok=%v err=%v", ok, err)
	}
	got, _, _ := c.GetString("k")
	if got != "first" {
		t.Fatalf("expected value to remain %q, got %q", "first", got)
	}
}

func TestReplaceSetsOnlyIfPresent(t *testing.T) {
	c := newTestCache(t)
	ok, err := c.Replace("missing", "value")
	if err != nil || ok {
		t.Fatalf("expected replace on missing key to fail: ok=%v err=%v", ok, err)
	}
	if err := c.Set("k", "first"); err != nil {
		t.Fatal(err)
	}
	ok, err = c.Replace("k", "second")
	if err != nil || !ok {
		t.Fatalf("expected replace to succeed: ok=%v err=%v", ok, err)
	}
	got, _, _ := c.GetString("k")
	if got != "second" {
		t.Fatalf("got %q", got)
	}
}

func TestGetSet(t *testing.T) {
	c := newTestCache(t)
	var dest string
	found, err := c.GetSet("k", "new", &dest)
	if err != nil || found {
		t.Fatalf("expected no previous value: found=%v err=%v", found, err)
	}

	found, err = c.GetSet("k", "newer", &dest)
	if err != nil || !found || dest != "new" {
		t.Fatalf("dest=%q found=%v err=%v", dest, found, err)
	}

	got, _, _ := c.GetString("k")
	if got != "newer" {
		t.Fatalf("got %q", got)
	}
}

func TestSetManyGetMany(t *testing.T) {
	c := newTestCache(t)
	err := c.SetMany(map[string]any{
		"a": "1",
		"b": "2",
	})
	if err != nil {
		t.Fatal(err)
	}

	results, err := c.GetMany([]string{"a", "b", "missing"})
	if err != nil {
		t.Fatal(err)
	}
	if len(results) != 2 {
		t.Fatalf("expected 2 results, got %d", len(results))
	}
	var a, b string
	if err := results["a"].Decode(&a); err != nil || a != "1" {
		t.Fatalf("a=%q err=%v", a, err)
	}
	if err := results["b"].Decode(&b); err != nil || b != "2" {
		t.Fatalf("b=%q err=%v", b, err)
	}
	if _, ok := results["missing"]; ok {
		t.Fatal("expected missing key to be absent from results")
	}
}

func TestFlush(t *testing.T) {
	c := newTestCache(t)
	if err := c.SetMany(map[string]any{"a": "1", "b": "2"}); err != nil {
		t.Fatal(err)
	}
	if err := c.Flush(); err != nil {
		t.Fatal(err)
	}
	if exists, _ := c.Exists("a"); exists {
		t.Fatal("expected a to be gone after flush")
	}
	if exists, _ := c.Exists("b"); exists {
		t.Fatal("expected b to be gone after flush")
	}
}

func TestNamespaceIsolation(t *testing.T) {
	path := tempDBPath(t)
	c1, err := lytecache.New(lytecache.WithPath(path), lytecache.WithNamespace("ns1"))
	if err != nil {
		t.Fatal(err)
	}
	defer func() { _ = c1.Close() }()
	c2, err := lytecache.New(lytecache.WithPath(path), lytecache.WithNamespace("ns2"))
	if err != nil {
		t.Fatal(err)
	}
	defer func() { _ = c2.Close() }()

	if err := c1.Set("k", "ns1-value"); err != nil {
		t.Fatal(err)
	}
	if exists, _ := c2.Exists("k"); exists {
		t.Fatal("expected namespaces to be isolated")
	}

	if err := c2.Set("k", "ns2-value"); err != nil {
		t.Fatal(err)
	}
	if err := c1.Flush(); err != nil {
		t.Fatal(err)
	}
	got, found, err := c2.GetString("k")
	if err != nil || !found || got != "ns2-value" {
		t.Fatalf("expected ns2's key to survive ns1's flush: got=%q found=%v err=%v", got, found, err)
	}
}

func TestCloseIsIdempotent(t *testing.T) {
	c, err := lytecache.New(lytecache.WithPath(tempDBPath(t)))
	if err != nil {
		t.Fatal(err)
	}
	if err := c.Close(); err != nil {
		t.Fatal(err)
	}
	if err := c.Close(); err != nil {
		t.Fatalf("second Close should be a no-op, got %v", err)
	}
}

func TestPath(t *testing.T) {
	path := tempDBPath(t)
	c, err := lytecache.New(lytecache.WithPath(path))
	if err != nil {
		t.Fatal(err)
	}
	defer func() { _ = c.Close() }()
	if c.Path() != path {
		t.Fatalf("expected %q, got %q", path, c.Path())
	}
}

func TestDecodeIncompatibleDestinationStrictModeErrors(t *testing.T) {
	c := newTestCache(t, lytecache.WithStrict(true))
	if err := c.Set("k", "a string"); err != nil {
		t.Fatal(err)
	}
	var n int64
	_, err := c.Get("k", &n)
	if err == nil {
		t.Fatal("expected an error decoding a string into *int64")
	}
	if !errors.Is(err, lytecache.ErrSerialization) {
		t.Fatalf("expected ErrSerialization, got %v", err)
	}
}

func TestDecodeIncompatibleDestinationNonStrictModeDegradesToMiss(t *testing.T) {
	c := newTestCache(t) // strict defaults to false
	if err := c.Set("k", "a string"); err != nil {
		t.Fatal(err)
	}
	var n int64
	found, err := c.Get("k", &n)
	if err != nil {
		t.Fatalf("expected non-strict mode to degrade to a miss, got error: %v", err)
	}
	if found {
		t.Fatal("expected found=false")
	}
}
