<?php
namespace Frizus\Module\Request\Base;

use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Frizus\Module\Controller\Exception\ResponseException;
use Frizus\Module\Validation\Validator;

class AjaxRequest extends Request
{
    protected $validatorCalled;

    protected function failedValidation($validator)
    {
        $response = Context::getCurrent()->getResponse();
        $response->setStatus('400 Bad Request');
        throw new ResponseException;
    }

    public function getValidatorInstance()
    {
        $isset = isset($this->validator);
        parent::getValidatorInstance();
        if (!$isset) {
            $this->validator->stopOnFirstFailure();
        }
        return $this->validator;
    }
}
