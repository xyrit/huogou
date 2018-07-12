CREATE TABLE `register_log` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户主页',
  `url` VARCHAR(255) DEFAULT '' COMMENT '来源url',
  `name` VARCHAR(100) DEFAULT '' COMMENT '来源网址名称',
  `ip` bigint(10) DEFAULT '0' COMMENT 'ip',
  `source` VARCHAR(50) DEFAULT '' COMMENT '终端类型',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0' ,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='注册日志表';