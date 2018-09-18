<?php  

namespace Micro\Sanitize;
use Micro\Sanitize\Filters\Email;
use Micro\Sanitize\Filters\Integer;
use Micro\Sanitize\Filters\Ip;
use Micro\Sanitize\Filters\StringSanitize;
use Micro\Sanitize\Filters\Url;
use Micro\Sanitize\Filters\Utf8;
use Micro\Sanitize\Filters\SqlInjection;
use Micro\Sanitize\Filters\NotBaseDir;

use Phalcon\Filter as Filter;


class SanitizeProvider extends Filter {


	/**
	 * [init_filters description]
	 * @return [type] [description]
	 */
	public function init_filters () {
		// add utf8 filter to default Phalcon
        $this->add( "Email"   ,  new Email() );
        $this->add( "Integer" ,  new Integer() );
        $this->add( "Ip" 	  ,  new Ip() );
        $this->add( "String"  ,  new StringSanitize() );
        $this->add( "Url" 	  ,  new Url() );
        $this->add( "Utf8" 	  ,  new Utf8() );
        $this->add( "SqlInjection" 	  ,  new SqlInjection() );
        $this->add( "NotBaseDir" 	  ,  new NotBaseDir() );
	}

	/**
	 * [sanitizeBatch description]
	 * @return [type] [description]
	 */
	public function sanitizeBatch ( $getJson = null ) {
		$this->init_filters(); // !important


		//print_r( $getJson );


		$returnSanitize = $this->array_clean_utf8( $getJson ); // mapping an array
		return $returnSanitize;
	}

	/**
	 * [array_recursive_utf8 description]
	 * @param  string $array [description]
	 * @return [type]        [description]
	 */
	public function array_recursive_utf8 ( $array = "" ) {

		$callback_arr = array();

		if ( !is_array( $array ) ) {
			return false;
		}

		foreach ( $array as $key => $value ) {
            
			if ( !is_array( $value ) ) {
				$callback_arr = $array;
			}

			if ( is_array( $value ) ) 
			{
				
				$array_structure_associative_arr = array(); // init restructure

				foreach ( $value as $key_in_key => $value_in_value ) 
				{
					
					//echo current($value);

					$value_in_value = $this->sanitize( $this->sanitize($value_in_value , "Utf8") ,"SqlInjection" );

					$array_structure_associative_arr[$key_in_key] = $value_in_value; // restructure

				}

				$callback_arr[$key] = $array_structure_associative_arr;	
			}

		}


		return $callback_arr;


	}

	/**
	 * [array_clean_utf8 description]
	 * @param  [type] $array [description]
	 * @param  [type] $map   [description]
	 * @return [type]        [description]
	 */
	public function array_clean_utf8 ( $data_arr = null , $map = null ) 
	{
		//$filter_input_sanitize = filter_var_array( $data_arr , $data_arr );

		if ( count( $data_arr ) > 0 && is_array($data_arr) ) {

			$array_structure = array();
			$array_structure_associative_key = array();


			//  get key and return back to value
			foreach ( $data_arr as $key => $value ) 
			{

			    if ( is_array( $value ) ) {
			    	$value = $this->array_recursive_utf8( $value ); 
			    }  else {
					$value = $this->sanitize( $this->sanitize($value , "Utf8") , "SqlInjection" );
			    }

			    $array_structure[$key] = $value;
			}	

		}


		return $array_structure;

	}
	

	/**
	 * [array_map description]
	 * @param  [type]  $input     [description]
	 * @param  [type]  $map       [description]
	 * @param  boolean $objectify [description]
	 * @return [type]             [description]
	 */
    public static function array_map($input, $map, $objectify = FALSE) {
        
    }











}




?>