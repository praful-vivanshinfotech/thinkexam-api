<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Relations between users and roles table
     * It's Many-to-Many Relations
     * The users that belong to the role.
     *
     * @return $this
     */
    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
