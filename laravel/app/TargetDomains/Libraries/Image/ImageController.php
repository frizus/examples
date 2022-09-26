<?php
namespace App\TargetDomains\Libraries\Image;

use App\Models\Image;
use App\Models\Status;
use App\TargetDomains\Libraries\Image\Exceptions\Conversion;
use App\TargetDomains\Libraries\Image\Exceptions\Downloader;

class ImageController
{
    /**
     * @var Status
     */
    public $status;

    public function init()
    {
        $this->status = Status::where('type', '=', Status::TYPE_IMAGES)->first();

        return isset($this->status);
    }

    public function setStatusBusy($busy)
    {
        $this->status->process_busy = $busy;
        if ($busy) {
            $this->status->locked_at = now();
        } else {
            $this->status->locked_at = null;
        }
        return $this->status->save();
    }

    public function unlock($after)
    {
        if (!isset($after) || !isset($this->status->locked_at) || (!is_numeric($after))) {
            return false;
        }
        return now()->diffInSeconds($this->status->locked_at, false) <= -$after;
    }

    public function step()
    {
        $image = Image::where('uploaded', '=', false)
            ->where('first_image', '=', true)
            ->whereNull('error')
            ->orderBy('id')
            ->first();

        if (!isset($image)) {
            $image = Image::where('uploaded', '=', false)
                ->where('first_image', '=', false)
                ->whereNull('error')
                ->orderBy('id')
                ->first();
        }

        if (!isset($image)) {
            return true;
        }

        $exceptionThrown = false;

        try {
            $downloader = new ImageDownloader;
            $downloader->download($image->source, true);

            $conversion = new ImageConversion;
            $conversion->convertToJpg($downloader->getDownloadedFilePath(), $downloader->getExtensionFromMime());

            $storage = new ImageStorage;

            $uploadDirectory = $storage->getItemUploadDirectory($image->domain, $image->item_id);
            $storage->createUploadDirectoryIfNotExists($uploadDirectory);

            $imagePath = $storage->generateImagePath($uploadDirectory, $image->source, $conversion->getConversionExtension());
            $reserveInfo = $storage->reserveUpload($imagePath);
            $uploadResult = $storage->uploadFile($downloader->getDownloadedResource(), $reserveInfo['uploadLink']);

            $image->fill([
                'error' => null,
                'filename' => $imagePath,
                'uploaded' => true,
                //'operation_id' => $reserveInfo['operationId'],
            ]);
            $image->save();
            $message = '';
            if ($uploadResult['async']) {
                $message .= 'Загружено и обрабатывается';
            } else {
                $message .= 'Загружено';
            }
            $message .= ' (id: ' . $image->id . ', domain: ' . $image->domain . ', item id: ' . $image->item_id . ', is first: ' . ($image->first_image ? 'true' : 'false') . ', source: ' . $image->source . ', filename: ' . $image->filename . ")\n";
            echo $message;
        } catch (
            Downloader\NotSuccessfulStatus |
            Downloader\NotSupportedResponseContentType |
            Downloader\EmptyResponseBody |
            Downloader\NotSupportedDownloadedImageFormat |
            Conversion\NotSupportedSourceFormat |
            Conversion\ConversionProcessError $e
        ) {
            if ($e instanceof Conversion\ConversionProcessError && $e->getCode() != Conversion\ConversionProcessError::INIT_IMAGE_ERROR_CODE) {
                throw $e;
            }
            $exceptionThrown = true;
            $image->error = $e->getMessage();
            $image->save();

            echo $image->error . ' (id: ' . $image->id . ', domain: ' . $image->domain . ', item id: ' . $image->item_id . ', is first: ' . ($image->first_image ? 'true' : 'false') . ', source: ' . $image->source . ")\n";
        } finally {
            $downloader->deleteTempResource();
        }

        return !$exceptionThrown;
    }
}
