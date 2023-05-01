<?php

namespace Frizus\Module\Validation\Traits;

use Frizus\Module\Helper\Arr;
use Frizus\Module\Request\Helper\UploadedFile;
use Frizus\Module\Request\Helper\UploadedFiles;

trait ValidateAttributes
{
    public function validateInteger($attribute, $value, $keyExists)
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    public function validateIntegerId($attribute, $value, $keyExists)
    {
        return (filter_var($value, FILTER_VALIDATE_INT) !== false) &&
            (intval($value) > 0);
    }

    public function validateRequired($attribute, $value, $keyExists)
    {
        return $keyExists;
    }

    public function validateNotRequired($attribute, $value, $keyExists)
    {
        if (!$keyExists) {
            $this->skipNotRequiredOrEmpty[$attribute] = true;
        }

        return true;
    }

    public function validateNotRequiredOrEmpty($attribute, $value, $keyExists)
    {
        if (
            !$keyExists ||
            (is_array($value) && empty($value)) ||
            (!is_array($value) && (strval($value) === ''))
        ) {
            $this->skipNotRequiredOrEmpty[$attribute] = true;
        }

        return true;
    }

    public function validateNotEmptyString($attribute, $value, $keyExists)
    {
        return is_string($value) && ($value !== '');
    }

    public function validateCsrf($attribute, $value, $keyExists)
    {
        $sessid = bitrix_sessid();
        if (!
        (
            $keyExists &&
            ($value !== '') &&
            ($value === $sessid)
        )
        ) {
            $this->extra('csrf', bitrix_sessid());
            return false;
        }

        return true;
    }

    public function validateInt1($attribute, $value, $keyExists)
    {
        return $value === '1';
    }

    public function validateMax($attribute, $value, $keyExists, $parameters)
    {
        if (isset($parameters[0])) {
            $max = doubleval(trim($parameters[0]));
        } else {
            $max = 0;
        }

        foreach (Arr::wrap($this->getSize($attribute, $value)) as $singleSize) {
            if ($singleSize > $max) {
                return false;
            }
        }

        return true;
    }

    protected function getSize($attribute, $value)
    {
        $hasNumeric = $this->hasRule($attribute, $this->numericRules);

        if (is_array($value)) {
            return count($value);
        } elseif ($value instanceof UploadedFiles) {
            return array_map(function ($elem) {
                return $elem / 1024;
            }, $value->getSizes());
        } elseif ($value instanceof UploadedFile) {
            return $value->getSize() / 1024;
        } elseif ($hasNumeric) {
            if (filter_var($value, FILTER_VALIDATE_INT) !== false) {
                return intval($value);
            }

            return doubleval($value);
        }

        return mb_strlen($value ?? '');
    }

    public function validateFile($attribute, $value, $keyExists, $parameters)
    {
        $multiple = $parameters[0] === 'true';

        if ($multiple) {
            if (!($value instanceof UploadedFiles)) {
                return false;
            }
        } else {
            if (!($value instanceof UploadedFile)) {
                return false;
            }
        }

        return $value->uploaded();
    }
}