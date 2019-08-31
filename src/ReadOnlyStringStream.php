<?php

declare(strict_types=1);

namespace Ancarda\Psr7\StringStream;

use RuntimeException;

/**
 * String based PSR-7 Stream that cannot be written to, only read and seeked
 */
class ReadOnlyStringStream extends StringStream
{
    /**
     * Returns false as this stream is read only
     *
     * @return bool
     */
    public function isWritable(): bool
    {
        return false;
    }

    /**
     * Throws a RuntimeException as this stream is read only
     *
     * @param string $string
     * @throws RuntimeException
     * @return int
     */
    public function write($string): int
    {
        throw new IllegalOperationException(__FUNCTION__, 'read-only');
    }
}
