<?php

namespace Frizus\Module\Rule\Base;

use Frizus\Module\Validation\Validator;

interface ValidatorAwareRule
{
    /**
     * @param  Validator  $validator
     * @return $this
     */
    public function setValidator($validator);
}
