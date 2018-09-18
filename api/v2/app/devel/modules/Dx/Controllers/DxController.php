<?php
namespace App\Dx\Controllers;

class DxController extends \Micro\Controller {
    
    public function findAction() {
        
    }

    /*public function profilesAction() {
        $data = array();

        foreach($this->dx->profiles() as $profile) {
            $data[] = array(
                'id' => $profile->profile_id,
                'name' => $profile->profile_name
            );
        }

        return array(
            'success' => TRUE,
            'data' => $data
        );
    }

    public function mappingAction($name) {
        
    }*/

    public function uploadAction($name) {
        if (isset($_POST['submit'])) {

            $profile = $this->dx->profile($name);

            $profile->initialize(array(
                'worksheet' => 0,
                'progress' => TRUE,
                'ignore_blank' => FALSE
            ));

            $profile->on('validatecol', function($e){
                
            });

            $profile->on('mapping', function($e){
                
            });

            $profile->on('validaterow', function($e){
                
            });

            $profile->on('executerow', function($e){
                
            });

            $profile->on('beforeinsertrow', function($e){
                
            });

            $profile->on('afterinsertrow', function($e){

            });

            $profile->on('beforeupdaterow', function($e){

            });

            $profile->on('afterupdaterow', function($e){
                
            });

            echo "<pre>";
            
            $profile->upload();
            
            echo json_encode($profile->result(), JSON_PRETTY_PRINT);
            echo "</pre>";

            /*if ( ! $profile->upload()) {
                foreach($profile->getMessages() as $message) {
                    var_dump($message);
               }
            } else {
                $result = $profile->result();
                print_r($result);
            }*/

        } else {
            echo '<form enctype="multipart/form-data" action="/api/v2/demo/dx/upload/example" method="post"><input type="file" name="userfile"><button name="submit">UPLOAD</button></form>';    
        }
    }

    public function downloadAction() {

    }

}