<?php
/**
* 
*/
class Company_Controller extends Layout_Controller
{
	function index(){}
	function clist(){
		$company_list = Q('apartment');
		$company = array();
		foreach ($company_list as $key => $value) {
			$company[$value->apartment_no] = $value->apartment_name;
		}
		$this->layout->body = V('company/company_list',array('apartment'=>$company));
	}
	function alist($apartment){
		$apartment=urldecode($apartment);
		$apa = O('apartment',array('apartment_no'=>$apartment));
		if($apa->id) $apartname = $apa->apartment_name;
		$position_list = Q("position[position_apart={$apartment}]");
		$positions = array();
		foreach ($position_list as $key => $value) {
			$one['position_no'] = $value->position_no;
			$one['position_name'] = $value->position_name;
			$perms = explode(',',$value->position_perms);
			$o = array();
			foreach ($perms as $perms_no) {

				$o[] = Permission::get_permsname($perms_no);
			}
			$one['position_perms'] = implode(',',$o);
			unset($o);
			$positions[] = $one;
			$apartment = $value->position_apart;
		}
		$apart = Config::get('company.apart');
		$apart = strtr($apart, array('%apart'=>$apartname));
		$this->layout->body = V('company/apartment_list',array('apart'=>$apartname,'apartment'=>$positions,'title'=>$apart));
	}
}
class Company_AJAX_Controller extends _AJAX_Controller{
	function index_add_click(){
		$form = Input::form();
		if ($form['type']=='apart') {
			JS::dialog(V('company/add_apart'),array('title'=>T('增加部门')));
		}elseif($form['type']=='position'){
			JS::dialog(V('company/add_pos',array('apart'=>$form['apart'])),array('title'=>T('增加职位')));
		}
	}
		
	function index_delete_click(){
		$form = Input::form();
		if ($form['type'] =='apart') {
			$apart = O('apartment',array('apartment_no'=>$form['no']));
			if ($apart->id) {
				if (JS::confirm(strtr('确定删除%apart?',array('%apart'=>$apart->apartment_name)))) {
					$apart->delete();
					JS::refresh();
				}
			}
		}
		if ($form['type']=='position') {
			$position = O('position',array('position_no'=>$form['no']));
			if ($position->id) {
				if (JS::confirm(strtr('确定删除%apart?',array('%apart'=>$position->position_name)))) {
					$position->delete();
					JS::refresh();
				}
			}
		}
		
	}
	function index_edit_pos_submit(){
		$form = Input::form();
		$newpos = O('position',array('position_no'=>$form['oldno']));
		$newpos->position_no = $form['newposno'];
		$newpos->position_name = $form['newposname'];
		$newpos->save();
		JS::refresh();
	}
	function index_edit_submit(){
		$form = Input::form();
		$newapart = O('apartment',array('apartment_no'=>$form['oldno']));
		$newapart->apartment_no = $form['newapartno'];
		$newapart->apartment_name = $form['newapartname'];
		$newapart->save();
		JS::refresh();
	}
	function index_edit_click(){
		$form = Input::form();
		if ($form['type']=='apart') {
			JS::dialog(V('company/add_apart',array('no'=>$form['no'],'name'=>$form['name'])),array('title'=>T('编辑部门')));
		}elseif($form['type']=='position'){
			JS::dialog(V('company/add_pos',array('apart'=>$form['apart'],'no'=>$form['no'],'name'=>$form['name'])),array('title'=>T('编辑职位')));
		}
	}
	function index_add_pos_submit(){
		$form = Input::form();
		$newpos = O('position',array('position_no'=>$form['newposno']));
		if ($newpos->id) {
			JS::alert('职位编号已经存在');
			return;
		}
		unset($newpos);
		$newpos = O('position',array('position_name'=>$form['newposname']));
		if ($newpos->id) {
			JS::alert('职位名称已经存在');
			return;
		}
		unset($newpos);
		$apart = O('apartment',array('apartment_name'=>$form['apart']));
		$newpos = O('position');
		$newpos->position_no = $form['newposno'];
		$newpos->position_name = $form['newposname'];
		$newpos->position_apart = $apart->apartment_no;
		$newpos->save();
		JS::refresh();
	}
	function index_add_submit(){
		$form = Input::form();
		$newapart = O('apartment',array('apartment_no'=>$form['newapartno']));
		if ($newapart->id) {
			JS::alert('部门编号已经存在');
			return;
		}
		unset($newapart);
		$newapart = O('apartment',array('apartment_name'=>$form['newapartname']));
		if ($newapart->id) {
			JS::alert('部门名称已经存在');
			return;
		}
		unset($newapart);
		$newapart = O('apartment');
		$newapart->apartment_no = $form['newapartno'];
		$newapart->apartment_name = $form['newapartname'];
		$newapart->save();
		JS::refresh();
	}
	function index_perms_click(){
		$form = Input::form();
		$position = O("position",array('position_no'=>$form['no']));
		$permissions = Q("permission");
		$pos_perms = explode(",", $position->position_perms);
		$permission_list= array();
		foreach ($permissions as $key => $value) {
			$permission_list[$value->id] = $value->perms_name;
		}
		JS::dialog(V('company/perms',array('pos_no'=>$form['no'],'pos_name'=>$position->position_name,'pos_perms'=>$pos_perms,'permission_list'=>$permission_list)),array('title'=>T('修改职位权限')));
	}
	function index_pos_perms_submit(){
		$form = Input::form();
		$new_perms = array();
		$pos = O('position',array('position_no'=>$form['pos_no']));
		foreach ($form['perms'] as $key => $value) {
			if ($value=='on') {
				$new_perms[] = $key;
			}
		}
		if ($pos->id) {
			$pos->position_perms = implode(",", $new_perms);
			$pos->save();
		}
		JS::refresh();
	}
}