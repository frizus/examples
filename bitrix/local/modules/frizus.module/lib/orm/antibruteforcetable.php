<?php

namespace Frizus\Module\ORM;

use Bitrix\Main\Entity\BooleanField;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\Type\DateTime;
use Frizus\Module\ORM\Traits\ConvertEmptyStringsToNull;

/**
 * Class AntiBruteForceTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ATTEMPTS int optional
 * <li> BLOCKED int optional
 * <li> BLOCKED_AT datetime optional
 * <li> BLOCK_TYPE string(255) mandatory
 * <li> BLOCK_TIME_MULTIPLIER int optional
 * <li> IP_ADDRESS string(255) optional
 * <li> USER_ID int optional
 * <li> FIELD_TYPE string(255) optional
 * <li> FIELD string(255) optional
 * <li> CREATED_AT datetime optional
 * <li> UPDATED_AT datetime optional
 * </ul>
 *
 **/
class AntiBruteForceTable extends DataManager
{
    use ConvertEmptyStringsToNull;

    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'frizus_anti_brute_force';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            new IntegerField(
                'ID',
                [
                    'primary' => true,
                    'autocomplete' => true,
                ]
            ),
            new StringField(
                'TYPE',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateType'],
                ]
            ),
            new IntegerField(
                'ATTEMPTS',
                [
                    'nullable' => true,
                ]
            ),
            new BooleanField(
                'BLOCKED',
                [
                    'required' => true,
                    'values' => [0, 1],
                ]
            ),
            new DatetimeField(
                'BLOCKED_AT',
                [
                    'nullable' => true,
                ]
            ),
            new StringField(
                'BLOCK_TYPE',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateBlockType'],
                ]
            ),
            new IntegerField(
                'BLOCK_TIME_MULTIPLIER',
                [
                    'nullable' => true,
                ]
            ),
            new StringField(
                'IP_ADDRESS',
                [
                    'validation' => [__CLASS__, 'validateIpAddress'],
                    'nullable' => true,
                ]
            ),
            new IntegerField(
                'USER_ID',
                [
                    'nullable' => true,
                ]
            ),
            new StringField(
                'FIELD_TYPE',
                [
                    'validation' => [__CLASS__, 'validateFieldType'],
                    'nullable' => true,
                ]
            ),
            new StringField(
                'FIELD',
                [
                    'validation' => [__CLASS__, 'validateField'],
                    'nullable' => true,
                ]
            ),
            new DatetimeField(
                'CREATED_AT',
                [
                    'nullable' => true,
                ]
            ),
            new DatetimeField(
                'UPDATED_AT',
                [
                    'nullable' => true,
                ]
            ),
        ];
    }

    /**
     * Returns validators for TYPE field.
     *
     * @return array
     */
    public static function validateType()
    {
        return [
            new LengthValidator(null, 255),
        ];
    }

    /**
     * Returns validators for BLOCK_TYPE field.
     *
     * @return array
     */
    public static function validateBlockType()
    {
        return [
            new LengthValidator(null, 255),
        ];
    }

    /**
     * Returns validators for IP_ADDRESS field.
     *
     * @return array
     */
    public static function validateIpAddress()
    {
        return [
            new LengthValidator(null, 255),
        ];
    }

    /**
     * Returns validators for FIELD_TYPE field.
     *
     * @return array
     */
    public static function validateFieldType()
    {
        return [
            new LengthValidator(null, 255),
        ];
    }

    /**
     * Returns validators for FIELD field.
     *
     * @return array
     */
    public static function validateField()
    {
        return [
            new LengthValidator(null, 255),
        ];
    }

    public static function onBeforeAdd(Event $event)
    {
        static::convertEmptyStringsToNullBeforeSave($event);
        $event->getParameter('object')['CREATED_AT'] = new DateTime();
        $event->getParameter('object')['UPDATED_AT'] = new DateTime();
    }

    public static function onBeforeUpdate(Event $event)
    {
        static::convertEmptyStringsToNullBeforeSave($event);
        $event->getParameter('object')['UPDATED_AT'] = new DateTime();
    }
}
