<?php

namespace App\Repositories;

use App\Models\AssetDerivative;
use App\Repositories\BaseRepository;

/**
 * Class AssetDerivativeRepository
 * @package App\Repositories
 * @version June 10, 2021, 3:06 pm UTC
*/

class AssetDerivativeRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'domain',
        'original_id',
        'item_id',
        'name1',
        'name2',
        'price',
        'old_price',
        'properties',
        'description',
        'active',
        'categories',
        'allow_export',
        'derivative_created_at',
        'derivative_updated_at',
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return AssetDerivative::class;
    }
}
