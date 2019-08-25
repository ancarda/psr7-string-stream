<?php

declare(strict_types=1);

namespace Tests;

use Ancarda\Psr7\StringStream\ReadOnlyStringStream;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ReadOnlyStringStreamTest extends TestCase
{
    public function testReadOnly(): void
    {
        $stream = new ReadOnlyStringStream('read only string');
        static::assertFalse($stream->isWritable());

        $this->expectException(RuntimeException::class);
        $stream->write('Cannot write(): Read Only Stream');
    }
}
