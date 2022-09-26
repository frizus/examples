<?php
namespace App\Filters\Concerns;

trait Like
{
    public function isLike($field)
    {
        return property_exists($this, 'likeFields') &&
            in_array($field, $this->likeFields, true);
    }

    public function setLike($field, $value)
    {
        if (!isset($value)) {
            return false;
        }

        $fieldLike = trim($value);
        $haveValue = $fieldLike !== '';
        if (!$haveValue) {
            $fieldLike = null;
        } else {
            // https://stackoverflow.com/questions/3683746/escaping-mysql-wild-cards
            $fieldLike = str_replace(["\\", '_', '%'], ["\\\\", "\\_", "\\%"], $fieldLike);
        }
        $this->{$field . 'Like'} = $fieldLike;
        return $haveValue;
    }
}
