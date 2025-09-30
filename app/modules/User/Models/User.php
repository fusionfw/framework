<?php

namespace App\Modules\User\Models;

use Fusion\Core\Model;

class User extends Model
{
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password'];
}
