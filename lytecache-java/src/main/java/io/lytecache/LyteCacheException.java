package io.lytecache;

/**
 * Base unchecked exception for all LyteCache errors.
 */
public class LyteCacheException extends RuntimeException {
    private static final long serialVersionUID = 1L;

    /**
     * Constructs a LyteCacheException with the given message.
     *
     * @param message the error message
     */
    public LyteCacheException(String message) {
        super(message);
    }

    /**
     * Constructs a LyteCacheException with the given message and cause.
     *
     * @param message the error message
     * @param cause the underlying cause
     */
    public LyteCacheException(String message, Throwable cause) {
        super(message, cause);
    }
}
