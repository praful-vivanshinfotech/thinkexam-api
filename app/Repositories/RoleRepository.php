<?php

namespace App\Repositories;

use App\Models\Role;
use App\Repositories\Interfaces\RoleRepositoryInterface;

class RoleRepository implements RoleRepositoryInterface
{
    protected $role;

    /**
     * RoleRepository constructor.
     *
     * @param \App\Models\Role $role
     *
     */
    public function __construct(Role $role)
    {
        $this->role = $role;
    }

    /**
     * [getModel Get role model query builder]
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getModel()
    {
        return $this->role;
    }

    /**
     * [find Find a role by id]
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]|static|null
     *
     */
    public function find($id, $columns = ['*'])
    {
        if (is_array($id) || $id instanceof Arrayable) {
            $id = $id instanceof Arrayable ? $id->toArray() : $id;
        }
        return $this->getModel()->find($id, $columns);
    }

    /**
     * [all Get all of the items in the role collection.]
     *
     * @param  array  $columns
     * @param  boolean  $archived
     * @param  array  $withStatusIn
     * @param  array  $withStatusNotIn
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all(
        $columns = ['*'],
        $archived = false,
        $withStatusIn = [],
        $withStatusNotIn = []
    ) {
        return $this->getModel($archived)->when(!empty($withStatusIn), function ($withStatusInQuery) use ($withStatusIn) {
            return $withStatusInQuery->withStatusIn($withStatusIn);
        })->when(!empty($withStatusNotIn), function ($withStatusNotInQuery) use ($withStatusNotIn) {
            return $withStatusNotInQuery->withStatusNotIn($withStatusNotIn);
        })->get($columns);
    }

    /**
     * [create Save a new role and return the instance]
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model
     *
     */
    public function create($attributes)
    {
        return $this->role->create($attributes);
    }

    /**
     * [update Update role]
     *
     * @param  integer  $id
     * @param  array  $attributes
     * @return int
     *
     */
    public function update($id, array $attributes)
    {
        return $this->find($id)->update($attributes);
    }

    /**
     * [createOrUpdateByWhereCondition Create/update the role by checking where condition]
     *
     * @param  array  $condition e.g: [['columnName1', '=', $value], ['columnName2', '<>', $value]]
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Collection|static[]|static|null
     *
     */
    public function createOrUpdateByWhereCondition($condition, $attributes)
    {
        // Find the role by conditions
        $role = $this->findByWhereCondition($condition);
        if ($role) {
            // Update the role
            $this->update($role->id, $attributes);
        } else {
            // Create the role
            $role = $this->create($attributes);
        }
        return $role;
    }

    /**
     * [findByWhereCondition Find a role by where condition]
     *
     * @param  array  $condition e.g: [['columnName1', '=', $value], ['columnName2', '<>', $value]]
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]|static|null
     *
     */
    public function findByWhereCondition($condition, $columns = ['*'])
    {
        return $this->getModel()
            ->where($condition)
            ->first($columns);
    }
}
