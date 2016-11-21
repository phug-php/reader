<?php

namespace Phug\Reader;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;

/**
 * A basic stream wrapper to unify reading methods.
 *
 * @package Tale
 */
class Stream implements StreamInterface
{

    /**
     * The default stream mode
     */
    const DEFAULT_MODE = 'rb+';

    /**
     * The current stream context (file resource)
     *
     * @var resource
     */
    private $context;

    /**
     * An array of meta data information
     *
     * @var array
     */
    private $metadata;


    /**
     * Stream constructor.
     *
     * @param UriInterface|string|resource $context
     * @param null $mode
     */
    public function __construct($context, $mode = null)
    {

        $this->context = $context;
        $mode = $mode ?: self::DEFAULT_MODE;

        if (is_object($context) && method_exists($context, '__toString'))
            $this->context = (string)$this->context;

        if (is_string($this->context))
            $this->context = fopen($this->context, $mode);

        if (!is_resource($this->context))
            throw new InvalidArgumentException(
                'Argument 1 needs to be resource or path/URI'
            );

        $this->metadata = stream_get_meta_data($this->context);
    }

    /**
     *
     */
    public function __destruct()
    {

        $this->close();
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {

        if (!$this->context) {

            return;
        }

        $context = $this->detach();
        fclose($context);
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {

        $context = $this->context;
        $this->context = null;
        $this->metadata = null;

        return $context;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {

        if ($this->context === null)
            return null;

        $stat = fstat($this->context);

        return $stat['size'];
    }

    /**
     * {@inheritdoc}
     */
    public function tell()
    {

        $result = ftell($this->context);
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function eof()
    {

        if (!$this->context)
            return true;

        return feof($this->context);
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable()
    {

        if (!$this->context)
            return false;

        return $this->getMetadata('seekable') ? true : false;
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = \SEEK_SET)
    {

        if (!$this->isSeekable())
            throw new RuntimeException(
                'Stream is not seekable'
            );

        fseek($this->context, $offset, $whence);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {

        return $this->seek(0);
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable()
    {

        if (!$this->context)
            return false;

        $mode = $this->getMetadata('mode');
        return (strstr($mode, 'w') || strstr($mode, 'x') || strstr($mode, 'c') || strstr($mode, '+'));
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {

        if (!$this->isWritable())
            throw new RuntimeException(
                'Stream is not writable'
            );

        return fwrite($this->context, $string);
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable()
    {

        if (!$this->context)
            return false;

        $mode = $this->getMetadata('mode');
        return (strstr($mode, 'r') || strstr($mode, '+'));
    }

    /**
     * {@inheritdoc}
     */
    public function read($length)
    {

        if (!$this->isReadable())
            throw new RuntimeException(
                'Stream is not readable'
            );

        return fread($this->context, $length);
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {

        if (!$this->isReadable())
            throw new RuntimeException(
                'Stream is not readable'
            );

        return stream_get_contents($this->context);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {

        if ($key === null)
            return $this->metadata;

        if (!isset($this->metadata[$key]))
            return null;

        return $this->metadata[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {

        if (!$this->isReadable())
            return '';

        if ($this->isSeekable())
            $this->rewind();

        return $this->getContents();
    }

    /**
     *
     */
    private function __clone() {}
}