<?php

namespace App\Modules\User\Controllers;

use Fusion\Core\Controller;
use Fusion\Core\Request;
use Fusion\Core\Response;
use App\Modules\User\Services\UserService;

class UserController extends Controller
{
    private UserService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new UserService();
    }

    public function index(Request $request): Response
    {
        $users = $this->service->all();
        return $this->json(['data' => $users]);
    }
}
