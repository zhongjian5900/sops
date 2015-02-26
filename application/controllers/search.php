<?php
	/**
	* 搜索文件
	*/
	class Search_Controller extends Layout_Controller
	{
		
		function index()
		{
			$form = Form::filter(Input::form());
			if ($form['submit']) {
				try
				{
					$form->validate('search','not_empty',I18N::T('application','请输入搜索条件'));
					if (!$form->no_error) {
						throw new Error_Exception;
					}
					$condition = $form['search'];
					$filelist = array();
					$apartment = array();
					if (preg_match("/[\x7f-\xff]/", $condition)) {	
						$positions = Config::get('position.apartment');
						foreach ($positions as $key => $value) {
							$apartment[] = $key;
						}
						if (in_array($condition,$apartment)){
							$position= array_flip($positions[$condition]);
						}else{
							foreach ($positions as $key => $value) {
								if (in_array($condition,$value)) {
									$array = array_flip($value);
									$position= $array[$condition];
								}
							}
						}
					}else{
						$position= $condition;
					}
					if (is_array($position)) {
						foreach ($position as $position_no) {
							$filelist[] = Q("files[file_type^=%".$position_no."]");
						}
					}else{
						$filelist[] = Q("files[file_type^=%".$position."]");
					}
					$filenames = array();
					if (is_array($filelist)) {
						foreach ($filelist as $key => $value) {
							foreach ($value as $k => $val) {
								if (!in_array($val->filename,$filenames)){
									$filenames[$i] = $val->filename;
								}
							}
						}
					}
					$this->layout->body->form = $form;
					echo V('application:searchfile', $filenames);
					return;
				}catch(Error_Exception $e){
				}
				$this->layout->body->form = $form;
				echo V('application:searchfile');
			}
		}
	}