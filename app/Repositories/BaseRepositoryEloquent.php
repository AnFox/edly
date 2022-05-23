<?php


namespace App\Repositories;


use App\Contracts\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class BaseRepositoryEloquent
 * @package App\Repositories
 */
class BaseRepositoryEloquent implements BaseRepository
{
    /** @var Model */
    protected $model;

    /** @var Builder */
    protected $builder;

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param $model
     * @return $this|mixed
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * @param Builder $builder
     * @return BaseRepositoryEloquent
     */
    public function setBuilder(Builder $builder)
    {
        $this->builder = $builder;

        return $this;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return get_class($this->getModel());
    }

    /**
     * @return Collection
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    public function paginate($perPageMax = 100, string $orderBy = 'id', string $orderDirection = 'asc'): Paginator
    {
        if ($this->getBuilder()) {
            $query = $this->getBuilder();
        } else {
            $query = $this->getModel();
        }

        return $query
            ->orderBy($orderBy, $orderDirection)
            ->jsonPaginate($perPageMax);
    }

    /**
     * @param int $id
     * @return $this|mixed
     */
    public function find(int $id)
    {
        $model = $this->model->find($id);
        $this->setModel($model);

        return $this;
    }

    /**
     * @param int $id
     * @throws ModelNotFoundException
     * @return $this|mixed
     */
    public function findOrFail(int $id)
    {
        $model = $this->model->findOrFail($id);
        $this->setModel($model);

        return $this;
    }

    /**
     * @param array $userIdList
     * @return Collection
     */
    public function getByListOfId(array $userIdList)
    {
        return $this->model->whereIn('id', $userIdList)->get();
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        return $this->model->save();
    }

    /**
     * @param array $attributes
     * @return $this|mixed
     */
    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }

    /**
     * @param array $attributes
     * @return $this|mixed
     */
    public function fill(array $attributes)
    {
        return $this->model->fill($attributes);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function delete(): bool
    {
        return $this->model->delete();
    }
}
