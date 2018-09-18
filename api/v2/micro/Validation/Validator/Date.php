<?php


namespace Micro\Validation\Validator;
use Micro\Validation\Settings\Setvar;

class Date {

    protected $prop;
    public    $result;
    protected $delimiter;
    public    $date;
    public    $error_message = array();
    public    $keyprop;
    protected $formatdate;


    /**
     * [__construct description]
     * @param [type] $prop        [description]
     * @param [type] $key_prop    [description]
     * @param [type] $param_two   [description]
     * @param [type] $param_three [description]
     */
    public function __construct ( $prop = null , $key_prop = null , $param_two = null , $param_three = null  ) {
        $_Email = $this->_Date( $prop , $key_prop , $param_two , $param_three );
        return $_Email;
    }

    /**
     * [Age description]
     * @param string $prop [description]
     */
    public function _Date( $prop = null , $key_prop = null , $param_two = null , $param_three = null , $param_four = null ,$param_five = null ) {
        $this->prop    = $prop;
        $this->keyprop = $key_prop;

        if ( !empty( $param_two ) ) {
            $this->delimiter( $param_two );
        }

        if ( !empty( $param_three ) ) {
            $this->format( $param_three );
        }


        return $this;
    }

    /**
     * [range description]
     * @param  string $date [description]
     * @return [type]       [description]
     */
    public function range () {
        $this->result = $this->prop;
        return $this;
    }

    /**
     * [delimiter description]
     * @param  string $delimiter [description]
     * @return [type]            [description]
     */
    public function delimiter ( $delimiter = "" ) {
        $this->delimiter = $delimiter;
        return $this;
    }

    /**
     * [format description]
     * @return [type] [description]
     */
    public function format ( $formatdate = "" ) {
        $this->formatdate = $formatdate;
        return $this;
    }

    /**
     * [valid_date description]
     * @return [type] [description]
     */
    public function validate ( $objectvalidator = null ) {
        $callback = 0;

        if ( !empty($objectvalidator) && method_exists( $this , $objectvalidator.'_validator' ) ) {
            $this->{$objectvalidator.'_validator'}();
        } else {
            //echo $format;
            if ( !empty($this->formatdate) && !empty($this->prop)  ) {
                if ( strpos( $this->formatdate , "-" ) !== false && strpos( $this->prop , $this->delimiter ) !== false  ) {
                    // explode format and date
                    $format_exp = explode( "-" , $this->formatdate);
                    $date_exp   = explode( $this->delimiter , $this->prop);

                    if ( count( $format_exp ) == 3 &&  count( $date_exp ) == 3  ) {
                        $arr_date     = array();
                        $arr_date_age = array();


                        // collect YMD position
                        if ( $format_exp[0] == "Y" ) $arr_date[0] = $date_exp[0]; if ( $format_exp[1] == "Y" ) $arr_date[1] = $date_exp[1]; if ( $format_exp[2] == "Y" ) $arr_date[2] = $date_exp[2];
                        if ( $format_exp[0] == "m" ) $arr_date[0] = $date_exp[0]; if ( $format_exp[1] == "m" ) $arr_date[1] = $date_exp[1]; if ( $format_exp[2] == "m" ) $arr_date[2] = $date_exp[2];
                        if ( $format_exp[0] == "d" ) $arr_date[0] = $date_exp[0]; if ( $format_exp[1] == "d" ) $arr_date[1] = $date_exp[1]; if ( $format_exp[2] == "d" ) $arr_date[2] = $date_exp[2];

                        // collect YMD position
                        if ( $format_exp[0] == "Y" ) $arr_date_age["Y"] = $date_exp[0]; if ( $format_exp[1] == "Y" ) $arr_date_age["Y"] = $date_exp[1]; if ( $format_exp[2] == "Y" ) $arr_date_age["Y"] = $date_exp[2];
                        if ( $format_exp[0] == "m" ) $arr_date_age["m"] = $date_exp[0]; if ( $format_exp[1] == "m" ) $arr_date_age["m"] = $date_exp[1]; if ( $format_exp[2] == "m" ) $arr_date_age["m"] = $date_exp[2];
                        if ( $format_exp[0] == "d" ) $arr_date_age["d"] = $date_exp[0]; if ( $format_exp[1] == "d" ) $arr_date_age["d"] = $date_exp[1]; if ( $format_exp[2] == "d" ) $arr_date_age["d"] = $date_exp[2];

                        if ( isset( $arr_date[0] ) && isset( $arr_date[1] ) && isset( $arr_date[2] ) ) {
                            $_date_one   = isset( $arr_date[0] ) ? $arr_date[0] : null;
                            $_date_two   = isset( $arr_date[1] ) ? $arr_date[1] : null;
                            $_date_three = isset( $arr_date[2] ) ? $arr_date[2] : null;
                        }

                        if ( isset( $arr_date_age["Y"] ) && isset( $arr_date_age["m"] ) && isset( $arr_date_age["d"] ) ) {
                            $_age_one   = isset( $arr_date_age["Y"] ) ? $arr_date_age["Y"] : null;
                            $_age_two   = isset( $arr_date_age["m"] ) ? $arr_date_age["m"] : null;
                            $_age_three = isset( $arr_date_age["d"] ) ? $arr_date_age["d"] : null;

                            $this->date = $_age_one . "-" . $_age_two . "-" . $_age_three; //  set date
                        }

                        $checkDate = checkdate( $_date_one , $_date_two , $_date_three );
                        if ( $checkDate == true ) {
                            $this->result = $this->prop;
                        } else {
                            $this->error_message[] = Setvar::date_not_valid();
                        }


                    } else {
                        $this->error_message[] = Setvar::date_delimiter_notsame();
                    }
                    //checkdate(month,day,year);
                } else {
                    $this->error_message[] = Setvar::date_delimiter_notfound();
                }

            }
        }

        return $this;
    }

    /**
     * [mandatory description]
     * @return [type] [description]
     */
    public function mandatory ( $status = false ) {
        if ( empty( $this->prop ) && $status == true ) {
            $error_message = Setvar::date_is_empty();
            $this->error_message[]  = $error_message;
        }

        return $this;
    }


    /**
     * [birthday description]
     * @return [type] [description]
     */
    public function age () {
        if ( !empty( $this->date ) ) {
            $from = new \DateTime( $this->date );
            $to   = new \DateTime('today');
            $result =  $from->diff($to)->y;
            $this->result = $result;
        }
        return $this;
    }

    /**
     * [fetch description]
     * @return [type] [description]
     */
    public function fetch () {
        return $this->result;
    }



}


?>
