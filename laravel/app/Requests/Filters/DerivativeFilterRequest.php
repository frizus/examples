<?php

namespace App\Http\Requests\Filters;

use App\Http\Requests\Request;
use App\Repositories\TargetDomainRepository;
use App\Rules\IntBoolean;
use App\Rules\NotEmptyString;
use App\Rules\TargetDomainExists;

class DerivativeFilterRequest extends FilterRequest
{
    /**
     * @inerhitdoc
     */
    public function authorize()
    {
        return true;
    }

    /**
     * @inerhitdoc
     */
    public function rules()
    {
        return [
            'name' => [
                new NotEmptyString,
            ],
            'domain' => [
                'bail',
                new NotEmptyString,
                new TargetDomainExists,
            ],
            'active' => [
                'bail',
                new NotEmptyString,
                new IntBoolean,
            ]
        ];
    }

    /**
     * @inerhitdoc
     */
    public function validationData()
    {
        return [
            'name' => $this->query->get('name'),
            'domain' => $this->query->get('domain'),
            'active' => $this->query->get('active'),
        ];
    }
}
