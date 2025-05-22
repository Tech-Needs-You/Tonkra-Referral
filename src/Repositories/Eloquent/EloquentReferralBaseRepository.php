<?php

namespace Tonkra\Referral\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Tonkra\Referral\Repositories\Contracts\ReferralBaseRepository;

class EloquentReferralBaseRepository implements ReferralBaseRepository
{

    protected $model;

    /**
     * BaseRepository constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @return Builder
     */
    public function query(): Builder
    {
        return $this->model->newQuery();
    }


    /**
     * @param      $query
     * @param null $callback
     * @return mixed
     *
     */
    public function search($query, $callback = null)
    {
        return $this->model->search($query, $callback);
    }

    /**
     * @param array $columns
     *
     * @return Builder
     */
    public function select(array $columns = ['*']): Builder
    {
        return $this->query()->select($columns);
    }

    /**
     * @param array $attributes
     *
     * @return Model
     */
    public function make(array $attributes = []): Model
    {
        return $this->query()->make($attributes);
    }
}
