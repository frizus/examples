<?php

namespace Frizus\Module\Rule\Base;

trait DataAwareRule
{
    protected $data;

    /**
     * @param array $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}