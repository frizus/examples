<?php
namespace App\Filters;

use App\Filters\Concerns\DomainsList;
use App\Repositories\AssetDerivativeRepository;

class DerivativeFilter extends Filter
{
    use DomainsList;

    public static $fillable = [
        'name',
        'domain',
        'active',
    ];

    public $likeFields = ['name'];

    public $name;

    public $nameLike;

    public $domain;

    public $active;

    public function __construct($fields, $repository = null)
    {
        parent::__construct($fields);
        $this->repository = $repository ?: app()->make(AssetDerivativeRepository::class);
    }

    public function addQuery($query)
    {
        if (isset($this->nameLike)) {
            $query->where(function($query) {
                $query->orWhere('name1', 'like', '%' . $this->nameLike . '%');
                $query->orWhere('name2', 'like', '%' . $this->nameLike . '%');
            });
        }

        if (isset($this->domain)) {
            $query->where('domain', $this->domain);
        }

        if (isset($this->active)) {
            $query->where('active', $this->active === '1');
        }
    }

    public function activeList()
    {
        return $this->countForFilter->addCounts('active', [
            '' => 'Не выбрано',
            '1' => 'Да',
            '0' => 'Нет',
        ]);
    }
}
