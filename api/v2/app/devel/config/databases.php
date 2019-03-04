<?php

switch($_SERVER['SERVER_ADMIN']) {
    case 'roso@go.vm':
    case 'roso@localhost':
    case 'roso@apache.local':
        $databases = array(
            'db' => array(
                'host' => '127.0.0.1',
                'user' => 'root',
                'pass' => 'secret',
                'name' => 'tanaga',
            ),
        );
        break;
    default:
        $databases = array(
            'db' => array(
                'host' => '51.255.208.66',
                'user' => 'root',
                'pass' => 'tanaga@12345#',
                'name' => 'tanaga',
            ),
        );
}

return $databases;