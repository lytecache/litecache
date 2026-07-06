package lytecache_test

import (
	"fmt"
	"sync"
	"testing"
	"time"
)

// TestConcurrentGoroutinesMixedOps hammers one Cache instance from many
// goroutines doing a mix of Set, Get, and Incr, and must be run with
// -race. The counter total must be exact: Incr's single-UPSERT design
// must not lose updates under concurrent access.
func TestConcurrentGoroutinesMixedOps(t *testing.T) {
	c := newTestCache(t)

	const goroutines = 50
	const incrPerGoroutine = 40

	var wg sync.WaitGroup
	for g := 0; g < goroutines; g++ {
		wg.Add(1)
		go func(g int) {
			defer wg.Done()
			key := fmt.Sprintf("key-%d", g%5) // deliberate overlap across goroutines
			for i := 0; i < incrPerGoroutine; i++ {
				if _, err := c.Incr("shared-counter", 1); err != nil {
					t.Error(err)
					return
				}
				if err := c.Set(key, i); err != nil {
					t.Error(err)
					return
				}
				var v int
				if _, err := c.Get(key, &v); err != nil {
					t.Error(err)
					return
				}
				if _, err := c.Exists(key); err != nil {
					t.Error(err)
					return
				}
			}
		}(g)
	}
	wg.Wait()

	got, _, err := c.GetInt64("shared-counter")
	if err != nil {
		t.Fatal(err)
	}
	want := int64(goroutines * incrPerGoroutine)
	if got != want {
		t.Fatalf("expected exact counter total %d, got %d", want, got)
	}
}

// TestConcurrentIncrExactTotal isolates Incr specifically (no Set/Get
// noise) to make an exact-total assertion easy to reason about under
// -race.
func TestConcurrentIncrExactTotal(t *testing.T) {
	c := newTestCache(t)

	const goroutines = 100
	const perGoroutine = 100

	var wg sync.WaitGroup
	for g := 0; g < goroutines; g++ {
		wg.Add(1)
		go func() {
			defer wg.Done()
			for i := 0; i < perGoroutine; i++ {
				if _, err := c.Incr("counter", 1); err != nil {
					t.Error(err)
					return
				}
			}
		}()
	}
	wg.Wait()

	got, _, err := c.GetInt64("counter")
	if err != nil {
		t.Fatal(err)
	}
	want := int64(goroutines * perGoroutine)
	if got != want {
		t.Fatalf("expected exact counter total %d, got %d", want, got)
	}
}

// TestConcurrentLockMutualExclusion verifies that many goroutines racing
// for the same lock name never observe two simultaneous holders.
func TestConcurrentLockMutualExclusion(t *testing.T) {
	c := newTestCache(t)

	const goroutines = 20
	var holders int32
	var maxObservedHolders int32
	var mu sync.Mutex

	var wg sync.WaitGroup
	for g := 0; g < goroutines; g++ {
		wg.Add(1)
		go func() {
			defer wg.Done()
			lock, err := c.Lock("resource", 5*time.Second)
			if err != nil {
				t.Error(err)
				return
			}
			mu.Lock()
			holders++
			if holders > maxObservedHolders {
				maxObservedHolders = holders
			}
			mu.Unlock()

			mu.Lock()
			holders--
			mu.Unlock()

			if err := lock.Release(); err != nil {
				t.Error(err)
			}
		}()
	}
	wg.Wait()

	if maxObservedHolders > 1 {
		t.Fatalf("observed %d simultaneous lock holders, want at most 1", maxObservedHolders)
	}
}
