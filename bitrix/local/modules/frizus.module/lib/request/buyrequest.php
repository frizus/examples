<?php
namespace Frizus\Module\Request;

use Bitrix\Iblock\Elements\ElementCatalogTable;
use Bitrix\Main\ORM\Query\Result;
use Frizus\Module\Request\Base\AjaxRequest;
use Frizus\Module\Rule\ValidateProductIdRule;

class BuyRequest extends AjaxRequest
{
    public function validationData()
    {
        return $this->requestData([], ['id']);
    }

    public function rules()
    {
        return [
            'id' => [
                new ValidateProductIdRule,
            ],
        ];
    }
}
