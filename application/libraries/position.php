<?php
/**
* 
*/
abstract class Position 
{
	
	static function positions()
	{
		$positions = array();
		$apartment = Q("apartment");
		$position = Q("position");
		foreach ($apartment as $key => $value) {
			$positions[$value->apartment_no] = array();
		}
		foreach ($position as $key => $value) {
			$positions[$value->position_apart][$value->position_no] = $value->position_name;
		}
		return $positions;
	}
	static function apartment(){
		$apartments = array();
		$apartment = Q("apartment");
		foreach ($apartment as $key => $value) {
			$apartments[$value->apartment_no] = $value->apartment_name;
		}
		return $apartments;
	}
}