<?php
namespace App\Filters\Concerns;

use Carbon\Carbon;

trait Date
{
    public function parseDate($string, $position)
    {
        $timezone = display_timezone();
        foreach ([
            'date_short_year' => [1, 'date'],
            'date' => [1, 'date'],
            'datetime_short_year_no_seconds' => [2, 'datetime'],
            'datetime_short_year' => [0, 'datetime'],
            'datetime_no_seconds' => [2, 'datetime'],
            'datetime' => [0, 'datetime']
        ] as $formatType => $config) {
            try {
                $date = Carbon::createFromFormat(config('app.' . $formatType . '_format'), $string, $timezone);
                if ($config[0] === 1) {
                    $date->setTime(0, 0, 0);
                } elseif ($config[0] === 2) {
                    $date->setSeconds(0);
                }

                return [
                    'type' => $config[1],
                    'formatType' => $formatType,
                    'position' => $position ?? 'equals',
                    'value' => $date,
                ];
            } catch (\Exception $e) {

            }
        }

        return null;
    }

    public function swapWrongDateRanges()
    {
        if (property_exists($this, 'dateRanges')) {
            foreach ($this->dateRanges as $dateRange) {
                $from = $this->{$dateRange[0] . 'Date'};
                $to = $this->{$dateRange[1] . 'Date'};
                if (!is_null($from) && !is_null($to) && $from['value']->gt($to['value'])) {
                    $this->{$dateRange[0] . 'Date'}['value'] = $to['value'];
                    $this->{$dateRange[1] . 'Date'}['value'] = $from['value'];
                }
            }
        }
    }

    protected function dateRangePosition($field)
    {
        if (property_exists($this, 'dateRanges')) {
            foreach ($this->dateRanges as $dateRange) {
                if ($dateRange[0] === $field) {
                    return 'start';
                }
                if ($dateRange[1] === $field) {
                    return 'end';
                }
            }
        }

        return null;
    }

    public function formatDate($date)
    {
        if (!isset($date)) {
            return null;
        }

        return $date['value']->format(config('app.' . $date['formatType'] . '_format'));
    }

    public function addDateToQuery($query, $column, $date)
    {
        if (!isset($date)) {
            return null;
        }

        $dateWithAppTimezone = $date['value']->clone()->setTimezone(config('app.timezone'));
        if ($date['type'] === 'datetime') {
            if ($date['position'] === 'start') {
                $query->where($column, '>=', $dateWithAppTimezone);
            } elseif ($date['position'] === 'end') {
                $query->where($column, '<=', $dateWithAppTimezone);
            }
        } elseif ($date['type'] === 'date') {
            if ($date['position'] === 'start') {
                $query->where($column, '>=', $dateWithAppTimezone);
            } elseif ($date['position'] === 'end') {
                $dateWithAppTimezone->addDay();
                $query->where($column, '<', $dateWithAppTimezone);
            }
        } elseif ($date['position'] === 'equals') {
            $query->where($column, $dateWithAppTimezone);
        }
    }

    public function isDate($field)
    {
        return property_exists($this, 'dateFields') &&
            in_array($field, $this->dateFields, true);
    }

    public function setDate($field, $value)
    {
        if (!isset($value)) {
            return false;
        }

        $fieldDate = trim($value);
        $haveValue = $fieldDate !== '';
        if (!$haveValue) {
            $fieldDate = null;
        } else {
            if (($fieldDate = $this->parseDate($fieldDate, $this->dateRangePosition($field))) === null) {
                $haveValue = false;
            }
        }
        $this->{$field . 'Date'} = $fieldDate;
        return $haveValue;
    }
}
