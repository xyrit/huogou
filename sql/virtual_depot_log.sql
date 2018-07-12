CREATE TABLE `virtual_depot_log` (
   `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '京东卡领取记录',
   `card_id` int(10) NOT NULL DEFAULT '0' COMMENT '对应卡id',
   `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '领取人',
   `phone` varchar(15) NOT NULL DEFAULT '' COMMENT '领取手机',
   `c_time` int(10) NOT NULL DEFAULT '0' COMMENT '领取时间',
   `admin_id` int(10) NOT NULL DEFAULT '0' COMMENT '发货人',
   PRIMARY KEY (`id`)
 ) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4