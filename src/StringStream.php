<?php

declare(strict_types=1);

namespace Ancarda\Psr7\StringStream;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Minimal string based PSR-7 Stream, ideal for use in Functional Tests
 */
class StringStream implements StreamInterface
{
    /** @var string|null */
    private $data;

    /** @var int */
    private $pointer = 0;

    /** @var int */
    private $length;

    /**
     * @param string $data
     */
    public function __construct(string $data)
    {
        $this->data   = $data;
        $this->length = strlen($data);
    }

    /**
     * Reads all data from the stream into a string, from the beginning to end.
     *
     * This method MUST attempt to seek to the beginning of the stream before
     * reading data and read the stream until the end is reached.
     *
     * Warning: This could attempt to load a large amount of data into memory.
     *
     * This method MUST NOT raise an exception in order to conform with PHP's
     * string casting operations.
     *
     * @see http://php.net/manual/en/language.oop5.magic.php#object.tostring
     * @return string
     */
    public function __toString(): string
    {
        return $this->data === null ? '' : $this->data;
    }

    /**
     * Closes the stream and any underlying resources.
     *
     * @return void
     */
    public function close(): void
    {
        $this->data    = null;
        $this->pointer = 0;
        $this->length  = 0;
    }

    /**
     * Separates any underlying resources from the stream.
     *
     * After the stream has been detached, the stream is in an unusable state.
     *
     * @return resource|null Underlying PHP stream, if any
     */
    public function detach()
    {
        $this->data    = null;
        $this->pointer = 0;
        $this->length  = 0;

        return null;
    }

    /**
     * Get the size of the stream if known.
     *
     * @return int Returns the size in bytes if known, or null if unknown.
     */
    public function getSize(): int
    {
        return $this->length;
    }

    /**
     * Returns the current position of the file read/write pointer
     *
     * @return int Position of the file pointer
     * @throws RuntimeException on error.
     */
    public function tell(): int
    {
        if ($this->data === null) {
            throw new StreamUnusableException(__FUNCTION__);
        }

        return $this->pointer;
    }

    /**
     * Returns true if the stream is at the end of the stream.
     *
     * @return bool
     */
    public function eof(): bool
    {
        return $this->pointer >= $this->length;
    }

    /**
     * Returns whether or not the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable(): bool
    {
        return $this->data !== null;
    }

    /**
     * Seek to a position in the stream.
     *
     * @link http://www.php.net/manual/en/function.fseek.php
     * @param int $offset Stream offset
     * @param int $whence Specifies how the cursor position will be calculated
     *     based on the seek offset. Valid values are identical to the built-in
     *     PHP $whence values for `fseek()`.  SEEK_SET: Set position equal to
     *     offset bytes SEEK_CUR: Set position to current location plus offset
     *     SEEK_END: Set position to end-of-stream plus offset.
     * @throws RuntimeException on failure.
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        if ($this->data === null) {
            throw new StreamUnusableException(__FUNCTION__);
        }

        switch ($whence) {
            case SEEK_SET:
                $this->pointer = $offset;
                return;
            case SEEK_CUR:
                $this->pointer = $this->pointer + $offset;
                return;
            case SEEK_END:
                $this->pointer = $this->length + $offset;
                return;
        }
    }

    /**
     * Seek to the beginning of the stream.
     *
     * If the stream is not seekable, this method will raise an exception;
     * otherwise, it will perform a seek(0).
     *
     * @throws RuntimeException on failure.
     * @link http://www.php.net/manual/en/function.fseek.php
     * @see seek()
     */
    public function rewind(): void
    {
        if ($this->data === null) {
            throw new StreamUnusableException(__FUNCTION__);
        }

        $this->pointer = 0;
    }

    /**
     * Returns whether or not the stream is writable.
     *
     * @return bool
     */
    public function isWritable(): bool
    {
        return $this->data !== null;
    }

    /**
     * Write data to the stream.
     *
     * @param string $string The string that is to be written.
     * @return int Returns the number of bytes written to the stream.
     * @throws RuntimeException on failure.
     */
    public function write($string): int
    {
        if ($this->data === null) {
            throw new StreamUnusableException(__FUNCTION__);
        }

        // If we're at the end of the data, we can just append.
        if ($this->eof()) {
            $this->length  += strlen($string);
            $this->data    .= $string;
            $this->pointer = $this->length;
            return strlen($string);
        }

        // If we're purely overwriting, we can do that with substr.
        // If we have more to write than we can fit, we'll just substr the start and then concatenate the rest.
        $this->data =
            substr($this->data, 0, $this->pointer) .
            $string .
            substr($this->data, $this->pointer + strlen($string));

        // Since we can do both overwriting and appending here, we'll just recalculate:
        $this->length  = strlen($this->data);
        $this->pointer = $this->length;
        return strlen($string);
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @return bool
     */
    public function isReadable(): bool
    {
        return $this->data !== null;
    }

    /**
     * Read data from the stream.
     *
     * @param int $length Read up to $length bytes from the object and return
     *     them. Fewer than $length bytes may be returned if underlying stream
     *     call returns fewer bytes.
     * @return string Returns the data read from the stream, or an empty string
     *     if no bytes are available.
     * @throws RuntimeException if an error occurs.
     */
    public function read($length): string
    {
        if ($this->data === null) {
            throw new StreamUnusableException(__FUNCTION__);
        }

        $slice = substr($this->data, $this->pointer, $length);
        $this->pointer = $this->pointer + $length;
        return $slice;
    }

    /**
     * Returns the remaining contents in a string
     *
     * @return string
     * @throws RuntimeException if unable to read or an error occurs while
     *     reading.
     */
    public function getContents(): string
    {
        if ($this->data === null) {
            throw new StreamUnusableException(__FUNCTION__);
        }

        return $this->read($this->length - $this->pointer);
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @link http://php.net/manual/en/function.stream-get-meta-data.php
     * @param ?string $key Specific metadata to retrieve.
     * @return array<mixed>|null Returns an associative array if no key is
     *     provided. Returns a specific key value if a key is provided and the
     *     value is found, or null if the key is not found.
     */
    public function getMetadata(?string $key = null): ?array
    {
        return null;
    }
}
