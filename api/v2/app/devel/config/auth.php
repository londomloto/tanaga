<?php

return array(

    'providers' => array(
        'user' => 'App\Users\Models\User',
        'role' => 'App\Roles\Models\Role'
    ),

    'captcha' => array(
        'enabled' => TRUE,
        'database' => 'db',
        'table' => 'sys_captcha'
    )

);