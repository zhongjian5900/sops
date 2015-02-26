<?php

// $config['session_handler'] = 'database';
$config['timezone'] = 'Asia/shanghai';
$path_prefix = preg_replace('/([^\/])$/', '$1/', dirname($_SERVER['SCRIPT_NAME']));

$config['base_url'] = 'http://'.$_SERVER['HTTP_HOST'].$path_prefix;
$config['script_url'] = 'http://'.$_SERVER['HTTP_HOST'].$path_prefix;

$config['default_id_feed'] = '10000000';
$config['session_handler'] = 'database';
$config['password'] = '83719730';