<?php
namespace App\Http\Requests;

use Carbon\Carbon;

trait ParseDate
{
    public function parseDate($string, $type)
    {
        $string = (string)$string;
        if ($string === '' || !in_array($type, ['start', 'end'], true)) {
            return null;
        }

        $timezone = display_timezone();
        try {
            $date = Carbon::createFromFormat(config('app.datetime_format'), $string, $timezone);
            return [
                'type' => 'datetime',
                'position' => $type,
                'value' => $date,
            ];
        } catch (\Exception $e) {}
        try {
            $date = Carbon::createFromFormat(config('app.datetime_wo_seconds_format'), $string, $timezone);
            $date->setSecond(0);
            return [
                'type' => 'datetime_wo_seconds',
                'position' => $type,
                'value' => $date,
            ];
        } catch (\Exception $e) {}
        try {
            $date = Carbon::createFromFormat(config('app.date_format'), $string, $timezone);
            $date->setTime(0, 0, 0);
            return [
                'type' => 'date',
                'position' => $type,
                'value' => $date,
            ];
        } catch (\Exception $e) {}

        return null;
    }
}
