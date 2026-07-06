<?php

declare(strict_types=1);

namespace Lytecache\Exceptions;

/**
 * Thrown when opening a database file whose schema_version is newer than
 * this version of the library understands.
 */
class SchemaVersionException extends LytecacheException {}
