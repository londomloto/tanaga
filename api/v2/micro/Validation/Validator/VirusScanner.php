<?php  


namespace Micro\Validation\Validator;



class VirusScanner {

	protected $_scanner = '';
	protected $_resultScanner = array();

	/**
	 * [__construct description]
	 * @param [type] $prop        [description]
	 * @param [type] $key_prop    [description]
	 * @param [type] $param_two   [description]
	 * @param [type] $param_three [description]
	 * @param [type] $param_four  [description]
	 * @param [type] $param_five  [description]
	 */
	public function __construct ( $prop = null , $key_prop = null , $param_two = null , $param_three = null , $param_four = null , $param_five = null ) {
		$this->filepath = $prop;
		$this->_VirusScanner( $prop , $key_prop , $param_two , $param_three );
		return $this;
	}

	/**
	 * [VirusScanner description]
	 * @param [type] $prop        [description]
	 * @param [type] $key_prop    [description]
	 * @param [type] $param_two   [description]
	 * @param [type] $param_three [description]
	 * @param [type] $param_four  [description]
	 * @param [type] $param_five  [description]
	 */
	public function _VirusScanner ( $prop = null , $key_prop = null , $param_two = null , $param_three = null , $param_four = null , $param_five = null   ) {
		$dirVirusScanner  = "../../../vendor/phpmussel/";
		$fileVirusScanner = $dirVirusScanner."loader.php";

		if ( is_dir( $dirVirusScanner ) ) {
			require_once( $fileVirusScanner );
			$this->_scanner = @$phpMussel;
		}
	}



	/**
	 * [scan description]
	 * @param  string $fileLocation [description]
	 * @return [type]               [description]
	 */
	public function scan () {

		// scan file
		if ( isset( $this->_scanner ) ) 
		{
			// check if array 
			if ( is_array( $this->filepath ) ) {
				foreach ( $this->filepath as $key_path => $val_path ) {
					$scanner = $this->_scanner["Scan"]($val_path , true , false);
					$scanner = self::__getViruses( $scanner , $val_path );
					$this->_resultScanner[] = $scanner;
				}				
			} else {
				$scanner = $this->_scanner["Scan"]($this->filepath , true , false);
				$scanner = self::__getViruses( $scanner , $this->filepath );
				$this->_resultScanner[] = $scanner;
			}
		}

		return $this->_resultScanner;

	}

	/**
	 * [parseInfo description]
	 * @return [type] [description]
	 */
	public static function __getViruses ( $text = null , $filepath = "" ) {

		$callback = array();
		// check if empty text
		if ( !empty($text) ) {
			$text .= '\n';
			$text = explode( "\n" , $text );

			// position and parse
			for ( $x = 0 ; $x < count($text)-1 ; $x++ ) {
				//echo $x . " = " . $text[$x] . "\n";
				if ( $x == 2 ) {
					$_position_information = trim(str_replace( array("->" , ".") , "" ,  $text[2]));

					if ( $_position_information == "No problems found" ) {
						
						$callback = array(
							"filethreat"   => 0,
							"filepath"     => $filepath,
							"filestatus"   => "uploaded" , 
							"message"	   => ""
						);

					} else {
							
						// file path
						$status = "uploaded";
						if ( @unlink( $filepath ) ) {
							$status = "deleted";
						}
						
						$callback = array(
							"filethreat"   => 1,
							"filepath" 	   => $filepath,
							"filestatus"   => $status,
							"message"	   => $_position_information
						);

						//"Detected potentially dangerous file tampering"

					}
				}
			} 

		}

		return $callback;

	}




}






?>