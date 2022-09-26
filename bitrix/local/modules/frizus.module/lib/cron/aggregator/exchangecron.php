<?php
namespace Frizus\Module\Cron\Aggregator;

use Frizus\Module\Aggregator\ExchangeProcess;
use Frizus\Module\Aggregator\UploadExchangeStats;
use Frizus\Module\Cron\Cron;
use Frizus\Module\HttpRequest\Requests\Aggregator\ExchangeRequest;

class ExchangeCron extends Cron
{
    public $opts = '
        {--process= : Имя процесса из инфоблока Процесс импорт [Агрегатор]}
        {--server= : Имя сервера. Если не указано, берется значение "URL сервера (без http://)" сайта s1}
        {--send-failed-report : Отправлять отчет агрегатору при не удачном запуске импорта}
    ';

    public function __construct()
    {
        $this->logFile = __DIR__ . '/' . pathinfo(__FILE__, PATHINFO_FILENAME) . '.txt';
        parent::__construct();
    }

    public function handle()
    {
        $exchangeProcess = new ExchangeProcess([
            'processIBlock' => 'aggregator_status',
            'process' => $this->option('process'),
            'cron' => $this,
        ]);
        if ($exchangeProcess->isBusy()) {
            if (!$exchangeProcess->unlock()) {
                return 1;
            }
        }

        register_shutdown_function([$this, 'setStatusBusy'], false);
        if (!$exchangeProcess->setStatusBusy(true)) {
            return 1;
        }

        $uploadStats = new UploadExchangeStats([
            'exchangeDomain' => $exchangeProcess->getExchangeDomain(),
        ]);
        try {
            $exchangeProcess->init(
                [
                    'catalogIBlock' => 'aspro_max_catalog',
                    'bindSectionByNameFullPath' => true,
                ],
                $this->hasOption('server') ? $this->option('server') : null
            );
            $exchangeProcess->requestImportData();
            $uploadStats->setExchangeUri($exchangeProcess->getExchangeUri());
            $exchangeProcess->initAfterRequest();
        } catch (\Throwable $e) {
            $uploadStats->setExchangeUri($exchangeProcess->getExchangeUri());
            $uploadStats->failed($e->getMessage());
            if ($this->hasOption('send-failed-report')) {
                $uploadStats->sendFailedReport();
            }
            return 1;
        }

        try {
            foreach ($exchangeProcess->getImportProducts() as $importProduct) {
                if ($importProduct->skipping()) {
                    $uploadStats->skip($importProduct);
                    continue;
                }

                $uploadStats->addProductStat(
                    $importProduct,
                    $importProduct->existedBefore(),
                    $saved = $importProduct->save(),
                    $saved === false ? $importProduct->restore() : null
                );
            }
        } catch (\Throwable $e) {
            $uploadStats->failed($e->getMessage());
            if ($this->hasOption('send-failed-report') || $uploadStats->haveProductStats()) {
                $uploadStats->sendFailedReport();
            }
            return 1;
        }

        $uploadStats->productsEnded();
        $reportSend = $uploadStats->sendReport();
        $processOffsetUpdated = $exchangeProcess->updateProcessOffset();

        return $reportSend && $processOffsetUpdated ? 1 : 0;
    }

    public function parseArguments()
    {
        parent::parseArguments();
        $this->checkOption('process');
    }
}
