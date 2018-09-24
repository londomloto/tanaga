<?php

Router::group(array(
    'prefix' => '/kanban',
    'handler' => 'App\Kanban\Controllers\KanbanController',
    'middleware' => 'auth'
))
->get('/grid', 'grid');

Router::group(array(
    'prefix' => '/projects',
    'handler' => 'App\Projects\Controllers\ProjectsController',
    'middleware' => 'auth'
))
->get('/load/{name}', 'load');

Router::group(array(
    'prefix' => '/users/users-panels',
    'handler' => 'App\Users\Controllers\UsersPanelsController',
    'middleware' => 'auth'
))
->post('/save', 'save');

Router::group(array(
    'prefix' => '/roles/roles-panels',
    'handler' => 'App\Roles\Controllers\RolesPanelsController',
    'middleware' => 'auth'
))
->post('/save', 'save');

Router::group(array(
    'prefix' => '/tasks/tasks-activities',
    'handler' => 'App\Tasks\Controllers\TasksActivitiesController',
    'middleware' => 'auth'
))
->post('/upload', 'upload');

Router::group(array(
    'prefix' => '/ponpes',
    'handler' => 'App\Ponpes\Controllers\PonpesController',
    'middleware' => 'auth'
))
->post('/{id}/upload', 'upload');

Router::group(array(
    'prefix' => '/ponpes/asset-gambar',
    'handler' => 'App\Ponpes\Controllers\AssetGambarController',
    'middleware' => 'auth'
))
->post('/upload', 'upload');