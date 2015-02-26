<?php

abstract class Layout_Controller extends _Layout_Controller {
		
	function _before_call($method, &$params) {

		$_SESSION['heartbeat_token'] = Auth::token();
		parent::_before_call($method, $params);

		$user = O('user',array('user_type'=>'super'));
		if (!$user->id) {
			$super = O('user');
			$super->user_name="超级管理员";
			$super->user_type="super";
			$super->token = "amdin";
			$super->save();
			$auth = new Auth('admin');
			$auth->create('83719730');
		}
		$this->add_js('dialog autocomplete');
		$this->add_css('title');
		$this->add_css('btn');
		$this->add_css('tab');
		$this->add_css('dialog autocomplete');
		$this->layout->sidebar = V('sidebar');
		$this->layout->searchbar = V('searchbar');
	}

	function _after_call($method, &$params) {
		parent::_after_call($method, $params);
	}
		
}
