CREATE TABLE `period_buylist_x` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `product_id` INT(10) UNSIGNED NOT NULL COMMENT '商品ID',
  `period_id` BIGINT(20) UNSIGNED NOT NULL COMMENT '期数ID',
  `user_id` INT(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `buy_num` BIGINT(20) UNSIGNED NOT NULL COMMENT '购码数量',
  `codes` MEDIUMTEXT COMMENT '购买的所有码',
  `ip` INT(10) UNSIGNED NOT NULL COMMENT 'IP地址',
  `source` TINYINT(1) DEFAULT '0' COMMENT '平台来源, 0=pc,1=触屏版,2=微信客户端,3=ios客户端,4=android客户端',
  `buy_time` CHAR(16) NOT NULL COMMENT '付款时间',
  PRIMARY KEY (`id`),
  KEY `idx_period_id_user_id` (`period_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '期数参与纪录表';