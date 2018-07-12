CREATE TABLE `card_export` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `batch_id` INT(10) UNSIGNED NOT NULL COMMENT '充值卡批次ID',
  `export_detail` text COMMENT '导出充值卡批次明细',
  `exported_at` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '导出时间',
  `user_export` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '导出管理员ID',
  PRIMARY KEY (`id`),
  KEY `idx_batch_id` (`batch_id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='充值卡导出记录';