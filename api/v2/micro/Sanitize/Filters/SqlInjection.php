<?php  


namespace Micro\Sanitize\Filters;


class SqlInjection {


	/**
	 * [filter description]
	 * @param  string $txt [description]
	 * @return [type]      [description]
	 */
	public function filter ( $input = "" ) {

		$input = $this->__replace_char_injection($input);

		/**
		 * '
		 * "
		 * /
		 * or
		 * and 
		 */

		$__checkType = $this->filter_Char2Num_id( $input );
		// jika bukan integer
		if ( $__checkType == 0 ) {
			$input = $this->filter_SQL_InjectionMatch( $input );
		}

		// jika integer
		if ( $__checkType != 0 ) {
			return $input ;
		}	

		return $input;
	}


	/**
	 * [is_html description]
	 * @param  string  $html [description]
	 * @return boolean       [description]
	 */
	public function is_html ( $html = '' ) {
	  return preg_match("/<\/?\w+((\s+\w+(\s*=\s*(?:\".*?\"|'.*?'|[^'\">\s]+))?)+\s*|\s*)\/?>/",$html) != 0;
	}


	/**
	 * [__replace_char_injection description]
	 * @return [type] [description]
	 */
	public function __replace_char_injection ($char = '') {
		$char_arr = array(
			"OR 1=1"  	 , 
			'" or ""="'  , 
			"' or ''=' " ,
			"or=''" ,
			'or="" ' ,
			'or=' ,
			"or =''",
			'or =' ,
			'and=' ,
			'and =' ,
			"' and ''=' ",
			'" and ""="' , 
			'" OR ""="'  , 
			"' OR ''=' " ,
			"' AND ''=' ",
			'" AND ""="' , 
			"DROP TABLE" , 
			"drop table" , 
			"user()" , 
			"system_user()" , 
			"/**/" , 
			"/*" , 
			"*/" , 
			"*/" , 
			"information_schema.schemata" , 
			"LOAD_FILE" , 
			"load_file" , 
			//"--" ,
			"UNION ALL" ,
			"union all" ,  
			"union" ,
			"UNION" ,  
			"#" ,
			";"
		);

		$replace = str_replace( $char_arr , "" ,  $char );

		return $replace;
	}


