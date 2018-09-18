<?php  

namespace Micro\Sanitize\Filters;
use Micro\Helpers\Encoding;


class Utf8 {


	/**
	 * [filters description]
	 * @return [type] [description]
	 */
	public function filter ( $txt = '' ) {
		if ( empty( $txt ) ) {
			return $txt;
		} else {
			return Encoding::removeBOM(Encoding::fixUTF8($txt));
		}
	}




}



?>