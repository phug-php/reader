<?php

namespace Phug\Reader;

/**
 * A helper trait to provide line and offset information to an object.
 * 
 */
trait LineOffsetTrait
{

    private $line = 0;
    private $offset = 0;

    /**
     * Returns the line this object is associated with.
     *
     * @return int
     */
    public function getLine()
    {

        return $this->line;
    }

    /**
     * Returns the offset in a line this object is associated with.
     *
     * @return int
     */
    public function getOffset()
    {

        return $this->offset;
    }
}