<?php

switch($_SERVER['SERVER_ADMIN']) {
    case 'roso@go.vm':
    case 'roso@localhost':
        $databases = array(
            'db' => array(
                'host' => 'localhost',
                'user' => 'root',
                'pass' => 'secret',
                'name' => 'tanaga',
            ),
        );
        break;
    default:
        $databases = array(
            'db' => array(
                'host' => 'localhost',
                'user' => 'root',
                'pass' => '12345',
                'name' => 'pu5d1k4dm123',
            ),
        );
}

return $databases;