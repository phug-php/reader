<?php

namespace Phug\Reader\Stream;

use Phug\Reader\Stream;

class MemoryStream extends Stream
{

    public function __construct($mode = null, $content = null)
    {
        parent::__construct('php://memory', $mode);

        if ($content) {

            $this->write($content);
            $this->rewind();
        }
    }
}