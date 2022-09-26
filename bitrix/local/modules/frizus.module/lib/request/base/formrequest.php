<?php
namespace Frizus\Module\Request\Base;

use Frizus\Module\Validation\Validator;

class FormRequest extends AjaxRequest
{
    protected function failedValidation()
    {
        throw new ValidationException($validator);
    }
}
