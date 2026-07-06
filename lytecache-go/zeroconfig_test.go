package lytecache_test

import (
	"os"
	"path/filepath"
	"testing"

	lytecache "github.com/YOURUSERNAME/lytecache-go"
)

// patchCacheDir redirects os.UserCacheDir to a temp directory for the
// duration of the test, across the platforms Go's UserCacheDir supports:
// HOME (used directly on macOS, and as the ".cache" fallback on Linux),
// XDG_CACHE_HOME (Linux, cleared so the HOME fallback applies), and
// LocalAppData (Windows).
func patchCacheDir(t *testing.T, dir string) {
	t.Helper()
	t.Setenv("HOME", dir)
	t.Setenv("XDG_CACHE_HOME", "")
	t.Setenv("LocalAppData", dir)
}

func TestZeroConfigCreatesFileAndParentDirs(t *testing.T) {
	patchCacheDir(t, t.TempDir())

	path, err := lytecache.DefaultPath()
	if err != nil {
		t.Fatal(err)
	}
	if _, err := os.Stat(path); !os.IsNotExist(err) {
		t.Fatalf("expected no file yet at %s", path)
	}
	if _, err := os.Stat(filepath.Dir(path)); !os.IsNotExist(err) {
		t.Fatalf("expected the parent directory not to exist yet either")
	}

	c, err := lytecache.New()
	if err != nil {
		t.Fatal(err)
	}
	defer func() { _ = c.Close() }()

	if _, err := os.Stat(path); err != nil {
		t.Fatalf("expected New() to create the file: %v", err)
	}
	if c.Path() != path {
		t.Fatalf("expected Path() %q, got %q", path, c.Path())
	}
}

func TestLytecachePathEnvOverride(t *testing.T) {
	dir := t.TempDir()
	override := filepath.Join(dir, "custom", "nested", "path.db")
	t.Setenv("LYTECACHE_PATH", override)

	path, err := lytecache.DefaultPath()
	if err != nil {
		t.Fatal(err)
	}
	if path != override {
		t.Fatalf("expected %q, got %q", override, path)
	}

	c, err := lytecache.New()
	if err != nil {
		t.Fatal(err)
	}
	defer func() { _ = c.Close() }()

	if c.Path() != override {
		t.Fatalf("expected %q, got %q", override, c.Path())
	}
	if _, err := os.Stat(override); err != nil {
		t.Fatalf("expected file to be created at the override path: %v", err)
	}
}

func TestDifferentWorkingDirectoriesResolveDifferentFiles(t *testing.T) {
	patchCacheDir(t, t.TempDir())

	origWD, err := os.Getwd()
	if err != nil {
		t.Fatal(err)
	}
	t.Cleanup(func() { _ = os.Chdir(origWD) })

	base := t.TempDir()
	dirA := filepath.Join(base, "project-a")
	dirB := filepath.Join(base, "project-b")
	if err := os.MkdirAll(dirA, 0o755); err != nil {
		t.Fatal(err)
	}
	if err := os.MkdirAll(dirB, 0o755); err != nil {
		t.Fatal(err)
	}

	if err := os.Chdir(dirA); err != nil {
		t.Fatal(err)
	}
	pathA, err := lytecache.DefaultPath()
	if err != nil {
		t.Fatal(err)
	}
	pathAAgain, err := lytecache.DefaultPath()
	if err != nil {
		t.Fatal(err)
	}
	if pathA != pathAAgain {
		t.Fatalf("expected DefaultPath to be stable for the same cwd: %q vs %q", pathA, pathAAgain)
	}

	if err := os.Chdir(dirB); err != nil {
		t.Fatal(err)
	}
	pathB, err := lytecache.DefaultPath()
	if err != nil {
		t.Fatal(err)
	}

	if pathA == pathB {
		t.Fatalf("expected different default paths for different working directories, both got %q", pathA)
	}
}
