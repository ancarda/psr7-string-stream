<?php

declare(strict_types=1);

namespace Ancarda\Psr7\StringStream;

use RuntimeException;
use Throwable;

/**
 * IllegalOperationException is thrown when a user attempts to perform an unsupported operation.
 *
 * An example might be calling write() on a read only stream.
 */
class IllegalOperationException extends RuntimeException
{
    /** @var string */
    private $operation;

    /**
     * @param string $operation The operation, such as "write", that was refused.
     * @param string $justification Why can't the user perform the operation?
     *   This should complete the sentence "This stream is X", e.g. "This stream is read-only".
     * @param Throwable|null $previous
     */
    public function __construct(string $operation, string $justification, ?Throwable $previous = null)
    {
        $this->operation = $operation;

        parent::__construct(
            "You cannot call `{$this->operation}' on this stream because it's $justification.",
            0,
            $previous
        );
    }

    /**
     * Returns the operation, such as "write", that was refused.
     *
     * @return string
     */
    public function getOperation(): string
    {
        return $this->operation;
    }
}
