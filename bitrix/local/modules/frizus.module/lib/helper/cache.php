<?php
namespace Frizus\Module\Helper;

use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache as BitrixCache;

class Cache
{
    public const INIT_DIR = 'Frizus';

    /**
     * @var BitrixCache
     */
    protected static $cache;

    protected static $abort;

    public static function remember($key, $ttl, $callback, $tags = null, $initDir = null)
    {
        $initDir = $initDir ?? self::dir($key);
        self::$cache = BitrixCache::createInstance();
        self::$abort = false;
        if (self::$cache->initCache($ttl, $key, $initDir)) {
            $value = self::$cache->getVars();
        } elseif (self::$cache->startDataCache()) {
            $value = $callback();
            if (!self::$abort) {
                self::tags($tags, $initDir);
                self::$cache->endDataCache($value);
            }
        }
        self::$cache = null;
        self::$abort = null;
        return $value;
    }

    public static function output($key, $ttl, $callback, $tags = null, $initDir = null)
    {
        $initDir = $initDir ?? self::dir($key);
        self::$cache = BitrixCache::createInstance();
        self::$abort = false;
        if (self::$cache->startDataCache($ttl, $key, $initDir)) {
            $callback();
            if (!self::$abort) {
                self::tags($tags, $initDir);
                self::$cache->endDataCache(true);
            }
        }
        self::$cache = null;
        self::$abort = null;
    }

    public static function abort()
    {
        if (isset(self::$cache)) {
            self::$cache->abortDataCache();
            self::$abort = true;
        }
    }

    public static function forget($key, $isDir = false)
    {
        $cache = BitrixCache::createInstance();
        $cache->clean($key, $isDir ? $key : self::dir($key));
    }

    protected static function tags($tags, $initDir)
    {
        if (!is_null($tags)) {
            $taggedCache = Application::getInstance()->getTaggedCache();
            $taggedCache->startTagCache($initDir);
            foreach ((array)$tags as $tag) {
                $taggedCache->registerTag($tag);
            }
            $taggedCache->endTagCache();
        }
    }

    /**
     * @see https://stackoverflow.com/questions/1976007/what-characters-are-forbidden-in-windows-and-linux-directory-names
     */
    public static function dir($dir)
    {
        return self::INIT_DIR . '/' . str_replace(
            [
                '<',
                '>',
                ':',
                '"',
                '/',
                "\\",
                '|',
                '?',
                '*',
            ],
            '_',
                $dir
        );
    }
}
