<?php
/**
* 
*/
class File extends _File
{
	
	static function check_path($path,$mode=0755){
		$file = explode('/', $path);
		$filename = array_pop($file);
		
		$files = O('logs',array('log_name'=>$filename));
		if (!$files->id) {
			$log_apartment = Config::get('position.system','system');
			$positions = Position::apartment();
			foreach ($positions as $key => $value) {
				if (strpos($filename, $value)>0) {
					$log_apartment = $key;
				}
			}
			$new_log = O('logs');
			$new_log->log_name = $filename;
			$new_log->log_apartment = $log_apartment;
			$new_log->save();
		}
		return parent::check_path($path,$mode);
	}
}