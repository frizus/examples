<?php

namespace Frizus\Module\Controller\Traits;

use Bitrix\Main\Loader;
use Exception;

trait LoadModule
{
    protected function loadModule($modules)
    {
        foreach ((array)$modules as $module) {
            if (!Loader::includeModule($module)) {
                throw new Exception;
            }
        }
    }
}
