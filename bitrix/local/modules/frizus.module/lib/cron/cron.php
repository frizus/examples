<?php
namespace Frizus\Module\Cron;

use Frizus\Module\Helper\MessageBag;

abstract class Cron
{
    public $opts;

    public $arguments;

    public $options;

    public $helpCommand = false;

    public $logFile;

    public $isQuietOutput;

    protected $logBuffer = '';

    protected $logToFile = false;

    protected $builtOpts;

    /**
     * @var MessageBag
     */
    protected $errorBag;

    public function __construct()
    {
        $this->errorBag = new MessageBag();
        $this->parseOpts();
        $this->parseArguments();
        $this->executeBaseOptions();
    }

    public function run()
    {
        if ($this->helpCommand) {
            echo $this->outHelp();
            exit(0);
        }

        try {
            $exitCode = $this->handle();
        } catch (\InvalidArgumentException $e) {
            echo $e->getMessage() . "\n\n\n";
            $this->outHelp();
            exit(1);
        }

        exit($exitCode);
    }

    abstract public function handle();

    protected function parseOpts()
    {
        $logFile = strpos($this->logFile, $_SERVER['DOCUMENT_ROOT']) === 0 ? ('<путь до сайта>' . substr($this->logFile, strlen($_SERVER['DOCUMENT_ROOT']))) : $this->logFile;
        $this->builtOpts = [
            'help' => [
                'type' => 'option',
                'has_value' => false,
                'help' => 'Показать команды'
            ],
            'log-to-file' => [
                'type' => 'option',
                'has_value' => false,
                'help' => "Записывать лог в файл $logFile вместо вывода в консоль"
            ],
            'quiet' => [
                'type' => 'option',
                'has_value' => false,
                'help' => 'Выводить меньше сообщений',
            ]
        ];
        if (isset($this->opts)) {
            $matches = null;
            preg_match_all('#\{\s*(?P<double_dash>\-\-)?(?P<name>[^\=\:\s]+)(?P<is_value>\=)?(\s*:\s*(?P<help>[^\}]+))?\}#', $this->opts, $matches, PREG_PATTERN_ORDER | PREG_UNMATCHED_AS_NULL);
            foreach ($matches['name'] as $i => $opt) {
                $this->builtOpts[$opt] = [
                    'type' => isset($matches['double_dash'][$i]) ? 'option' : 'argument',
                    'has_value' => isset($matches['is_value'][$i]),
                    'help' => isset($matches['help'][$i]) ? rtrim($matches['help'][$i]) : null,
                ];
            }
        }
    }

    public function executeBaseOptions()
    {
        if ($this->hasOption('help')) {
            $this->helpCommand = true;
        }

        if ($this->hasOption('log-to-file') && isset($this->logFile)) {
            $this->logToFile();
        }

        $this->isQuietOutput = $this->hasOption('quiet');
    }

    public function parseArguments()
    {
        $longOptions = [];
        foreach (array_keys($this->builtOpts) as $opt) {
            $longOptions[] = $opt . '::';
        }

        $parsedArguments = getopt("", $longOptions);

        $this->arguments = [];
        $this->options = [];

        foreach ($parsedArguments as $name => $value) {
            $opt = $this->builtOpts[$name];
            if ($opt['type'] === 'option') {
                $this->options[$name] = $opt['has_value'] ? $value : true;
            }
        }
    }

    public function hasArgument($name)
    {
        return array_key_exists($name, $this->arguments);
    }

    public function argument($name)
    {
        $this->checkArgument($name);

        return $this->arguments[$name];
    }

    public function checkArgument($name)
    {
        if (!array_key_exists($name, $this->arguments)) {
            throw new \InvalidArgumentException("Не указан аргумент $name");
        }
    }

    public function hasOption($name)
    {
        return array_key_exists($name, $this->options);
    }

    public function option($name)
    {
        $this->checkOption($name);

        return $this->options[$name];
    }

    public function checkOption($name)
    {
        if (!array_key_exists($name, $this->options)) {
            throw new \InvalidArgumentException("Не указана опция $name");
        }
    }

    public function outHelp()
    {
        $hasArguments = false;
        $hasOptions = false;
        $maxNameColumnLength = 0;
        $out = [];
        foreach ($this->builtOpts as $name => $opt) {
            if ($opt['type'] === 'argument') {
                if (!$hasArguments) {
                    $hasArguments = true;
                }
                $outName = "  $name";
            } else {
                if (!$hasOptions) {
                    $hasOptions = true;
                }
                $outName = "  --$name" . ($opt['has_value'] ? ('[=' . strtoupper($name) . ']') : '');
            }
            $out[$opt['type']][$name] = [
                'name' => $outName,
                'help' => isset($opt['help']) ? $opt['help'] : '',
            ];
            $nameColumnLength = mb_strlen($outName);
            if ($maxNameColumnLength < $nameColumnLength) {
                $maxNameColumnLength = $nameColumnLength;
            }
        }

        echo "Использование:\n";

        echo "  php " . $_SERVER['SCRIPT_NAME'];
        if ($hasOptions) {
            echo ' [опции]';
        }
        if ($hasArguments) {
            foreach (array_keys($out['argument']) as $name) {
                echo " <$name>";
            }
        }
        echo "\n";

        echo "\n\n";
        echo "Аргументы:\n";
        if ($hasArguments) {
            foreach ($out['argument'] as $columns) {
                echo str_pad($columns['name'], $maxNameColumnLength, ' ') . '  ';
                echo $columns['help'];
                echo "\n";
            }
        }

        echo "\n\n";
        echo "Опции:\n";
        if ($hasOptions) {
            foreach ($out['option'] as $columns) {
                echo str_pad($columns['name'], $maxNameColumnLength, ' ') . '  ';
                echo $columns['help'];
                echo "\n";
            }
        }

        echo "\n";
    }

    public function text($text, $verbose = false)
    {
        $this->textInternal($text . "\n", '', $verbose);
    }

    public function error($text, $verbose = false)
    {
        $this->textInternal($text . "\n", 'error', $verbose);
    }

    protected function textInternal($text, $color, $verbose)
    {
        if ($verbose && $this->isQuietOutput) {
            return;
        }

        if ($this->logToFile) {
            $this->logBuffer .= $text;
        } else {
            echo $text;
        }
    }

    public function hasErrors()
    {
        return $this->errorBag->isNotEmpty();
    }

    public function outErrors()
    {
        foreach ($this->errorBag->messages() as $key => $messages) {
            foreach ($messages as $message) {
                echo $message . "\n";
            }
        }
    }

    public function logToFile()
    {
        $this->logToFile = true;
        register_shutdown_function([$this, 'saveLogToFile']);
    }

    public function saveLogToFile()
    {
        if ($this->logBuffer === '') {
            return;
        }

        file_put_contents($this->logFile, '[' . date('d.m.Y H:i:s') . '] ' . $this->outBuffer . "\n", FILE_APPEND | LOCK_EX);
    }
}
