<?php

namespace App\Modules\TestModule\Controllers;

use Flexify\Core\Controller;
use Flexify\Core\Request;
use Flexify\Core\Response;

class TestController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->view('TestModule.TestController.index');
    }
}
