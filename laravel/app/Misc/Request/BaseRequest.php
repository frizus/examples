<?php

namespace App\Misc\Request;

use Illuminate\Support\Facades\Http;

class BaseRequest
{
    public $defaultOptions = [
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

    public function send($method, $url, $options = [], $query = [], $post = [], $attach = [], $cookies = [])
    {
        if (!empty($query)) {
            $options['query'] = $query;
        }

        $request = Http::withOptions($options);

        $data = !empty($post) ? $post : [];

        if (!empty($attach)) {
            if (is_array(reset($attach))) {
                $request->attach($attach);
            } else {
                $request->attach(...$attach);
            }
            $sendOptions = [
                'multipart' => $data
            ];
        } elseif (!empty($post)) {
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

    protected function buildUrl($url, $query)
    {
        if (!empty($query)) {
            $url = strtok($url, '?') . '?' . http_build_query($query);
        }
        return $url;
    }

    protected function mergeOptions($options, $defaultOptions)
    {
        $newOptions = [];

        foreach ($options as $key => $value) {
            if (array_key_exists($key, $defaultOptions)) {
                if (is_array($value) && is_array($defaultOptions[$key])) {
                    $newOptions[$key] = $this->mergeOptions($options[$key], $defaultOptions[$key]);
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
