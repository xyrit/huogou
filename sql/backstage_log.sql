CREATE TABLE `backstage_log_x` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `admin_id` int(10) unsigned NOT NULL COMMENT '操作人id',
  `module` tinyint(1) NOT NULL COMMENT '操作模块',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '内容',
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_admin_id` (`admin_id`,`created_at`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=375 DEFAULT CHARSET=utf8 COMMENT='后台操作日志';