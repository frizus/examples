<?php
namespace Frizus\Module\Validation\Traits;

trait ValidateAttributes
{
    public function validateInteger($attribute, $value, $keyExists)
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    public function validateInteger_Id($attribute, $value, $keyExists)
    {
        return (filter_var($value, FILTER_VALIDATE_INT) !== false) &&
            (intval($value) > 0);
    }

    public function validateRequired($attribute, $value, $keyExists)
    {
        return $keyExists;
    }
}
