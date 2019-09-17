<?php

namespace T2G\Common\Contract;

/**
 * Class RepositoryInterface
 */
interface RepositoryInterface
{
    public function all();

    public function create(array $data);

    public function update(array $data, $id);

    public function delete($id);

    public function find($id);
}
