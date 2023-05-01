<?php

namespace Frizus\Module\Request;

use Bitrix\Iblock\Elements\ElementCatalogTable;
use Bitrix\Main\ORM\Query\Result;
use CIBlockSection;
use Frizus\Module\Request\Base\AjaxRequest;

class CategorySaleLeadersRequest extends AjaxRequest
{
    public function validationData()
    {
        return $this->requestData(['categoryId']);
    }

    public function rules()
    {
        if ($this->step === 1) {
            return [
                'categoryId' => [
                    'bail',
                    'required',
                    'integer_id',
                    [$this, 'processingCategoryId'],
                ],
            ];
        } elseif ($this->step === 2) {
            return [
                'categoryId' => [
                    [$this, 'validateCategoryId'],
                ],
            ];
        }
    }

    public function processingCategoryId($attribute, $value, $keyExists, $fail)
    {
        $this->processing('categoryId', intval($value));
    }

    public function validateCategoryId($attribute, $value, $keyExists, $fail)
    {
        $value = $this->processing('categoryId');
        $rsData = CIBlockSection::GetList(
            [],
            [
                'ACTIVE' => 'Y',
                'GLOBAL_ACTIVE' => 'Y',
                'IBLOCK_ID' => FRIZUS_CATALOG,
                'IBLOCK_ACTIVE' => 'Y',
                'CHECK_PERMISSIONS' => 'Y',
                'MIN_PERMISSION' => 'R',
                'ID' => $value,
            ],
            false,
            ['ID'],
            false
        );
        $arData = $rsData->Fetch();
        if (!$arData) {
            $fail('');
            return;
        }
    }
}
