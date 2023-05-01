<?php

namespace Frizus\Module\Controller;

use Bitrix\Main\Application;
use Throwable;

abstract class Base
{
    public $action;

    public function __construct($action)
    {
        $this->action = $action;
    }

    public function runAction()
    {
        $this->internalAction(true);
    }

    protected function internalAction($beforeAction)
    {
        try {
            if ($beforeAction) {
                $beforeActionClosures = $this->beforeActionClosures();
                if (isset($beforeActionClosures) && !empty($beforeActionClosures)) {
                    foreach ($beforeActionClosures as $closure) {
                        $closure($this);
                    }
                }
            }
            $this->{$this->action}();
        } catch (Throwable $e) {
            if (method_exists($e, 'redirectTo')) {
                $e->redirectTo();
            } elseif (property_exists($e, 'isResponse')) {
                Application::getInstance()->end(0);
            } else {
                throw $e;
            }
        }
    }

    public function beforeActionClosures()
    {
        return [];
    }

    public function callAction()
    {
        $this->internalAction(false);
    }

    public function renderJson($result)
    {
        Application::getInstance()->getContext()->getResponse()->addHeader('Content-Type', 'application/json; charset=UTF-8');
        echo json_encode($result);
    }
}
