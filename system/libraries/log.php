<?php

abstract class _Log {
	
	static function get_path($ident = 'common') {
		$tpl_path = Config::get('system.'.$ident.'_log_path');
		if (!$tpl_path) $tpl_path = Config::get('system.log_path' , ROOT_PATH.'logs/%ident.log');
		return strtr($tpl_path, array('%ident'=>$ident));
	}
	
	static function add($message, $ident='common'){
		//export nothing
		$log_path = Log::get_path($ident);

		@File::check_path($log_path);
		$time = date('Y/m/d H:i:s');
		$host = $_SERVER['REMOTE_ADDR'];
		if (!$host) $host = $_SERVER['REMOTE_HOST'];
		if (!$host) $host = 'LOCALHOST';

		$mode = PHP_SAPI;
		$uri = $_SERVER['REQUEST_URI'];
        $pid = posix_getpid();
		
        @file_put_contents($log_path, "$time $mode $host$uri [$pid] \t{$message}\n", FILE_APPEND);
	}
	
}
