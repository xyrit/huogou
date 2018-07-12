CREATE TABLE `payment_orders_x` (
  `id` CHAR(25) NOT NULL COMMENT '支付订单号',
  `user_id` INT(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `status` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '支付状态',
  `payment` TINYINT(1) NOT NULL COMMENT '支付方式',
  `bank` VARCHAR(30) NOT NULL DEFAULT '' COMMENT '银行',
  `money` INT(10) UNSIGNED NOT NULL COMMENT '消费金额',
  `point` INT(10) UNSIGNED NOT NULL COMMENT '消费积分',
  `total` INT(10) UNSIGNED NOT NULL COMMENT '订单金额',
  `user_point` INT(10) UNSIGNED NOT NULL COMMENT '用户提交的积分',
  `ip` INT(10) UNSIGNED NOT NULL COMMENT 'IP地址',
  `source` TINYINT(1) DEFAULT '0' COMMENT '平台来源, 0=pc,1=触屏版,2=微信客户端,3=ios客户端,4=android客户端',
  `create_time` CHAR(16) DEFAULT NULL COMMENT '订单创建时间',
  `buy_time` CHAR(16) DEFAULT NULL COMMENT '订单付款时间',
  `recharge_orderid` CHAR(25) DEFAULT NULL COMMENT '充值订单id',
  PRIMARY KEY (`id`),
  KEY `idx_user_id_create_time` (`user_id`, `create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '支付订单';
ALTER TABLE `payment_orders_100` ADD `deduction1` INT(5)  NULL  DEFAULT '0'  COMMENT '优惠券抵扣额度'  AFTER `spread_source`;
ALTER TABLE `payment_orders_100` ADD `coupon1` CHAR(10)  NULL  DEFAULT '0'  COMMENT '优惠券码-抵扣券'  AFTER `deduction1`;
ALTER TABLE `payment_orders_100` ADD `deduction2` INT(5)  NULL  DEFAULT '0'  COMMENT '优惠券抵扣额度'  AFTER `coupon1`;
ALTER TABLE `payment_orders_100` ADD `coupon2` CHAR(10)  NULL  DEFAULT '0'  COMMENT '优惠券码-折扣券'  AFTER `deduction2`;
ALTER TABLE `payment_orders_100` ADD `pay_for` TINYINT(1) DEFAULT '0'  COMMENT '支付商品类型'  AFTER `recharge_orderid`;
