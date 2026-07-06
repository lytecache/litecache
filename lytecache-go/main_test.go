package lytecache_test

import (
	"testing"

	"go.uber.org/goleak"
)

// TestMain verifies that no test leaves goroutines running -- in
// particular, that every Cache's background sweeper is stopped by Close.
// TestHelperProcess (in multiprocess_test.go) is a no-op unless invoked as
// a re-exec'd child process, so it doesn't interfere with this.
func TestMain(m *testing.M) {
	goleak.VerifyTestMain(m)
}
