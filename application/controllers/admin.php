<?php
	/**
	* 管理员管理
	*/
	class Admin_Controller extends Layout_Controller
	{
		function index(){}
		function adminlist(){
			try{
				$user = L('ME');
					if (!Permission::is_allowed('管理员信息')) {
						throw new Error_Exception;
						
					}
				$positions = Position::positions();
				$admin = Q("user[user_type=admin]");
				$adminlist = array();
				$apartment = Q("apartment");
				$apart = array();
				foreach ($apartment as $key => $value) {
					$apart[$value->apartment_no] = $value->apartment_name;
				}
				foreach ($admin as $key => $value) {
					$id = $value->id;
					$one['id'] = $id;
					$one['user_name'] = $value->user_name;
					$one['user_apartment'] = $apart[$value->user_apartment];
					$one['user_position'] = $positions[$value->user_apartment][$value->user_position];
					$adminlist[] = $one;
				}
				$this->layout->body=V('application:admin',array('adminlist'=>$adminlist));
			}catch(Error_Exception $e){
				$this->layout->body=V('errors/401');
			}
		}
		function addadmin()
		{
			$this->layout->body= V('application:addadmin');
		}
		function checklist(){
			$form = Form::filter(Input::form());
			$positions = Position::positions();
			$users = Q("user[user_apartment=".$form['apartment']."][user_type=user]");
			$userlist = array();
			foreach ($users as $value) {
				$one['id'] = $value->id;
				$one['user_name'] = $value->user_name;
				$one['user_position'] = $positions[$value->user_apartment][$value->user_position];
				$userlist[] = $one;
				unset($one);
			}
			$this->layout->body= V('application:addadmin',array('selectapart'=>$form['apartment'],'userlist'=>$userlist));
		}
		function added(){
			$form = Form::filter(Input::form());
			foreach ($form as $key => $value) {
				if (strpos($key, 'userid')!='-1') {
					$o = O('user',array('id'=>$value));
					if ($o->id) {
						$o->user_type = 'admin';
						$o->save();
						unset($o);
					}
				}
			}
			URI::redirect('admin/adminlist');
		}
		function deleteadmin($userid){
			$users = O('user',array('id'=>$userid));
			if ($users->id) {
				$users->user_type = 'user';
				$users->save();
			}
			URI::redirect('admin/adminlist');
		}
	}