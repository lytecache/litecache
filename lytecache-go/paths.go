package lytecache

import (
	"crypto/sha256"
	"encoding/hex"
	"fmt"
	"os"
	"path/filepath"
	"strings"
)

// envPathOverride is checked before the platform default; see DefaultPath.
const envPathOverride = "LYTECACHE_PATH"

// DefaultPath returns the default database file location for the current
// working directory: "<platform cache dir>/lytecache/<project-id>.db".
//
// The platform cache dir comes from [os.UserCacheDir], which already
// implements the XDG Base Directory spec on Linux, ~/Library/Caches on
// macOS, and %LocalAppData% on Windows.
//
// <project-id> is the first 12 hex characters of the SHA-256 hash of the
// resolved, absolute current working directory -- the same derivation used
// by the Python, Java, and Node.js implementations of lytecache, so a
// process in any of those languages started from the same directory
// resolves to the same file.
//
// If the LYTECACHE_PATH environment variable is set (after "~" expansion),
// it is returned instead, taking priority over the derived default. A
// relative LYTECACHE_PATH is returned as-is, not resolved to absolute --
// matching the other implementations.
func DefaultPath() (string, error) {
	if override, ok := os.LookupEnv(envPathOverride); ok && override != "" {
		return expandHome(override)
	}

	cacheDir, err := os.UserCacheDir()
	if err != nil {
		return "", fmt.Errorf("lytecache: resolving platform cache dir: %w", err)
	}

	id, err := projectID()
	if err != nil {
		return "", err
	}

	return filepath.Join(cacheDir, "lytecache", id+".db"), nil
}

// projectID derives the 12-hex-character project identifier from the
// resolved current working directory. See DefaultPath's doc comment for
// the cross-language contract this must uphold.
func projectID() (string, error) {
	cwd, err := os.Getwd()
	if err != nil {
		return "", fmt.Errorf("lytecache: resolving working directory: %w", err)
	}

	resolved, err := filepath.EvalSymlinks(cwd)
	if err != nil {
		// A cwd that can't be resolved (e.g. removed out from under the
		// process) still needs a deterministic id, so fall back to the
		// unresolved path rather than failing outright.
		resolved = cwd
	}

	sum := sha256.Sum256([]byte(resolved))
	return hex.EncodeToString(sum[:])[:12], nil
}

// expandHome expands a leading "~" or "~/" to the user's home directory.
// It does not otherwise touch the path -- a relative path stays relative.
func expandHome(path string) (string, error) {
	if path != "~" && !strings.HasPrefix(path, "~/") {
		return path, nil
	}

	home, err := os.UserHomeDir()
	if err != nil {
		return "", fmt.Errorf("lytecache: expanding ~ in %s: %w", envPathOverride, err)
	}
	if path == "~" {
		return home, nil
	}
	return filepath.Join(home, path[2:]), nil
}
