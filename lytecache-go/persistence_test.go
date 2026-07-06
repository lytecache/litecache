package lytecache_test

import (
	"testing"

	lytecache "github.com/YOURUSERNAME/lytecache-go"
)

func TestPersistenceAcrossReopen(t *testing.T) {
	path := tempDBPath(t)

	c1, err := lytecache.New(lytecache.WithPath(path))
	if err != nil {
		t.Fatal(err)
	}
	if err := c1.Set("k", "value"); err != nil {
		t.Fatal(err)
	}
	if _, err := c1.Incr("hits", 7); err != nil {
		t.Fatal(err)
	}
	if err := c1.Close(); err != nil {
		t.Fatal(err)
	}

	c2, err := lytecache.New(lytecache.WithPath(path))
	if err != nil {
		t.Fatal(err)
	}
	defer func() { _ = c2.Close() }()

	got, found, err := c2.GetString("k")
	if err != nil || !found || got != "value" {
		t.Fatalf("got=%q found=%v err=%v", got, found, err)
	}
	n, found2, err := c2.GetInt64("hits")
	if err != nil || !found2 || n != 7 {
		t.Fatalf("n=%d found=%v err=%v", n, found2, err)
	}
}
