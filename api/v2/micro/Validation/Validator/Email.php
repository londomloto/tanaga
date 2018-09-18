<?php

namespace Micro\Validation\Validator;
use Micro\Validation\Settings\Setvar;

class Email {

    public $error_messages = array();

    protected $prop   ;
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
        $_Email = $this->_Email( $prop , $key_prop , $param_two , $param_three );
        return $_Email;
    }



    /**
     * [Email description]
     * @param string $prop        [description]
     * @param string $key_prop    [description]
     * @param string $param_trhee [description]
     */
    public function _Email (  $prop = null , $key_prop = null , $param_two = null , $param_three = null , $param_four = null , $param_five = null ) {
        $this->prop    = $prop;
        $this->keyprop = $key_prop;
        return $this;
    }

    /**
     * [valid_email description]
     * @param  string $email [description]
     * @return [type]        [description]
     */
    public function validate (  $objectvalidator = null  ) {
        if ( !empty($objectvalidator) && method_exists( $this , $objectvalidator.'_validator' ) ) {
            $this->{$objectvalidator.'_validator'}();
        } else {
            if (!filter_var( $this->prop , Setvar::validate_email() )) {
                $message = "Invalid email format";
                $this->error_message[] = $message;
            } else {
                $this->result = filter_var( $this->prop , Setvar::sanitize_email()  );
            }
        }

        return $this;
    }

    /**
     * [minlength description]
     * @param  [type] $minlength [description]
     * @return [type]            [description]
     */
    public function minlen ( $minlength = null ) {
        if ( !empty( $this->prop ) && is_numeric( $minlength ) ) {
            $countLen = strlen( $this->prop );
            if ( $countLen < $minlength ) {
                $this->error_message[] = Setvar::minlength() . $minlength;
            }
        } else {
            $this->error_message[] = Setvar::setminlength();
        }

        return $this;
    }

    /**
     * [maxlength description]
     * @param  [type] $maxlength [description]
     * @return [type]            [description]
     */
    public function maxlen ( $maxlength = null ) {
        if ( !empty( $this->prop ) && is_numeric( $maxlength ) ) {
            $countLen = strlen( $this->prop );
            if ( $countLen > $maxlength ) {
                $this->error_message[] = Setvar::maxlength() . $maxlength;
            }
        } else {
            $this->error_message[] = Setvar::setmaxlength();
        }

        return $this;

    }




    /**
     * [mandatory description]
     * @return [type] [description]
     */
    public function mandatory ( $status = false ) {
        if ( empty( $this->prop ) && $status == true ) {
            $error_message = Setvar::mail_is_empty();
            $this->error_message[]  = $error_message;
        }

        return $this;
    }




}


?>
