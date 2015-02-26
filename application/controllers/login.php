<?php
/*
 *登录
 */
class Login_Controller extends Layout_Controller {

	function index(){
		if (Auth::logged_in()) {
			URI::redirect('/');
		}
		$form = Form::filter(Input::form());
		if($form['submit']){
			try
			{
				
				$form
					->validate('token','not_empty',I18N::T('application','帐号不能为空'))
					->validate('password','not_empty',I18N::T('application','密码不能为空'));
				if(!$form->no_error){
					throw new Error_Exception;
				}
				$token = trim($form['token']);
				$user = O('user',array('token'=>$token));
				$auth = new Auth($token);
				if(!$auth->verify($form['password'])){
					if ($user->id)
						$form->set_error('password',I18N::T('application','密码输入不正确'));
					else
						$form->set_error('token',I18N::T('application','用户名不存在'));
					throw new Error_Exception;
				}
				Auth::login($token);
				Log::add(strtr('[application] %user_name[%user_id]登录系统成功', array(
									'%user_name' => $user->user_name,
									'%user_id' => $user->id,						
						)), 'logon');
				URI::redirect('message/msglist');
				
			}
			catch (Error_Exception $e) {
					
			}
		}
		$this->layout->body = V('login');
		$this->layout->body->form = $form;
	}
		
}
class Login_AJAX_Controller extends AJAX_Controller
{
	function index_login_on_click(){
		$form = Input::form();
		Output::$AJAX['dialog'] = (string) V('signup',array('form'=>$form));
		return;
	}
}
