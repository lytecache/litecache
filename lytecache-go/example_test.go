package lytecache_test

import (
	"fmt"
	"os"
	"path/filepath"
	"time"

	lytecache "github.com/lytecache/lytecache-go"
)

// exampleDBPath gives each example (other than the zero-config one below)
// its own throwaway file, so the examples are deterministic and don't
// collide with each other or with a real project's cache file.
func exampleDBPath(name string) string {
	dir, err := os.MkdirTemp("", "lytecache-example-"+name)
	if err != nil {
		panic(err)
	}
	return filepath.Join(dir, "cache.db")
}

// Example demonstrates the zero-configuration flagship usage: no path, no
// setup. New creates the database file (and any missing parent
// directories) on first use.
func Example() {
	c, err := lytecache.New()
	if err != nil {
		panic(err)
	}
	defer func() { _ = c.Close() }()

	if err := c.Set("k", map[string]any{"name": "Ada"}); err != nil {
		panic(err)
	}

	var v map[string]any
	if _, err := c.Get("k", &v); err != nil {
		panic(err)
	}
	fmt.Println(v)
	// Output: map[name:Ada]
}

// ExampleCache_Incr demonstrates atomic counters. Incr/Decr/IncrFloat are
// each a single SQL UPSERT, so they stay correct under concurrent access
// from many goroutines or many processes sharing the file.
func ExampleCache_Incr() {
	c, err := lytecache.New(lytecache.WithPath(exampleDBPath("incr")))
	if err != nil {
		panic(err)
	}
	defer func() { _ = c.Close() }()

	n, _ := c.Incr("hits", 1)
	fmt.Println(n)
	n, _ = c.Incr("hits", 5)
	fmt.Println(n)
	n, _ = c.Decr("hits", 2)
	fmt.Println(n)
	// Output:
	// 1
	// 6
	// 4
}

// ExampleCache_Add demonstrates atomic set-if-absent (Add, Redis "SET NX")
// and set-if-present (Replace, Redis "SET XX").
func ExampleCache_Add() {
	c, err := lytecache.New(lytecache.WithPath(exampleDBPath("add")))
	if err != nil {
		panic(err)
	}
	defer func() { _ = c.Close() }()

	ok, _ := c.Add("key", "first")
	fmt.Println("add:", ok)
	ok, _ = c.Add("key", "second")
	fmt.Println("add again:", ok)

	ok, _ = c.Replace("key", "replaced")
	fmt.Println("replace:", ok)
	ok, _ = c.Replace("missing", "value")
	fmt.Println("replace missing:", ok)

	s, _, _ := c.GetString("key")
	fmt.Println("value:", s)
	// Output:
	// add: true
	// add again: false
	// replace: true
	// replace missing: false
	// value: replaced
}

// ExampleTTL demonstrates expiration. A key set with TTL expires on its
// own; TTLOf reports the remaining time.
func ExampleTTL() {
	c, err := lytecache.New(lytecache.WithPath(exampleDBPath("ttl")))
	if err != nil {
		panic(err)
	}
	defer func() { _ = c.Close() }()

	if err := c.Set("session", "42", lytecache.TTL(5*time.Minute)); err != nil {
		panic(err)
	}

	_, hasExpiry, found, _ := c.TTLOf("session")
	fmt.Println("found:", found, "has expiry:", hasExpiry)

	if _, err := c.Persist("session"); err != nil {
		panic(err)
	}
	_, hasExpiry, found, _ = c.TTLOf("session")
	fmt.Println("found:", found, "has expiry:", hasExpiry)
	// Output:
	// found: true has expiry: true
	// found: true has expiry: false
}

// ExampleCache_Keys demonstrates lazily scanning keys with a GLOB pattern.
func ExampleCache_Keys() {
	c, err := lytecache.New(lytecache.WithPath(exampleDBPath("keys")))
	if err != nil {
		panic(err)
	}
	defer func() { _ = c.Close() }()

	if err := c.SetMany(map[string]any{
		"session:1": "a",
		"session:2": "b",
		"user:1":    "c",
	}); err != nil {
		panic(err)
	}

	var matched []string
	for key, err := range c.Keys("session:*") {
		if err != nil {
			panic(err)
		}
		matched = append(matched, key)
	}
	fmt.Println(len(matched))
	// Output: 2
}

// ExampleMemoize demonstrates the read-through caching helper. Memoize is
// a package-level generic function, since Go methods cannot have type
// parameters.
func ExampleMemoize() {
	c, err := lytecache.New(lytecache.WithPath(exampleDBPath("memoize")))
	if err != nil {
		panic(err)
	}
	defer func() { _ = c.Close() }()

	calls := 0
	compute := func() (int, error) {
		calls++
		return 42, nil
	}

	v1, _ := lytecache.Memoize(c, "answer", time.Hour, compute)
	v2, _ := lytecache.Memoize(c, "answer", time.Hour, compute)
	fmt.Println(v1, v2, "calls:", calls)
	// Output: 42 42 calls: 1
}

// ExampleCache_Lock demonstrates the process-safe distributed lock.
func ExampleCache_Lock() {
	c, err := lytecache.New(lytecache.WithPath(exampleDBPath("lock")))
	if err != nil {
		panic(err)
	}
	defer func() { _ = c.Close() }()

	lock, err := c.Lock("resource", 5*time.Second)
	if err != nil {
		panic(err)
	}
	fmt.Println("locked")
	if err := lock.Release(); err != nil {
		panic(err)
	}
	fmt.Println("released")
	// Output:
	// locked
	// released
}
