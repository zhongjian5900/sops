<?php
/**
* 个人资料
*/
class Personal_Controller extends Layout_Controller
{
	
	function index()
	{
	}
	function pos(){
		$apartment_list = Q("apartment");
		$new_pos = array();
		foreach ($apartment_list as $key => $value) {
			$new_pos[$value->apartment_no] = $value->apartment_name;
		}
		$form = Form::filter(Input::form());
		$this->layout->body = V('pos/change',array('position'=>$new_pos));
	}
	function password(){
		$form = Form::filter(Input::form());
		$user = L('ME');
		if ($form['modifypwd']) {
			try{

				$oldpwd = $form['oldpwd'];
				$newpwd = $form['newpwd'];
				$confirm = $form['confirm'];
				$auth = new Auth($user->token);
				if (!$auth->verify($oldpwd)) {
					$form->set_error('password',I18N::T('application','密码输入错误'));
				}elseif (strlen($newpwd)<6) {
					$form->set_error('password',I18N::T('application','密码长度至少大于等于6'));
				}elseif ($newpwd!==$confirm) {
					$form->set_error('password',I18N::T('application','两次密码输入不一致'));
				}
				if (!$form->no_error) {
					throw new Error_Exception;
				}
				if($auth->change_password($newpwd)){
					echo '修改成功';
				}
			}catch(Error_Exception $e){

			}
		}
		$this->layout->body=V('application:password');
	}
	function signup(){
		$apartment_list = Q("apartment");
		$new_pos = array();
		foreach ($apartment_list as $key => $value) {
			$new_pos[$value->apartment_no] = $value->apartment_name;
		}
		$this->layout->body =V('signup',array('position' => $new_pos));
	}
}
class Personal_AJAX_Controller extends AJAX_Controller
{
	function index_apartment_position_change(){
		$form = Input::form(); 
  		$transaction_type = $form['transaction_type']; 
  		$pos_list = Q("position[position_apart=$transaction_type]");
  		$position = array();
  		foreach ($pos_list as $key => $value) {
  			$position[$value->position_no] = $value->position_name;
  		}
  		$view_path = 'application:position_dropdown';
  		if ($view_path) {
  			Output::$AJAX['view_data'] = (string) V($view_path, array('type'=> $form['sub_transaction_type'],'position'=>$position));
  		}
	}
	function index_apartment_list_change()
	{
		$form = Input::form(); 
  		$transaction_type = $form['transaction_type']; 
  		$pos_list = Q("user[user_apartment=$transaction_type]");
  		$position = Position::positions();
  		$list = array();
  		foreach ($pos_list as $key => $value) {
  			$one['user_name'] = $value->user_name;
  			$one['user_position'] = $position[$value->user_apartment][$value->user_position];
  			$one['id'] = $value->id;
  			$list[] = $one;
  		}
  		$view_path = 'application:pos/apart_list';
  		if ($view_path) {
  			Output::$AJAX['view_data'] = (string) V($view_path, array('list'=> $list));
  		}
	}
	function index_user_change_submit(){
		$form = Input::form();
		$u = O('user',array('id'=>$form['id']));
		if($u->id){
			$u->user_position = $form['position']?$form['position']:'';
			$u->user_apartment = $form['apart']?$form['apart']:'';
			$u->save();
		}
		JS::refresh();
	}
	function index_pos_click()
	{
		$form = Input::form();
		$u = O('user',array('id'=>$form['id']));
		$user = array();
		$position = Position::positions();
		if ($u->id) {
			$user['id'] = $u->id;
			$user['user_name'] = $u->user_name;
			$user['user_position'] = $position[$u->user_apartment][$u->user_position];
		}
		$apartment_list = Q("apartment");
		$new_pos = array();
		foreach ($apartment_list as $key => $value) {
			$new_pos[$value->apartment_no] = $value->apartment_name;
		}
		JS::dialog(V('application:pos/employer',array('user'=>$user,'position'=>$new_pos)),array('title'=>'员工职位变更'));
	}
	function index_signup_submit(){
		$form = Form::filter(Input::form());
		foreach ($form as $key=>$value) {
			switch ($key) {
				case 'token':
				if (!$form['token']) {
					JS::alert(I18N::T('application','帐号不能为空'));
					return;
				}elseif (trim($form['token'])) {
					$ref_user = O('user', array('token' => $form['token']));
					if ($ref_user->token == $form['token']) {
						JS::alert(I18N::T('application','用户名已经存在'));
						return;
					}
				}
				break;
				case 'apartment':
				if ($form['apartment']&&!$form['position']) {
					JS::alert(I18N::T('application','请选择职位'));
					return;
				}
				break;
			}
		}
		$password = Config::get('system.password','83719730');
		$user = O('user');
		$user->token = $form['token'];
		$user->user_position = $form['position']?$form['position']:'';
		$user->user_name = $form['name'];
		$user->user_type = 'user';
		$user->user_apartment = $form['apartment']?$form['apartment']:'';
		$user->save();
		$auth = new Auth($form['token']);
		$auth->create($password);
		JS::alert('添加成功');
		JS::refresh();
	}
	
}