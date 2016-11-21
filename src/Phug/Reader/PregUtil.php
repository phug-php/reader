<?php

namespace Phug\Reader;

class PregUtil
{

    const DEFAULT_ERROR = 'default';

    private static $errorTexts = [
        self::DEFAULT_ERROR        => 'Unknown error',
        PREG_NO_ERROR              => 'No error occured',
        PREG_INTERNAL_ERROR        => 'An internal error occured',
        PREG_BACKTRACK_LIMIT_ERROR => 'The backtrack limit was exhausted (Increase pcre.backtrack_limit in php.ini)',
        PREG_RECURSION_LIMIT_ERROR => 'Recursion limit was exhausted (Increase pcre.recursion_limit in php.ini)',
        PREG_BAD_UTF8_ERROR        => 'Bad UTF8 error!',
        PREG_BAD_UTF8_OFFSET_ERROR => 'Bad UTF8 offset error'
    ];

    private function __construct() {}

    public static function setErrorText($constant, $text)
    {

        self::$errorTexts[$constant] = $text;
    }

    public static function getErrorText($constant)
    {

        return isset(self::$errorTexts[$constant])
            ? self::$errorTexts[$constant]
            : self::$errorTexts[self::DEFAULT_ERROR];
    }

    public static function setDefaultErrorText($text)
    {

        self::setErrorText(self::DEFAULT_ERROR, $text);
    }

    public static function getLastPregErrorText()
    {

        $code = preg_last_error();
        $text = isset(self::$errorTexts[$code])
            ? self::$errorTexts[$code]
            : self::$errorTexts['default'];

        return "$text ($code)";
    }
}