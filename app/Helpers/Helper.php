<?php

namespace App\Helpers;

class Helper
{
    public static function stringToTime(?string $string): ?int
    {
        if (is_null($string)) {
            return null;
        }

        $string = preg_replace("/^([\d]{1,2}):([\d]{2})$/", '00:$1:$2', $string);
        sscanf($string, '%d:%d:%d', $hours, $minutes, $seconds);

        return $hours * 3600 + $minutes * 60 + $seconds;
    }
}
