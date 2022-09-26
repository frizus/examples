<?php
namespace App\TargetDomains\Libraries\Image\Storage;

use App\Misc\Helpers\Json;
use App\TargetDomains\Libraries\Image\Exceptions\Storage\BrokenResponseBody;
use App\TargetDomains\Libraries\Image\Exceptions\Storage\ConnectionException;
use App\TargetDomains\Libraries\Image\Exceptions\Storage\NotSuccessfulStatus;
use App\TargetDomains\Libraries\Request;
use Illuminate\Http\Client\ConnectionException as LaravelHttpConnectionException;
use Throwable;

class Base
{
    protected const UNKNOWN_STATUS = NotSuccessfulStatus::UNKNOWN_STATUS_CODE;

    protected const GET_BODY_WHEN_STATUS = null;

    protected const GET_BODY_WHEN_STATUS_NOT = [500, 502];

    protected const STATUSES = [];

    protected static function controller($query = [], $postData = [], $attach = [], $url = null)
    {
        try {
            $response = Request::send(
                static::QUERY[0], $url ?? static::QUERY[1],
                static::getRequestOptions(),
                $query, $postData, $attach
            );

            $status = $response->status();
            if (static::needToGetResponseBody($status)) {
                $body = $response->body();
            }
            $isFailure = $response->failed();
        } catch (LaravelHttpConnectionException $e) {
            throw new ConnectionException(['connectionExceptionMessage' => $e->getMessage()], ConnectionException::DEFAULT_CODE, $e);
        } catch (Throwable $e) {
            throw new ConnectionException(['error' => $e->getMessage()], ConnectionException::ANY_ERROR_CODE, $e);
        }

        if (isset($body)) {
            $body = static::validateBody($status, $isFailure, $body, $response, $query);
        }

        $result = static::getResult($status, $body ?? null, $response, $query);
        if ($result === false) {
            throw new NotSuccessfulStatus(['response' => $response, 'responseJson' => $body ?? null], static::getStatus($status));
        }

        return $result;
    }

    protected static function getRequestOptions()
    {
        return [
            'allow_redirects' => config('scrapper.image_upload_allow_redirects'),
            'timeout' => config('scrapper.image_upload_timeout'),
            'read_timeout' => config('scrapper.image_upload_read_timeout'),
            'connect_timeout' => config('scrapper.image_upload_connect_timeout'),
            'stream' => true,
            'headers' => [
                'Authorization' => 'OAuth ' . config('scrapper.yandex_disk_token'),
                'Accept' => 'application/json',
            ],
        ];
    }

    protected static function getStatus($status)
    {
        foreach (static::STATUSES as $code => $statusCodes) {
            if (in_array($status, $statusCodes, true)) {
                return $code;
            }
        }

        return static::UNKNOWN_STATUS;
    }

    protected static function needToGetResponseBody($status)
    {
        $success = true;

        if (!is_null(static::GET_BODY_WHEN_STATUS)) {
            if (!in_array($status, static::GET_BODY_WHEN_STATUS, true)) {
                $success = false;
            }
        }

        if (!is_null(static::GET_BODY_WHEN_STATUS_NOT)) {
            if (in_array($status, static::GET_BODY_WHEN_STATUS_NOT, true)) {
                $success = false;
            }
        }

        return $success;
    }

    protected static function validateBody($status, $isFailure, $body, $response, $query)
    {
        $json = Json::parse($body);

        static::isNotJson($json, $response, $status);
        if ($isFailure) {
            static::validateJsonError($json, $response, $status);
        }

        return $json;
    }

    protected static function isNotJson($json, $response, $status)
    {
        if ($json === false) {
            throw new BrokenResponseBody(['response' => $response], BrokenResponseBody::NOT_JSON_CODE, null, ['statusType' => static::getStatus($status)]);
        }
    }

    protected static function validateJsonError($array, $response, $status)
    {
        static::validateJson($array, ['message', 'description', 'error'], false, $response, $status);
    }

    protected static function validateJson($array, $fields, $canHaveAnotherFields, $response, $status)
    {
        $exceptionData = [
            'missingFields' => $fields,
            'haveAnotherFields' => false,
        ];
        $haveAnotherFields = false;

        foreach ($array as $key => $value) {
            $searchI = array_search($key, $exceptionData['missingFields'], true);
            if ($searchI !== false) {
                unset($exceptionData['missingFields'][$searchI]);
            } else {
                $haveAnotherFields = true;
            }
        }

        if (!$canHaveAnotherFields && $haveAnotherFields) {
            $exceptionData['haveAnotherFields'] = true;
        }

        if (!empty($exceptionData['missingFields']) || $exceptionData['haveAnotherFields']) {
            $exceptionData['response'] = $response;
            throw new BrokenResponseBody($exceptionData, BrokenResponseBody::INVALID_STRUCT_CODE, null, ['statusType' => static::getStatus($status)]);
        }
    }
}
