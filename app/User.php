<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'role_id',
        'email',
        'password'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Get role
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function role()
    {
        return $this->belongsToMany('App\Role', 'user_role', 'user_id', 'role_id');
    }

    /**
     * Check if has a specific role
     *
     * @param $roles
     * @param null $section
     * @return bool
     */
    public function hasRole($roles, $section = null)
    {
        // Get all user's roles
        $userRoles = $this->role()->getResults();

        // Check which section they belond to
        foreach($userRoles as $hasRole) {
            if (preg_match('/^\/admin/', $section)) {
                if (in_array($hasRole->name, ['Root', 'Administrator'])) {
                    return true;
                }
            } else {
                if (in_array($hasRole->name, ['Root', 'Administrator', 'User'])) {
                    return true;
                }
            }
        }

        return false;
    }
}