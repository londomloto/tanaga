<?php


namespace Micro\Validation;
use Micro\Validation\Validator\Encoding;

class ValidationProvider {

    protected $data_arr        = array();
    public    $result          = array();
    protected $method          = array();
    public    $error_message   = array();
    private   $DIExec          ;
    protected $data_not_validated    = array();
    public    $notify          = array();
    protected $value_prop      = array();
    protected static $set_value_init  = array();
    protected static $instance        = null;


    private static function __load__helpers () {
        return array(
            "Text"           => "Micro\Validation\Validator\Text" ,
            "Date"           => "Micro\Validation\Validator\Date" ,
            "Email"          => "Micro\Validation\Validator\Email" ,
            "Entities"       => "Micro\Validation\Validator\Entities" ,
            "File"           => "Micro\Validation\Validator\File" ,
            "IdentityNumber" => "Micro\Validation\Validator\IdentityNumber" ,
            "Ip"             => "Micro\Validation\Validator\Ip" ,
            "Number"         => "Micro\Validation\Validator\Number" ,
            "URL"            => "Micro\Validation\Validator\URL" , 
            "Password"       => "Micro\Validation\Validator\Password" , 
        );

    }

    /**
     * [Init description]
     * @param [type] $data_arr [description]
     */
    public function init ( $data_arr = null ) {
        if ( !is_null( $data_arr ) ) {
            if ( is_object( $data_arr ) ) {
                $data_arr = (array)$data_arr;
            }
            $this->data_arr = $data_arr;
        }

        if ( count($data_arr) == 0 ) {
            return array( "success" => false , "message" => "Date contains not found.(83)" );
        }

        return $this;

    }

    /**
     * [manual description]
     * @param  string $string [description]
     * @return [type]         [description]
     */
    public static function value ( $value = null ) {
        self::$set_value_init = $value;
        if ( self::$instance == null ) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * [__execClass description]
     * @return [type] [description]
     */
    public function __execClass ( $DI = "" ) {}


    /**
     * [__call description]
     * @param  string $method [description]
     * @param  string $args   [description]
     * @return [type]         [description]
     */
    public function __call( $method = '' , $args = '' ) {
        $prop        = isset( $args[0] ) ? $args[0] : null;

        // set if param 1 is an array
        if ( is_array( $prop ) && count($prop) > 0 ) {
            $props             = isset( $prop[0] ) ? $prop[0] : null;
            $param_two         = isset( $prop[1] ) ? $prop[1] : null;
            $param_three       = isset( $prop[2] ) ? $prop[2] : null;
            $param_four        = isset( $prop[3] ) ? $prop[3] : null;
            $param_five        = isset( $prop[4] ) ? $prop[4] : null;
            $this->value_prop  = isset( $this->data_arr[$props] ) ? $this->data_arr[$props] : null;
        } else {
            $props             = isset( $args[0] ) ? $args[0] : null;
            $param_two         = isset( $args[1] ) ? $args[1] : null;
            $param_three       = isset( $args[2] ) ? $args[2] : null;
            $param_four        = isset( $args[3] ) ? $args[3] : null;
            $param_five        = isset( $args[4] ) ? $args[4] : null;
            $this->value_prop  = isset( $this->data_arr[$props] ) ? $this->data_arr[$props] : null;
        }

        // set value property from manual
        if ( empty( $this->value_prop ) ) {
            $this->value_prop = self::$set_value_init;
        }

        /**
         * [$__load_helpers description] 
         * @var [type]
         */

        $__load_helpers = self::__load__helpers();
        if ( isset( $__load_helpers[$method] ) )
        {

            $ClassName = $__load_helpers[$method];
            $C = new $ClassName();

            if ( method_exists( $C , "_".$method ) )
            {
                $method = "_".$method;
                // if value is doesn't have contains , don't return

                if ( empty( $this->value_prop ) ) $this->value_prop = " ";
                $DIs = $C->$method( $this->value_prop , $props , $param_two , $param_three , $param_four , $param_five );
                $this->DIExec[$method] = $DIs;

                return $DIs;
            }


        }

    }

    /**
     * [collect_all description]
     * @return [type] [description]
     */
    public function result ()  {
        $callback_result_each = array();

        if ( count( $this->DIExec ) > 0 ) {
            // get error_message and result
            
            foreach ( $this->DIExec as $rule => $Cname )
            {      
                $this->result[$Cname->keyprop] = $Cname->result;
                $this->error_message[$Cname->keyprop] = $Cname->error_message;

                // check if more than 1 then just take error first position
                if ( count($Cname->error_message) > 0 )
                {   
                    $keyProps = ucfirst($Cname->keyprop);
                    $message  = ucfirst($Cname->error_message[0]);
                    $messages = "(" . $keyProps . ") " . $message;
                    $this->notify = (object)array( "success" => false , "message" => $messages );
                }


            }


            // get by each data sender
            $callback_result_each = array();
            foreach ( $this->data_arr as $keyData => $valData )
            {
                $result        = isset($this->result[$keyData]) ? $this->result[$keyData] : null;
                // check if result is empty and then set return to default post data
                if ( empty( $result ) ) {
                    
                    if ( !is_object( $valData ) ) {
                        $result = $valData;
                    } else {
                        $result = 0;
                    }

                    $this->data_not_validated[] = $keyData;
                }

                $error_message                  = isset($this->error_message[$keyData]) ? $this->error_message[$keyData] : array();
                $allResult["result"]            = $result;
                $allResult["error_message"]     = $error_message;
                $callback_result_each[$keyData] = (object)$allResult;

                if ( isset($this->{$keyData}) ) {
                    $this->{"prop_".$keyData} = $result; // set result as an object
                } else {
                    $this->{$keyData} = $result; // set result as an object
                }

            }

            $this->result = $callback_result_each;

        }

        return $this;
    }

    /**
     * [hasError description]
     * @return boolean [description]
     */
    public function hasError () {
        // set not directed
        $this->result();

        // init message
        $message_tot_error = 0;
        if ( count($this->result) > 0 )
        {   
            foreach ( $this->result as $key => $val )
            {
                $count_message = $val->error_message;
                if ( is_array($count_message) && count( $count_message ) ) {
                    $message_tot_error += 1;
                }

            }

        }

        if ( $message_tot_error > 0 ) {
            return $this;
        } else {
            return false;
        }

    }

    /**
     * @return [type] [description]
     * [__get description]
     */
    public function __get( $var = "" ) {}





}



?>
