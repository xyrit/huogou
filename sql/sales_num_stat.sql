CREATE TABLE `sales_num_stat` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `day` VARCHAR(32) NOT NULL COMMENT '天',
  `hour` VARCHAR(32) NOT NULL COMMENT '小时',
  `result` INT(10) NOT NULL COMMENT '结果',
  `created_at` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_day_hour` (`day`, `hour`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='参与人次统计报表';