<?php

$config['user'] = array(
	'fields' => array(
		'user_name'=>array('type'=>'varchar(45)'),
		'user_position'=>array('type'=>'varchar(45)','null'=>FALSE),
		'user_apartment'=>array('type'=>'varchar(45)', 'null'=>FALSE, 'default'=>''),
		'token'=>array('type'=>'varchar(45)', 'null'=>FALSE, 'default'=>''),
		'user_type'=>array('type'=>'varchar(45)', 'null'=>FALSE),
		'user_perms'=>array('type'=>'varchar(45)','null'=>FALSE,'default'=>'2')
	),
	'indexes' => array(
		'user_name'=>array('fields'=>array('user_name')),
		'user_position'=>array('fields'=>array('user_position')),
		'user_apartment'=>array('fields'=>array('user_apartment')),
		'token'=>array('fields'=>array('token')),
		'user_type'=>array('fields'=>array('user_type'))
	),
);
$config['files'] = array(
	'fields' => array(
		'fileno'=>array('type'=>'varchar(45)'),
		'filename'=>array('type'=>'varchar(45)'),
		'filesize'=>array('type'=>'varchar(120)','null'=>FALSE),
		'ctime'=>array('type'=>'int(22)','null'=>FALSE),
		'mtime'=>array('type'=>'int(22)','null'=>FALSE),
		'file_type'=>array('type'=>'varchar(120)', 'null'=>FALSE, 'default'=>''),
		'file_modify'=>array('type' =>'int(11)','null'=>FALSE),
		'f_version'=>array('type'=>'varchar(45)','null'=>FALSE),
		'isrecord'=>array('type'=>'tinyint(1)','null'=>FALSE,'default'=>0),
		'file_apartment'=>array('type'=>'varchar(45)','null'=>FALSE)
	),
	'indexes' => array(
		'fileno'=>array('fields'=>'fileno'),
		'filename'=>array('fields'=>array('filename')),
		'file_type'=>array('fields'=>array('file_type')),
		'file_apartment'=>array('fields'=>array('file_apartment'))
	),
);
$config['logs'] = array(
	'fields' => array(
		'log_name'=>array('type'=>'varchar(45)'),
		'ctime'=>array('type'=>'int(22)','null'=>FALSE),
		'mtime'=>array('type'=>'int(22)','null'=>FALSE),
		'log_apartment'=>array('type' =>'varchar(45)')
	),
	'indexes' => array(
		'log_name'=>array('fields'=>array('log_name')),
		'log_apartment'=>array('fields'=>array('log_apartment'))
	),
);
$config['message'] = array(
	'fields' => array(
		'user_id'=>array('type'=>'int(45)','null'=>FALSE),
		'ctime'=>array('type'=>'int(22)','null'=>FALSE),
		'content'=>array('type' =>'varchar(45)'),
		'isread'=>array('type'=>'tinyint(1)','null'=>FALSE,'default'=>0)
	),
	'indexes' => array(
		'user_id'=>array('fields'=>array('user_id')),
		'isread'=>array('fields'=>array('isread'))
	),
);
$config['position'] = array(
	'fields' => array(
		'position_no'=>array('type'=>'varchar(45)','null'=>FALSE),
		'position_name'=>array('type'=>'varchar(45)','null'=>FALSE),
		'position_apart'=>array('type' =>'varchar(45)','null'=>FALSE),
		'position_perms'=>array('type'=>'varchar(45)','null'=>FALSE,'default'=>'2')
	),
	'indexes' => array(
		'position_no'=>array('fields'=>array('position_no')),
		'position_name'=>array('fields'=>array('position_name')),
		'position_apart'=>array('fields'=>array('position_apart'))
	),
);
$config['apartment'] = array(
	'fields' => array(
		'apartment_no'=>array('type'=>'varchar(45)','null'=>FALSE),
		'apartment_name'=>array('type'=>'varchar(45)','null'=>FALSE),
	),
	'indexes' => array(
		'apartment_no'=>array('fields'=>array('apartment_no')),
		'apartment_name'=>array('fields'=>array('apartment_name')),
	)
);
$config['permission'] = array(
	'fields'=>array(
		'perms_name'=>array('type'=>'varchar(45)','null'=>FALSE),
		'perms_func'=>array('type'=>'varchar(45)','null'=>FALSE)
		),
	'indexes'=>array(
		'perms_name'=>array('fields'=>array('perms_name'))
		)
	);
$config['id'] = array(
	'fields'=>array(
		'id_feed'=>array('type'=>'int(11)','null'=>FALSE),
		),
	'indexes'=>array(
		'id_feed'=>array('fields'=>array('id_feed'))
		)
	);