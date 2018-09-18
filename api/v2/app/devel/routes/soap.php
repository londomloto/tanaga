<?php

Router::group(array(
    'prefix' => '/soap/confirm',
    'handler' => 'App\Soap\Controllers\ConfirmController',
    'middleware' => ''
))
->get('/service', 'service')
->post('/service', 'service');