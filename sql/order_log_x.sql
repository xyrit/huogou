CREATE TABLE `order_log_x` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `order_id` CHAR(25) NOT NULL COMMENT '订单ID',
  `op_id` INT(10) UNSIGNED DEFAULT NULL COMMENT '操作员ID',
  `op_name` VARCHAR(25) DEFAULT NULL COMMENT '操作人名称',
  `alt_time` INT(10) UNSIGNED DEFAULT NULL COMMENT '操作时间',
  `behavior` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '日志记录操作的行为',
  `result` TINYINT(1) NOT NULL COMMENT '日志结果,0=失败,1=成功',
  `log_text` VARCHAR(255) COMMENT '操作内容',
  `addon` TEXT COMMENT '序列化数据',
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='中奖订单日志表';