<?

namespace Frizus\Module\CLI;

class Example extends CLI
{
    public const STATS = [
        [
            'stats' => [
                'total' => 'Пример'
            ],
            'check' => false,
        ],
    ];

    protected $modules = [];

    public function handle()
    {
        $this->initStats();

        $this->stats['total']++;

        return true;
    }

    public function init()
    {
        if (!$this->initModules()) {
            return false;
        }

        return true;
    }
}