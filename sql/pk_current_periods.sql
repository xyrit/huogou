CREATE TABLE `pk_current_periods` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '期数ID',
  `product_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '商品ID',
  `table_id` INT(10) UNSIGNED NOT NULL COMMENT '期数参与纪录分表ID',
  `period_number` INT(10) UNSIGNED NOT NULL COMMENT '第几期',
  `period_no` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '期号',
  `price` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '商品价值',
  `start_time` CHAR(16)  NOT NULL COMMENT '开始时间',
  `end_time` CHAR(16)  NOT NULL COMMENT '截止时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT 'pk当前期数表';