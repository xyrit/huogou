CREATE TABLE `user_virtual` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) DEFAULT NULL COMMENT '用户id',
  `type` varchar(10) DEFAULT NULL COMMENT '卡类型',
  `orderid` int(10) DEFAULT NULL COMMENT '获奖订单id',
  `par_value` int(5) DEFAULT NULL COMMENT '面值',
  `card` varchar(30) DEFAULT NULL COMMENT '卡号',
  `pwd` varchar(30) DEFAULT NULL COMMENT '密码',
  `create_time` int(10) DEFAULT NULL COMMENT '获取时间',
  `status` int(1) DEFAULT '0' COMMENT '是否查看卡密',
  `update_time` int(10) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

ALTER TABLE `user_virtual` CHANGE `par_value` `par_value` INT(5)  NULL  DEFAULT NULL  COMMENT '面值';
ALTER TABLE `user_virtual` ADD `status` INT(1)  NULL  DEFAULT NULL  COMMENT '是否查看卡密'  AFTER `create_time`;
ALTER TABLE `user_virtual` ADD `update_time` INT(10)  NULL  DEFAULT NULL  COMMENT '更新时间'  AFTER `status`;
ALTER TABLE `user_virtual` CHANGE `status` `status` INT(1)  NULL  DEFAULT '0'  COMMENT '是否查看卡密';
ALTER TABLE `user_virtual` CHANGE `update_time` `update_time` INT(10)  NULL  DEFAULT '0'  COMMENT '更新时间';
ALTER TABLE `user_virtual` CHANGE `update_time` `update_time` INT(10)  NULL  DEFAULT '0'  COMMENT '更新时间';
ALTER TABLE `user_virtual` CHANGE `type` `type` VARCHAR(10)  CHARACTER SET utf8  COLLATE utf8_general_ci  NULL  DEFAULT NULL  COMMENT '卡类型';