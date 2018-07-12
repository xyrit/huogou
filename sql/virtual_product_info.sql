CREATE TABLE `virtual_product_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `order_id` char(25) NOT NULL COMMENT '中奖订单号',
  `type` varchar(10) NOT NULL COMMENT '充值类型（支付宝:tb QQ:qb 话费:dh）',
  `account` varchar(32) NOT NULL COMMENT '充值账号',
  `name` varchar(32) DEFAULT NULL COMMENT '姓名',
  `created_at` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`),
  KEY `idx_order_id` (`order_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '虚拟物品充值信息';