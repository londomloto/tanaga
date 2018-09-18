<?php


namespace Micro\Validation\Validator;
use Micro\Validation\Settings\Setvar;

class Password {

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
        $_Password = $this->_Password( $prop , $key_prop , $param_two , $param_three );
        return $_Password;
    }

    
    /**
     * [Number description]
     * @param string $prop        [description]
     * @param string $key_prop    [description]
     * @param string $param_trhee [description]
     */
    public function _Password (  $prop = null , $key_prop = null , $param_two = null , $param_three = null , $param_four = null , $param_five = null  ) {
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
            $this->default_validator();
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
    public function default_validator () {
        $password = $this->prop;

        // check if length is more than or equals 8
        if (strlen($this->prop) <= '8') {
            $this->error_message[] = "Your Password Must Contain At Least 8 Characters!";
        }
            
        // check number
        if(!preg_match("#[0-9]+#",$password)) {
            $this->error_message[] = "Your Password Must Contain At Least 1 Number!";
        }
            
        // check capital 
        if(!preg_match("#[A-Z]+#",$password)) {
            $this->error_message[] = "Your Password Must Contain At Least 1 Capital Letter!";
        }
                
        // check symbol
        if ( !preg_match("/[\'^£$%&*()}{@#~?><>,|=_+!-]/", $password ) ) {
            $this->error_message[] = "Your Password Must Contain At Least 1 Symbol!";        
        }

        // check lowercase
        if(!preg_match("#[a-z]+#",$password)) {
            $this->error_message[] = "Your Password Must Contain At Least 1 Lowercase Letter!";
        } 

        /**
        else {
            $this->error_message[] = "Please Check You've Entered Or Confirmed Your Password!";
        }**/

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
