<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class IntBoolean implements Rule
{
    /**
     * @inerhitdoc
     */
    public function passes($attribute, $value)
    {
        return in_array($value, ['0', '1'], true);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Поле :attribute должно иметь значение 0 или 1.';
    }
}
