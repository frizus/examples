<?php

namespace App\TargetDomains\Libraries;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

// TODO переделать
class Request
{
    public static $defaultOptions = [
        'timeout' => 30,
        'read_timeout' => 30,
        'connect_timeout' => 30,
        'allow_redirects' => [
            'max'             => 5,
            'protocols'       => ['http', 'https'],
            'strict'          => false,
            'referer'         => false,
            'track_redirects' => true,
        ],
    ];

    /**
     * @param $url
     * @param array $options
     * @return \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
     */
    static function load($url, $options = [], $query = [])
    {
        $options = static::getOptions($options);

        if (!empty($query)) {
            return Http::withOptions($options)->get($url, $query);
        } else {
            return Http::withOptions($options)->get($url);
        }
    }

    static function send($method, $url, $options = [], $query = [], $postData = [], $attach = [], $cookies = [])
    {
        $options = static::getOptions($options);

        if (!empty($query)) {
            $options['query'] = $query;
        }

        $request = Http::withOptions($options);

        $data = !empty($postData) ? $postData : [];

        if (!empty($attach)) {
            if (is_array(reset($attach))) {
                $request->attach($attach);
            } else {
                $request->attach(...$attach);
            }
            $sendOptions = [
                'multipart' => $data
            ];
        } elseif (!empty($postData)) {
            $sendOptions = [
                'form_params' => $data,
            ];
        } else {
            $sendOptions = [];
        }

        if (!empty($cookies)) {
            $request->withCookies($cookies, '.');
        }

        return $request->send($method, $url, $sendOptions);
    }

    static function redirectedOnAnotherDomain(Response $response, $origHostname)
    {
        $redirectHistory = $response->getHeader('X-Guzzle-Redirect-History');

        if (!empty($redirectHistory)) {
            $effectiveUri = $response->effectiveUri();
            $scheme = $effectiveUri->getScheme() == 'https' ? 'http' : $effectiveUri->getScheme();
            $builtHostname = $effectiveUri::composeComponents($scheme, $effectiveUri->getAuthority(), '', '', '');

            $origHostname = str_replace('https://', 'http://', Str::lower($origHostname));
            $changedDomain = $builtHostname != $origHostname;
        } else {
            $changedDomain = false;
        }

        if ($changedDomain) {
            // @TODO log this situation
        }

        return $changedDomain;
    }

    protected static function getOptions($options)
    {
        static $defaultOptions;

        if (!isset($defaultOptions)) {
            $defaultOptions = static::$defaultOptions;

            $userAgent = config('scrapper.download_request_user_agent');
            if (isset($userAgent)) {
                $defaultOptions['headers']['User-Agent'] = $userAgent;
            }
            $referer = config('scrapper.download_request_referer');
            if (isset($referer)) {
                $defaultOptions['headers']['Referer'] = $referer;
            }
        }

        return static::getOptionsHelper($options, $defaultOptions);
    }

    protected static function getOptionsHelper($options, $defaultOptions)
    {
        $newOptions = [];

        foreach ($options as $key => $value) {
            if (array_key_exists($key, $defaultOptions)) {
                if (is_array($value) && is_array($defaultOptions[$key])) {
                    $newOptions[$key] = static::getOptionsHelper($options[$key], $defaultOptions[$key]);
                } else {
                    $newOptions[$key] = $value;
                }
                unset($defaultOptions[$key]);
            } else {
                $newOptions[$key] = $value;
            }
        }

        if (!empty($defaultOptions)) {
            foreach($defaultOptions as $key => $value) {
                $newOptions[$key] = $value;
                unset($defaultOptions[$key]);
            }
        }

        return $newOptions;
    }
}
