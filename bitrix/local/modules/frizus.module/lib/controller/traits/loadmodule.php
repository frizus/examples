<?php
namespace Frizus\Module\Controller\Traits;

use Bitrix\Main\Loader;

trait LoadModule
{
    protected function loadModule($modules)
    {
        foreach ((array)$modules as $module) {
            if (!Loader::includeModule($module)) {
                throw new \Exception;
            }
        }
    }
}
