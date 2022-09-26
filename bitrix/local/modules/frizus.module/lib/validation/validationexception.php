<?php
namespace Frizus\Module\Validation;

class ValidationException extends \Exception
{
    public $validator;

    public function __construct($validator)
    {
        parent::__construct();

        $this->validator = $validator;
    }
}
