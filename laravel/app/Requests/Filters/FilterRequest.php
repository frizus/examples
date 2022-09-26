<?php
namespace App\Http\Requests\Filters;

use App\Http\Requests\Request;
use Illuminate\Contracts\Validation\Validator;

class FilterRequest extends Request
{
    protected function failedValidation(Validator $validator)
    {
        $keys = $validator->getMessageBag()->keys();
        foreach ($keys as $field) {
            $this->query->set($field, null);
        }
    }

    public function setInput($key, $value)
    {
        $this->query->set($key, $value);
    }

    /**
     * @inheritdoc
     */
    public function input($key = null, $default = null)
    {
        return data_get($this->query->all(), $key, $default);
    }
}
