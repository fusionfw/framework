<?php

namespace App\Modules\User\Services;

use Fusion\Core\Service;
use App\Modules\User\Models\User;

class UserService extends Service
{
    public function all(): array
    {
        return User::query()->limit(50)->get();
    }
}
