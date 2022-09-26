<?php
namespace Frizus\Module\Controller\Exception;

class RedirectException extends \Exception
{
    public $redirect;

    public function redirectTo()
    {
        LocalRedirect($this->redirect, true);
    }
}
