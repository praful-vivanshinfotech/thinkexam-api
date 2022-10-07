<?php

namespace App\Repositories\Interfaces;

interface RoleRepositoryInterface
{
    /**
     * [getModel Get role model query builder]
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getModel();

    /**
     * [find Find a role by id]
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]|static|null
     *
     */
    public function find($id, $columns = ['*']);

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
    );

    /**
     * [create Save a new role and return the instance]
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model
     *
     */
    public function create($attributes);

    /**
     * [update Update role]
     *
     * @param  integer  $id
     * @param  array  $attributes
     * @return int
     *
     */
    public function update($id, array $attributes);

    /**
     * [createOrUpdateByWhereCondition Create/update the role by checking where condition]
     *
     * @param  array  $condition e.g: [['columnName1', '=', $value], ['columnName2', '<>', $value]]
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Collection|static[]|static|null
     *
     */
    public function createOrUpdateByWhereCondition($condition, $attributes);

    /**
     * [findByWhereCondition Find a role by where condition]
     *
     * @param  array  $condition e.g: [['columnName1', '=', $value], ['columnName2', '<>', $value]]
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]|static|null
     *
     */
    public function findByWhereCondition($condition, $columns = ['*']);
}
