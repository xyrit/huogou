CREATE TABLE `periods` (
  `id` BIGINT(20) UNSIGNED NOT NULL COMMENT '期数ID',
  `table_id` INT(10) UNSIGNED NOT NULL COMMENT '期数参与纪录分表ID',
  `product_id` INT(10) UNSIGNED NOT NULL COMMENT '商品ID',
  `limit_num` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '限购数量',
  `cat_id` INT(10) UNSIGNED NOT NULL COMMENT '分类ID',
  `period_number` INT(10) UNSIGNED NOT NULL COMMENT '第几期',
  `lucky_code` INT(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '幸运码',
  `user_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '中奖用户ID',
  `price` INT(10) UNSIGNED NOT NULL COMMENT '商品价值',
  `start_time` CHAR(16) NOT NULL COMMENT '开始时间',
  `end_time` CHAR(16) NOT NULL COMMENT '满员时间',
  `exciting_time` CHAR(16) NOT NULL DEFAULT '' COMMENT '开奖时间',
  PRIMARY KEY (`id`),
  KEY `idx_uid_cid_end_time` (`user_id`, `cat_id`, `end_time`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '已满员期数表';