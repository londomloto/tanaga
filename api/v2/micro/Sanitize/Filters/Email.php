<?php  

namespace Micro\Sanitize\Filters;
use Micro\Helpers\Encoding;


class Email {


	/**
	 * [filters description]
	 * @return [type] [description]
	 */
	public function filter ( $txt = '' ) {
		return filter_var($txt, FILTER_SANITIZE_EMAIL);
	}




}



?>