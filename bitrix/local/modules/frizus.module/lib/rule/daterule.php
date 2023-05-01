<?php

namespace Frizus\Module\Rule;

use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Frizus\Module\Helper\Phone;
use Frizus\Module\Rule\Base\Rule;
use Frizus\Module\Rule\Base\ValidatorAwareRule;

class DateRule extends Rule
{
    use ValidatorAwareRule;

    protected $message = 'Неверная дата';

    protected $format;

    protected $min;

    protected $max;

    protected $timezone;

    public function __construct($format = 'd.m.Y', $min = null, $max = null, $timezone = null)
    {
        $this->format = $format ?? 'd.m.Y';
        $this->min = $min;
        $this->max = $max;
        $this->timezone = $timezone;
    }

    public function passes($attribute, $value, $keyExists)
    {
        if (!$keyExists ||
            is_null($value) ||
            !is_string($value) ||
            ($value === '')
        ) {
            return false;
        }

        try {
            $value = new DateTime($value, $this->format, $this->timezone);
            $value->setTime(0, 0, 0);
        } catch (SystemException $e) {
            return false;
        }

        if (isset($this->min)) {
            if ($this->min > $value) {
                $this->setMessage('Дата не может быть раньше ' . $this->min->format($this->format));
                return false;
            }
        }

        if (isset($this->max)) {
            if ($this->max < $value) {
                $this->setMessage('Дата не может быть позже ' . $this->max->format($this->format));
                return false;
            }
        }

        return true;
    }
}