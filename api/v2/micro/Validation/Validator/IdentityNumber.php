<?php


namespace Micro\Validation\Validator;
use Micro\Validation\Settings\Setvar;

class IdentityNumber {

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
        $_IdentityNumber = $this->_IdentityNumber( $prop , $key_prop , $param_two , $param_three );
        return $_IdentityNumber;
    }



    public function _IdentityNumber (  $prop = null , $key_prop = null , $param_two = null , $param_three = null , $param_four = null , $param_five = null  ) {
        $this->prop = $prop;
        return $this;
    }


}


?>
