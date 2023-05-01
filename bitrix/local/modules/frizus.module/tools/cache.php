<?php

$startTime = microtime(true);

$isIncluded = defined('B_PROLOG_INCLUDED') && (B_PROLOG_INCLUDED === true);
if (!$isIncluded && (php_sapi_name() !== 'cli')) {
    return;
}

$_SERVER["DOCUMENT_ROOT"] = realpath(__DIR__ . '/../../../..');

define('NO_AGENT_CHECK', true);
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('PUBLIC_AJAX_MODE', true);
define('DisableEventsCheck', true);
define('STATISTIC_SKIP_ACTIVITY_CHECK', true);
define('STOP_STATISTICS', true);
define('NOT_CHECK_PERMISSIONS', true);
define('BX_NO_ACCELERATOR_RESET', true);
@set_time_limit(0);
@ignore_user_abort(true);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;
use Frizus\Module\CLI\Cache;

if (!Loader::includeModule('frizus.module')) {
    fwrite(STDOUT, "Не подключен нужный модуль:\n");
    return 1;
}

$opts = getopt('h', [
    'help::',
    'verbose::',
]);

if (array_key_exists('h', $opts) || array_key_exists('help', $opts)) {
    fwrite(STDOUT, "Помощь:\n");
    fwrite(STDOUT, "  Удаление всего кеша.\n");
    fwrite(STDOUT, "\n");
    fwrite(STDOUT, "  php " . pathinfo(__FILE__, PATHINFO_BASENAME) . " [опции]\n");
    fwrite(STDOUT, "\n");
    fwrite(STDOUT, "Опции:\n");
    fwrite(STDOUT, "\n");
    fwrite(STDOUT, "  --help, -h - помощь\n");
    fwrite(STDOUT, "  --verbose= - если true, то будет более подробный вывод\n");
    fwrite(STDOUT, "\n");
    return 0;
}

$verbose = $opts['verbose'] === 'true';

$process = new Cache($startTime, $verbose);

if (!$process->init()) {
    return 1;
}

return $process->run();