<?php
namespace Frizus\Module\Rule;

use Frizus\Module\Rule\Base\Rule;
use Frizus\Module\Rule\Base\ValidatorAwareRule;
use Frizus\Module\Validation\Validator;

class ValidateProductIdRule implements Rule, ValidatorAwareRule
{
    /**
     * @var Validator
     */
    protected $validator;

    public function setValidator($validator)
    {
        $this->validator = $validator;
    }

    public function passes($attribute, $value, $keyExists)
    {
        if (!$keyExists || is_null($value) || (filter_var($value, FILTER_VALIDATE_INT) === false)) {
            return false;
        }
        $value = intval($value);
        if (!($value > 0)) {
            return false;
        }

        $result = \CIBlockElement::GetList(
            [],
            [
                'IBLOCK_ID' => FRIZUS_CATALOG,
                'IBLOCK_ACTIVE' => 'Y',
                'SECTION_ACTIVE' => 'Y',
                'SECTION_GLOBAL_ACTIVE' => 'Y',
                'SECTION_SCOPE' => 'IBLOCK',
                'ACTIVE_DATE' => 'Y',
                'ACTIVE' => 'Y',
                'CHECK_PERMISSIONS' => 'Y',
                'MIN_PERMISSION' => 'R',
                'ID' => $value,
            ],
            false,
            false,
            [
                'ID',
            ]
        );
        if ($result->SelectedRowsCount() > 0) {
            $request = $this->validator->request();
            if (isset($request)) {
                $request->processing('id', $value);
            }
            return true;
        }
        return false;
    }

    public function message()
    {
        return '';
    }
}
