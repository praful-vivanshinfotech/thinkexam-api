<?php

namespace App\Repositories\Interfaces;

use App\Models\User;

interface UserRepositoryInterface
{
    /**
     * [getModel Get user model query builder]
     *
     * @param  boolean  $archived
     * @return \Illuminate\Database\Eloquent\Builder
     *
     */
    public function getModel($archived = false);

    /**
     * [getTokens Get token of user.]
     *
     * @param  mixed  $id
     * @param  boolean  $archived
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]|static|null
     */
    public function getTokens($id, $archived = false);

    /**
     * [generate2FACode Generate 4 digit code for login.]
     *
     * @param  mixed  $id
     * @param  boolean  $archived
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]|static|null
     */
    public function generate2FACode($id, $archived = false);

    /**
     * [authorizeRoles Authorize that user has any role from given roles]
     *
     * @param \App\Models\User $user
     * @param string|array $roles
     * @return bool
     *
     */
    public function authorizeRoles(User $user, $roles);

    /**
     * [hasAnyRole Check that the user belongs to any role from array]
     *
     * @param \App\Models\User $user
     * @param array $roles
     * @return bool
     *
     */
    public function hasAnyRole(User $user, $roles);

    /**
     * [hasRole Check that the user belongs to given role]
     *
     * @param \App\Models\User $user
     * @param string $role
     * @return bool
     *
     */
    public function hasRole(User $user, $role);

    /**
     * [getUserRoles Get the roles belongs to given user]
     *
     * @param \App\Models\User $user
     * @return array
     *
     */
    public function getUserRoles(User $user);

    /**
     * [find Find a user by id.]
     *
     * @param  mixed  $id
     * @param  boolean  $archived
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]|static|null
     *
     */
    public function find(
        $id,
        $archived = false,
        $columns = ['*']
    );

    /**
     * [create Save a new user and return the instance]
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model
     *
     */
    public function create($attributes);

    /**
     * [getUserListingCount Get users count]
     *
     * @param  array  $search
     * @param  boolean  $archived
     * @return int
     *
     */
    public function getUserListingCount($search, $archived = false);

    /**
     * [getUserListing Get users listing data with sorting]
     *
     * @param  array  $search
     * @param  string  $expression
     * @param  string  $orderBy
     * @param  integer  $iDisplayLength
     * @param  integer  $iDisplayStart
     * @param  boolean  $archived
     * @return \Illuminate\Database\Eloquent\Collection
     *
     */
    public function getUserListing(
        $search,
        $expression,
        $orderBy,
        $iDisplayLength = PHP_INT_MAX,
        $iDisplayStart = 0,
        $archived = false
    );

    /**
     * [delete Delete user]
     *
     * @param  int  $id
     * @param  boolean  $archived
     * @return array
     *
     */
    public function delete($id, $archived = false);

    /**
     * [updateStatus Update user status]
     *
     * @param  integer  $id
     * @return array
     *
     */
    public function updateStatus($id);

    /**
     * [update Update user]
     *
     * @param  integer  $id
     * @param  array  $attributes
     * @return int
     *
     */
    public function update(
        $id,
        array $attributes,
        $archived = false
    );

    /**
     * [createOrUpdateByWhereCondition Create/update the user by checking where condition]
     *
     * @param  array  $condition e.g: [['columnName1', '=', $value], ['columnName2', '<>', $value]]
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Collection|static[]|static|null
     *
     */
    public function createOrUpdateByWhereCondition($condition, $attributes);

    /**
     * [findByWhereCondition Find a user by where condition]
     *
     * @param  array  $condition
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]|static|null
     *
     */
    public function findByWhereCondition($condition, $columns = ['*']);
}
