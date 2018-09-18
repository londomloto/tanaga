<?php


namespace Micro\Validation\Validator;
use Micro\Validation\Settings\Setvar;


class URL {

    protected $prop ;
    public    $result;
    public    $error_message = array();
    public    $keyprop;


    /**
     * [__construct description]
     * @param [type] $prop        [description]
     * @param [type] $key_prop    [description]
     * @param [type] $param_two   [description]
     * @param [type] $param_three [description]
     */
    public function  __construct ( $prop = null , $key_prop = null , $param_two = null , $param_three = null  ) {
        $_URL = $this->_URL( $prop , $key_prop , $param_two , $param_three );
        return $_URL;
    }


    public function _URL ( $prop = null , $key_prop = null , $param_two = null , $param_three = null , $param_four = null , $param_five = null ) {
        $this->prop    = $prop;
        $this->keyprop = $key_prop;

        return $this;
    }

    /**
     * [valid_url description]
     * @param  string $url [description]
     * @return [type]      [description]
     */
    public function validate ( $objectvalidator = null ) {
        if ( !empty($objectvalidator) && method_exists( $this , $objectvalidator.'_validator' ) ) {
            $this->{$objectvalidator.'_validator'}();
        } else {
            if (!preg_match( Setvar::preg_url() , $this->prop )) {
                $message = Setvar::invalid_url();
                $this->error_message[] = $message;
            }
        }

        return $this;
    }

    /**
     * [require description]
     * @return [type] [description]
     */
    public function mandatory ( $status = false ) {
        if ( $status == true && empty( $this->prop ) ) {
            $this->error_message[] = Setvar::url_is_empty();
        }

        return $this;
    }



}


?>
