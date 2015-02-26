<?php
/**
* 
*/
class Pagination
{
		static function pagi(& $objects, $start, $per_page, $url=NULL) {
		$start = $start - ($start % $per_page);

		if($start > 0) {
			$last = floor($objects->total_count() / $per_page) * $per_page;
			if ($last == $objects->total_count()) $last = max(0, $last - $per_page);
			if ($start > $last) {
				$start = $last;
			}
			$objects = $objects->limit($start, $per_page);
		} else {
			$objects = $objects->limit($per_page);
		}

		$pagination = Widget::factory('pagination');
		$pagination->set(array(
			'start' => $start,
			'per_page' => $per_page,
			'total' => $objects->total_count(),
			'url' => $url
		));
		
		return $pagination;
	}
}