	/**
	 * [filter_SQL_InjectionMatch description]
	 * @param  string $id [description]
	 * @return [type]     [description]
	 */
	public function filter_SQL_InjectionMatch ( $id = "" ) {
		$id = html_entity_decode($id, ENT_NOQUOTES, 'UTF-8');
		

		$sql_injection_command = 0;
		$code_injection = 0;

		if ( $this->is_html( $id ) ) {
			//$id = $this->filter_SQL_Escaping($id);
			$id = htmlentities($id);
		}

		// send back url
		if ( strpos( $id , "../" ) !== false ) {
			$sql_injection_command += 1;
			$code_injection = 1001;
		} 


		if ( !preg_match( "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i" , $id ) ) {
			$preg_sql_injection = preg_match( '/(\s*([\0\b\'\"\n\r\t\%\_\\\\]*\s*(((select\s*.+\s*from\s*.+)|(insert\s*.+\s*into\s*.+)|(update\s*.+\s*set\s*.+)|(delete\s*.+\s*from\s*.+)|(drop\s*.+)|(truncate\s*.+)|(alter\s*.+)|(exec\s*.+)|(\s*(all|any|not|and|between|in|like|or|some|contains|containsall|containskey)\s*.+[\=\>\<=\!\~]+.+)|(let\s+.+[\=]\s*.*)|(begin\s*.*\s*end)|(\s*(\-\-)\s*.*\s+)|(\s*(contains|containsall|containskey)\s+.*)))(\s*[\;]\s*)*)+)/im' , $id. " " );
			
			if ( preg_match( '(\/?[^\/\.\n]+?\/(?P<DOUBLE>\.\.\/))' , $id) ) {
				$sql_injection_command += 1;
				$code_injection = 1102;
			}	

			if ( $preg_sql_injection === 1 ) {
				$sql_injection_command += 1;
				$code_injection = 1002;
			} 	
		}

		if ( preg_match( '/(?:etc\/\W*passwd)/im' , $id) ) {
			$sql_injection_command += 1;
			$code_injection = 1003;
		}

		if ( preg_match( '/(?:(\%SYSTEMROOT\%))/im' , $id)) {
			$sql_injection_command += 1;
			$code_injection = 1004;
		}

		/**
		if(preg_match('/\s/', $id)) {
			throw new \Exception("No Whitespaces", 500);
		}

		if(preg_match('/[\'"]/', $id)) { // code 1001
			$sql_injection_command += 1;
			$code_injection = 1003;
			//throw new \Exception("No Quotes", 500);
		} 

		if(preg_match('/[\/\\\\]/', $id)) { // code 1002
			// no slash 
			//throw new \Exception("No Slashes", 500);
		}
		
		if(preg_match('/(null)/i', $id)) { // code 1003
			$sql_injection_command += 1;
			$code_injection = 1003;
			//throw new \Exception("no sqli boolean keywords", 500);
		} 
		
		if(preg_match('/(union|select)/i', $id)) { // code 1004
			$sql_injection_command += 1;
			$code_injection = 1004;
			//throw new \Exception("no sqli select keywords", 500);
		}
		
		if(preg_match('/(group|order|having|limit)/i', $id)) { // code 1005
			$sql_injection_command += 1;
			$code_injection = 1005;
			//throw new \Exception("no sqli select keywords", 500);
		}
		
		if(preg_match('/(into|case)/i', $id)) { // code 1006
			$sql_injection_command += 1;
			$code_injection = 1006;
			//throw new \Exception("no sqli operators", 500);
		}
		
		// |#
		if(preg_match('/(--|\/\*)/', $id)) { // code 1007
			$sql_injection_command += 1;
			$code_injection = 1007;
			//throw new \Exception("no sqli comments", 500);
		}
		
		/**
		if(preg_match('/(=)/', $id)) { // code 1008
			$sql_injection_command += 1;
			$code_injection = 1008;
			//throw new \Exception("no boolean operators", 500);
		}**/





		if ( $sql_injection_command > 0 ) {
			throw new \Exception("Permission Denied : " . $code_injection, 500);
		}

		return $id;
	}

	/**
	 * [filter_SQL_Escaping description]
	 * @param  [type] $input [description]
	 * @return [type]        [description]
	 */
	public function filter_SQL_Escaping($input = ""){
		$input = html_entity_decode($input, ENT_NOQUOTES, 'UTF-8');

		$replace = array(
			'\\' => '\\\\',
			"\x00" => "\\x00",
			"\x1a" => "\\x1a",
			"\n" => "\\n",
			"\r" => "\\r",
			"'" => "\'",
			'"' => '\"'
		);
		
		$output = strtr($input, $replace);
		return $output;
	}

	/*
	Escaping for SQL attack:->
		Escaping each element if not alphanumeric.
	*/	
	public function filter_SQL_CompleteEscape($input){
		$input = html_entity_decode($input, ENT_NOQUOTES, 'UTF-8');
		
		$arr = str_split($input);
		$output1 = '';
		foreach($arr as $i)
		{
			if (!ctype_alnum($i))
                $output1 .='\\'.$i;
            else
                $output1 .= $i;
        }       
        return $output1;
	}


	//Not allowing character in variables (such as $id for cases such as SELECT name FROM users WHERE id = $id) 
	//causing unexpected outputs.
	public function filter_Char2Num_id($input){
		$input = html_entity_decode($input, ENT_NOQUOTES, 'UTF-8');
		if(!is_numeric($input)) {
			$input = 0;
		} else {
			$input = (int)$input;
		}

		return $input;
		
	}	



}




?>