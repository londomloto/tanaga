<?php


namespace Micro\Validation\Validator;
use Micro\Validation\Settings\Setvar;
use Micro\Validation\Settings\Mime;
use Micro\Validation\Validator\VirusScanner as VirusScanner;

class File  {

    protected $prop;
    public    $result;
    public    $error_message = array();
    public    $keyprop;
    protected $is_file		 = 0;
    protected $counterror	 = 0;



    /**
     * [__construct description]
     * @param [type] $prop        [description]
     * @param [type] $key_prop    [description]
     * @param [type] $param_two   [description]
     * @param [type] $param_three [description]
     */
    public function  __construct ( $prop = null , $key_prop = null , $param_two = null , $param_three = null  ) {
        $_File = $this->_File( $prop , $key_prop , $param_two , $param_three );
        return $_File;
    }



    /**
     * [File description]
     * @param [type] $prop        [description]
     * @param [type] $key_prop    [description]
     * @param [type] $param_two   [description]
     * @param [type] $param_three [description]
     * @param [type] $param_four  [description]
     * @param [type] $param_five  [description]
     */
    public function _File(  $prop = null , $key_prop = null , $param_two = null , $param_three = null , $param_four = null , $param_five = null  ) {
        $this->prop    = $prop;

        // set key property 
        if ( !empty($param_two) ) {
        	$this->keyprop = $param_two;
        } else {
        	$this->keyprop = $key_prop;
        }

        // check if prop is files
        if ( is_object( $this->prop ) ) { // check jika file
        	if ( !method_exists( $this->prop , "getName" ) ) {
        		$this->error_message[] = "This is not file";
        	} else { 
        		$this->is_file = 1;
        	}
        } else {
        	$this->counterror += 1;
        	$this->error_message[] = "This is not file (31)";
        }


        return $this;

    }

    /**
     * [format description]
     * @param  [type] $mime [description]
     * @return [type]       [description]
     */
    public function format ( $mime = null ) {
    	if ( $mime != null && $this->is_file  ) {
    		$mime_is_exists = Mime::type($mime);
    		if ( $mime_is_exists == false && $this->prop->getType() != $mime ) {
	        	$this->counterror += 1;
    			$this->error_message[] = "File just allow type" . $mime;
    		}
    	}

    	return $this;

    }

    /**
     * [maxsize description]
     * @param  [type] $size [description]
     * @return [type]       [description]
     */
    public function maxsize ( $size = null ) {
    	if ( !is_numeric($size) ) {
	        $this->counterror += 1;
    		$this->error_message[] = "Size is not numeric";
    	} else {
    		if ( $this->is_file ) {
    			$file_size = $this->prop->getSize();
    			if ( $file_size > $size ) {
    				$this->counterror += 1;
    				$this->error_message[] = "File size is more than " . $size;
    			}
    		}
    	}

    	return $this;
    }

    /**
     * [validate description]
     * @return [type] [description]
     */
    public function validate () {
    	if ( $this->counterror > 0 || $this->is_file == false ) {
    		$this->error_message[] = "This is not file and too much error";
    	} else {
    		$this->result = $this->prop;
    	}
    	return $this;
    }




}


?>
