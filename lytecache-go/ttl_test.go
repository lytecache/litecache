package lytecache_test

import (
	"testing"
	"time"

	lytecache "github.com/lytecache/lytecache-go"
)

func TestTTLNoExpiryByDefault(t *testing.T) {
	c := newTestCache(t)
	if err := c.Set("k", "v"); err != nil {
		t.Fatal(err)
	}
	_, hasExpiry, found, err := c.TTLOf("k")
	if err != nil || !found || hasExpiry {
		t.Fatalf("hasExpiry=%v found=%v err=%v", hasExpiry, found, err)
	}
}

func TestTTLOfMissingKey(t *testing.T) {
	c := newTestCache(t)
	_, hasExpiry, found, err := c.TTLOf("nope")
	if err != nil || found || hasExpiry {
		t.Fatalf("hasExpiry=%v found=%v err=%v", hasExpiry, found, err)
	}
}

func TestTTLZeroExpiresImmediately(t *testing.T) {
	c := newTestCache(t)
	if err := c.Set("k", "v", lytecache.TTL(0)); err != nil {
		t.Fatal(err)
	}
	found, err := c.Exists("k")
	if err != nil || found {
		t.Fatalf("expected key to be immediately expired: found=%v err=%v", found, err)
	}
}

func TestTTLNegativeExpiresImmediately(t *testing.T) {
	c := newTestCache(t)
	if err := c.Set("k", "v", lytecache.TTL(-time.Second)); err != nil {
		t.Fatal(err)
	}
	found, err := c.Exists("k")
	if err != nil || found {
		t.Fatalf("expected key to be immediately expired: found=%v err=%v", found, err)
	}
}

func TestTTLSubSecond(t *testing.T) {
	c := newTestCache(t)
	if err := c.Set("k", "v", lytecache.TTL(50*time.Millisecond)); err != nil {
		t.Fatal(err)
	}
	if found, err := c.Exists("k"); err != nil || !found {
		t.Fatalf("expected key to still exist immediately: found=%v err=%v", found, err)
	}
	time.Sleep(100 * time.Millisecond)
	if found, err := c.Exists("k"); err != nil || found {
		t.Fatalf("expected key to have expired: found=%v err=%v", found, err)
	}
}

func TestTTLBoundary(t *testing.T) {
	c := newTestCache(t)
	if err := c.Set("k", "v", lytecache.TTL(80*time.Millisecond)); err != nil {
		t.Fatal(err)
	}
	time.Sleep(40 * time.Millisecond)
	if found, err := c.Exists("k"); err != nil || !found {
		t.Fatalf("expected key to still exist before expiry: found=%v err=%v", found, err)
	}
	time.Sleep(80 * time.Millisecond)
	if found, err := c.Exists("k"); err != nil || found {
		t.Fatalf("expected key to have expired: found=%v err=%v", found, err)
	}
}

func TestLazyExpirationOnGet(t *testing.T) {
	c := newTestCache(t, lytecache.WithSweepInterval(0)) // no background sweeper
	if err := c.Set("k", "v", lytecache.TTL(10*time.Millisecond)); err != nil {
		t.Fatal(err)
	}
	time.Sleep(30 * time.Millisecond)

	var v string
	found, err := c.Get("k", &v)
	if err != nil || found {
		t.Fatalf("expected a miss on expired key: found=%v err=%v", found, err)
	}
}

func TestExpireOverwritesTTL(t *testing.T) {
	c := newTestCache(t)
	if err := c.Set("k", "v", lytecache.TTL(time.Minute)); err != nil {
		t.Fatal(err)
	}
	ok, err := c.Expire("k", 50*time.Millisecond)
	if err != nil || !ok {
		t.Fatalf("ok=%v err=%v", ok, err)
	}
	time.Sleep(100 * time.Millisecond)
	if found, _ := c.Exists("k"); found {
		t.Fatal("expected key to have expired after Expire shortened its TTL")
	}
}

func TestExpireOnMissingKey(t *testing.T) {
	c := newTestCache(t)
	ok, err := c.Expire("missing", time.Minute)
	if err != nil || ok {
		t.Fatalf("ok=%v err=%v", ok, err)
	}
}

func TestPersistRemovesTTL(t *testing.T) {
	c := newTestCache(t)
	if err := c.Set("k", "v", lytecache.TTL(50*time.Millisecond)); err != nil {
		t.Fatal(err)
	}
	ok, err := c.Persist("k")
	if err != nil || !ok {
		t.Fatalf("ok=%v err=%v", ok, err)
	}
	time.Sleep(100 * time.Millisecond)
	if found, err := c.Exists("k"); err != nil || !found {
		t.Fatalf("expected key to survive past its original TTL: found=%v err=%v", found, err)
	}
	_, hasExpiry, _, _ := c.TTLOf("k")
	if hasExpiry {
		t.Fatal("expected no expiry after Persist")
	}
}

func TestTouchRefreshesTTL(t *testing.T) {
	c := newTestCache(t)
	if err := c.Set("k", "v", lytecache.TTL(40*time.Millisecond)); err != nil {
		t.Fatal(err)
	}
	time.Sleep(20 * time.Millisecond)
	ok, err := c.Touch("k", 200*time.Millisecond)
	if err != nil || !ok {
		t.Fatalf("ok=%v err=%v", ok, err)
	}
	time.Sleep(40 * time.Millisecond) // past the original 40ms TTL, within the refreshed 200ms
	if found, err := c.Exists("k"); err != nil || !found {
		t.Fatalf("expected touch to have extended the TTL: found=%v err=%v", found, err)
	}
}

func TestSweeperRemovesExpiredKeys(t *testing.T) {
	c := newTestCache(t, lytecache.WithSweepInterval(30*time.Millisecond))
	if err := c.Set("k", "v", lytecache.TTL(10*time.Millisecond)); err != nil {
		t.Fatal(err)
	}
	time.Sleep(150 * time.Millisecond)

	stats, err := c.Stats()
	if err != nil {
		t.Fatal(err)
	}
	if stats.KeyCount != 0 {
		t.Fatalf("expected the sweeper to have removed the expired row, got KeyCount=%d", stats.KeyCount)
	}
	if stats.ExpiredRemoved == 0 {
		t.Fatal("expected ExpiredRemoved to be nonzero")
	}
}
