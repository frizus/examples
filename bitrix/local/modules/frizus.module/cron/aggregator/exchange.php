<?
if (php_sapi_name() !== "cli") return;
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__) . "/../../../../..");

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

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;
use Frizus\Module\Cron\Aggregator\ExchangeCron;

Loader::includeModule('frizus.module');

$cron = new ExchangeCron();
$cron->run();
