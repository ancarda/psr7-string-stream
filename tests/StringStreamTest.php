<?php

declare(strict_types=1);

namespace Tests;

use Ancarda\Psr7\StringStream\StreamAlreadyClosedException;
use Ancarda\Psr7\StringStream\StreamUnusableException;
use Ancarda\Psr7\StringStream\StringStream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class StringStreamTest extends TestCase
{
    public function testRead(): void
    {
        $stringStream = new StringStream('hello world');

        static::assertTrue($stringStream->isReadable());

        // getContents will read until the end, so the next time round it should be empty
        static::assertSame('hello world', $stringStream->getContents());
        static::assertSame('', $stringStream->getContents());

        // Simple functions
        static::assertSame(11, $stringStream->getSize());

        // __toString always returns everything
        static::assertSame('hello world', (string) $stringStream);
        static::assertSame('hello world', (string) $stringStream);
    }

    public function testSeeking(): void
    {
        $stringStream = new StringStream('hello world');

        static::assertTrue($stringStream->isSeekable());

        // We should start at 0
        static::assertSame(0, $stringStream->tell());
        static::assertFalse($stringStream->eof());

        // Read the first 5 bytes.
        static::assertSame('hello', $stringStream->read(5));
        static::assertSame(5, $stringStream->tell());
        static::assertFalse($stringStream->eof());

        // Read till the rest of the file with getContents
        static::assertSame(' world', $stringStream->getContents());
        static::assertTrue($stringStream->eof());
        static::assertSame($stringStream->getSize(), $stringStream->tell());

        // Now rewind and do it all again
        $stringStream->rewind();
        static::assertSame(0, $stringStream->tell());

        // Finally, test more esoteric seeking
        $stringStream->seek(2, SEEK_SET);
        static::assertSame(2, $stringStream->tell());
        $stringStream->seek(3, SEEK_CUR);
        static::assertSame(5, $stringStream->tell());
        $stringStream->seek(2, SEEK_END);
        static::assertSame(13, $stringStream->tell());

        // @TODO(ancarda): The specification doesn't say what to do for an unknown $whence value.
        // Given an invalid SEEK flag (oh how I wish PHP had enums...) what should the implementation do?
        // It seems it could either do nothing or do the default behavior (SEEK_SET).
        // This implementation does nothing as that appears to be the correct behavior (going by what fseek() does).
        $stringStream->rewind();
        $stringStream->seek(2);
        $stringStream->seek(5, -1);
        static::assertSame(2, $stringStream->tell()); // Do nothing when whence is invalid
    }

    public function testWriting(): void
    {
        $stringStream = new StringStream('hello world');
        static::assertTrue($stringStream->isWritable());

        // Can we write at the end of a string?
        $stringStream->seek(0, SEEK_END);
        $bytesWritten = $stringStream->write(', isn\'t it a lovely day');
        $fullString = 'hello world, isn\'t it a lovely day';
        static::assertSame(strlen($fullString), $stringStream->getSize());
        static::assertSame($fullString, (string) $stringStream);
        static::assertSame(strlen(', isn\'t it a lovely day'), $bytesWritten);

        // Can we overwrite at the start of the string to fix the capitalization?
        $stringStream->seek(0);
        $bytesWritten = $stringStream->write('H');
        $fullString = 'Hello world, isn\'t it a lovely day';
        static::assertSame(strlen($fullString), $stringStream->getSize());
        static::assertSame($fullString, (string) $stringStream);
        static::assertSame(1, $bytesWritten);

        // Can we make a multi-word replacement? We'll replace 2 bytes with 0x7F (DEL) which in a
        // real world application could be filtered out as deleted bytes.
        $stringStream->seek(0);
        $bytesWritten = $stringStream->write('Hey' . chr(127) . chr(127));
        $fullString = 'Hey' . chr(127) . chr(127) . ' world, isn\'t it a lovely day';
        static::assertSame(strlen($fullString), $stringStream->getSize());
        static::assertSame($fullString, (string) $stringStream);
        static::assertSame(5, $bytesWritten);

        // Finally, can we replace and append?
        $stringStream->seek(31);
        $bytesWritten = $stringStream->write('evening?');
        $fullString = 'Hey' . chr(127) . chr(127) . ' world, isn\'t it a lovely evening?';
        static::assertSame(strlen($fullString), $stringStream->getSize());
        static::assertSame($fullString, (string) $stringStream);
        static::assertSame(8, $bytesWritten);
    }

    public function testOverwriteWorksCorrectly(): void
    {
        $stream = new StringStream('');
        $stream->write('abc');
        $stream->write('def');
        $stream->rewind();
        $stream->write('XXX');
        static::assertSame('XXXdef', (string) $stream);
    }

    public function testMiscFunctions(): void
    {
        $stringStream = new StringStream('hello world');

        // These functions do nothing as we don't use streams.
        static::assertNull($stringStream->detach());
        static::assertNull($stringStream->getMetadata());
    }

    private function checkStreamIsDead(StreamInterface $stream): void
    {
        static::assertSame(0, $stream->getSize(), 'Closed/detached streams have no data');
        static::assertFalse($stream->isReadable(), 'Closed/detached streams cannot be read.');
        static::assertFalse($stream->isWritable(), 'Closed/detached streams cannot be written to.');
        static::assertFalse($stream->isSeekable(), 'Closed/detached streams cannot be seeked.');
        static::assertTrue($stream->eof());
        static::assertSame('', (string) $stream);
    }

    public function testDetach(): void
    {
        $stringStream = new StringStream('hello world');
        $stringStream->detach();
        $this->checkStreamIsDead($stringStream);
    }

    public function testClose(): void
    {
        $stringStream = new StringStream('hello world');
        $stringStream->close();
        $this->checkStreamIsDead($stringStream);
    }

    public function testTellThrowsExceptionAfterClose(): void
    {
        $stringStream = new StringStream('hello world');
        $stringStream->close();
        $this->expectException(StreamUnusableException::class);
        $this->expectExceptionMessage("You cannot call `tell' on this stream because it's closed.");
        $stringStream->tell();
    }

    public function testSeekThrowsExceptionAfterClose(): void
    {
        $stringStream = new StringStream('hello world');
        $stringStream->close();
        $this->expectException(StreamUnusableException::class);
        $this->expectExceptionMessage("You cannot call `seek' on this stream because it's closed.");
        $stringStream->seek(0);
    }

    public function testRewindThrowsExceptionAfterClose(): void
    {
        $stringStream = new StringStream('hello world');
        $stringStream->close();
        $this->expectException(StreamUnusableException::class);
        $this->expectExceptionMessage("You cannot call `rewind' on this stream because it's closed.");
        $stringStream->rewind();
    }

    public function testWriteThrowsExceptionAfterClose(): void
    {
        $stringStream = new StringStream('hello world');
        $stringStream->close();
        $this->expectException(StreamUnusableException::class);
        $this->expectExceptionMessage("You cannot call `write' on this stream because it's closed.");
        $stringStream->write('!');
    }

    public function testReadThrowsExceptionAfterClose(): void
    {
        $stringStream = new StringStream('hello world');
        $stringStream->close();
        $this->expectException(StreamUnusableException::class);
        $this->expectExceptionMessage("You cannot call `read' on this stream because it's closed.");
        $stringStream->read(1);
    }

    public function testGetContentsThrowsExceptionAfterClose(): void
    {
        $stringStream = new StringStream('hello world');
        $stringStream->close();
        $this->expectException(StreamUnusableException::class);
        $this->expectExceptionMessage("You cannot call `getContents' on this stream because it's closed.");
        $stringStream->getContents();
    }

    public function testEOFAfterOverReading(): void
    {
        $stringStream = new StringStream('hello world');
        self::assertFalse($stringStream->eof());
        $text = $stringStream->read(1048576);
        self::assertSame('hello world', $text);
        self::assertTrue($stringStream->eof());
    }
}
