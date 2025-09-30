<?php

namespace App\Modules\Home\Controllers;

use Flexify\Core\Controller;
use Flexify\Core\Request;
use Flexify\Core\Response;

class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        $data = [
            'title' => 'Welcome to Flexify Framework',
            'message' => 'Framework PHP custom yang powerful dan mudah digunakan!',
            'features' => [
                'Hybrid MVC + Modular Architecture',
                'Service Layer untuk Business Logic',
                'Middleware System',
                'Security Features (CSRF, XSS Protection)',
                'Simple CLI Tools',
                'PSR-4 Autoloading',
                'Dependency Injection Container'
            ]
        ];

        return $this->view('Home.home.index', $data);
    }

    public function api(Request $request): Response
    {
        $data = [
            'status' => 'success',
            'message' => 'API endpoint working!',
            'timestamp' => date('Y-m-d H:i:s'),
            'framework' => 'Flexify v1.0.0'
        ];

        return $this->json($data);
    }
}
