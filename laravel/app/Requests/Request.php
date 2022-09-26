<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class Request extends FormRequest
{
    /**
     * @inerhitdoc
     */
    public function authorize()
    {
        return true;
    }

    /**
     * @inerhitdoc
     */
    public function all($keys = null)
    {
        $input = $this->input();

        if (! $keys) {
            return $input;
        }

        $results = [];

        foreach (is_array($keys) ? $keys : func_get_args() as $key) {
            Arr::set($results, $key, Arr::get($input, $key));
        }

        return $results;
    }

    public function setInput($key, $value)
    {
        $this->request->set($key, $value);
    }
}
