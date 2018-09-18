<?php

Router::group(array(
    'prefix' => '/bpmn/diagrams',
    'handler' => 'App\Bpmn\Controllers\DiagramsController',
    'middleware' => 'auth'
))
->get('/{id}/expand', 'expand')
->post('/store', 'store')
->post('/{id}/upload', 'upload')
->post('/source', 'source');

Router::post('/bpmn/diagrams/{id}/export', 'App\Bpmn\Controllers\DiagramsController@export');
Router::post('/bpmn/diagrams/import', 'App\Bpmn\Controllers\DiagramsController@import');

Router::group(array(
    'prefix' => '/bpmn/generator',
    'handler' => 'App\Bpmn\Controllers\GeneratorController',
    // 'middleware' => 'auth'
))
->get('/generate/{worker}', 'generate');

Router::group(array(
    'prefix' => '/bpmn/forms',
    'handler' => 'App\Bpmn\Controllers\FormsController',
    'middleware' => 'auth'
))
->post('/upload/{id}', 'upload')
->get('/download/{filename}','download')
->get('/view/{filename}','view');

Router::group(array(
    'prefix' => '/bpmn',
    'handler' => 'App\Bpmn\Controllers\BpmnController',
    'middleware' => 'auth'
))
->get('/workers', 'workers')
->get('/activities/{worker}', 'activities')
->get('/statuses/{worker}', 'statuses')
->post('/start/{worker}', 'start')
->post('/stop/{worker}', 'stop')
->post('/prev/{worker}', 'prev')
->post('/next/{worker}', 'next');

