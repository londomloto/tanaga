<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {       

    define('APPPATH', dirname(__DIR__).'/');
    define('PUBPATH', dirname(__DIR__).'/public/');
    define('SYSPATH', dirname(dirname(APPPATH)).'/micro/');

    require_once SYSPATH . 'App.php';

    $name = basename(
        substr(
            $_SERVER['SCRIPT_NAME'], 
            0, 
            strpos(
                $_SERVER['SCRIPT_NAME'], 
                '/public/'.basename($_SERVER['SCRIPT_FILENAME'])
            )
        )
    );

    $app = new \Micro\App($name);
    $app->run();

} catch(\Phalcon\Exception $e) {

    header('Content-Type: application/json');

    $response = array(
        'success' => FALSE,
        'status' => $e->getCode(),
        'message' => $e->getMessage()
    );

    print(json_encode($response, JSON_PRETTY_PRINT));


} catch(PDOException $e) {

    header('Content-Type: application/json');

    $response = array(
        'success' => FALSE,
        'status' => $e->getCode(),
        'message' => $e->getMessage()
    );
    
    print(json_encode($response, JSON_PRETTY_PRINT));
}