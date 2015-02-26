<?php
/**
* 
*/
class Files_Controller extends Layout_Controller
{
	
	function index()
	{
	}
	function build()
	{
		$user =L('ME');
		if (!Permission::is_allowed('文件管理',$user->user_type)) {
			return;
		}
		$form = Form::filter(Input::form());
		$filename = "文件表格-".date("y-m-d",time());
		header("Content-type:application/vnd.ms-excel;"); 
		header("Content-Disposition:attachment;filename=".$filename.".xls");
		echo "文件名称\t";
		echo "有效版本\t\n";
		foreach (Q("files") as $value) {
			$filename = $value->filename;
			$fileversion = $value->f_version.'版／'.$value->file_modify.'修改';
			echo "$filename\t";
			echo "$fileversion\t\n";
		}
		exit;
	}
	function flist(){
		try{
			$form = Form::filter(Input::form());
			$token = Auth::token();
			$user = O('user', array('token' =>$token));
			if (!$user->id) {
				$form->set_error('user',I18N::T('application','用户错误'));
				throw new Error_Exception;		
			}else{
				$position = $user->user_position;
				$filelist = Q("files[file_type^=%".$position."]:sort(filename ASC)");
				$pa=Pagination::pagi($filelist,$form['st'],4);
				if ($filelist) {
					$file = array();
					foreach ($filelist as $key => $value) {
						$one['fileno'] = $value->fileno;	
						$one['filename'] = $value->filename;
						$one['mtime'] = date('y-m-d h:i:s',$value->mtime);
						$one['filesize'] = $value->filesize;
						$file[]=$one;
					}
					$this->layout->body=V('filelist', array('file'=>$file,'pagination'=>$pa));
					$this->layout->body->form=$form;
					return;
				}
			}
		}catch(Error_Exception $e){

		}
		$this->layout->body = V('filelist',array('form'=>$form));
	}
	function upload(){
		$user = L('ME');
		$positions=Position::positions();
        $apartment = '';
        if ($positions) {
        	foreach ($positions as $apart => $position) {
         		if ($position[$user->user_position]) {
         			$apartment = $apart;
         		}
        	}
        }
        if($user->user_type == 'super'||$user->user_type == 'admin')
        	$apartment = 'admin';
		$form = Form::filter(Input::form());
			try{
				$file = Input::file('files');
				if($file){
					if ($file['name']) {
						$fileformat = File::extension($file['name']);				
							$formats = Config::get('file.format');
							if (!in_array($fileformat,$formats)) {
								$form->set_error('fileformat',I18N::T('application','上传失败，文件格式不正确'));
							}
							if ($file['size']>20971522) {
								$form->set_error('fileformat',I18N::T('application','上传失败，文件太大'));
							}
							if ($form['Oapartment']===0) {
								$form->set_error('Oapartment',I18N::T('application','请选择文件所属部门'));
							}
							error_log(json_encode($form['checkbox']));
							if (count($form['checkbox'])<=0) {
								$form->set_error('checkbox','请选择文件对应职位');
							}
							if (!$form->no_error) {
								throw new Error_Exception;
							}
							$file_name_new = strtr($file['name'],array(' '=>''));
							$arr = preg_split("/([a-zA-Z0-9-_:]+)/", $file_name_new, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
							$file_no = $arr[0];
							$file_path = SAVE_PATH.'upload/'.$file_name_new;
							$is_createID = false;
							if (count($arr)<=2){
								$new_file = O('files',array('filename'=>$file_name_new));
								$is_createID = true;
							}
							else
								$new_file = O('files',array('fileno'=>$file_no));
							if ($new_file->id) {
								$modify = $new_file->file_modify+1;
								$file_no = $new_file->fileno;
								$file_path = SAVE_PATH.'upload/'.$new_file->filename;
								if (file_exists($file_path)) {
									unlink($file_path);
								}
							}else
							{
								if ($is_createID) {
									$file_no = ID::createID();
								}
								$new_file = O('files');
								$modify = 0;
							}
							if ($form['modify']) {
								$modify = $form['modify'];
							}
							$file_version = Config::get('nfs.version','B');
							if(move_uploaded_file($file['tmp_name'],$file_path)){
								$new_file->fileno = $file_no;
								$new_file->filename = $file_name_new;
								$new_file->filesize = File::bytes($file['size']);
								$new_file->file_type ='';
								$new_file->file_modify = $modify;
								$new_file->f_version = $file_version;
								$new_file->file_apartment = $form['Oapartment']?:'XZ';
								$new_file->save();
								$pos = array();
								Tip::addMessage(strtr("[上传文件]%user_name上传了%filename",array('%user_name'=>$user->user_name,'%filename'=>$file['name'])),$pos);
								Log::add(strtr("[上传文件]%user_name上传了%filename",array('%user_name'=>$user->user_name,'%filename'=>$file['name'])),$apartment);
								$this->layout->body = V('application:upload/return', array('type'=>'allfiles'));
								return;
							}
						}
					}
				}
				catch(Error_Exception $e){
				}
			 $this->layout->body = V('application:upload/nfs', array('form'=>$form));
	}
	function download($fileno,$filetype='files'){
		$form = Form::filter(Input::form());

		$user = L('ME');
		$positions=Position::positions();
        $apartment = '';
        if ($positions) {
        	foreach ($positions as $apart => $position) {
           	 	if ($position[$user->user_position]) {
           	 		$apartment = $apart;
           	 	}
           	}
        }

		if($fileno){
			$file = O('files',array('fileno'=>$fileno));
			$filename = '';
			if ($file->id) {
				$filename = $file->filename;
			}
			if($filetype=='files')
			{
				$file_path = SAVE_PATH.'upload/'.$filename;
			}elseif ($filetype=='logs') {
				$file_path = ROOT_PATH.'logs/'.$filename;
			}
			if(is_file($file_path)) {
          		Header("Content-type: application/octet-stream");
				Header("Accept-Ranges: bytes");
				Header("Accept-Length: ".filesize($file_path));
				Header("Content-Disposition: attachment; filename=" . $filename);
				header('Pragma: no-cache');
        		header('Expires: 0');
           		readfile($file_path);		
          		Log::add(strtr("[下载文件]%user_name下载了%filename",array('%user_name'=>$user->user_name,'%filename'=>$filename)),$apartment);
        		exit;
        	}
		}
	}
	function edit($filename,$filetype='files'){
		if ($filename) {
			$filename = urldecode($filename);
			error_log($filename);
			$file_path = SAVE_PATH.'upload/'.$filename;
			if(is_file($file_path)) {
				error_log(222);
				$this->layout->body=V('application:edit',array('filename'=>$filename));
				return;
			}
		}
		if ($form['save']) {
			if ($form['filename']) {
				$file_path = SAVE_PATH.'upload/'.$form['oldname'];
				$file_new_path = SAVE_PATH.'upload/'.$form['filename'];
				if (is_file($file_path)) {
					if(rename($file_path, $file_new_path)){
						$files = O('files',array('filename'=>$form['oldname']));
						$pos = array();
						if ($files->id) {
							$files->filename = $form['filename'];
							$files->file_modify +=1;
							$files->save();
							$pos= explode(',', $files->file_type);
							Tip::addMessage(strtr("[修改文件]%user_name修改了%filename",array('%user_name'=>$user->user_name,'%filename'=>$form['files'])),$pos);
							Log::add(strtr("[修改文件]%user_name修改了%filename",array('%user_name'=>$user->user_name,'%filename'=>$form['files'])),$apartment);
							URI::redirect(URI::url($form['act']));
						}
					}
				}
			}
		}
	}
	function zipDownload(){

	}
	function delete(){
		if($form['delete']){
			$file_path = SAVE_PATH.'upload/'.$form['files'];
			if(is_file($file_path)) {
				if(unlink($file_path)){
					$files = O('files',array('filename'=>$form['files']));
					$pos = array();
					if ($files->id) {
						$pos = explode(',', $files->file_type);
						$files->delete();
					}
					Tip::addMessage(strtr("[删除文件]%user_name删除了%filename",array('%user_name'=>$user->user_name,'%filename'=>$form['files'])),$pos);
					Log::add(strtr("[删除文件]%user_name删除了%filename",array('%user_name'=>$user->user_name,'%filename'=>$form['files'])),$apartment);
					URI::redirect(URI::url($form['act']));
				}
			}
		}
	}
	function allfilelist(){
		$user = L('ME');
		$apartment = Q('apartment');
		$apart = array();
		$form = Form::filter(Input::form());
		foreach ($apartment as $key => $value) {
			$apart[$value->apartment_no] = $value->apartment_name;
		}
		$position = Q('position');
		$pos = array();
		foreach ($position as $key => $value) {
			$pos[$value->position_no] = $value->position_name;
		}
		if (Permission::is_allowed('文件管理',$user->user_type)) {
			$filelist = Q("files:sort(filename ASC)");
			$pa=Pagination::pagi($filelist,$form['st'],4);
			$files = array();			
			foreach ($filelist as $key => $value) {
				$one['fileno'] = $value->fileno;
				$one['filename'] = $value->filename;
				$one['file_version'] = $value->f_version.'版／'.$value->file_modify.'修改';
				$one['fileapart'] = $apart[$value->file_apartment];
				$fpos = explode(",", $value->file_type);
				$pos_name = array();
				if ($fpos) {
					foreach ($fpos as $k => $val) {
						$pos_name[] = $pos[$val];
					}
				}
				$one['filepos'] = implode(",", $pos_name);
				$one['mtime']=date('y-m-d h:i:s',$value->mtime);
				$one['filesize']= $value->filesize;
				$files[] = $one;

			}
			$this->layout->body = V('allfiles',array('files' =>$files,'pagination'=>$pa));
			return;
		}
	}
	function apartflist(){
		$form = Form::filter(Input::form());
		$user = L('ME');
		if (!Permission::is_allowed('记录管理',$user->user_type)) {
			URI::redirect('errors/401');
		}
		$logs = Q("files[isrecord=1]");
		$pa=Pagination::pagi($logs,$form['st'],4);
		$loglist = array();			
		foreach ($logs as $key => $value) {
			$one['fileno'] = $value->fileno;
			$one['filename'] = $value->filename;
			$one['mtime'] = date('y-m-d h:i:s',$value->mtime);
			$one['filesize'] = $value->filesize;
			$loglist[] = $one;
		}
		$this->layout->body = V("application:logs", array('loglist'=>$loglist,'pagination'=>$pa));
	}
	function nfsapart(){
		$user = L('ME');
		$form = Form::filter(Input::form());
		if (!Permission::is_allowed('上传记录文件',$user->user_type)) {
			echo V('errors/401');
			return;
		}
		if($form['apartment'])
		{
			try{
				$file = Input::file('file');
				if($file){
					if ($file['name']) {
						$fileformat = File::extension($file['name']);
						$formats = Config::get('file.format');
						if (!in_array($fileformat,$formats)) {
							$form->set_error('fileformat',I18N::T('application','上传失败，文件格式不正确'));
						}
						if ($file['size']>20971522) {
							$form->set_error('fileformat',I18N::T('application','上传失败，文件太大'));
						}
						if (!$form->no_error) {
							throw new Error_Exception;
						}
						$file_name_new=$file['name'];
						$arr = preg_split("/([a-zA-Z0-9-_:]+)/", $file_name_new, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
						$file_no = $arr[0];
						$new_file = O('files',array('fileno'=>$file_no));
						$file_path = SAVE_PATH.'upload/'.$file_name_new;
						if ($new_file->id) {
							$file_path = SAVE_PATH.'upload/'.$new_file->filename;
							if(file_exists($file_path)){
								unlink($file_path);
							}
							$version = $new_file->file_modify+1;
						}else
						{
							unset($new_file);
							$new_file = O('files');
							$version = 0;
						}
						$apartment = Position::positions();
						$pos = array();
						if ($user->user_apartment) {
							$apart = array_flip($apartment[$user->user_apartment]);
							
							foreach ($apart as $key => $value) {
								$pos[] = $value;
							}
						}
						$file_version = Config::get('system.version','B');
						if(move_uploaded_file($file['tmp_name'],$file_path)){
							$new_file->fileno = $file_no;
							$new_file->filename = $file_name_new;
							$new_file->filesize = File::bytes($file['size']);
							$new_file->file_type = implode(',', $pos);
							$new_file->file_modify = $version;
							$new_file->f_version = $file_version;
							$new_file->file_apartment = $user->user_apartment;
							$new_file->isrecord = 1;
							$new_file->save();
							Tip::addMessage(strtr("[上传文件]%user_name上传了%filename",array('%user_name'=>$user->user_name,'%filename'=>$file['name'])),$pos);
							Log::add(strtr("[上传文件]%user_name上传了%filename",array('%user_name'=>$user->user_name,'%filename'=>$file['name'])),$apartment);
							$type = 'nfsapart';
							$this->layout->body=V('application:upload/return',array('type'=>$type));
							return;
						}
					}
				}
			}
			catch(Error_Exception $e){
			}
		}
		$filelist = array();
		$files = Q("files[file_apartment=".$user->user_apartment."][isrecord=1]:sort(filename ASC)");
		foreach ($files as $key => $value) {
			$one['filename'] = $value->filename;
			$one['mtime'] = date('Y-m-d h:i:s',$value->mtime);
			$one['file_version'] = $value->f_version.'版/'.$value->file_modify.'修改';
			$filelist[] = $one;
		}
		$this->layout->body=V('application:upload/nfs_apart',array('filelist'=>$filelist));
	}
	function actionlog(){
		$logs = Q('logs');
		$log_list = array();
		foreach ($logs as $key => $value) {
			$one['id'] = $value->id;
			$one['logname'] = $value->log_name;
			$one['mtime'] = date('Y-m-d h:i:s',$value->mtime);
			$one['log_apart'] = $value->log_apartment;
			$log_list[] = $one;
		}
		$this->layout->body = V('application:actionlog',array('loglist'=>$log_list));
	}
}
/**
* 
*/
class Files_AJAX_Controller extends _AJAX_Controller
{
	function index_upload_file_click(){
		JS::dialog(V('upload/nfs'),array('title'=>'上传文件'));
	}
	function index_nfs_files_submit(){
		$user = L('ME');
		$form = Input::form();
		$file = Input::file();
		$fileformat = File::extension($file['name']);
		$formats = Config::get('file.format');
		
		if (!in_array($fileformat,$formats)) {
			JS::alert(I18N::T('application','上传失败，文件格式不正确'));
			return;
		}
		if ($file['size']>20971522) {
			JS::alert(I18N::T('application','上传失败，文件太大'));
			return;
		}
		$file_name_new=$file['name'];
		$arr = preg_split("/([a-zA-Z0-9-_:]+)/", $file_name_new, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		$file_no = $arr[0];
		$file_path = SAVE_PATH.'upload/'.$file_name_new;
		$is_createID = false;
		if (count($arr)<=2){
			$new_file = O('files',array('filename'=>$file_name_new));
			$is_createID = true;
		}
		else
			$new_file = O('files',array('fileno'=>$file_no));
		if ($new_file->id) {
			$file_path = SAVE_PATH.'upload/'.$new_file->filename;
			if(file_exists($file_path)){
				unlink($file_path);
			}
			$version = $new_file->file_modify+1;
		}else
		{
			unset($new_file);
			$new_file = O('files');
			$version = 0;
		}
		$apartment = Position::positions();
		$positions = array();
		if ($form['checkbox']) {
			$postions = $form['checkbox'];
		}
		$file_version = Config::get('system.version','B');
		if(move_uploaded_file($file['tmp_name'],$file_path)){
			$new_file->fileno = $file_no;
			$new_file->filename = $file_name_new;
			$new_file->filesize = File::bytes($file['size']);
			$new_file->file_type = implode(',', $postions);
			$new_file->file_modify = $version;
			$new_file->f_version = $file_version;
			$new_file->file_apartment = $user->user_apartment;
			$new_file->isrecord = 1;
			$new_file->save();
			Tip::addMessage(strtr("[上传文件]%user_name上传了%filename",array('%user_name'=>$user->user_name,'%filename'=>$file['name'])),$postions);
			Log::add(strtr("[上传文件]%user_name上传了%filename",array('%user_name'=>$user->user_name,'%filename'=>$file['name'])),$apartment);
		}
		JS::refresh();
	}
	function index_upload_box_change()
	{
		$form = Input::form(); 
  		$transaction_type = strtr($form['transaction_type'],array(' '=>''));
  		if ($transaction_type) {
			$arr = preg_split("/([a-zA-Z0-9-_:]+)/", $transaction_type, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);  
	  		$pos_list = Q("files[fileno=".$arr[0]."]");
	  		$view_path = 'applicaiton:upload/exist';
	  		foreach ($pos_list as $key => $value) {
	  			if ($value->id) {
	  				if(!JS::confirm('文件已经存在，继续上传将会覆盖原文件')){
	  					Output::$AJAX['view_data'] ='empty';
	  				}
	  			}
	  		}
	  	}
  	}
  	function index_edits_submit(){
  		$form = Input::form();
  		$pos = array();
  		foreach ($form['Position'] as $key => $value) {
  			if ($value=='on') {
  				$pos[] = $key;
  			}
  		}
  		if ($form['filenos']) {
  			foreach ($form['filenos'] as $key => $value) {
  				$file = O('files',array('fileno'=>$value));
  				if ($file->id) {
  					$file->file_type = implode(",", $pos);
  					$file->file_apartment = $form['file_apart'];
  					$file->f_version = $form['version'];
  					$file->file_modify = $form['modify'];
  					$file->save();
  				}
  			}
  		}
  		JS::refresh();
  	}
  	function index_edit_file_click(){
  		$form = Input::form();

  		JS::dialog(V("application:edit",array('filename'=>$form['filename'])));
  	}
  	function index_files_edit_submit(){
  		$form = Input::form();
  		$filenos = array();
		foreach ($form['fileno'] as $key => $value) {
			if($value =='on')
				$filenos[] = $key;
		}
		if (count($filenos)<=0) {
			JS::alert("请选择你要修改的文件");
			return;
		}
  		JS::dialog(V("upload/batch_edit",array('filenos'=>$filenos)),array('title'=>'批量修改'));
  	}
  	function index_edit_file_submit(){
  		$form = Input::form();
  		$file = O('files',array('filename'=>$form['oldname']));
  		if ($file->id) {
  			$file->filename = $form['filename'];
  			$file->file_modify =$file->file_modify+1;
  			$file->save();
  		}
  		JS::refresh();

  	}
  	function index_delete_file_click(){
  		$form = Input::form();
  		if (JS::confirm(strtr('确定删除%filename',array('%filename'=>$form['filename'])))) {
  			$files = O('files',array('filename'=>$form['filename']));
  			$files->delete();
  			$file_path = SAVE_PATH.'upload/'.$form['filename'];
  			if (File::check_path($file_path)) {
  			 	unlink($file_path);
  			}
  			JS::refresh(); 
  		}
  	}
}