<?php

namespace App\Console\Commands;

use App\Models\Status;
use Illuminate\Console\Command;

class ParserDeploy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parser:deploy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Первоначальная подготовка таблиц баз данных';

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
        Status::truncate();

        $statusFast = Status::create([
            'type' => Status::TYPE_PRICE,
            'last_domain' => 0,
            'last_url' => 0,
            'last_pagination' => 0,
            'is_last_pagination' => false,
            'process_busy' => false,
            'locked_at' => null,
        ]);
        $statusFull = Status::create([
            'type' => Status::TYPE_DETAILS,
            'last_domain' => 0,
            'last_url' => 0,
            'last_pagination' => 0,
            'is_last_pagination' => false,
            'process_busy' => false,
            'locked_at' => null,
        ]);
        $statusImageUploader = Status::create([
            'type' => Status::TYPE_IMAGES,
            'last_domain' => 0,
            'last_url' => 0,
            'last_pagination' => 0,
            'is_last_pagination' => false,
            'process_busy' => false,
            'locked_at' => null,
        ]);

        if ($statusFast->exists && $statusFull->exists) {
            $this->info('Таблица подготовлена');
        } else {
            $this->error('Не удалось подготовить таблицу');
        }

        return 0;
    }
}
