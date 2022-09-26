<?php
namespace App\Casts;

use Illuminate\Database\Eloquent\Casts\ArrayObject;

class Properties extends AsCustomArrayObject
{
    /**
     * @param ArrayObject|array $value
     * @return mixed
     */
    public static function sort($value)
    {
        if (isset($value)) {
            if (isset($value['attributes'])) {
                $value['attributes'] = static::attributesSort($value['attributes']);
            }
            if ($value instanceof ArrayObject) {
                $value->uksort([__CLASS__, 'propertiesSort']);
            } else {
                uksort($value, [__CLASS__, 'propertiesSort']);
            }
        }
        return $value;
    }

    public static function propertiesSort($a, $b)
    {
        if ($a === 'pictures') {
            return -1;
        } elseif ($a === 'attributes') {
            if ($b === 'pictures') {
                return 1;
            } else {
                return -1;
            }
        } elseif ($b === 'pictures' || $b === 'attributes') {
            return 1;
        } else {
            return strcmp($a, $b);
        }
    }

    public static function attributesSort($value)
    {
        if (isset($value)) {
            ksort($value);
        }
        return $value;
    }
}
