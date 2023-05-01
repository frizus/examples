<?php

namespace Frizus\Module\Rule;

use Bitrix\Main\UserTable;
use Frizus\Module\Rule\Base\Rule;

class UniqueUserEmailRule extends Rule
{
    public $exceptUserId;
    protected $message = 'Пользователь с такой почтой есть';

    public function __construct($exceptUserId = null)
    {
        $this->exceptUserId = $exceptUserId;
    }

    public function passes($attribute, $value, $keyExists)
    {
        if (!$keyExists ||
            is_null($value) ||
            !is_string($value) ||
            ($value === '')
        ) {
            return false;
        }

        $result = UserTable::getList([
            'select' => ['ID'],
            'filter' => [
                '=EMAIL' => $value,
            ],
            'limit' => 1,
        ]);

        if ($row = $result->fetchObject()) {
            if (isset($this->exceptUserId)) {
                if (intval($row['ID']) === intval($this->exceptUserId)) {
                    $this->processing($attribute . '-same-email', true);
                    return true;
                }
            }

            return false;
        }

        return true;
    }
}