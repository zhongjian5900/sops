<?php
	/**
	* 
	*/
	class Controller extends _Controller
	{
		
		function _before_call($method, &$params)
		{
			parent::_before_call($method,$params);
			if ( !$this->_is_accessible()) {
				URI::redirect('/');
			}
		}
		private function _is_accessible() {
			$me = L('ME');
			$path = Config::get('system.controller_path');
			$methods = Config::get('system.controller_path').'/'.Config::get('system.controller_method');
			while ($path) {
				$requires = Config::get('access.'.$path);
				if ($requires === TRUE) {
					return TRUE;
				}
				elseif ($requires === FALSE) {
					if (!Auth::logged_in()) {
						return FALSE;
					}elseif (Auth::logged_in()) {
						$perms = Config::get('permission.super');
						switch ($path) {
							case 'admin':
								if($me->user_type=='super') return TRUE;
									else return FALSE;
								break;
							
							case 'company':
								if($me->user_type=='super'||$me->user_type=='admin') return TRUE;
									else return FALSE;
								break;
						}
						if (isset($perms[$methods])&&!Permission::is_allowed($perms[$methods])) {
							return FALSE;
						}
						return TRUE;
					}
				}
			}
			return TRUE;
		}
	}