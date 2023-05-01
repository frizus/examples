<?php

namespace Frizus\Module\HttpRequests;

use Frizus\Module\HttpRequests\HttpClientWrapper\HttpClientWrapper;

class AuthRequest extends Base2
{
    public function __construct()
    {
        $this->httpClient = new HttpClientWrapper([
            'getBodyStatus' => [200],
            'validateBody' => function (HttpClientWrapper $httpClient) {
                if ($httpClient->validateBody()) {
                    if ($httpClient->status === 200) {
                        $httpClient->validateJson(
                            [
                                'access_token' => 'string',
                            ],
                            [],
                            true
                        );
                    }
                }
            },
            'makeResult' => function (HttpClientWrapper $httpClient) {
                if ($httpClient->status === 200) {
                    return $httpClient->result;
                }

                return false;
            },
            'options' => [
                'waitResponse' => true,
                'socketTimeout' => 60,
                'streamTimeout' => 600,
            ],
        ]);
    }

    public function request($login, $password)
    {
        if ($this->httpClient->request(
            'GET',
            'https://example.com/auth',
            [],
            [],
            [],
            [
                'Authorization' => 'Basic ' . base64_encode($login . ':' . $password),
            ]
        )) {
            return is_array($this->httpClient->result);
        }

        return false;
    }
}