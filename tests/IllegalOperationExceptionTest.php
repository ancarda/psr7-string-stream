<?php

declare(strict_types=1);

namespace Tests;

use Ancarda\Psr7\StringStream\IllegalOperationException;
use PHPUnit\Framework\TestCase;

class IllegalOperationExceptionTest extends TestCase
{
    public function testException(): void
    {
        $exception = new IllegalOperationException('write', 'read-only');
        static::assertSame(0, $exception->getCode());
        static::assertSame(
            "You cannot call `write' on this stream because it's read-only.",
            $exception->getMessage()
        );
        static::assertSame('write', $exception->getOperation());
    }
}
