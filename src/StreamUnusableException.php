<?php

declare(strict_types=1);

namespace Ancarda\Psr7\StringStream;

use Throwable;

/**
 * StreamUnusableException is thrown when a user attempts to read, seek, or write to a closed or detached stream
 */
final class StreamUnusableException extends IllegalOperationException
{
    /**
     * @param string $operation
     * @param Throwable|null $previous
     */
    public function __construct(string $operation, Throwable $previous = null)
    {
        parent::__construct($operation, 'closed', $previous);
    }
}
