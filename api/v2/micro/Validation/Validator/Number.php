<?php


namespace Micro\Validation\Validator;
use Micro\Validation\Settings\Setvar;

class Number {

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
    public  function __construct ( $prop = null , $key_prop = null , $param_two = null , $param_three = null  ) {
        $_Number = $this->_Number( $prop , $key_prop , $param_two , $param_three );
        return $_Number;
    }

    
    /**
     * [Number description]
     * @param string $prop        [description]
     * @param string $key_prop    [description]
     * @param string $param_trhee [description]
     */
    public function _Number (  $prop = null , $key_prop = null , $param_two = null , $param_three = null , $param_four = null , $param_five = null  ) {
        $this->prop = $prop;
        $this->keyprop = $key_prop;
        return $this;
    }


    /**
     * [mandatory description]
     * @return [type] [description]
     */
    public function mandatory ( $status = false ) {
        if ( empty( $this->prop ) && $status == true ) {
            $error_message = Setvar::number_is_empty();
            $this->error_message[]  = $error_message;
        }

        return $this;
    }

    /**
     * [validate description]
     * @return [type] [description]
     */
    public function validate ( $objectvalidator = null ) {
        if ( !empty($objectvalidator) && method_exists( $this , $objectvalidator.'_validator' ) ) {
            $this->{$objectvalidator.'_validator'}();
        } else {
            if ( !is_numeric( $this->prop ) ) {
                $error_message = Setvar::number_is_notnumeric();
                $this->error_message[]  = $error_message;
            } else {
                $this->result = $this->prop;
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
     * [phonenumber description]
     * @return [type] [description]
     */
    public function phonenumber_validator () {
        if ( !preg_match( Setvar::preg_phonenumber() , trim($this->prop))) {
            $message = Setvar::invalid_phone_number();
            $this->error_message[] = $message;
        } else {
            $this->result = $this->prop;
        }

        return $this;
    }

    /**
     * [fetch description]
     * @return [type] [description]
     */
    public function fetch() {
        return $this->result;
    }




}


?>
