<?php

use Fusion\Core\Router;

$router = new Router();

// Blog routes
$router->get('/', 'Blog\Controllers\BlogController@index');
$router->get('/post/{slug}', 'Blog\Controllers\BlogController@show');
$router->get('/search', 'Blog\Controllers\BlogController@search');
$router->get('/category/{category}', 'Blog\Controllers\BlogController@category');

// Admin routes (protected by middleware)
$router->group(['middleware' => 'auth'], function ($router) {
    $router->get('/admin/create', 'Blog\Controllers\BlogController@create');
    $router->post('/admin/store', 'Blog\Controllers\BlogController@store');
    $router->get('/admin/edit/{id}', 'Blog\Controllers\BlogController@edit');
    $router->put('/admin/update/{id}', 'Blog\Controllers\BlogController@update');
    $router->delete('/admin/delete/{id}', 'Blog\Controllers\BlogController@destroy');
});
