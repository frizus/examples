<?php

namespace Frizus\Module\Validation;

use Exception;
use Frizus\Module\Helper\MessageBag;
use Throwable;

class ValidationException extends Exception
{
    /**
     * @var Validator
     */
    protected $validator;

    // TODO сделать редирект и запомнить ошибки
    public function __construct($validator, $message = "", $code = 0, Throwable $previous = null)
    {
        $this->validator = $validator;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return MessageBag
     */
    public function getMessageBag()
    {
        return $this->validator->messages();
    }
}