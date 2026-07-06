<?php

declare(strict_types=1);

namespace Lytecache\Exceptions;

/**
 * Thrown when a value cannot be encoded for storage, when a stored value
 * cannot be decoded (for example, a value written by the Python or Java
 * implementations using their language-specific escape hatches), or when
 * typed rehydration into a class does not match the stored shape.
 */
class SerializationException extends LytecacheException {}
