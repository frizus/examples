<?php

namespace Frizus\Module\Rule\Base;

interface DataAwareRule
{
    /**
     * @param  array  $data
     * @return $this
     */
    public function setData($data);
}
