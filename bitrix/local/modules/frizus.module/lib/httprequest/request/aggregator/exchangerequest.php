<?php
namespace Frizus\Module\HttpRequest\Requests\Aggregator;

use Frizus\Module\HttpRequest\Requests\Base;
use Frizus\Module\HttpRequest\Request;

class ExchangeRequest extends Base
{
    public function __construct()
    {
        $this->request = new Request([
            'getBodyStatus' => [200, 400],
            'statuses' => [
                'вероятно временная ошибка' => [503, 502, 500],
            ],
            'validateBody' => function ($request) {
                /** @var Request $request */
                if ($request->validateBody()) {
                    if ($request->status === 200) {
                        $request->validateJson(
                            [
                                'total',
                                'sent_products',
                                'items' => 'array',
                                'properties' => 'array',
                            ],
                            [],
                            true
                        );
                    }
                }
            },
            'makeResult' => function($request) {
                /** @var Request $request */
                if ($request->status === 200) {
                    return $request->result;
                } elseif ($request->status === 400) {
                    return [
                        'error' => is_array($request->result['error']) ? implode("\n", $request->result['error']) : $request->result['error'],
                    ];
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

    public function request($domain, $limit, $offset, $server)
    {
        if ($this->request->request(
            'GET',
            'http://parser/exchange',
            [
                'domain' => $domain,
                'limit' => $limit,
                'offset' => $offset,
                'server' => $server,
            ]
        )) {
            return isset($this->request->result) && !isset($this->request->result['error']);
        }

        return false;
    }
}
