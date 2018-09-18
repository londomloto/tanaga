<?php  
namespace Micro\Helpers;

// base singleton
class Activitylog extends \Micro\Component {


	//public static $sual_activity_id;
	
	public static $sual_activity_action;
	public static $sual_activity_message;
	public static $sual_activity_ip;
	public static $sual_activity_userid   = 'SYSTEM';
	public static $sual_activity_email    = 'SYSTEM';
	public static $sual_activity_username = 'SYSTEM';
	public static $sual_activity_created;
	public static $sual_activity_clientbrowser;
	public static $sual_activity_data;

	public static $request;
	public static $auth;
	public static $instance;



	/**
	 * [instance description]
	 * @return [type] [description]
	 */
	protected static function instance () {
		self::$instance = new self(); // define for objector
		return self::$instance;
	}

	/**
	 * [log description]
	 * @return [type] [description]
	 */
	public static function log () {

		self::$auth 				   = self::instance()->auth->user(); // auth data

		// collect all information base column name
		self::$sual_activity_ip		       = self::instance()->request->getServerAddress() ;
		self::$sual_activity_clientbrowser = self::instance()->request->getUserAgent();

		$dataJsonReceived 				   = self::instance()->request->getJson();
		unset($dataJsonReceived["password"]);
		self::$sual_activity_data		   = json_encode($dataJsonReceived);

		if ( is_array( self::$auth ) && isset(self::$auth["su_id"]) ) {

			$user_id = self::$auth["su_id"];
			if ( !empty( $user_id ) ) {
				self::$sual_activity_userid	   = $user_id ;
				self::$sual_activity_email	   = self::$auth["su_email"];
				self::$sual_activity_username  = self::$auth["su_fullname"] ;
				self::$sual_activity_created   = date('Y-m-d H:i:s') ;
			}

		}

		return self::instance();
	}

	/**
	 * [__getModel description]
	 * @return [type] [description]
	 * Load instance Model
	 */
	private function __record_activity () {
		$activitylog = new \App\Users\Models\UserActivitylog();
		return $activitylog;
	}

	/**
	 * [__getModel description]
	 * @return [type] [description]
	 * Load instance Model
	 */
	private function __record_users () {
		$User = new \App\Users\Models\User();
		return $User;
	}


    /**
     * [set description]
     * @param [type] $action  [description]
     * @param [type] $message [description]
     */
	public function save ( $action = null , $message = null ) {
		if ( !is_null($action) ) {

			//var_dump(self::$request->getServerAddress());
			//var_dump(self::$auth->user());
			self::$sual_activity_action	   = $action ;
			self::$sual_activity_message   = $message ;

			return $this->insertActivity();
		} 

	}

	/**
	 * [insertActivity description]
	 * @return [type] [description]
	 */
	public function insertActivity () {
		$record = $this->__record_activity();
		$record->sual_activity_ip 			  = self::$sual_activity_ip;
		$record->sual_activity_userid   	  = self::$sual_activity_userid;
		$record->sual_activity_email		  = self::$sual_activity_email;
		$record->sual_activity_username 	  = self::$sual_activity_username;
		$record->sual_activity_created  	  = self::$sual_activity_created;
		$record->sual_activity_action   	  = self::$sual_activity_action;
		$record->sual_activity_message  	  = self::$sual_activity_message;
		$record->sual_activity_clientbrowser  = self::$sual_activity_clientbrowser;
		$record->sual_activity_data 	 	  = self::$sual_activity_data;

		if ( $record->save() ) {
			return true;	
		}
	}


}



?>