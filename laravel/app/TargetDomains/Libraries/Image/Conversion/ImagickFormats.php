<?php
namespace App\TargetDomains\Libraries\Image\Conversion;

use Imagick;

/**
 * Trait ImagickSupportedFormats
 * @see \Intervention\Image\Imagick\Encoder
 * @package App\TargetDomains\Libraries\Traits
 */
class ImagickFormats
{
    /**
     * @see \Intervention\Image\Imagick\Driver::coreAvailable()
     * @return bool
     */
    public static function isInstalled()
    {
        return (extension_loaded('imagick') && class_exists('Imagick'));
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
        return true;
    }

    public static function webp()
    {
        return Imagick::queryFormats('WEBP');
    }

    public static function tiff()
    {
        return true;
    }

    public static function bmp()
    {
        return true;
    }

    public static function ico()
    {
        return true;
    }

    public static function psd()
    {
        return true;
    }

    public static function avif()
    {
        return Imagick::queryFormats('AVIF');
    }

    public static function __callStatic($method, $arguments) {
        return Imagick::queryFormats(strtoupper($method));
    }
}
