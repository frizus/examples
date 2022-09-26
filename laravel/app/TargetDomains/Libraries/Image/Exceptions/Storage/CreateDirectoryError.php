<?php

namespace App\TargetDomains\Libraries\Image\Exceptions\Storage;

use App\TargetDomains\Libraries\Image\Exceptions\StorageException;
use Throwable;

class CreateDirectoryError extends StorageException
{
    public const DEFAULT_CODE = 0;

    public const CANT_CREATE_UPLOAD_DIRECTORY_CODE = 1;

    public const DIRECTORY_PATH_IS_FILE_CODE = 2;

    public function __construct($mixed = "", $code = self::DEFAULT_CODE, Throwable $previous = null)
    {
        // TODO: залогировать
        switch ($code) {
            case self::CANT_CREATE_UPLOAD_DIRECTORY_CODE:
                if (is_array($mixed)) {
                    $message = 'Не удалось создать директорию "' . @$mixed['uploadDirectory'] . '" для ассета';
                }
                break;
            case self::DIRECTORY_PATH_IS_FILE_CODE:
                if (is_array($mixed)) {
                    $message = 'По пути директории ассета создан файл ' . @$mixed['responseJson']['path'];
                }
                break;
        }

        if (!isset($message)) {
            $message = is_string($mixed) ? $mixed : "";
        }

        parent::__construct(['error' => $message], $code, $previous);
    }
}
