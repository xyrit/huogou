CREATE TABLE `pk_payment_order_items_x` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '支付订单明细ID',
  `payment_order_id` char(25) NOT NULL COMMENT '支付订单号',
  `product_id` int(10) unsigned NOT NULL COMMENT '商品ID',
  `period_id` bigint(20) unsigned NOT NULL COMMENT '期数ID',
  `period_no` char(25) NOT NULL COMMENT '期号',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `post_nums` int(10) unsigned NOT NULL COMMENT '提交的参与次数',
  `nums` bigint(20) unsigned NOT NULL COMMENT '实际的参与次数',
  `buy_tables` mediumtext COMMENT '购买的桌号',
  `buy_size` tinyint(1) DEFAULT NULL COMMENT '购买的大小',
  `item_buy_time` varchar(16) DEFAULT NULL COMMENT '购买时间',
  `source` tinyint(1) DEFAULT '0' COMMENT '平台来源, 0=pc,1=触屏版,2=微信客户端,3=ios客户端,4=android客户端',
  PRIMARY KEY (`id`),
  KEY `idx_payment_order_id` (`payment_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='PK支付订单明细';