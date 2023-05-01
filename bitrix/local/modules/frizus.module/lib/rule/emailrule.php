<?php

namespace Frizus\Module\Rule;

use Frizus\Module\Rule\Base\Rule;

class EmailRule extends Rule
{
    /**
     * @see https://stackoverflow.com/questions/7786058/find-the-regex-used-by-html5-forms-for-validation
     */
    public const EMAIL_REGEX = "/^[a-z0-9!#$%&'*+\/=?^_`{|}~.-]+@[a-z0-9-]+(\.[a-z0-9-]+)+$/i";

    protected $message = 'Неверная почта';

    public function passes($attribute, $value, $keyExists)
    {
        if (!$keyExists ||
            is_null($value) ||
            !is_string($value) ||
            ($value === '')
        ) {
            return false;
        }

        /** @see check_email() */
        if (!preg_match(self::EMAIL_REGEX, $value) || (mb_strlen($value) > 320)) {
            return false;
        }

        $this->input($attribute, mb_strtolower($value));

        return true;
    }
}