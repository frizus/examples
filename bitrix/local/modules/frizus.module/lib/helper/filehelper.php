<?

namespace Frizus\Module\Helper;

class FileHelper
{
    public static function formatSize($bytes)
    {
        static $byteUnits = ["Б", "КБ", "МБ", "ГБ"];
        static $bytePrecision = [0, 0, 1, 2, 2, 3, 3, 4, 4];
        static $byteNext = 1024;
        $bytes = (int)$bytes;
        for ($i = 0; ($bytes / $byteNext) >= 0.9 && $i < count($byteUnits); $i++) {
            $bytes /= $byteNext;
        }

        return round($bytes, $bytePrecision[$i]) . ' ' . $byteUnits[$i];
    }
}