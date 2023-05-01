<?php

namespace Frizus\Module\Helper;

class Str
{
    public static function ucfirst($string)
    {
        $string = mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1);
        return $string;
    }

    public static function convertToLinuxLineFeed($string)
    {
        $string = @(string)$string;

        if (strpos($string, "\r") !== false) {
            $string = preg_replace('~(*BSR_ANYCRLF)\R~', "\n", $string);
        }

        return $string;
    }
}
