<?php

namespace Frizus\Module\Request\Base;

class FormRequest extends AjaxRequest
{
    protected function failedValidation($validator)
    {
        throw new ValidationException($validator);
    }
}
