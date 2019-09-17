<?php

namespace T2G\Common\Repository;

use Illuminate\Database\Eloquent\Model;
use T2G\Common\Contract\RepositoryInterface;

/**
 * Class BaseRepository
 */
abstract class AbstractEloquentRepository implements RepositoryInterface
{
    const DEFAULT_PER_PAGE = 10;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * AbstractEloquentRepository constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->model = $this->makeModel();
    }

    /**
     * @return string
     */
    abstract public function model():string;

    /**
     * @return Model
     * @throws \Exception
     */
    protected function makeModel()
    {
        $model = app($this->model());
        if (!$model instanceof Model) {
            throw new \Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $model;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    protected function query()
    {
        return $this->model->newModelQuery();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function all() {
        return $this->query()->get();
    }

    /**
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     * @throws \Throwable
     */
    public function create(array $data){
        $model = $this->makeModel();
        $model->fill($data)
            ->saveOrFail();

        return $model;
    }

    /**
     * @param array $data
     * @param       $id
     *
     * @return bool
     * @throws \Throwable
     */
    public function update(array $data, $id){
        $record = $this->find($id);
        if (!$record) {
            return false;
        }
        $record->fill($data);

        return $record->saveOrFail();
    }

    /**
     * @param $id
     *
     * @return bool|mixed|null
     */
    public function delete($id){
        return $this->query()->whereKey($id)->delete();
    }

    /**
     * @param $id
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function find($id){
        return $this->model->newModelQuery()->find($id);
    }
}
