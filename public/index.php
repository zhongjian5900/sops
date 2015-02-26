<?php 

$dir = dirname(__FILE__).'/..';
if(function_exists('realpath') && @realpath($dir) !== FALSE){
	define('ROOT_PATH',  @realpath($dir).'/');
}else{
	define('ROOT_PATH', $dir.'/');
}
define('SAVE_PATH', '/data/SOP-Project/');
$phar_path = ROOT_PATH.'system.phar';
if (is_file($phar_path)) {
	define('SYS_PATH', 'phar://'.ROOT_PATH.'system.phar/');
}
else {
	define('SYS_PATH', ROOT_PATH.'system/');
}

$GLOBALS['SCRIPT_START_AT'] = microtime(TRUE);
require SYS_PATH.'core/bootstrap.php';
