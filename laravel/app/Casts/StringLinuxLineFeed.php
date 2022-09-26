<?php
namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class StringLinuxLineFeed implements CastsAttributes
{
    /**
     * @see https://stackoverflow.com/a/7836692
     *
     * @inheritdoc
     */
    public function set($model, $key, $value, $attributes)
    {
        $value = @(string)$value;

        if (strpos($value, "\r") !== false) {
            $value = preg_replace('~(*BSR_ANYCRLF)\R~', "\n", $value);
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function get($model, $key, $value, $attributes)
    {
        if (strpos($value, "\r") !== false) {
            $value = $this->set($model, $key, $value, $attributes);
            // $model->{$key} = $value;
        }

        return $value;
    }
}
