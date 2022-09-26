<?php
namespace App\Models;

use App\Casts\AsCustomArrayObject;
use App\Casts\StringLinuxLineFeed;
use Eloquent as Model;
use Illuminate\Support\Arr;

class Base extends Model
{
    public function hasModelEvent($event)
    {
        $name = static::class;

        return
            isset(static::$dispatcher) &&
            (
                isset($this->dispatchesEvents[$event]) ||
                static::$dispatcher->hasListeners("eloquent.{$event}: {$name}")
            );
    }

    /**
     * inheritdoc
     */
    public function originalIsEquivalent($key)
    {
        if (array_key_exists($key, $this->original)) {
            $match = false;
            $classCastable = $this->isClassCastable($key);
            $class = $this->getCasts()[$key];
            if ($classCastable && in_array($class, [StringLinuxLineFeed::class], true)) {
                $match = StringLinuxLineFeed::class;
            } elseif ($classCastable && is_a($class, AsCustomArrayObject::class, true)) {
                $match = AsCustomArrayObject::class;
            }
            if ($match !== false) {
                $attribute = Arr::get($this->attributes, $key);
                $original = Arr::get($this->original, $key);
                if ($attribute === $original) {
                    return true;
                } elseif (is_null($attribute)) {
                    return false;
                } elseif ($match === StringLinuxLineFeed::class) {
                    return $this->castAttribute($key, $attribute) ===
                        $this->castAttribute($key, $original);
                } elseif ($match === AsCustomArrayObject::class) {
                    return AsCustomArrayObject::compare($attribute, $original);
                }
            }
        }

        return parent::originalIsEquivalent($key);
    }
}
