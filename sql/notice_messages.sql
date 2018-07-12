CREATE TABLE `notice_messages` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` VARCHAR(100)  NOT NULL COMMENT '用户',
  `mode` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '发送方式，0短信，1邮件，2站内信',
  `type_name` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '类型名',
  `message` TEXT  COMMENT '内容',
  `ip` bigint(10) DEFAULT '0' COMMENT 'ip',
  `status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0成功，1失败',
  `created_at` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `mode` (`mode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='通知发送表';