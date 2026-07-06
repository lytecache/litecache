package lytecache_test

import (
	"errors"
	"testing"
	"time"

	lytecache "github.com/YOURUSERNAME/lytecache-go"
)

func TestLRUEvictsLeastRecentlyUsedFirst(t *testing.T) {
	c := newTestCache(t, lytecache.WithMaxKeys(3), lytecache.WithEviction(lytecache.LRU))

	// last_accessed has millisecond resolution (matching the cross-language
	// wire format), so operations need to be spaced out to get a
	// deterministic order instead of colliding within the same millisecond.
	const tick = 5 * time.Millisecond

	if err := c.Set("a", "1"); err != nil {
		t.Fatal(err)
	}
	time.Sleep(tick)
	if err := c.Set("b", "2"); err != nil {
		t.Fatal(err)
	}
	time.Sleep(tick)
	if err := c.Set("c", "3"); err != nil {
		t.Fatal(err)
	}
	time.Sleep(tick)

	// Touch "a" so it's no longer the least-recently-used.
	var v string
	if _, err := c.Get("a", &v); err != nil {
		t.Fatal(err)
	}
	time.Sleep(tick)

	if err := c.Set("d", "4"); err != nil {
		t.Fatal(err)
	}

	if exists, _ := c.Exists("a"); !exists {
		t.Error("expected recently-touched key 'a' to survive")
	}
	if exists, _ := c.Exists("b"); exists {
		t.Error("expected least-recently-used key 'b' to have been evicted")
	}
	if exists, _ := c.Exists("c"); !exists {
		t.Error("expected 'c' to survive")
	}
	if exists, _ := c.Exists("d"); !exists {
		t.Error("expected newly-added 'd' to survive")
	}
}

func TestTTLPolicyEvictsSoonestToExpireFirst(t *testing.T) {
	c := newTestCache(t, lytecache.WithMaxKeys(2), lytecache.WithEviction(lytecache.TTLPolicy))

	if err := c.Set("soon", "1", lytecache.TTL(10*time.Minute)); err != nil {
		t.Fatal(err)
	}
	if err := c.Set("later", "2", lytecache.TTL(time.Hour)); err != nil {
		t.Fatal(err)
	}
	if err := c.Set("new", "3"); err != nil {
		t.Fatal(err)
	}

	if exists, _ := c.Exists("soon"); exists {
		t.Error("expected the soonest-to-expire key to be evicted first")
	}
	if exists, _ := c.Exists("later"); !exists {
		t.Error("expected 'later' to survive")
	}
	if exists, _ := c.Exists("new"); !exists {
		t.Error("expected 'new' to survive")
	}
}

func TestNoEvictionRejectsNewKeyPastLimit(t *testing.T) {
	c := newTestCache(t, lytecache.WithMaxKeys(2), lytecache.WithEviction(lytecache.NoEviction))

	if err := c.Set("a", "1"); err != nil {
		t.Fatal(err)
	}
	if err := c.Set("b", "2"); err != nil {
		t.Fatal(err)
	}

	err := c.Set("c", "3")
	if !errors.Is(err, lytecache.ErrCacheFull) {
		t.Fatalf("expected ErrCacheFull, got %v", err)
	}

	// Updating an existing key never grows the dataset, so it must still
	// be allowed even at the limit.
	if err := c.Set("a", "updated"); err != nil {
		t.Fatalf("expected updating an existing key to succeed at the limit, got %v", err)
	}
}

func TestRandomEvictionKeepsWithinLimit(t *testing.T) {
	c := newTestCache(t, lytecache.WithMaxKeys(3), lytecache.WithEviction(lytecache.Random))

	for i := 0; i < 10; i++ {
		if err := c.Set(string(rune('a'+i)), i); err != nil {
			t.Fatal(err)
		}
	}

	stats, err := c.Stats()
	if err != nil {
		t.Fatal(err)
	}
	if stats.KeyCount > 3 {
		t.Fatalf("expected at most 3 keys, got %d", stats.KeyCount)
	}
	if stats.Evictions == 0 {
		t.Fatal("expected some evictions to have occurred")
	}
}

func TestMaxBytesEviction(t *testing.T) {
	c := newTestCache(t, lytecache.WithMaxBytes(20), lytecache.WithEviction(lytecache.LRU))

	// Each value is several bytes; writing enough of them should trigger
	// eviction to stay near the byte budget.
	for i := 0; i < 10; i++ {
		if err := c.Set(string(rune('a'+i)), "0123456789"); err != nil {
			t.Fatal(err)
		}
	}

	stats, err := c.Stats()
	if err != nil {
		t.Fatal(err)
	}
	if stats.Evictions == 0 {
		t.Fatal("expected byte-limit evictions to have occurred")
	}
}
