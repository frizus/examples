<?php

namespace Frizus\Module\Request\Base;

use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Frizus\Module\Controller\Exception\ResponseException;

class AjaxRequest extends Request
{
    protected $validatorCalled;

    public function getValidatorInstance()
    {
        $isset = isset($this->validator);
        parent::getValidatorInstance();
        if (!$isset) {
            $this->validator->stopOnFirstFailure();
        }
        return $this->validator;
    }

    protected function failedValidation($validator)
    {
        $response = Context::getCurrent()->getResponse();
        $response->setStatus('400 Bad Request');
        throw new ResponseException;
    }
}
