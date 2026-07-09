package lytecache_test

import (
	"errors"
	"math"
	"testing"

	lytecache "github.com/lytecache/lytecache-go"
)

func TestIncrStartsMissingKeysAtZero(t *testing.T) {
	c := newTestCache(t)
	n, err := c.Incr("hits", 1)
	if err != nil || n != 1 {
		t.Fatalf("n=%d err=%v", n, err)
	}
}

func TestIncrAccumulates(t *testing.T) {
	c := newTestCache(t)
	if _, err := c.Incr("hits", 5); err != nil {
		t.Fatal(err)
	}
	n, err := c.Incr("hits", 3)
	if err != nil || n != 8 {
		t.Fatalf("n=%d err=%v", n, err)
	}
}

func TestDecr(t *testing.T) {
	c := newTestCache(t)
	if _, err := c.Incr("hits", 10); err != nil {
		t.Fatal(err)
	}
	n, err := c.Decr("hits", 3)
	if err != nil || n != 7 {
		t.Fatalf("n=%d err=%v", n, err)
	}
}

func TestIncrNegativeAmount(t *testing.T) {
	c := newTestCache(t)
	n, err := c.Incr("hits", -5)
	if err != nil || n != -5 {
		t.Fatalf("n=%d err=%v", n, err)
	}
}

func TestIncrFloat(t *testing.T) {
	c := newTestCache(t)
	f, err := c.IncrFloat("ratio", 0.5)
	if err != nil || f != 0.5 {
		t.Fatalf("f=%v err=%v", f, err)
	}
	f, err = c.IncrFloat("ratio", 0.25)
	if err != nil || f != 0.75 {
		t.Fatalf("f=%v err=%v", f, err)
	}
}

func TestIncrFloatOnExistingInt(t *testing.T) {
	c := newTestCache(t)
	if _, err := c.Incr("n", 5); err != nil {
		t.Fatal(err)
	}
	f, err := c.IncrFloat("n", 0.5)
	if err != nil || f != 5.5 {
		t.Fatalf("f=%v err=%v", f, err)
	}
}

func TestIncrOnNonNumericValueErrors(t *testing.T) {
	c := newTestCache(t)
	if err := c.Set("k", "not a number"); err != nil {
		t.Fatal(err)
	}
	_, err := c.Incr("k", 1)
	if !errors.Is(err, lytecache.ErrNotNumeric) {
		t.Fatalf("expected ErrNotNumeric, got %v", err)
	}
}

func TestIncrOnFloatValueErrors(t *testing.T) {
	c := newTestCache(t)
	if _, err := c.IncrFloat("k", 1.5); err != nil {
		t.Fatal(err)
	}
	// Incr (integer-only) on an existing float should error, matching real
	// Redis's INCR-on-a-float-string behavior.
	_, err := c.Incr("k", 1)
	if !errors.Is(err, lytecache.ErrNotNumeric) {
		t.Fatalf("expected ErrNotNumeric, got %v", err)
	}
}

func TestIncrFloatOnNonNumericValueErrors(t *testing.T) {
	c := newTestCache(t)
	if err := c.Set("k", "nope"); err != nil {
		t.Fatal(err)
	}
	_, err := c.IncrFloat("k", 1)
	if !errors.Is(err, lytecache.ErrNotNumeric) {
		t.Fatalf("expected ErrNotNumeric, got %v", err)
	}
}

func TestIncrOnExpiredKeyStartsOver(t *testing.T) {
	c := newTestCache(t)
	if err := c.Set("k", int64(100), lytecache.TTL(0)); err != nil { // expires immediately
		t.Fatal(err)
	}
	n, err := c.Incr("k", 1)
	if err != nil || n != 1 {
		t.Fatalf("expected incr on expired key to start at 1, got n=%d err=%v", n, err)
	}
}

func TestIncrLargeValues(t *testing.T) {
	c := newTestCache(t)
	const big = math.MaxInt64 - 10
	if err := c.Set("k", int64(big)); err != nil {
		t.Fatal(err)
	}
	n, err := c.Incr("k", 5)
	if err != nil || n != big+5 {
		t.Fatalf("n=%d err=%v", n, err)
	}
}
