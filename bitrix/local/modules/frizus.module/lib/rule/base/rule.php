<?php
namespace Frizus\Module\Rule\Base;

interface Rule
{
    public function passes($attribute, $value, $keyExists);

    public function message();
}
