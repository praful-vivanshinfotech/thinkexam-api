<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use DB;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

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
    protected $hidden = [
        'password', 'remember_token', 'updated_at', 'created_at', 'email_verified_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'status_label',
    ];

    /**
     * Get the user status label name.
     *
     * @return string
     */
    public function getStatusLabelAttribute()
    {
        $status = $this->status;
        switch ($status) {
            case config('constant.INACTIVE_FLAG'):
                return config('constant.INACTIVE_FLAG_LABEL');
                break;
            case config('constant.ARCHIVED_FLAG'):
                return config('constant.ARCHIVED_FLAG_LABEL');
                break;
            default:
                return config('constant.ACTIVE_FLAG_LABEL');
        }
    }

    /**
     * Set the user's password
     *
     * @param string  $password
     * @return void
     */
    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }

    /**
     * Not archived users
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotArchived($query)
    {
        return $query->where('status', '<>', config('constant.ARCHIVED_FLAG'));
    }
}
