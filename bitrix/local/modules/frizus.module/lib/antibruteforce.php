<?

namespace Frizus\Module;

use Bitrix\Main\Application;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Type\DateTime;
use Frizus\Module\ORM\AntiBruteForceTable;

class AntiBruteForce
{
    public const BLOCK_TYPE_USER = 'user';

    public const BLOCK_TYPE_IP_ADDRESS = 'ip_address';

    public const BLOCK_TYPE_IP_ADDRESS_AND_FIELD = 'ip_address_and_field';

    public const BLOCK_TYPE_USER_AND_FIELD = 'field';

    public static function isNotBlocked($type, $blockType, $config, $value, $fieldType = null, $field = null)
    {
        $row = self::getAntiBruteForceRecordAlt($type, $blockType, $value, $fieldType, $field);

        if ($row) {
            $now = new DateTime();
            $expires = clone $row['BLOCKED_AT'];
            $expires->add('T' . ($row['BLOCK_TIME_MULTIPLIER'] * $config['block_time']) . 'S');

            if ($now > $expires) {
                $forgets = clone $row['BLOCKED_AT'];
                for ($i = $row['BLOCK_TIME_MULTIPLIER']; $i > 0; $i--) {
                    $forgets->add('T' . ($i * $config['block_time'] * 2) . 'S');
                    if ($now <= $forgets) {
                        break;
                    }
                }

                if ($i === 0) {
                    $row->delete();
                } elseif ($i !== $row['BLOCK_TIME_MULTIPLIER']) {
                    $row['BLOCK_TIME_MULTIPLIER'] = $i;
                    $row['ATTEMPTS'] = 0;
                    $row['BLOCKED'] = false;
                    $row->save();
                } else {
                    $row['ATTEMPTS'] = 0;
                    $row['BLOCKED'] = false;
                    $row->save();
                }

                return ['status' => 'success'];
            }

            return ['status' => 'error', 'error' => 'anti_brute_force', 'block_type' => $blockType, 'expires' => $expires, 'block_time_left' => $expires->getTimestamp() - $now->getTimestamp()];
        }

        return ['status' => 'success'];
    }

    public static function failAttempt($type, $blockType, $config, $ipAddress, $userId, $fieldType = null, $field = null)
    {
        $row = self::getAntiBruteForceRecord($type, $blockType, $ipAddress, $userId, $fieldType, $field);

        if ($row) {
            if ($row['BLOCKED']) {
                return;
            }

            $row['ATTEMPTS'] += 1;
            $row->save();
        } else {
            AntiBruteForceTable::add([
                'TYPE' => $type,
                'ATTEMPTS' => 1,
                'BLOCKED' => false,
                'BLOCK_TYPE' => $blockType,
                'IP_ADDRESS' => $ipAddress,
                'USER_ID' => $userId,
                'FIELD_TYPE' => $fieldType,
                'FIELD' => $field,
            ]);
        }
    }
}