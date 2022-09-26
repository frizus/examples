<?php
namespace Frizus\Module\HttpRequest\Requests;

use Frizus\Module\HttpRequest\Request;

class Base
{
    /**
     * @var Request
     */
    public $request;

    public function getError()
    {
        return isset($this->request->result) && isset($this->request->result['error']) ?
            $this->request->result['error'] :
            isset($this->request->error) ?
                $this->request->error :
                'неизвестная ошибка запроса';
    }

    public function getResult()
    {
        return $this->request->result;
    }
}
