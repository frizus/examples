<?php

namespace Frizus\Module\Helper;

use CIBlock;
use Exception;

class IBlock
{
    public static function getIdByCode($code)
    {
        static $ids = [];
        if (!array_key_exists($code, $ids)) {
            $result = static::getIBlock($code, false);
            if ($result === false) {
                throw new Exception("Инфоблок с кодом $code не найден.");
            }
            $ids[$code] = $result['ID'];
        }

        return $ids[$code];
    }

    protected static function getIBlock($value, $byId)
    {
        $filter = ['CHECK_PERMISSIONS' => 'N'];
        if ($byId) {
            $filter['ID'] = $value;
        } else {
            $filter['CODE'] = $value;
        }

        $rsData = CIBlock::GetList([], $filter);
        if (!$rsData) {
            throw new Exception("Ошибка запроса к БД в файле " . __FILE__ . " строка " . __LINE__);
        }
        if ($arData = $rsData->Fetch()) {
            return $arData;
        }

        return false;
    }
}
