<?php
namespace App\TargetDomains\Libraries\Image;

use App\TargetDomains\Libraries\Image\Exceptions\Downloader\ConnectionException;
use App\TargetDomains\Libraries\Image\Exceptions\Downloader\DownloaderStreamError;
use App\TargetDomains\Libraries\Image\Exceptions\Downloader\EmptyResponseBody;
use App\TargetDomains\Libraries\Image\Exceptions\Downloader\NotSupportedDownloadedImageFormat;
use App\TargetDomains\Libraries\Image\Exceptions\Downloader\NotSuccessfulStatus;
use App\TargetDomains\Libraries\Image\Exceptions\Downloader\NotSupportedResponseContentType;
use App\TargetDomains\Libraries\Image\Exceptions\Downloader\TemporaryFileCreationFailed;
use App\TargetDomains\Libraries\Request;
use Illuminate\Http\Client\ConnectionException as LaravelHttpConnectionException;
use Illuminate\Http\Client\Response;
use Throwable;

class ImageDownloader
{
    protected static $downloadMaxSize;

    protected static $downloadChunkSize;

    protected static $extensions;

    protected static $downloadTimeout;

    protected static $downloadConnectTimeout;

    protected static $downloadReadTimeout;

    /**
     * @var Response
     */
    protected $response;

    protected $responseMime;

    protected $downloadedMime;

    protected $tempResource;

    protected $tempPath;

    public function __construct()
    {
        static $gotConfig;
        if (!isset($gotConfig)) {
            $this::$downloadMaxSize = config('scrapper.image_download_max_size');
            $this::$downloadChunkSize = config('scrapper.image_download_chunk_size');
            $this::$extensions = config('scrapper.image_mime');
            $this::$downloadTimeout = config('scrapper.image_download_timeout');
            $this::$downloadReadTimeout = config('scrapper.image_download_read_timeout');
            $this::$downloadConnectTimeout = config('scrapper.image_download_connect_timeout');
            $gotConfig = true;
        }
    }

    public function download($url, $skipResponseContentTypeCheck = false, $validWhenResponseContentTypeMissing = true)
    {
        try {
            $this->response = Request::load($url, [
                'stream' => true,
                'timeout' => $this::$downloadTimeout,
                'read_timeout' => $this::$downloadReadTimeout,
                'connect_timeout' => $this::$downloadConnectTimeout,
            ]);
        } catch (LaravelHttpConnectionException $e) {
            throw new ConnectionException(['connectionExceptionMessage' => $e->getMessage()], ConnectionException::DEFAULT_CODE, $e);
        } catch (Throwable $e) {
            throw new ConnectionException(['error' => $e->getMessage()], ConnectionException::ANY_ERROR_CODE, $e);
        }

        $this->checkHttpStatus();

        if (!$skipResponseContentTypeCheck) {
            $this->checkResponseContentType($validWhenResponseContentTypeMissing);
        }

        $this->createTempResource();

        $this->downloadResponseBody();

        $this->checkDownloadedMimeType();
    }

    protected function checkHttpStatus()
    {
        /** @see https://developer.mozilla.org/ru/docs/Web/HTTP/Status */
        if ($this->response->successful() || $this->response->status() == 304) {
            return;
        }

        $level = (int) \floor($this->response->status() / 100);
        if ($level == 3) {
            $code = NotSuccessfulStatus::REDIRECT_CODE;
        } elseif ($level == 4) {
            $code = NotSuccessfulStatus::CLIENT_ERROR_CODE;
        } elseif ($level == 5) {
            $code = NotSuccessfulStatus::SERVER_ERROR_CODE;
        } else {
            $code = NotSuccessfulStatus::UNKNOWN_ERROR_CODE;
        }
        throw new NotSuccessfulStatus(['response' => $this->response], $code);
    }

