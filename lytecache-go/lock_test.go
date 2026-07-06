package lytecache_test

import (
	"errors"
	"testing"
	"time"

	lytecache "github.com/YOURUSERNAME/lytecache-go"
)

func TestLockAcquireAndRelease(t *testing.T) {
	c := newTestCache(t)
	lock, err := c.Lock("resource", time.Second)
	if err != nil {
		t.Fatal(err)
	}
	if err := lock.Release(); err != nil {
		t.Fatal(err)
	}
}

func TestLockTimesOutWhileHeld(t *testing.T) {
	c := newTestCache(t)
	lock, err := c.Lock("resource", 5*time.Second)
	if err != nil {
		t.Fatal(err)
	}
	defer func() { _ = lock.Release() }()

	_, err = c.Lock("resource", 100*time.Millisecond)
	if !errors.Is(err, lytecache.ErrLockTimeout) {
		t.Fatalf("expected ErrLockTimeout, got %v", err)
	}
}

func TestLockCanBeReacquiredAfterRelease(t *testing.T) {
	c := newTestCache(t)
	lock, err := c.Lock("resource", time.Second)
	if err != nil {
		t.Fatal(err)
	}
	if err := lock.Release(); err != nil {
		t.Fatal(err)
	}

	lock2, err := c.Lock("resource", time.Second)
	if err != nil {
		t.Fatalf("expected to reacquire the lock after release, got %v", err)
	}
	if err := lock2.Release(); err != nil {
		t.Fatal(err)
	}
}

func TestLockDifferentNamesDoNotContend(t *testing.T) {
	c := newTestCache(t)
	lockA, err := c.Lock("a", time.Second)
	if err != nil {
		t.Fatal(err)
	}
	defer func() { _ = lockA.Release() }()

	lockB, err := c.Lock("b", time.Second)
	if err != nil {
		t.Fatalf("expected a different lock name not to contend: %v", err)
	}
	defer func() { _ = lockB.Release() }()
}
