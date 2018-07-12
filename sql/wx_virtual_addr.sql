CREATE TABLE `wx_virtual_addr` (
   `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '微信地址信息',
   `virtual_addr_id` int(10) NOT NULL COMMENT '关联的地址',
   `nickname` varchar(60) NOT NULL DEFAULT '' COMMENT '微信昵称',
   `headimg` varchar(100) NOT NULL DEFAULT '' COMMENT '微信头像',
   `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
   PRIMARY KEY (`id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信地址信息关联';