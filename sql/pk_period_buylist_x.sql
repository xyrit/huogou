CREATE TABLE `pk_period_buylist_x` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `product_id` int(10) unsigned NOT NULL COMMENT '商品ID',
  `period_id` bigint(20) unsigned NOT NULL COMMENT '期数ID',
  `user_id` int(10) unsigned NOT NULL COMMENT '用户ID',
  `buy_size` tinyint(1) DEFAULT NULL COMMENT '购买的大小',
  `buy_table` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '购买当期桌号',
  `ip` int(10) unsigned NOT NULL COMMENT 'IP地址',
  `source` tinyint(1) DEFAULT '0' COMMENT '平台来源, 0=pc,1=触屏版,2=微信客户端,3=ios客户端,4=android客户端',
  `buy_time` char(16) NOT NULL COMMENT '付款时间',
  PRIMARY KEY (`id`),
  KEY `idx_period_id_user_id` (`period_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='期数参与纪录表';