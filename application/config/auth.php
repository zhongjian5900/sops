<?php

$config['backends']['database'] = array(
	'handler' => 'database',
	'database.table' => '_auth',
	'title' => '本地用户',
);

$config['default_backend'] = 'database';
