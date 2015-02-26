<?php
/**
* 登出
*/
class Logout_Controller extends Layout_Controller
{
	
	function index(){
		
		$user = L('ME');
		Auth::logout();
		Log::add(strtr('[application] %user_name[%user_id]登出系统成功', array(
									'%user_name' => $user->user_name,
									'%user_id' => $user->id,						
						)), 'logon');
		URI::redirect('/');
		
	}
}