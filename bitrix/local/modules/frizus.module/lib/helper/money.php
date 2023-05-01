<?

namespace Frizus\Module\Helper;

class Money
{
    public static function roubles($value, $addSuffix = false)
    {
        return str_replace('.00', '', number_format(floatval($value), 2, '.', ' ')) . ($addSuffix ? ' руб.' : '');
    }
}