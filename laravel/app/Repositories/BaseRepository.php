<?php

namespace App\Repositories;

use Illuminate\Container\Container as Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;


abstract class BaseRepository
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Application
     */
    protected $app;

    public $created;

    public $updated;

    /**
     * @param Application $app
     *
     * @throws \Exception
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->makeModel();
    }

    /**
     * Get searchable fields array
     *
     * @return array
     */
    abstract public function getFieldsSearchable();

    /**
     * Configure the Model
     *
     * @return string
     */
    abstract public function model();

    /**
     * Make Model instance
     *
     * @throws \Exception
     *
     * @return Model
     */
    public function makeModel()
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model) {
            throw new \Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    /**
     * Paginate records for scaffold.
     *
     * @param int $perPage
     * @param array $columns
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage, $columns = ['*'], $search = [], $order = null, $with = null)
    {
        $query = $this->allQuery($search, null, null, $order, $with);
        return $query->paginate($perPage, $columns);
    }

    public function paginateWithFilter($perPage, $filter, $columns = ['*'], $search = [], $order = null, $with = null)
    {
        $query = $this->allQueryWithFilter($filter, $search, null, null, $order, $with);
        $paginate = $query->paginate($perPage, $columns);
        $filter->addPaginate($paginate);
        return $paginate;
    }

    /**
     * Build a query for retrieving all records.
     *
     * @param array $search
     * @param int|null $skip
     * @param int|null $limit
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function allQuery($search = [], $skip = null, $limit = null, $order = null, $with = null)
    {
        $query = $this->model->newQuery();

        if (count($search)) {
            foreach($search as $key => $value) {
                if (filter_var($key, FILTER_VALIDATE_INT) !== false) {
                    $query->where($value);
                } else {
                    if (in_array($key, $this->getFieldsSearchable(), true)) {
                        $query->where($key, $value);
                    }
                }
            }
        }

        if (!is_null($skip)) {
            $query->skip($skip);
        }

        if (!is_null($limit)) {
            $query->limit($limit);
        }

        if (!is_null($order)) {
            $this->addOrder($query, $order);
        }

        if (!is_null($with)) {
            $query->with($with);
        }

        return $query;
    }

    public function allQueryWithFilter($filter, $search = [], $skip = null, $limit = null, $order = null, $with = null)
    {
        $query = $this->allQuery($search, $skip, $limit, $order, $with);
        $filter->addQuery($query);
        return $query;
    }

    public function count($search = [])
    {
        $query = $this->allQuery($search);

        return $query->count();
    }

    /**
     * Retrieve all records with given filter criteria
     *
     * @param array $search
     * @param int|null $skip
     * @param int|null $limit
     * @param array $columns
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function all($search = [], $skip = null, $limit = null, $columns = ['*'], $orders = null, $with = null)
    {
        $query = $this->allQuery($search, $skip, $limit, $orders, $with);
        return $query->get($columns);
    }

    public function allWithFilter($filter, $search = [], $skip = null, $limit = null, $columns = ['*'], $orders = null, $with = null)
    {
        $query = $this->allQueryWithFilter($filter, $search, $skip, $limit, $with);
        $this->addOrder($query, $orders);
    }

    protected function addOrder($query, $orders)
    {
        foreach ($orders as $order) {
            if (is_array($order)) {
                call_user_func_array([$query, 'orderBy'], $order);
            } else {
                $query->orderBy($order);
            }
        }
    }

    /**
     * Create model record
     *
     * @param array $input
     *
     * @return Model
     */
    public function create($input)
    {
        $model = $this->model->newInstance($input);

        $this->created = $model->save();

        return $model;
    }

    /**
     * Find model record for given id
     *
     * @param int $id
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Model|null
     */
    public function find($id, $columns = ['*'], $with = null)
    {
        $query = $this->model->newQuery();

        if (!is_null($with)) {
            $query->with($with);
        }

        return $query->find($id, $columns);
    }

    /**
     * Update model record for given id
     *
     * @param array $input
     * @param int|Model $id
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Model
     */
    public function update($input, $id)
    {
        $query = $this->model->newQuery();

        if ($id instanceof Model) {
            $model = $id;
        } else {
            $model = $query->findOrFail($id);
        }

        $model->fill($input);

        $this->updated = $model->save();

        return $model;
    }

    /**
     * @param int $id
     *
     * @throws \Exception
     *
     * @return bool|mixed|null
     */
    public function delete($id)
    {
        $query = $this->model->newQuery();

        if ($id instanceof Model) {
            $model = $id;
        } else {
            $model = $query->findOrFail($id);
        }

        return $model->delete();
    }
}
