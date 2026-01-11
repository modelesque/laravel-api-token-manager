<?php

namespace Modelesque\ApiTokenManager\Contracts;

use Modelesque\ApiTokenManager\Abstracts\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Interface for base repository supporting Eloquent.
 * @see BaseRepository
 */
interface EloquentRepositoryInterface
{
    /**
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes): Model;

    /**
     * @param $id
     * @return Model|null
     */
    public function find($id): ?Model;
}