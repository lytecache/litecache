package lytecache_test

import (
	"fmt"
	"sort"
	"testing"

	lytecache "github.com/lytecache/lytecache-go"
)

func TestKeysGlobPattern(t *testing.T) {
	c := newTestCache(t)
	entries := map[string]any{
		"session:1": "a",
		"session:2": "b",
		"user:1":    "c",
	}
	if err := c.SetMany(entries); err != nil {
		t.Fatal(err)
	}

	var got []string
	for k, err := range c.Keys("session:*") {
		if err != nil {
			t.Fatal(err)
		}
		got = append(got, k)
	}
	sort.Strings(got)
	want := []string{"session:1", "session:2"}
	if fmt.Sprint(got) != fmt.Sprint(want) {
		t.Fatalf("got %v, want %v", got, want)
	}
}

func TestKeysEmptyPatternMatchesAll(t *testing.T) {
	c := newTestCache(t)
	if err := c.SetMany(map[string]any{"a": 1, "b": 2, "c": 3}); err != nil {
		t.Fatal(err)
	}
	count := 0
	for _, err := range c.Keys("") {
		if err != nil {
			t.Fatal(err)
		}
		count++
	}
	if count != 3 {
		t.Fatalf("expected 3 keys, got %d", count)
	}
}

func TestKeysEarlyBreak(t *testing.T) {
	c := newTestCache(t)
	if err := c.SetMany(map[string]any{"a": 1, "b": 2, "c": 3}); err != nil {
		t.Fatal(err)
	}
	count := 0
	for range c.Keys("*") {
		count++
		break
	}
	if count != 1 {
		t.Fatalf("expected iteration to stop after 1, got %d", count)
	}
}

func TestKeysExcludesExpired(t *testing.T) {
	c := newTestCache(t)
	if err := c.Set("gone", "v", lytecache.TTL(0)); err != nil {
		t.Fatal(err)
	}
	if err := c.Set("here", "v"); err != nil {
		t.Fatal(err)
	}

	var got []string
	for k, err := range c.Keys("*") {
		if err != nil {
			t.Fatal(err)
		}
		got = append(got, k)
	}
	if len(got) != 1 || got[0] != "here" {
		t.Fatalf("expected only [\"here\"], got %v", got)
	}
}

func TestKeysCrossesBatchBoundary(t *testing.T) {
	c := newTestCache(t)
	entries := make(map[string]any, 550)
	for i := 0; i < 550; i++ {
		entries[fmt.Sprintf("k:%04d", i)] = i
	}
	if err := c.SetMany(entries); err != nil {
		t.Fatal(err)
	}

	count := 0
	for _, err := range c.Keys("*") {
		if err != nil {
			t.Fatal(err)
		}
		count++
	}
	if count != 550 {
		t.Fatalf("expected 550 keys (crossing the internal batch size), got %d", count)
	}
}
