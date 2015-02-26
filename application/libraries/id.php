<?php 
/**
* 
*/
class ID
{
	
	static function createID()
	{
		$ids = Q('id');
		foreach ($ids as $key => $value) {
		 	if ($value->id) {
		 		$id_feed = $value->id_feed;
		 	}
		}
		if (!isset($id_feed)) {
		 	$id_feed = Config::get('system.default_id_feed');
		}
		$new_id = $id_feed+1;
		$new_feed = O('id');
		$new_feed->id_feed = $new_id;
		$new_feed->save();
		return $new_id; 
	}
}