<?php
namespace Micro\File;
use Micro\Validation\Validator\VirusScanner as VirusScanner;

class UploadProvider  {  

    protected $_types;
    protected $_path;
    protected $_encrypt;
    public    $notify_error;
    
    protected $_results = array();
    protected $_messages = array();

    protected $_result ;
    protected $_message;

    protected $_upload_method;

    protected $_options = array();
    protected $_viruses = array();
    protected $_filepath= array();
    protected $_error = 0;
    private   $__folder_quarantine = '/track/whoops/climb/';

    protected static $_mimes;

    public function __construct() {
        $this->_message = '';
        $this->_result = NULL;

        $this->reset();

        if (is_null(self::$_mimes)) {
            // load mimes
            self::$_mimes = require_once __DIR__.'/mimes.php';
        }
    }

    /**
     * [initialize description]
     * @param  array  $options [description]
     * @return [type]          [description]
     */
    public function initialize($options = array()) {
        foreach($options as $key => $val) {
            $this->{'_'.$key} = $val;
        }
        return $this;
    }

    /**
     * [reset description]
     * @return [type] [description]
     */
    public function reset() {
        $options = \Micro\App::getDefault()->config->uploader->toArray();
        $this->initialize($options);
    }

    /**
     * [getMessage description]
     * @return [type] [description]
     */
    public function getMessage() {
        if ( $this->_upload_method == 'single' ) return $this->_message;  
        return $this->_messages;
    }

    /**
     * [getResult description]
     * @return [type] [description]
     */
    public function getResult() {
        if ( $this->_upload_method == 'single' ) return $this->_result;  
        return $this->_results;
    }


    /**
     * [getError description]
     * @return [type] [description]
     */
    public function getError() {
        if ( $this->_error > 0 ) {
            throw new \Phalcon\Exception( $this->notify_error["message"] , 500 );
        } else {
            return false;
        }
    }


    /**
     * [__doUpload description]
     * @param  string $file [description]
     * @return [type]       [description]
     */
    private function __doUpload( $file = "" ) {

        if ( is_object( $file ) ) {
            $mime = $file->getType();
            $orig = $file->getName();
            $name = $orig;

            $valid = FALSE;

            // validate mime
            if (count($this->_types) > 0) {
                $maps = isset(self::$_mimes[$mime]) ? self::$_mimes[$mime] : FALSE;
                if ($maps) {
                    $maps = array_flip($maps);
                    foreach($this->_types as $t) {
                        if (isset($maps[$t])) {
                            $valid = TRUE;
                            break;
                        }
                    }
                }
                if ( ! $valid) {
                    $this->_message = 'Not allowed type' . $mime;
                    $this->_messages[] = 'Not allowed type' . $mime;
                    $this->_error += 1;
                }
            } else {
                $valid = TRUE;
            }

            if ($valid) {
                if ($this->_encrypt) {
                    $name = \Micro\App::getDefault()->security->getRandom()->uuid();
                    $name = str_replace('-', '', $name).'.'.$file->getExtension();
                }

                $path = str_replace('\\', '/', $this->_path.$name );

                //$path_quarantine = $this->__folder_quarantine.$name;
                //$path_quarantine = str_replace('\\', '/', $path_quarantine);

                // move file to quarantine 
                if (@$file->moveTo($path)) {
                    // collecting all result
                    
                    $collect_results = new \stdClass;
                    $collect_results->origname = $orig;
                    $collect_results->filename = $name;
                    $collect_results->filepath = $path;
                    $collect_results->filetype = $mime;
                    $collect_results->filesize = $file->getSize();

                    //print_r( $collect_result_arr );

                    $this->_results[] = $collect_results;
                    $this->_result    = $collect_results;

                    $this->reset();
                    return TRUE;
                } else {
                    $this->_message = 'Upload failed';
                    $this->_messages[] = 'Upload failed';
                    $this->_error += 1;

                }
            }
        } 



        $this->reset();
        return FALSE;
    }

    /**
     * [upload description]
     * @return [type] [description]
     */
    public function upload() {

        $this->_upload_method = 'single';

        $request = \Micro\App::getDefault()->request;

        // check request if has files
        if ($request->hasFiles()) {

            // get all file posted
            $file = $request->getFiles();

            // check file is exists
            $file = $file[0];
            $this->__doUpload( $file );
        } else {
            $this->_message = 'No file to upload';
            $this->_messages[] = 'No file to upload';
            $this->_error += 1;
        }


        //$this->__virusScanner();


        return $this;
    }

    /**
     * [multiupload description]
     * @return [type] [description]
     */
    public function multiupload() {

        $this->_upload_method = 'multi';

        $request = \Micro\App::getDefault()->request;

        // check request if has files
        if ($request->hasFiles()) {
            // get all file posted
            $files = $request->getFiles();
            if ( is_array( $files ) && count($files) > 0 ) {
                foreach ( $files as $key_position => $file ) {
                    $this->__doUpload( $file , $key_position );
                }
            }
        } else {
            $this->_message = 'No file to upload';
            $this->_messages[] = 'No file to upload';
            $this->_error += 1;
        }

        $this->__virusScanner();


        return $this;
    }


    /**
     * [_scan description]
     * @return [type] [description]
     * scan file viruses
     */
    protected function __virusScanner () {

        //print_r( $this->_results );
        if ( is_array( $this->_results ) && count( $this->_results ) ) {
            //$virusScanner = new VirusScanner( $this->_path );

            // check path data 
            foreach ( $this->_results as $key => $val ) {
                $this->_filepath[] = $val->filepath;
            }
        }


        if ( is_array( $this->_filepath ) && count( $this->_filepath ) ) {
            

            $scanner = new VirusScanner( $this->_filepath );
            $res_scanner = $scanner->scan();
            $this->_viruses = $res_scanner;
            
            // extend information
            if ( is_array($this->_viruses) && count( $this->_viruses ) ) {
                foreach ( $this->_viruses as $key_vir => $val_vir ) {
                    if ( isset( $this->_results[$key_vir] ) ) {

                        $message = $val_vir["message"];
                        if ( !empty( $message ) ) {
                            $this->notify_error  = array( "success" => false , "message" => $this->_viruses[$key_vir]["message"] );
                            $this->_messages[]   = $message;
                            $this->_error        += 1;
                            $this->_message      = $message;
                        }

                        $_result_position_file = (array)$this->_results[$key_vir];
                        $this->_results[$key_vir] = (object)array_merge( $_result_position_file , $val_vir );
                        
                    }
                }

                $this->__moveSafeFile();
            }

        } 

    }



    /**
     * [__moveSafeFile description]
     * @return [type] [description]
     */
    public function __moveSafeFile () {

        // moving file if was safe
        if ( is_array($this->_results) && count($this->_results) > 0 ) {
            foreach ( $this->_results as $key => $obj ) {
                if ( $obj->filethreat == 0 ) {
                    if ( file_exists( $obj->filepath ) ) {
                        if ( !rename( $obj->filepath , $this->_path.$obj->filename ) ) {
                            $this->messages[] = "File not moved..";
                        }
                    } else {
                        $this->messages[] = "File not found and not moved..";
                    }
                }
            }
        }
        

    }





    
}