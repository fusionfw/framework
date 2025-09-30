<?php

use Fusion\Core\Router;

$router = new Router();

$router->get('/users', 'User\\Controllers\\UserController@index');
