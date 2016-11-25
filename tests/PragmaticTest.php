<?php

namespace Tale\Phug\Test;

use Phug\Reader;
use Phug\ReaderException;

class ReaderTest extends \PHPUnit_Framework_TestCase
{

    public function testReadString()
    {


        $this->assertEquals('abc"def', (new Reader('"abc\"def" ghi'))->readString());
        $this->assertEquals('abc"def', (new Reader('\'abc"def\' ghi'))->readString());
        $this->assertEquals('abc`def', (new Reader('`abc\`def` ghi'))->readString());
        $this->assertEquals('"abc\"def"', (new Reader('"abc\"def" ghi'))->readString(null, true));
        $this->assertEquals('`abc\`def`', (new Reader('`abc\`def` ghi'))->readString(null, true));
        $this->assertEquals('abc a fucking bear def', (new Reader('"abc\Xdef" ghi'))->readString([
            'X' => ' a fucking bear '
        ]));


        $this->setExpectedException(ReaderException::class);
        (new Reader('"abc'))->readString();

        $this->setExpectedException(ReaderException::class);
        (new Reader('"\'abc\''))->readString();
    }

    public function testReadExpression()
    {

        $this->assertEquals('{ $abc (def) }', (new Reader('{ $abc (def) } ghi'))->readExpression([' ']));
        $this->assertEquals('$a ? ($b, $c) : $d', (new Reader('$a ? ($b, $c) : $d, $f, $g'))->readExpression([',']));
        $this->assertEquals('$a["1, 2", $f, $g]', (new Reader('$a["1, 2", $f, $g], $f, $g'))->readExpression([',']));

        $this->setExpectedException(ReaderException::class);
        (new Reader('([), '))->readExpression([',']);

        $this->setExpectedException(ReaderException::class);
        (new Reader('([)]'))->readExpression([',']);

        $this->setExpectedException(ReaderException::class);
        (new Reader('($a{$b},'))->readExpression([',']);
    }

    public function testReadmeFirstLexingExample()
    {


        //Some C-style example code
        $code = 'someVar = {a, "this is a string (really, it \"is\")", func(b, c), d}';

        $reader = new Reader($code);
        $tokens = [];
        $blockLevel = 0;
        $expressionLevel = 0;
        while ($reader->hasLength()) {
            //Skip spaces of any kind.
            $reader->readSpaces();

            //Scan for identifiers
            if ($identifier = $reader->readIdentifier()) {
                $tokens[] = ['type' => 'identifier', 'name' => $identifier];
                continue;
            }

            //Scan for Assignments
            if ($reader->peekChar('=')) {
                $reader->consume();
                $tokens[] = ['type' => 'assignment'];
                continue;
            }

            //Scan for strings
            if (($string = $reader->readString()) !== null) {
                $tokens[] = ['type' => 'string', 'value' => $string];
                continue;
            }

            //Scan block start
            if ($reader->peekChar('{')) {
                $reader->consume();
                $blockLevel++;
                $tokens[] = ['type' => 'blockStart'];
                continue;
            }

            //Scan block end
            if ($reader->peekChar('}')) {
                $reader->consume();
                $blockLevel--;
                $tokens[] = ['type' => 'blockEnd'];
                continue;
            }

            //Scan parenthesis start
            if ($reader->peekChar('(')) {
                $reader->consume();
                $expressionLevel++;
                $tokens[] = ['type' => 'listStart'];
                continue;
            }

            //Scan parenthesis end
            if ($reader->peekChar(')')) {
                $reader->consume();
                $expressionLevel--;
                $tokens[] = ['type' => 'listEnd'];
                continue;
            }

            //Scan comma
            if ($reader->peekChar(',')) {
                $reader->consume();
                $tokens[] = ['type' => 'next'];
                continue;
            }

            throw new \Exception(
                "Unexpected ".$reader->peek(10)
            );
        }

        $this->assertSame([
            ['type' => 'identifier', 'name' => 'someVar'],
            ['type' => 'assignment'],
            ['type' => 'blockStart'],
            ['type' => 'identifier', 'name' => 'a'],
            ['type' => 'next'],
            ['type' => 'string', 'value' => 'this is a string (really, it "is")'],
            ['type' => 'next'],
            ['type' => 'identifier', 'name' => 'func'],
            ['type' => 'listStart'],
            ['type' => 'identifier', 'name' => 'b'],
            ['type' => 'next'],
            ['type' => 'identifier', 'name' => 'c'],
            ['type' => 'listEnd'],
            ['type' => 'next'],
            ['type' => 'identifier', 'name' => 'd'],
            ['type' => 'blockEnd']
        ], $tokens);
    }

    public function testReadmeSecondLexingExample()
    {

        $jade = 'a(href=getUri(\'/abc\', true), title=(title ? title : \'Sorry, no title.\'))';

        $reader = new Reader($jade);

        //Scan Identifier ("a")
        $identifier = $reader->readIdentifier();

        $attributes = [];
        //Enter an attribute block if available
        if ($reader->peekChar('(')) {
            $reader->consume();
            while ($reader->hasLength()) {
                //Ignore spaces
                $reader->readSpaces();


                //Scan the attribute name
                if (!($name = $reader->readIdentifier())) {
                    throw new \Exception("Attributes need a name!");
                }


                //Ignore spaces
                $reader->readSpaces();


                //Make sure there's a =-character
                if (!$reader->peekChar('=')) {
                    throw new \Exception("Failed to read: Expected attribute value");
                }

                $reader->consume();


                //Ignore spaces
                $reader->readSpaces();


                //Read the expression until , or ) is encountered
                //It will ignore , and ) inside any kind of brackets and count brackets correctly until we actually
                //reached the end-bracket
                $value = $reader->readExpression([',', ')']);


                //Add the attribute to our attribute array
                $attributes[$name] = $value;


                //If we don't encounter a , to go on, we break the loop
                if (!$reader->peekChar(',')) {
                    break;
                }


                //Else we consume the , and continue our attribute parsing
                $reader->consume();
            }

            //Now make sure we actually closed our attribute block correctly.
            if (!$reader->peekChar(')')) {
                throw new \Exception("Failed to read: Expected closing bracket");
            }
        }


        $element = ['identifier' => $identifier, 'attributes' => $attributes];
        $this->assertEquals([
            'identifier' => 'a',
            'attributes' => [
                'href' => 'getUri(\'/abc\', true)',
                'title' => '(title ? title : \'Sorry, no title.\')'
            ]], $element);
    }
}
