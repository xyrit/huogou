CREATE TABLE `free_current_periods` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '期数ID',
  `table_id` INT(10) UNSIGNED NOT NULL COMMENT '期数参与纪录分表ID',
  `product_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '商品ID',
  `period_number` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '第几期',
  `sales_num` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '参与人次',
  `start_time` INT(10) NOT NULL COMMENT '开始时间',
  `end_time` INT(10) NOT NULL COMMENT '结束时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '当前期数表';