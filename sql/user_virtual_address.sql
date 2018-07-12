CREATE TABLE `user_virtual_address` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户ID,1:手机,2:QQ,3:支付宝',
  `type` VARCHAR (10) NOT NULL COMMENT '充值类型（支付宝:tb QQ:qb 话费:dh）',
  `account` varchar(64) NOT NULL DEFAULT '' COMMENT '账号',
  `name` varchar(64) NOT NULL DEFAULT '' COMMENT '姓名',
  `created_at` int(10) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `updated_at` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='虚拟物品收货地址表';