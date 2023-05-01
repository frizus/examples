<?php

namespace Frizus\Module\Controller\Exception;

use Exception;

class ResponseException extends Exception
{
    public $isResponse;
}
