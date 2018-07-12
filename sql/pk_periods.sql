CREATE TABLE `pk_periods` (
  `id` BIGINT(20) UNSIGNED NOT NULL COMMENT '期数ID',
  `table_id` INT(10) UNSIGNED NOT NULL COMMENT '期数参与纪录分表ID',
  `product_id` INT(10) UNSIGNED NOT NULL COMMENT '商品ID',
  `period_number` INT(10) UNSIGNED NOT NULL COMMENT '第几期',
  `period_no` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '期号',
  `lucky_code` INT(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '幸运码',
  `price` INT(10) UNSIGNED NOT NULL COMMENT '商品价值',
  `start_time` CHAR(16) NOT NULL COMMENT '开始时间',
  `end_time` CHAR(16) NOT NULL COMMENT '截止时间',
  `size` TINYINT(1) NOT NULL COMMENT '结果大小',
  `match_num` INT(10) NOT NULL COMMENT '匹配数量',
  `exciting_time` CHAR(16) NOT NULL DEFAULT '' COMMENT '开奖时间',
  PRIMARY KEY (`id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT 'pk已满员期数表';