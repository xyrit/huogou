CREATE TABLE `payment_order_items_x` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '支付订单明细ID',
  `payment_order_id` CHAR(25)  NOT NULL COMMENT '支付订单号',
  `product_id` INT(10) UNSIGNED NOT NULL COMMENT '商品ID',
  `period_id` BIGINT(20) UNSIGNED NOT NULL COMMENT '期数ID',
  `period_number` INT(10) UNSIGNED NOT NULL COMMENT '第几期',
  `user_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
  `post_nums` INT(10) UNSIGNED NOT NULL COMMENT '提交的购码数量',
  `nums` BIGINT(20) UNSIGNED NOT NULL COMMENT '实际的购码数量',
  `codes` MEDIUMTEXT COMMENT '购买的码',
  `item_buy_time` varchar(16) COMMENT '购买时间',
  `source` TINYINT(1) DEFAULT '0' COMMENT '平台来源, 0=pc,1=触屏版,2=微信客户端,3=ios客户端,4=android客户端',
  PRIMARY KEY (`id`),
  KEY `idx_payment_order_id` (`payment_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '支付订单明细';