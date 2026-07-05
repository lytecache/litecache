package io.lytecache;

/**
 * Test helper (not a JUnit test): launched as a separate JVM process by ZeroConfigTest to verify
 * that the {@code LYTECACHE_PATH} environment variable override -- which can only be exercised by
 * actually setting an environment variable for a process, not by mutating a running JVM's own
 * environment -- is honored by the zero-argument {@code new LyteCache()} constructor.
 *
 * <p>Prints the resolved {@link LyteCache#defaultPath()}, writes a key, and exits.
 */
public final class ZeroConfigWorker {
    private ZeroConfigWorker() {}

    public static void main(String[] args) {
        System.out.println(LyteCache.defaultPath());
        try (LyteCache cache = new LyteCache()) {
            cache.set("from-worker", "hello");
        }
    }
}
