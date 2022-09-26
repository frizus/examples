<?php
namespace App\TargetDomains\Libraries\Image;

use App\TargetDomains\Libraries\Image\Conversion\GdFormats;
use App\TargetDomains\Libraries\Image\Conversion\ImagickFormats;
use App\TargetDomains\Libraries\Image\Exceptions\Conversion\ConversionProcessError;
use App\TargetDomains\Libraries\Image\Exceptions\Conversion\NoImageDriversException;
use App\TargetDomains\Libraries\Image\Exceptions\Conversion\NotSupportedSourceFormat;
use Intervention\Image\Exception\ImageException;
use Intervention\Image\ImageManager;

class ImageConversion
{
    protected static $maxWidth;

    protected static $maxHeight;

    protected static $quality;

    protected static $useLibrary;

    protected $_gdImage;

    protected $_imagickImage;

    public function __construct()
    {
        static $gotConfig;

        if (!isset($gotConfig)) {
            $this::$maxWidth = config('scrapper.image_max_width');
            $this::$maxHeight = config('scrapper.image_max_height');
            $this::$quality = config('scrapper.image_quality');
            $this::$useLibrary = config('scrapper.image_use_library');
            if (!in_array($this::$useLibrary, ['imagick', 'gd'], true)) {
                $this::$useLibrary = null;
            }
            $gotConfig = true;
        }
    }

    public function convertToJpg($sourcePath, $extension)
    {
        try {
            $gdAvailable = GdFormats::isInstalled();
            $imagickAvailable = ImagickFormats::isInstalled();

            if (!$gdAvailable && !$imagickAvailable) {
                throw new NoImageDriversException([]);
            }

            $useLibrary = isset($this::$useLibrary) ? $this::$useLibrary : 'imagick';
            $usedImageDriver = false;
            if ($this::$useLibrary == 'imagick') {
                if ($imagickAvailable && ImagickFormats::{$extension}()) {
                    $usedImageDriver = 'imagick';
                } elseif ($gdAvailable && GdFormats::{$extension}()) {
                    $usedImageDriver = 'gd';
                }
            } elseif ($this::$useLibrary == 'gd') {
                if ($gdAvailable && GdFormats::{$extension}()) {
                    $usedImageDriver = 'gd';
                } elseif ($imagickAvailable && ImagickFormats::{$extension}()) {
                    $usedImageDriver = 'imagick';
                }
            }

            if ($usedImageDriver === false) {
                throw new NotSupportedSourceFormat(['extension' => $extension]);
            }

            if ($usedImageDriver == 'gd') {
                $imageManager = $this->getGdImageManager();
            } elseif ($usedImageDriver == 'imagick') {
                $imageManager = $this->getImagickImageManager();
            }

            try {
                $image = $imageManager->make($sourcePath);
            } catch (ImageException $e) {
                throw new ConversionProcessError(['conversionError' => $e->getMessage(), 'extensionExceptionCode' => $e->getCode()], ConversionProcessError::INIT_IMAGE_ERROR_CODE, $e);
            }

            try {
                $width = $image->getWidth();
                $height = $image->getHeight();

                if ($width > $this::$maxWidth || $height > $this::$maxHeight) {
                    $image->resize($this::$maxWidth, $this::$maxHeight, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                }

                $image->save($sourcePath, $this::$quality, $this->getConversionExtension());
            } catch (ImageException $e) {
                throw new ConversionProcessError(['conversionError' => $e->getMessage(), 'extensionExceptionCode' => $e->getCode()], ConversionProcessError::RESIZE_SAVE_ERROR_CODE, $e);
            }
        } finally {
            if (isset($image)) {
                $image->destroy();
                unset($image);
            }
        }
    }

    public function getConversionExtension()
    {
        return 'jpg';
    }

    protected function getGdImageManager()
    {
        if (!isset($this->_gdImage)) {
            $this->_gdImage = new ImageManager(['driver' => 'gd']);
        }

        return $this->_gdImage;
    }

    protected function getImagickImageManager()
    {
        if (!isset($this->_imagickImage)) {
            $this->_imagickImage = new ImageManager(['driver' => 'imagick']);
        }

        return $this->_imagickImage;
    }
}
