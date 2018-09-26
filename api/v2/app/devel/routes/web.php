<?php

Router::get('/', function(){
    return array(
        'success' => TRUE
    );
});

Router::get('/test/info', function(){
    return array(
        'success' => TRUE
    );
});

Router::get('/test/excel', function(){
    return array(
        'success' => TRUE
    );
});

Router::get('/test/ldap', function(){
    return array(
        'success' => TRUE
    );
});

Router::get('/test/mail', function(){
    $mailer = App::getDefault()->mailer;
    $sent = $mailer->send(array(
        'from' => array('Admin Tanaga' => 'tanagadevel@gmail.com'),
        'to' => 'roso.sasongko@gmail.com',
        'subject' => 'Terima kasih',
        'body' => 'Hello apa kabar'
    ));
    var_dump($sent);
    // return array(
    //     'success' => TRUE
    // );
});

Router::post('/test/upload', function(){
    return array(
        'success' => TRUE
    );
});

Router::get('/test/markdown', function(){
    $text = '**Roso Sasongko** change status to **Doing** on task: "Bxxx"';
    echo \Micro\Helpers\Markdown::html($text);
    $html = '<p><strong>Roso Sasongko</strong> change status to <strong>Doing</strong> on task: "Bxxx"</p>';
    echo \Micro\Helpers\Markdown::text($html);

});

Router::get('/test/dx', function(){
    $dx = App::getDefault()->dx;

    $file = PUBPATH.'resources/attachments/dbf74c68b8ecc8326deb70bc121d9eef.xlsx';

    $prof = $dx->profile('ProjectUpload');
    
    $prof->on('beforeinsert', function($e){
        $e->preventDefault();
    });

    $prof->read($file);
    $prof->save();

    print_r($prof->result());
});

Router::get('/test/build', function(){

    $base = APPPATH;
    $name = basename($base);
    $path = realpath($base.'/../../../../'.$name.'/src/modules/');

    $list = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);

    foreach($list as $item) {
        if ($item->isFile()) {
            $name = $item->getFilename();
            if (substr($name, -5) == '.html') {
                $href = str_replace($path, 'modules', $item->getPath());
                $href = str_replace('\\', '/', $href);
                $href = $href.'/'.$name;

                echo '<link rel="import" href="'.$href.'">'."\n";
            }    
        }
    }

});