<?php
namespace App\Filters;

use App\Filters\Concerns\Date;
use App\Filters\Concerns\Like;
use App\Misc\Helpers\CountForFilter;
use App\Repositories\BaseRepository;

class Filter
{
    use Date;
    use Like;

    public static $fillable = [];

    public $commonFilter = [];

    public $haveFilters = false;

    /** @var BaseRepository */
    public $repository;

    /**
     * @var CountForFilter
     */
    public $countForFilter;

    public function __construct($fields)
    {
        foreach (static::$fillable as $field) {
            if (array_key_exists($field, $fields)) {
                $this->$field = $fields[$field];
                if ($this->isLike($field)) {
                    $haveValue = $this->setLike($field, $fields[$field]);
                } elseif ($this->isDate($field)) {
                    $haveValue = $this->setDate($field, $fields[$field]);
                } else {
                    $haveValue = isset($fields[$field]);
                }
                if (!$this->haveFilters && $haveValue) {
                    $this->haveFilters = true;
                }
            }
        }
        $this->swapWrongDateRanges();
        $this->countForFilter = app()->makeWith(CountForFilter::class, ['filter' => $this]);
    }

    public function addQuery($query)
    {

    }

    public function addPaginate($paginate)
    {
        foreach (static::$fillable as $field) {
            if (isset($filter->$field)) {
                if ($filter->isDate($field)) {
                    $paginate->appends($field, $filter->formatDate($filter->$field));
                } else {
                    $paginate->appends($field, $filter->$field);
                }
            }
        }
    }

    public function getValue($field)
    {
        if ($this->isLike($field)) {
            return $this->{$field . 'Like'};
        }

        if ($this->isDate($field)) {
            return $this->formatDate($this->{$field . 'Date'});
        }

        return $this->$field;
    }

    public function setCommonFilter($commonFilter)
    {
        $this->commonFilter = $commonFilter;
    }
}
