CREATE TABLE `recharge_orders_x` (
  `id` CHAR(25)  NOT NULL COMMENT '充值订单号',
  `user_id` INT(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `status` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '充值状态',
  `type` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '充值类型',
  `post_money` INT(10) UNSIGNED NOT NULL COMMENT '提交的充值金额',
  `money` INT(10) UNSIGNED NOT NULL COMMENT '实际的充值金额',
  `payment` TINYINT(1) NOT NULL COMMENT '支付方式',
  `bank` VARCHAR(30) NOT NULL DEFAULT '' COMMENT '银行',
  `point` int(10) NOT NULL DEFAULT '0' COMMENT '积分',
  `source` int(1) NOT NULL DEFAULT '' COMMENT '来源',
  `result` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '第三方支付返回值',
  `create_time` INT(10) UNSIGNED NOT NULL COMMENT '创建时间',
  `pay_time` CHAR(16) NOT NULL COMMENT '充值时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id_create_time` (`user_id`, `create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '充值订单';
ALTER TABLE `recharge_orders_100` ADD `ip` INT(10)  NULL  DEFAULT NULL  AFTER `pay_time`;