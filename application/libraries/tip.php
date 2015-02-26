<?php
/**
* 
*/
abstract class Tip
{	
	static function addMessage($content,$position){
		$peopleid = self::getRelations($position);
		if ($peopleid) {
			foreach ($peopleid as $userid) {
				$newmessage = O('message');
				$newmessage->content = $content;
				$newmessage->user_id = $userid;
				$newmessage->save();
			}
		}
		return TRUE;
	}
	static function getRelations($position){
		if (count($position)>0) {
			$peopleid = array();
			foreach ($position as $value) {
				$people = Q("user[user_position=$value]");
				foreach ($people as $val) {
					$peopleid[] = $val->id;
				}
			}
			return $peopleid;
		}
		return FALSE;
	}
}