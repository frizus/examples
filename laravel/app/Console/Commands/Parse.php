<?php

namespace App\Console\Commands;

use App\TargetDomains\Controller;
use Illuminate\Console\Command;

class Parse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parser:go
                            {scrape-type : Тип скрапинга (price или details)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Запускает скрапер на одну итерацию';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $controller = new Controller($this->argument('scrape-type'));

        if (!$controller->init()) {
            $this->error('Ошибка инициализации');
            return 1;
        }

        if ($controller->status->process_busy) {
            $unlockAfter = null;
            if ($this->argument('scrape-type') == Controller::SCRAPE_TYPE_PRICE) {
                $unlockAfter = config('scrapper.price_unlock_after');
            } elseif ($this->argument('scrape-type') == Controller::SCRAPE_TYPE_DETAILS) {
                $unlockAfter = config('scrapper.details_unlock_after');
            }
            if ($controller->unlock($unlockAfter)) {
                $this->info('Парсер разблокирован');
            } else {
                $this->info('Парсер занят');
                return 1;
            }
        }

        register_shutdown_function([$controller, 'setStatusBusy'], false);

        if (!$controller->setStatusBusy(true)) {
            $this->error('Ошибка: не удалось выставить статус "занят"');
            return 1;
        }

        if (!$controller->step()) {
            return 1;
        }

        return 0;
    }
}
