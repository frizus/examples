<?

namespace Frizus\Module\Helper;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Data\DataManager;

class HLBlockHelper
{
    /**
     * @param $name
     * @return DataManager|false
     */
    public static function getClass($name)
    {
        static $entityDataClasses = [];

        if (!$name ||
            !Loader::includeModule('highloadblock')
        ) {
            return false;
        }

        if (!isset($entityDataClasses[$name])) {
            $hlblock = HighloadBlockTable::getList([
                'filter' => ['=NAME' => $name],
                'limit' => 1,
            ])->fetch();
            if ($hlblock) {
                $entity = HighloadBlockTable::compileEntity($hlblock);
                $entityDataClasses[$name] = $entity->getDataClass();
            } else {
                $entityDataClasses[$name] = false;
            }
        }

        return $entityDataClasses[$name];
    }
}