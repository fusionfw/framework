<?php

use Flexify\Core\Router;

$router = new Router();

// Home routes
$router->get('/', 'Home\Controllers\HomeController@index');
$router->get('/api', 'Home\Controllers\HomeController@api');
