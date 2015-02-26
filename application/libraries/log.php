<?php
/**
* 
*/
class Log extends _Log
{
	
	static function get_path($ident='common')
	{
		$tpl_path = Config::get('nfs.'.$ident.'_log_path');
		if (!$tpl_path) $tpl_path = Config::get('nfs.log_path' , ROOT_PATH.'logs/%ident.txt');
		return strtr($tpl_path, array('%ident'=>$ident));
	}
}