    protected function checkResponseContentType($validWhenEmpty = false)
    {
        try {
            $contentType = $this->response->getHeader('content-type');
        } catch (Throwable $e) {
            throw new DownloaderStreamError([], DownloaderStreamError::GET_HEADERS_CODE);
        }

        if (empty($contentType) || !strlen($contentType[0])) {
            if ($validWhenEmpty) {
                return;
            }

            throw new NotSupportedResponseContentType([], NotSupportedResponseContentType::EMPTY_CODE);
        }

        $parts = explode(';', $contentType[0], 2);
        $this->responseMime = strtolower(trim($parts[0]));

        if (!array_key_exists($this->responseMime, $this::$extensions)) {
            throw new NotSupportedResponseContentType(['mime' => $this->responseMime]);
        }
    }

    protected function createTempResource()
    {
        $this->tempResource = tmpfile();

        if (!$this->tempResource) {
            throw new TemporaryFileCreationFailed([]);
        }

        $this->tempPath = stream_get_meta_data($this->tempResource)['uri'];
    }

    public function deleteTempResource()
    {
        if (isset($this->tempResource)) {
            fclose($this->tempResource);
            $this->tempResource = null;
        }
    }

    /**
     * @see https://stackoverflow.com/a/44156586
     */
    protected function downloadResponseBody()
    {
        if (!\ini_get('allow_url_fopen')) {
            try {
                $data = $this->response->body();
                $bytesRead = strlen($data);
            } catch (LaravelHttpConnectionException $e) {
                throw new ConnectionException(['connectionExceptionMessage' => $e->getMessage()], ConnectionException::DEFAULT_CODE, $e);
            } catch (Throwable $e) {
                throw new ConnectionException(['error' => $e->getMessage()], ConnectionException::ANY_ERROR_CODE, $e);
            }

            if ($bytesRead >= $this::$downloadMaxSize) {
                throw new DownloaderStreamError(['bytesRead' => $bytesRead, 'maxSize' => $this::$downloadMaxSize], DownloaderStreamError::FILE_SIZE_IS_TOO_BIG);
            }

            if ($data === "") {
                throw new EmptyResponseBody([]);
            }

            if (!fwrite($this->tempResource, $data)) {
                throw new DownloaderStreamError(['filePath' => $this->tempPath], DownloaderStreamError::WRITE_TO_FILE_CODE);
            }

            return;
        }

        $body = $this->response->toPsrResponse()->getBody();
        $bytesRead = 0;
        while (!$body->eof()) {
            try {
                $data = $body->read($this::$downloadChunkSize);
            } catch (\RuntimeException $e) {
                $body->close();
                throw new DownloaderStreamError(['runtimeException' => $e->getMessage()], DownloaderStreamError::STREAM_ERROR_CODE);
            }

            if (($data === "") && ($bytesRead == 0)) {
                throw new EmptyResponseBody([]);
            }

            $bytesRead += strlen($data);

            if ($bytesRead >= $this::$downloadMaxSize) {
                $body->close();
                throw new DownloaderStreamError(['bytesRead' => $bytesRead, 'maxSize' => $this::$downloadMaxSize], DownloaderStreamError::FILE_SIZE_IS_TOO_BIG);
            }

            if (!fwrite($this->tempResource, $data)) {
                $body->close();
                throw new DownloaderStreamError(['filePath' => $this->tempPath], DownloaderStreamError::WRITE_TO_FILE_CODE);
            }
        }
    }

    /**
     * @see https://github.com/bjorno43/ImageSecure/blob/master/imgupload.class.php#L177
     * @return bool
     */
    protected function checkDownloadedMimeType()
    {
        $this->downloadedMime = @mime_content_type($this->tempResource);
        if ($this->downloadedMime === false) {
            throw new NotSupportedDownloadedImageFormat(['responseMimeType' => $this->responseMime], NotSupportedDownloadedImageFormat::EMPTY_CODE);
        } elseif (!array_key_exists($this->downloadedMime, $this::$extensions)) {
            throw new NotSupportedDownloadedImageFormat(['mime' => $this->downloadedMime, 'responseMimeType' => $this->responseMime]);
        }
    }

    public function getExtensionFromMime()
    {
        return $this::$extensions[$this->downloadedMime];
    }

    public function getDownloadedFilePath()
    {
        return $this->tempPath;
    }

    public function getDownloadedResource()
    {
        if (isset($this->tempResource)) {
            if (ftell($this->tempResource) != 0) {
                fseek($this->tempResource, 0);
            }
        }
        return $this->tempResource;
    }
}
