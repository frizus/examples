<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Support\Arr;

class AsCustomArrayObject implements Castable
{
    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @param  array  $arguments
     * @return object|string
     */
    public static function castUsing(array $arguments)
    {
        return new class implements CastsAttributes
        {
            public function get($model, $key, $value, $attributes)
            {
                return isset($attributes[$key]) ? new ArrayObject(json_decode($attributes[$key], true)) : null;
            }

            public function set($model, $key, $value, $attributes)
            {
                return [$key => json_encode($value)];
            }

            public function serialize($model, string $key, $value, array $attributes)
            {
                return $value->getArrayCopy();
            }
        };
    }

    public static function compare($attribute, $original)
    {
        if (is_null($attribute) && is_null($original)) {
            return true;
        } elseif (is_null($attribute) !== is_null($original)) {
            return false;
        } else {
            $attributeArray = json_decode($attribute, true);
            $originalArray = json_decode($original, true);
            if (!is_array($attributeArray) || !is_array($originalArray)) {
                return $attributeArray === $originalArray;
            }
            return static::arraysMatch($attributeArray, $originalArray);
        }
    }

    protected static function arraysMatch($new, $original)
    {
        if (count($new) !== count($original)) {
            return false;
        }
        $originalIsList = Arr::isList($original);
        $newIsList = Arr::isList($new);
        if ($originalIsList !== $newIsList) {
            return false;
        }
        foreach ($new as $key => $newValue) {
            if (!$originalIsList && !array_key_exists($key, $original)) {
                return false;
            }
            if (gettype($newValue) !== gettype($original[$key])) {
                return false;
            }
            if (is_array($newValue)) {
                if (!static::arraysMatch($newValue, $original[$key])) {
                    return false;
                }
            } else {
                if ($newValue !== $original[$key]) {
                    return false;
                }
            }
        }
        return true;
    }
}
