<?php

namespace Frizus\Module\Controller\Traits;

use Frizus\Module\Request\Base\Request;
use Frizus\Module\Request\ProductRequest;

trait PrepareRequest
{
    protected $requestClass;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @param Request $requestClass
     * @return void
     */
    public function prepareRequest($requestClass, $step = null)
    {
        $this->request = new $requestClass();
        if (isset($step)) {
            $this->request->step($step);
        }
        $this->request->validate();
    }
}
