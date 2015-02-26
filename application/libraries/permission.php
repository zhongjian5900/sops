<?php
/**
* 
*/
abstract class Permission
{
	static function get_all_perms(){
		$perms_res = Q('permission');
		$perms = array();
		foreach ($perms_res as $key => $value) {
			$perms[$value->perms_func] = $value->name;
		}
		return $perms;
	}
	static function is_allowed($perms)
	{
		$user = L('ME');
		$user_perms = array();
		if ($user->user_type =='admin'||$user->user_type =='super') {
			$str = 'permission.'.$user->user_type;
			$perm_list = Config::get($str);
			if (in_array($perms, $perm_list)) {
				return TRUE;
			}
		}
		$pos = O('position',array('position_no'=>$user->user_position));
		$pos_perms = explode(",", $pos->position_perms);
		foreach ($pos_perms as $key => $value) {
			$one = O('permission',array('id' =>$value));
			if ($one->id) {
				$user_perms[] = $one->perms_name;
			}
		}
		if (in_array($perms, $user_perms)) {
			return true;
		}
		return false;
	}
	static function get_allowed($user_type=NULL){
		if ($user_type==NULL) {
			$user = L('ME');
			$user_type = $user->user_type;
		}
		$user_perms = array();
		if ($user_type=='user') {
			$user_pos = O('position',array('position_no'=>$user->user_position));
			if ($user_pos->id) {
				$pos_perms = explode(",", $user_pos->position_perms);
				foreach ($pos_perms as $key => $value) {
					$perms = O('permission',array('id'=>$value));
					if ($perms->id) {
						$user_perms[$perms->perms_func] = $perms->perms_name;
					}
				}
			}else $user_perms['files/nfsapart'] = '上传记录文件';
		}else{
			$perms = 'permission.'.$user_type;
			$user_perms = Config::get($perms);
		}
		return $user_perms;
	}
	static function get_permsname($permno){
		$perms = O('permission',array('id'=>$permno));
		if ($perms->id) {
			return $perms->perms_name;
		}
		return ;
	}
}