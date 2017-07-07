<?php

namespace Phug;

use Phug\Util\Partial\PugFileLocationTrait;
use Phug\Util\PugFileLocationInterface;

/**
 * An exception thrown by the pug reader.
 */
class ReaderException extends \RuntimeException implements PugFileLocationInterface
{
    use PugFileLocationTrait;
}
