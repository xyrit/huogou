CREATE TABLE `jdcard_buyback_mobile` (
   `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '京东卡回购手机列表',
   `mobile` varchar(13) NOT NULL DEFAULT '0' COMMENT '手机号',
   `admin_id` int(10) NOT NULL DEFAULT '0' COMMENT '操作人',
   `add_time` int(10) NOT NULL DEFAULT '0' COMMENT '添加时间',
   PRIMARY KEY (`id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8