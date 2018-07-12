CREATE TABLE `user_buylist_x` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` INT(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `product_id` INT(10) UNSIGNED NOT NULL COMMENT '商品ID',
  `period_id` BIGINT(20) UNSIGNED NOT NULL COMMENT '期数ID',
  `buy_num` INT(10) UNSIGNED NOT NULL COMMENT '购码数量',
  `buy_time` CHAR(16) NOT NULL COMMENT '购买时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_id_period_id` (`user_id`, `period_id`),
  KEY `idx_user_id_time` (`user_id`, `buy_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '用户购买纪录表';