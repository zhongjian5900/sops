#!/usr/bin/env php
<?php
    /*
     * file create_orm_tables.php
     * author Rui Ma <rui.ma@geneegroup.com>
     * date  2013/06/24
     *
     * useage SITE_ID=cf LAB_ID=test php create_orm_tables.php
     * brief 对系统中的ORM对象的schema进行遍历，根据schema创建ORM对象在数据库中的表结构
     */

require 'base.php';

foreach(Config::$items['schema'] as $name=>$schema) {
	$db = Database::factory();
	$schema = ORM_Model::schema($name);
	if ($schema) {
		$ret = $db->prepare_table($name, $schema);
        var_dump($ret);
        if (!$ret) {
            echo $name."表更新失败\n";
        }
	}
}
