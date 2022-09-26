<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NotEmptyString implements Rule
{
    /**
     * @inerhitdoc
     */
    public function passes($attribute, $value)
    {
        return is_string($value) && $value !== '';
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Поле :attribute не должно быть пустым.';
    }
}
