<?php

declare(strict_types=1);

namespace Lytecache\Exceptions;

/**
 * Thrown by incr()/decr()/incrFloat() when the existing value for a key is
 * not numeric.
 */
class NotNumericException extends LytecacheException {}
