<?php


namespace Micro\Validation;
use Micro\Validation\Helper\Encoding;

use Micro\Validation\Helper\Age,
    Micro\Validation\Helper\Date,
    Micro\Validation\Helper\Email,
    Micro\Validation\Helper\Entities,
    Micro\Validation\Helper\File,
    Micro\Validation\Helper\IdentityNumber,
    Micro\Validation\Helper\Ip,
    Micro\Validation\Helper\Number,
    Micro\Validation\Helper\URL,
    Micro\Validation\Helper\Text;


class Validation extends Email {

    protected $data_arr        = array();
    protected $validation_type = array();
    protected $message         = array();
    public    $errors          = array();
    protected $mandatory       = array();
    protected $validation_entities;
    public    $result          = array();
    protected $method          = array();
    protected $DI              = array();

    /**
     * [Validation description]
     * @param [type] $arr [description]
     * Example : new Validation( array($PostData) );
     */
    public function __construct ( $data_arr = null  ) {

        if ( !is_null( $data_arr ) ) {
            if ( is_object( $data_arr ) ) {
                $data_arr = (array)$data_arr;
            }
            $this->Init( $data_arr );
        }


    }

    /**
     * [Init description]
     * @param [type] $data_arr [description]
     */
    public function Init ( $data_arr = null ) {
        if ( is_array( $data_arr ) ) {
            $this->data_arr = $data_arr;
        }
    }

    /**
     * [Initialize description]
     * @param [type] $Init [description]
     * Example : $ObjValidation->set_init( array("postdata" => array( "type" => "typeData" , "message" => "Hello world!" )) );
     */
    public function set_init ( $Init = null ) {
        $message = array();
        $type    = array();
        if ( !is_null( $Init ) && is_array( $Init ) && count( $Init ) > 0 ) {

            // set Initialize
            foreach ( $Init as $key => $val )
            {

                if ( isset($val["type"]) )         $this->validation_type[$key]   = $val["type"];
                if ( isset($val["message"]) )      $this->message[$key]           = $val["message"];
                if ( isset($val["mandatory"]) )    $this->mandatory[$key]         = $val["mandatory"];
                if ( isset($val["method"]) )       $this->method[$key]            = $val["method"];

            }
        }
    }

    /**
     * [set_validation description]
     * @param string $validation_type [description]
     * Example : $ObjValidation->set_validation( "key_array" , "type_data" );
     * By Pass By Array : $ObjValidation->set_validation( array( "key_property" => "type_data" ) );
     */
    public function set_validation ( $prop = "" , $type = "" , $mandatory=false , $method = null  ) {
        if ( is_array( $prop ) ) {
            $this->validation_type = $prop;
        } else {
            if ( !empty($prop) && !empty($type) ) {
                $this->validation_type[$prop] = $type;
                $this->mandatory[$prop]       = $mandatory;

                if ( !empty($method) && is_array($method) ) {
                    $this->method[$prop] = $method;
                }
            }
        }

        return $this;
    }

    /**
     * [set_message description]
     * @param [type] $prop [description]
     * Example : $ObjValidation->set_message( "key_array" , "value_message" );
     * By Pass By Array : $ObjValidation->set_message( array( "key_property" => "message" ) );
     */
    public function set_message( $prop = null , $message = '' ) {
        if ( is_array( $prop ) ) {
            $this->message = $prop;
        } else {
            if ( !empty($prop) && !empty($message) ) {
                $this->message[$prop] = $message;
            }
        }

        return $this;

    }

    /**
     * [set_method description]
     * @param [type] $prop       [description]
     * @param string $arr_method [description]
     */
    public function set_method ( $prop = null , $arr_method = "" ) {
        if ( !empty( $prop ) && is_array( $arr_method ) ) {
            if ( isset( $arr_method["method"] ) && isset( $arr_method ) ) $this->method[$prop] = $arr_method;
        }

        return $this;
    }

    /**
     * [fetch_ready description]
     * @return [type] [description]
     */
    public function fetch_ready () {
        if ( is_array($this->data_arr) && count($this->data_arr) > 0 ) {

            // check if fetch is ready
            foreach ( $this->data_arr as $key_data => $val_data )
            {

                // set result
                $val_result                 = $this->__value( $key_data );
                $this->result[$key_data]    = $val_result;
                $this->{$key_data}          = $val_result;

                // set mandatory and message
                $mandatory = isset( $this->mandatory[$key_data] ) ? $this->mandatory[$key_data] : null;

                // push to error array
                if ( empty( $val_result ) && $mandatory == true )
                {
                    $message = isset( $this->message[$key_data] ) ? $this->message[$key_data] : null;
                    $this->errors[$key_data] = $message;
                }

            }

            if ( count($this->result) > 0 ) {
                return true;
            }

        } else {
            return false;
        }
    }

    /**
     * [filter_default description]
     * @param  [type] $prop [description]
     * @return [type]       [description]
     */
    public function _get_data ( $prop = null ) {
        $data       = array( "value" => "" , "type" => "" , "message" => "" );
        if ( !is_null($prop) ) {
            if ( isset( $this->data_arr[$prop] ) ) {

                // get all data by pulling data
                $value      = isset( $this->data_arr[$prop] )         ? $this->data_arr[$prop] : null;
                $type       = isset( $this->validation_type[$prop] )  ? $this->validation_type[$prop] : null;
                $message    = isset( $this->message[$prop] )          ? $this->message[$prop] : null;

                // collect
                $data['value']       = $value;
                $data['type']        = $type;
                $data['message']     = $message;
            }
        }

        return (object)$data;
    }

    /**
     * [set_value description]
     * @return [type] [description]
     */
    public function __value( $prop = null , $additional = null ) {
        $data = $this->_get_data( $prop );
        if ( !empty($data->type) )
        {
            // get method
            $_get_method = isset( $this->method[$prop] ) ? $this->method[$prop] : null ; // get method
            $additional = '';
            if ( !empty($_get_method) && is_array( $_get_method ) ) {
                $valid      = $_get_method["method"];
                $additional = $_get_method["param"];
            } else {
                $valid = 'valid_' . $data->type;
            }

            // check if method in validation helper is exists
            if ( method_exists( $this , $valid ) )
            {
                // set result for debug
                $result = $this->{$valid}( $data->value , $additional );

                if ( is_object( $result ) ) {
                    $this->message[$prop] = $result->error;
                    return;
                } else {
                    return $result;
                }

            } else {
                return $this->data_arr[$prop];
            }

        } else {
            return $this->data_arr[$prop];
        }

    }

    /**
     * [get_value description]
     * @return [type] [description]
     */
    public function get_value( $prop = null , $method = null , $additional = null , $additional_two = null ) {
        if ( isset($this->{$prop}) )
        {
            $prop = $this->{$prop};
            // set method and return value
            if ( !empty($method) ) {

                // check method if exists execute it!
                if ( method_exists( $this , $method ) )
                {
                    $result = $this->{$method}( $prop , $additional , $additional_two );

                    // if result is object it's mean get error message
                    if ( is_object( $result ) ) {
                        $this->message[$prop] = $result->error;
                    } else {
                        return $result;
                    }

                } else {
                    return $prop;
                }

            }
        }
    }


    /**
     * [get_array_value description]
     * @return [type] [description]
     */
    public function get_array_value ( $prop = null ) {

    }


    /**
     * [debug description]
     * @return [type] [description]
     */
    public function debug () {
        print_r((object)$this->result);
    }




}



?>
