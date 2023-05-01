<?php

namespace Frizus\Module\Rule\Base;

abstract class Rule
{
    public $nonStandard = false;
    protected $message = 'Некорректное значение :attribute.';

    abstract public function passes($attribute, $value, $keyExists);

    public function message()
    {
        return $this->message;
    }

    public function processing($key, $value, $validatorOnly = false)
    {
        if (isset($this->validator)) {
            $this->validator->processing($key, $value);

            if (!$validatorOnly && !is_null($this->validator->request())) {
                $this->validator->request()->processing($key, $value);
            }
        }
    }

    public function input($key, $value)
    {
        if (isset($this->validator)) {
            $this->validator->setData($key, $value);

            if (!is_null($this->validator->request())) {
                $this->validator->request()->input($key, $value);
            }
        }
    }

    protected function setMessage($message, $nonStandard = true)
    {
        $this->message = $message;
        $this->nonStandard = $nonStandard;
    }
}