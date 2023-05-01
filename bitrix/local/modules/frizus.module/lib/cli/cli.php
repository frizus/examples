<?php

namespace Frizus\Module\CLI;

use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;

abstract class CLI
{
    public const STATS = [];

    public $stats;

    public $verboseMode;

    public $config = [];

    protected $startTime;

    protected $modules = [];

    protected $cli;

    public function __construct($startTime = 0, $verboseMode = true)
    {
        $this->startTime = $startTime;
        $this->verboseMode = php_sapi_name() === 'cli' ? $verboseMode : true;
        $this->cli = php_sapi_name() === 'cli';
    }

    public function run()
    {
        $result = $this->handle();

        if ($this->outputStats()) {
            $this->info('-----------------------------------------', true);
        }
        $this->outElapsedTime();

        return $result ? 0 : 1;
    }

    abstract public function handle();

    protected function outputStats()
    {
        if (!is_array(static::STATS)) {
            return false;
        }

        $haveOutput = false;

        foreach (static::STATS as $stats) {
            if ($stats['check']) {
                $passes = false;
                $check = is_array($stats['check']) ? $stats['check'] : array_keys($stats['stats']);
                foreach ($check as $stat) {
                    if ($this->stats[$stat] > 0) {
                        $passes = true;
                        break;
                    }
                }
            } else {
                $passes = true;
            }

            if ($passes) {
                $this->info('-----------------------------------------', true);
                foreach ($stats['stats'] as $stat => $message) {
                    $this->info($message . ': ' . $this->stats[$stat], true);
                }

                if (!$haveOutput) {
                    $haveOutput = true;
                }
            }
        }

        return $haveOutput;
    }

    public function info($text, $verbose = false)
    {
        $this->_text($text, '', $verbose);
    }

    protected function _text($text, $color, $verbose)
    {
        if ($verbose && !$this->verboseMode) {
            return;
        }

        if ($this->cli) {
            fwrite(STDOUT, (is_array($text) ? implode("\n", $text) : $text) . "\n");
        } else {
            echo (is_array($text) ? implode("\n<br>", $text) : $text) . "\n<br>";
        }
    }

    protected function outElapsedTime()
    {
        $endTime = microtime(true);
        $nowFormatted = (new DateTime())->format('d.m.Y H:i:s');
        $took = $endTime - $this->startTime;
        $seconds = intval($took);
        $startTimeFormatted = date('d.m.Y H:i:s', strtotime('-' . $seconds . ' seconds'));
        $hours = 0;
        $minutes = 0;

        if ($seconds > 60) {
            $minutes = intval($seconds / 60);
            $seconds -= $minutes * 60;

            if ($minutes > 60) {
                $hours = intval($minutes / 60);
                $minutes -= $hours * 60;
            }
        }

        $hours = str_pad($hours, 2, '0', STR_PAD_LEFT);
        $minutes = str_pad($minutes, 2, '0', STR_PAD_LEFT);
        $seconds = str_pad($seconds, 2, '0', STR_PAD_LEFT);
        $tookFormatted = $hours . ':' . $minutes . ':' . $seconds;

        $this->info('Скрипт работал: ' . $tookFormatted . ' (с ' . $startTimeFormatted . ' по ' . $nowFormatted . ')', true);
    }

    abstract public function init();

    public function processName()
    {
        return get_called_class();
    }

    protected function initModules()
    {
        if (!empty($this->modules)) {
            foreach ($this->modules as $module) {
                if (!Loader::includeModule($module)) {
                    $this->error('Не подключен модуль ' . $module . '.');
                    return false;
                }
            }
        }

        return true;
    }

    public function error($text, $verbose = false)
    {
        $this->_text($text, 'error', $verbose);
    }

    protected function initStats()
    {
        $this->stats = [];

        if (!is_array(static::STATS)) {
            return;
        }

        foreach (static::STATS as $stats) {
            foreach ($stats['stats'] as $stat => $message) {
                $this->stats[$stat] = 0;
            }
        }
    }
}