<?php

namespace Rennokki\Chargeswarm\Test\Models;

use Rennokki\Chargeswarm\Traits\Billable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Billable;

    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];
}
