CREATE TABLE `pk_user_buylist_x` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int(10) unsigned NOT NULL COMMENT '用户ID',
  `product_id` int(10) unsigned NOT NULL COMMENT '商品ID',
  `period_id` bigint(20) unsigned NOT NULL COMMENT '期数ID',
  `buy_size` tinyint(1) DEFAULT NULL COMMENT '购买的大小',
  `buy_table` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '购买当期桌号',
  `buy_time` char(16) NOT NULL COMMENT '购买时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id_period_id` (`user_id`,`period_id`),
  KEY `idx_user_id_time` (`user_id`,`buy_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户购买纪录表';