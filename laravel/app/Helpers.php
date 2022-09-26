<?php

use Carbon\Carbon;

function display_timezone()
{
    static $timezone;
    if (!isset($timezone)) {
        $timezone = config('app.display_timezone');
    }

    return $timezone;
}

function display_format()
{
    static $format;
    if (!isset($format)) {
        $format = config('app.datetime_format');
    }

    return $format;
}

function filter_counts()
{
    static $static;
    if (!isset($static)) {
        $static = config('scrapper.filter_counts');
    }

    return $static;
}

function display_datetime($datetime)
{
    if (isset($datetime) && $datetime instanceof Carbon) {
        return $datetime->clone()->setTimezone(display_timezone())->format(display_format());
    }
}

function asset_skip_cache($path, $secure = null)
{
    $absolute_path = public_path($path);
    if (file_exists($absolute_path)) {
        $path .= '?' . filemtime($absolute_path);
    }
    return asset($path, $secure);
}

function format_price($value)
{
    return str_replace('.00', '', number_format($value / 100, 2, '.', '&nbsp;')) . ' руб.';
}
