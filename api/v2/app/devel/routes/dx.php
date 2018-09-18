<?php

Router::group(array(
    'prefix' => '/dx',
    'handler' => 'App\Dx\Controllers\DxController',
    //'middleware' => 'auth'
))
->post('/upload/{profile}', 'upload')
->get('/upload/{profile}', 'upload')
->post('/download', 'download');