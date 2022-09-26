<?php
namespace App\Misc\Helpers;

use App\Filters\Filter;

class CountForFilter
{
    /**
     * @var Filter
     */
    public $filter;

    protected $counts = [];

    protected $cacheKeyValues;

    protected $cacheDisabled;

    protected $oldFilterValue;

    protected $switchedFilterValue;

    public function __construct($filter)
    {
        $this->filter = $filter;
    }

    public function addCounts($field, $list)
    {
        if (filter_counts()) {
            foreach ($list as $value => &$text) {
                $text .= ' (' . $this->getCount($field, $value === '' ? null : $value) . ')';
            }
        }

        return $list;
    }

    public function addCount($text)
    {
        if (filter_counts()) {
            $text .= ' (' . $this->getCount() . ')';
        }
        return $text;
    }

    public function getCount($field = null, $value = null)
    {
        $cacheKey = $this->cacheKey($field, $value);
        if (!array_key_exists($cacheKey, $this->counts)) {
            if ($this->cacheDisabled()) {
                $this->counts[$cacheKey] = $this->getCountInternal($field, $value);
            } else {
                $this->counts[$cacheKey] = cache()->remember($cacheKey, now()->addWeek(), function() use ($field, $value) {
                    return $this->getCountInternal($field, $value);
                });
            }
        }

        return $this->counts[$cacheKey];
    }

    protected function getCountInternal($field, $value)
    {
        if (isset($field)) {
            $this->switchFilterValue($field, $value);
        }
        $query = $this->filter->repository->allQueryWithFilter($this->filter, $this->filter->commonFilter);
        if (isset($field)) {
            $this->restoreFilterValue($field);
        }
        return $query->count();
    }

    protected function cacheDisabled()
    {
        return true;
        if (!isset($this->cacheDisabled)) {
            $this->cacheDisabled = false;
            if (property_exists($this->filter, 'likeFields')) {
                foreach ($this->filter->likeFields as $field) {
                    if (!is_null($this->filter->getValue($field))) {
                        $this->cacheDisabled = true;
                    }
                }
            }
            if (!$this->cacheDisabled) {
                if (property_exists($this->filter, 'dateFields')) {
                    foreach ($this->filter->dateFields as $field) {
                        if (!is_null($this->filter->getValue($field))) {
                            $this->cacheDisabled = true;
                        }
                    }
                }
            }
        }
        return $this->cacheDisabled;
    }

    public function filterChanged()
    {
        if (isset($this->cacheDisabled)) {
            $this->cacheDisabled = null;
        }
        if (isset($this->cacheKeyValues)) {
            $this->cacheKeyValues = null;
        }
    }

    protected function cacheKey($field = null, $value = null)
    {
        $values = $this->getCacheKeyValues();
        if (isset($field)) {
            $values[$field] = $value;
        }
        return get_class($this->filter) . '-' . serialize($values);
    }

    protected function getCacheKeyValues()
    {
        if (!isset($this->cacheKeyValues)) {
            $this->cacheKeyValues = [];
            foreach ($this->filter::$fillable as $field) {
                $this->cacheKeyValues[$field] = $this->filter->getValue($field);
            }
        }
        return $this->cacheKeyValues;
    }

    protected function switchFilterValue($field, $value)
    {
        $this->oldFilterValue = $this->filter->{$field};
        $this->filter->{$field} = $value;
        $this->switchedFilterValue = true;
    }

    protected function restoreFilterValue($field)
    {
        if ($this->switchedFilterValue) {
            $this->filter->{$field} = $this->oldFilterValue;
            $this->oldFilterValue = null;
            $this->switchedFilterValue = false;
        }
    }
}
