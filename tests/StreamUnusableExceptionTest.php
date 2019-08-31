<?php

declare(strict_types=1);

namespace Tests;

use Ancarda\Psr7\StringStream\StreamUnusableException;
use PHPUnit\Framework\TestCase;

class StreamUnusableExceptionTest extends TestCase
{
    public function testException(): void
    {
        $exception = new StreamUnusableException('read');

        static::assertSame(0, $exception->getCode());
        static::assertSame(
            "You cannot call `read' on this stream because it's closed.",
            $exception->getMessage()
        );
    }
}
