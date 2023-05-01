<?

namespace Frizus\Module\CLI;

use Bitrix\Main\Application;
use Bitrix\Main\Composite\Page;
use CFileCacheCleaner;
use Frizus\Module\Helper\FileHelper;

class Cache extends CLI
{
    /**
     * @var CFileCacheCleaner
     */
    protected $obCacheCleaner;

    /**
     * @see /bitrix/modules/main/admin/cache.php
     */
    public function handle()
    {
        $this->info('Чистка кеша...', true);

        $paths = [
            BX_PERSONAL_ROOT . '/cache/',
            BX_PERSONAL_ROOT . '/managed_cache/',
            BX_PERSONAL_ROOT . '/stack_cache/',
            BX_PERSONAL_ROOT . '/html_pages/',
        ];
        $this->info(implode(', ', $paths) . ' ...', true);

        $spaceFreed = 0;
        $this->obCacheCleaner->Start();
        while ($file = $this->obCacheCleaner->GetNextFile()) {
            if (!is_string($file)) {
                continue;
            }

            $fileSize = filesize($file);

            if (@unlink($file)) {
                $spaceFreed += $fileSize;
            }
        }
        $this->info(implode(', ', $paths) . ' ... завершено (' . FileHelper::formatSize($spaceFreed) . ')', true);

        $this->info('/bitrix/cache/ ...', true);
        BXClearCache(true, '/');
        $this->info('/bitrix/cache/ ... завершено', true);

        global $CACHE_MANAGER, $stackCacheManager;
        $this->info('/bitrix/managed_cache/ ...', true);
        $CACHE_MANAGER->CleanAll();
        $this->info('/bitrix/managed_cache/ ... завершено', true);

        $this->info('/bitrix/stack_cache/ ...', true);
        $stackCacheManager->CleanAll();
        $this->info('/bitrix/stack_cache/ ... завершено', true);

        $this->info('b_cache_tag...', true);
        Application::getInstance()->getTaggedCache()->deleteAllTags();
        $this->info('b_cache_tag... завершено', true);

        $this->info('Композитный кеш...', true);
        Page::getInstance()->deleteAll();
        $this->info('Композитный кеш... завершено', true);

        $this->info('Кеш очищен');

        return true;
    }

    public function init()
    {
        require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/classes/general/cache_files_cleaner.php");

        $this->obCacheCleaner = new CFileCacheCleaner('all');
        if (!$this->obCacheCleaner->InitPath('')) {
            $this->error('Не удалось инициализировать класс чистки кеша');
            return false;
        }

        return true;
    }
}