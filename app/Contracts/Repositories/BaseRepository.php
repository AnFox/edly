<?php


namespace App\Contracts\Repositories;


use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Interface BaseRepository
 * @package App\Contracts
 */
interface BaseRepository
{
    /**
     * @param $model
     * @return mixed
     */
    public function setModel($model);

    public function getModel();

    public function getClass();

    public function all();

    public function paginate(int $perPageMax = 100, string $orderBy = 'id', string $orderDirection = 'desc');

    /**
     * @param int $id
     * @return mixed
     */
    public function find(int $id);

    /**
     * @param int $id
     * @throws ModelNotFoundException
     * @return mixed
     */
    public function findOrFail(int $id);

    /**
     * @param array $userIdList
     * @return mixed
     */
    public function getByListOfId(array $userIdList);

    public function save();

    public function create(array $attributes);

    public function fill(array $attributes);

    public function delete();
}
