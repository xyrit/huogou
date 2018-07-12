CREATE TABLE `free_periods` (
  `id` BIGINT(20) UNSIGNED NOT NULL COMMENT '期数ID',
  `table_id` INT(10) UNSIGNED NOT NULL COMMENT '期数参与纪录分表ID',
  `product_id` INT(10) UNSIGNED NOT NULL COMMENT '商品ID',
  `period_number` INT(10) UNSIGNED NOT NULL COMMENT '第几期',
  `lucky_code` INT(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '幸运码',
  `user_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '中奖用户ID',
  `sales_num` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '参与人次',
  `ip` bigint(20) NOT NULL DEFAULT '0' COMMENT 'IP地址',
  `start_time` INT(10) NOT NULL COMMENT '开始时间',
  `end_time` INT(10) NOT NULL COMMENT '结束时间',
  `exciting_time` INT(10) NOT NULL DEFAULT '0' COMMENT '开奖时间',
  PRIMARY KEY (`id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '已结束期数表';