<?php
namespace App\TargetDomains\Libraries\Image\Exceptions;

use Throwable;

class StorageException extends ImageException
{
    public function __construct($mixed = "", $code = 0, Throwable $previous = null)
    {
        if (is_array($mixed)) {
            $message = 'Загрузка на Яндекс.Диск: ' . @$mixed['error'];
        } else {
            $message = $mixed;
        }
        parent::__construct($message, $code, $previous);
    }
}
