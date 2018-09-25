<?php
/**
 * frontend routes config
 */

return array(
    'fallback' => '/home',
    'routes' => array(
        '/login' => array(
            'layout' =>  'auth',
            'module' =>  'auth',
            'page' =>  'login-page',
            'authentication' =>  false
        ),
        '/forgot' => array(
            'layout' => 'auth',
            'module' => 'auth',
            'page' => 'forgot-page',
            'authentication' => false
        ),
        '/register' => array(
            'layout' => 'auth',
            'module' => 'auth',
            'page' => 'register-page',
            'authentication' => false
        ),
        '/recover' => array(
            'layout' => 'auth',
            'module' => 'auth',
            'page' => 'recover-page',
            'authentication' => false
        ),
        '/invitation' => array(
            'layout' =>  'page',
            'module' =>  'invitation',
            'page' =>  'invitation-page',
            'authentication' =>  false
        ),
        '/projects/:project' => array(
            'module' =>  'projects',
            'page' =>  'project-page'
        ),
        '/settings/:setting/:params' => array(
            'module' =>  'settings',
            'page' =>  'settings-page'
        ),
        '/settings/:setting' => array(
            'module' =>  'settings',
            'page' =>  'settings-page'
        ),
        '/worksheet/:project/:action/:params' => array(
            'module' =>  'worksheet',
            'page' =>  'worksheet-page'
        ),
        '/worksheet/:project/:action' => array(
            'module' =>  'worksheet',
            'page' =>  'worksheet-page'
        ),
        '/worksheet/:project' => array(
            'module' =>  'worksheet',
            'page' =>  'worksheet-page'
        ),
        '/ponpes/:menu/:params' => array(
            'module' => 'ponpes',
            'page' => 'ponpes-page'
        ),
        '/ponpes/:menu' => array(
            'module' => 'ponpes',
            'page' => 'ponpes-page'
        ),
        '/masjid/:menu/:params' => array(
            'module' => 'masjid',
            'page' => 'masjid-page'
        ),
        '/masjid/:menu' => array(
            'module' => 'masjid',
            'page' => 'masjid-page'
        ),
    )
);