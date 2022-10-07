<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\UserCode;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Contracts\Support\Arrayable;
use Log;

class UserRepository implements UserRepositoryInterface
{
    protected $user;

    /**
     * UserRepository constructor.
     *
     * @param \App\Models\User $user
     *
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * [getModel Get user model query builder]
     *
     * @param  boolean  $archived
     * @return \Illuminate\Database\Eloquent\Builder
     *
     */
    public function getModel($archived = false)
    {
        return $this->user->when($archived === false, function ($archivedQuery) {
            return $archivedQuery->notArchived();
        });
    }

    /**
     * [getTokens Get token of user.]
     *
     * @param  mixed  $id
     * @param  boolean  $archived
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]|static|null
     */
    public function getTokens($id, $archived = false)
    {
        return $this->getModel($archived)->with(['tokens' => function ($que) {
            $que->where('revoked', 0);
        }])->find($id);
    }

    /**
     * [generate2FACode Generate 4 digit code for login.]
     *
     * @param  mixed  $id
     * @param  boolean  $archived
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]|static|null
     */
    public function generate2FACode($id, $archived = false)
    {
        $user = $this->find($id, $archived);
        $userCode = UserCode::updateOrCreate(
            ['user_id' => $user->id],
            ['code' => rand(1000, 9999)]
        );
        return $userCode;
    }

    /**
     * [find2FACode Find user 4 digit code for login.]
     *
     * @param  int  $id
     * @param  int  $code
     * @param  boolean  $archived
     * @return \Illuminate\Database\Eloquent\Collection|static[]|static|null
     */
    public function find2FACode($id, $code, $archived = false)
    {
        $user = $this->find($id, $archived);
        $userCode = UserCode::where('user_id',$user->id)->where('code', $code)->first();
        return $userCode;
    }

    /**
     * [authorizeRoles Authorize that user has any role from given roles]
     *
     * @param \App\Models\User $user
     * @param string|array $roles
     * @return bool
     *
     */
    public function authorizeRoles(User $user, $roles)
    {
        if (is_array($roles)) {
            return $this->hasAnyRole($user, $roles) ||
                false;
        }
        return $this->hasRole($user, $roles) ||
            false;
    }

    /**
     * [hasAnyRole Check that the user belongs to any role from array]
     *
     * @param \App\Models\User $user
     * @param array $roles
     * @return bool
     *
     */
    public function hasAnyRole(User $user, $roles)
    {
        return $user->roles()->whereIn('slug', $roles)->exists();
    }

    /**
     * [hasRole Check that the user belongs to given role]
     *
     * @param \App\Models\User $user
     * @param string $role
     * @return bool
     *
     */
    public function hasRole(User $user, $role)
    {
        return $user->roles()->where('slug', $role)->exists();
    }

    /**
     * [getUserRoles Get the roles belongs to given user]
     *
     * @param \App\Models\User $user
     * @return array
     *
     */
    public function getUserRoles(User $user)
    {
        return $user->roles()->pluck('slug')->toArray();
    }

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
    ) {
        if (is_array($id) || $id instanceof Arrayable) {
            $id = $id instanceof Arrayable ? $id->toArray() : $id;
        }
        return $this->getModel($archived)->find($id, $columns);
    }

    /**
     * [create Save a new user and return the instance]
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model
     *
     */
    public function create($attributes)
    {
        return $this->user->create($attributes);
    }

    /**
     * [getUserListingCount Get users count]
     *
     * @param  array  $search
     * @param  boolean  $archived
     * @return int
     *
     */
    public function getUserListingCount($search, $archived = false)
    {
        $val = $search['value'];
        return $this->getModel($archived)
            ->leftJoin('role_user', 'role_user.user_id', '=', 'users.id')
            ->leftJoin('roles', 'role_user.role_id', '=', 'roles.id')
            ->when($val, function ($searchQuery) use ($val) {
                return $searchQuery->where(function ($query) use ($val) {
                    $query->SearchUserName($val)
                        ->SearchUserEmail($val)
                        ->SearchUserRole($val)
                        ->SearchUserStatus($val);
                });
            })
            ->where('users.id', '<>', auth()->user()->id)
            ->distinct('users.id')
            ->count('users.id');
    }

    /**
     * [getUserListing Get users listing data with filter and sorting]
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
    ) {
        $val = $search['value'];
        return $this->getModel($archived)
            ->leftJoin('role_user', 'role_user.user_id', '=', 'users.id')
            ->leftJoin('roles', 'role_user.role_id', '=', 'roles.id')
            ->when($val, function ($searchQuery) use ($val) {
                return $searchQuery->where(function ($query) use ($val) {
                    $query->SearchUserName($val)
                        ->SearchUserEmail($val)
                        ->SearchUserRole($val)
                        ->SearchUserStatus($val);
                });
            })
            ->where('users.id', '<>', auth()->user()->id)
            ->orderBy($expression, $orderBy)
            ->take($iDisplayLength)
            ->offset($iDisplayStart)
            ->get([
                'users.id',
                'users.name',
                'users.email',
                'roles.name as role',
                'users.status',
            ]);
    }

    /**
     * [delete Delete user]
     *
     * @param  int  $id
     * @param  boolean  $archived
     * @return array
     *
     */
    public function delete($id, $archived = false)
    {
        try {
            $this->find($id, $archived)->update([
                'status' => config('constant.ARCHIVED_FLAG'),
            ]);

            // All good so return the response
            return [
                'status' => true,
                'message' => trans('message.USER_DELETED_SUCCESSFULLY_MESSAGE'),
                'code' => 200,
            ];
        } catch (\Exception$e) {
            Log::error(__METHOD__ . " | Error: {$e->getMessage()}");
            return [
                'status' => false,
                'message' => trans('message.USER_DELETED_ERROR_MESSAGE'),
                'code' => 500,
            ];
        }
    }

    /**
     * [updateStatus Update user status]
     *
     * @param  integer  $id
     * @return array
     *
     */
    public function updateStatus($id)
    {
        try {
            $oldStatus = $this->find($id)->status;
            // Update status of user
            $status = (
                $oldStatus == config('constant.ACTIVE_FLAG')
                ? config('constant.INACTIVE_FLAG')
                : (
                    $oldStatus == config('constant.INACTIVE_FLAG')
                    ? config('constant.ACTIVE_FLAG')
                    : $oldStatus
                )
            );

            // Update form status
            $this->find($id)->update([
                'status' => $status,
            ]);

            // All good so return the response
            return [
                'status' => true,
                'message' => trans('message.USER_STATUS_UPDATED_SUCCESS_MESSAGE'),
                'code' => 200,
            ];
        } catch (\Exception$e) {
            Log::error(__METHOD__ . " | Error: {$e->getMessage()}");
            return [
                'status' => false,
                'message' => trans('message.USER_STATUS_UPDATE_ERROR_MESSAGE'),
                'code' => 500,
            ];
        }
    }

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
    ) {
        return $this->find($id, $archived)->update($attributes);
    }

    /**
     * [createOrUpdateByWhereCondition Create/update the user by checking where condition]
     *
     * @param  array  $condition e.g: [['columnName1', '=', $value], ['columnName2', '<>', $value]]
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Collection|static[]|static|null
     *
     */
    public function createOrUpdateByWhereCondition($condition, $attributes)
    {
        // Find the user by conditions
        $user = $this->findByWhereCondition($condition);
        if ($user) {
            // Update the user
            $this->update($user->id, $attributes);
        } else {
            // Create the user
            $user = $this->create($attributes);
        }
        return $user;
    }

    /**
     * [findByWhereCondition Find a user by where condition]
     *
     * @param  array  $condition
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
