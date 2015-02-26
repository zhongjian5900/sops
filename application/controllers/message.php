<?php 
/**
* 消息处理
*/
class Message_Controller extends Layout_Controller
{
	
	function index()
	{
	}
	function msglist()
	{
		$user = L('ME');
		$userid = $user->id;
		$form = Form::filter(Input::form());
		$messages = Q("message[user_id=$userid]:sort(isread ASC ctime DESC)");
		$pa=Pagination::pagi($messages,$form['st'],10);
		$message = array();
		if ($messages) {
			foreach ($messages as $key => $value) {
				$one['content'] = $value->content;
				$one['time'] = date('Y-m-d H:i:s', $value->ctime);
				$one['id'] = $value->id;
				$one['isread'] = $value->isread;
				$message[]= $one;
			}
			
		}
		$this->layout->body=V('message',array('message'=>$message,'pagination'=>$pa));

	}
	function onemsg($id){
		$msg = O('message',array('id'=>$id));
		if ($msg->id) {
			$one['ctime'] = $msg->ctime;
			$one['content'] = $msg->content;
			$msg->isread = 1;
			$msg->save();
			$this->layout->body=V('application:onemsg',array('onemsg'=>$one));
		}
	}
}