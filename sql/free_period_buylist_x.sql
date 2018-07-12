CREATE TABLE `free_period_buylist_x` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `product_id` INT(10) UNSIGNED NOT NULL COMMENT '商品ID',
  `period_id` BIGINT(20) UNSIGNED NOT NULL COMMENT '期数ID',
  `user_id` INT(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `code` VARCHAR (32) NOT NULL COMMENT '伙购码',
  `ip` INT(10) UNSIGNED NOT NULL COMMENT 'IP地址',
  `source` TINYINT(1) DEFAULT '0' COMMENT '平台来源, 0=pc,1=触屏版,2=微信客户端,3=ios客户端,4=android客户端',
  `pay_type` TINYINT(1) DEFAULT '0' COMMENT '购买类型,赠送,分享,分享猪儿',
  `pay_bank` TINYINT(1) DEFAULT '0' COMMENT '购买方式',
  `buy_time` CHAR(16) NOT NULL COMMENT '购买时间',
  PRIMARY KEY (`id`),
  KEY `idx_period_id` (`period_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '期数参与纪录表';