<?php
/**
* 
*/
class Application
{
	
	static function setup()
	{
		Session::setup();
		ORM_Model::setup();
		Q::setup();

		date_default_timezone_set(Config::get('system.timezone') ?: (Config::get('system.timezone') ?: 'Asia/Shanghai'));
		
		if (Auth::logged_in()) {
			$token = Auth::token();
			$me = O('user', array('token'=>$token));
			Cache::L('ME', $me);
		}
	}

	static function shutdown(){
		
	}
}
