<?php

namespace Tale\Phug\Test;

use Phug\Reader;

/**
 * @coversDefaultClass Phug\Reader
 */
class ReaderTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers ::__construct
     * @covers ::getInput
     */
    public function testGetInput()
    {
        
        $reader = new Reader('some string');
        $this->assertEquals('some string', $reader->getInput());
    }

    /**
     * @covers ::__construct
     * @covers ::getEncoding
     */
    public function testIfDefaultEncodingIsUtf8ByDefault()
    {

        $reader = new Reader('');
        $this->assertEquals('UTF-8', $reader->getEncoding());
    }

    /**
     * @covers ::__construct
     * @covers ::getEncoding
     */
    public function testGetEncoding()
    {

        $reader = new Reader('', 'ASCII');
        $this->assertEquals('ASCII', $reader->getEncoding());
    }

    /**
     * @covers ::getLastPeekResult
     * @covers ::peek
     */
    public function testGetLastPeekResult()
    {

        $reader = new Reader('abc');
        $this->assertEquals(null, $reader->getLastPeekResult(), 'not peeked yet');

        $reader->peek(2);
        $this->assertEquals('ab', $reader->getLastPeekResult(), 'peeked');
    }

    /**
     * @covers ::getLastMatchResult
     * @covers ::match
     */
    public function testGetLastMatchResult()
    {

        $reader = new Reader('abc');
        $this->assertEquals(null, $reader->getLastMatchResult(), 'not matched yet');

        //On valid match
        $reader->match('a(.*)');
        $this->assertEquals(['abc', 'bc'], $reader->getLastMatchResult(), 'matched valid');

        //On invalid match
        $reader->match('b(.*)');
        $this->assertEquals(null, $reader->getLastMatchResult(), 'matched invalid');
    }

    /**
     * @covers ::getNextConsumeLength
     * @covers ::peek
     * @covers ::match
     */
    public function testGetNextConsumeLength()
    {

        $reader = new Reader('abc def');
        $this->assertEquals(null, $reader->getNextConsumeLength(), 'not peeked/matched yet');
        
        $reader->peek(2);
        $this->assertEquals(2, $reader->getNextConsumeLength(), 'peeked');

        $reader->match('b[^ ]+');
        $this->assertEquals(null, $reader->getNextConsumeLength(), 'matched invalid');

        $reader->match('a[^ ]+');
        $this->assertEquals(3, $reader->getNextConsumeLength(), 'matched valid');
    }

    /**
     * @covers ::getNextConsumeLength
     * @covers ::match
     */
    public function testIfMatchIgnoresTrailingNewLines()
    {


        $reader = new Reader("some\nstring");
        $reader->match('some\s');
        $this->assertEquals(4, $reader->getNextConsumeLength());
    }

    /**
     * @covers ::getPosition
     * @covers ::getLine
     * @covers ::getOffset
     */
    public function testCorrectCalculationOfPositionInformation()
    {


        $reader = new Reader("some\nstring");
        $this->assertEquals(0, $reader->getPosition(), 'position after construct');
        $this->assertEquals(1, $reader->getLine(), 'line after construct');
        $this->assertEquals(1, $reader->getOffset(), 'offset after construct');

        $reader->consume(3);
        $this->assertEquals(3, $reader->getPosition(), 'position after 3 bytes');
        $this->assertEquals(1, $reader->getLine(), 'line after 3 bytes');
        $this->assertEquals(4, $reader->getOffset(), 'offset after 3 bytes');

        $reader->consume(4);
        $this->assertEquals(7, $reader->getPosition(), 'position after 7 bytes');
        $this->assertEquals(2, $reader->getLine(), 'line after 7 bytes');
        $this->assertEquals(2, $reader->getOffset(), 'offset after 7 bytes');
    }
}
