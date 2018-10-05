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

Router::get('/test/hijri', function(){
    foreach(App\Ponpes\Models\Ponpes::find() as $item) {
        if (empty($item->thn_berdiri_hijr) && ! empty($item->thn_berdiri_masehi)) {
            $h = Micro\Helpers\Date::greg2hijri($item->thn_berdiri_masehi.'-01-01');
            $h = explode('-', $h);
            $item->thn_berdiri_hijr = $h[0];
            $item->save();
        }
    }
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

Router::get('/test/addr', function(){

    $items = \App\Ponpes\Models\Ponpes::find();
    foreach($items as $item) {
        if (empty($item->kelurahan) && ! empty($item->alamat)) {
            preg_match('#(.*)\s+(desa\.?)\s?(.*)#i', $item->alamat, $match);
            print_r($match);
            if ( ! empty($match)) {
                $match = array_pad($match, 5, NULL);
                $alamat = trim($match[1].(!empty($match[4]) ? ' '.$match[4] : ''));
                $kelurahan = trim($match[2].' '.$match[3]);
                $item->alamat = $alamat;
                $item->kelurahan = $kelurahan;
                $item->save();    
            }
        }
    }

});