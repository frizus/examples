<?php
namespace App\TargetDomains\Libraries\Image;

use App\TargetDomains\Controller;
use App\TargetDomains\Libraries\Image\Exceptions\Storage\CreateDirectoryError;
use App\TargetDomains\Libraries\Image\Storage\CreateFolder;
use App\TargetDomains\Libraries\Image\Storage\Delete;
use App\TargetDomains\Libraries\Image\Storage\DirectoryExists;
use App\TargetDomains\Libraries\Image\Storage\ReserveUpload;
use App\TargetDomains\Libraries\Image\Storage\MakeUpload;
use Illuminate\Support\Str;

class ImageStorage
{
    public function uploadFile($resource, $uploadLink)
    {
        $result = MakeUpload::request($resource, $uploadLink);

        return [
            'async' => $result['async'],
        ];
    }

    public function reserveUpload($imagePath)
    {
        $result = ReserveUpload::request($imagePath);

        return [
            'uploadLink' => $result['uploadLink'],
            'operationId' => $result['operationId'],
        ];
    }

    public function generateImagePath($uploadDirectory, $url, $extension)
    {
        return $uploadDirectory . '/' . md5($url) . '.' . $extension;
    }

    public function getItemUploadDirectory($domain, $item_id)
    {
        if (array_key_exists($domain, Controller::TARGET_DOMAINS)) {
            $domainClass = 'App\\TargetDomains\\' . Controller::TARGET_DOMAINS[$domain];
            if (class_exists($domainClass)) {
                $domainFolder = $domainClass::NAME;
            }
        }

        if (!isset($domainFolder)) {
            $domainFolder = $domain;
        }

        return $domainFolder . '/' . $item_id;
    }

    public function createUploadDirectoryIfNotExists($uploadDirectory)
    {
        if (!$this->directoryExists($uploadDirectory)) {
            $this->createMissingFolders($uploadDirectory);
        }
    }

    protected function createMissingFolders($uploadDirectory)
    {
        $folders = array_reverse(explode('/', $uploadDirectory), true);
        $findingHighestMissingFolder = true;
        $folder = reset($folders);
        $j = 0;
        $end = Str::length($uploadDirectory);
        do {
            $i = key($folders);
            if ($findingHighestMissingFolder) {
                if ($j > 0) {
                    $end -= Str::length($folders[$i + 1]) + 1;
                }
                $j++;
            } else {
                $end += Str::length($folders[$i]) + 1;
            }

            $folderPath = Str::substr($uploadDirectory, 0, $end);

            $result = CreateFolder::request($folderPath);
            if ($result['status'] == 'error') {
                if ($findingHighestMissingFolder) {
                    if ($result['folderState'] == 'exists') {
                        $findingHighestMissingFolder = false;
                    }
                } else {
                    break;
                }
            } elseif ($result['status'] == 'success') {
                if ($findingHighestMissingFolder) {
                    $findingHighestMissingFolder = false;
                }
            }

            if ($findingHighestMissingFolder) {
                $folder = next($folders);
            } else {
                $folder = prev($folders);
            }
        } while ($folder !== false);

        if ($findingHighestMissingFolder) {
            throw new CreateDirectoryError(['uploadDirectory' => $uploadDirectory], CreateDirectoryError::CANT_CREATE_UPLOAD_DIRECTORY_CODE);
        }
    }

    protected function directoryExists($uploadDirectory)
    {
        $result = DirectoryExists::request($uploadDirectory);
        return $result['status'] == 'success';
    }

    public function delete($path, $async)
    {
        $result = Delete::request($path, $async);

        $returnResult = [
            'async' => $result['async'],
        ];
        if ($result['async']) {
            $returnResult['operationId'] = $result['operationId'];
        }

        return $returnResult;
    }
}
