<?php

namespace App\Modules\TestModule\Repositories;

use Flexify\Core\Repository;

class TestModuleRepository extends Repository
{
    protected $table = 'testmodules';
    protected $primaryKey = 'id';
}
