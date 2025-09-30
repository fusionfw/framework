<?php

namespace App\Modules\Blog\Repositories;

use Fusion\Core\Repository;

class BlogRepository extends Repository
{
    protected $table = 'blogs';
    protected $primaryKey = 'id';
}
