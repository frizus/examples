<?php
namespace App\TargetDomains\Libraries\Image\Storage;

use App\TargetDomains\Libraries\Image\Exceptions\Storage\NotSuccessfulStatus;

/**
 * @see https://yandex.ru/dev/disk/api/reference/create-folder.html
 *
 * Class ReserveUpload
 * @package App\TargetDomains\Libraries\Image\Storage
 */
class CreateFolder extends Base
{
    protected const QUERY = ['PUT', 'https://cloud-api.yandex.net/v1/disk/resources'];

    protected const GET_BODY_WHEN_STATUS = null;

    protected const GET_BODY_WHEN_STATUS_NOT = [500, 502];

    protected const STATUSES = [
        NotSuccessfulStatus::NOT_ENOUGH_SPACE_CODE => [507, 403],
        NotSuccessfulStatus::UNAUTHORIZED_CODE => [401],
        NotSuccessfulStatus::PROBABLY_TEMPORAL_SERVER_ERROR_CODE => [400, 404, 406, 423, 429, 503, 405, 502, 500],
    ];

    protected const QUERY_FIELDS = ['href', 'method'];

    public static function request($path)
    {
        return static::controller([
            'path' => $path,
            'fields' => implode(',', static::QUERY_FIELDS),
        ]);
    }

    protected static function validateBody($status, $isFailure, $body, $response, $query)
    {
        $json = parent::validateBody($status, $isFailure, $body, $response, $query);

        if ($status == 201) {
            static::validateJson($json, ['href', 'method'], true, $response, $status);
        }

        return $json;
    }

    protected static function getResult($status, $json, $response, $query)
    {
        if ($status == 409) {
            if ($json['error'] === 'DiskPathPointsToExistentDirectoryError') {
                return [
                    'status' => 'error',
                    'folderState' => 'exists',
                ];
            } elseif ($json['error'] === 'DiskPathDoesntExistsError') {
                return [
                    'status' => 'error',
                    'folderState' => 'not exists',
                ];
            }
        } elseif ($status == 201) {
            return [
                'status' => 'success',
                'folderState' => 'created',
            ];
        }

        return false;
    }
}
