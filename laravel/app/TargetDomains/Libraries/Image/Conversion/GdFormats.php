<?php
namespace App\TargetDomains\Libraries\Image\Conversion;

/**
 * Trait GdSupportedFormats
 * @see \Intervention\Image\Gd\Encoder
 * @package App\TargetDomains\Libraries\Traits
 */
class GdFormats
{
    /**
     * @see \Intervention\Image\Gd\Driver::coreAvailable()
     * @return bool
     */
    public static function isInstalled()
    {
        return (extension_loaded('gd') && function_exists('gd_info'));
    }

    public static function jpg()
    {
        return true;
    }

    public static function png()
    {
        return true;
    }

    public static function gif()
    {
        return true;
    }

    public static function svg()
    {
        return false;
    }

    public static function webp()
    {
        return function_exists('imagewebp');
    }

    public static function tiff()
    {
        return false;
    }

    public static function bmp()
    {
        return function_exists('imagebmp');
    }

    public static function ico()
    {
        return false;
    }

    public static function psd()
    {
        return false;
    }

    public static function avif()
    {
        return false;
    }
}
