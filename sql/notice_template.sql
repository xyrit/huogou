CREATE TABLE `notice_template` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `desc` VARCHAR(255) NOT NULL COMMENT '通知描述',
  `notice_way` VARCHAR(255) NOT NULL COMMENT '通知方式',
  `title` VARCHAR(255) NOT NULL COMMENT '通知标题',
  `sms_content` TEXT NOT NULL COMMENT '短信通知内容',
  `email_content` TEXT NOT NULL COMMENT '邮件通知内容',
  `sysmsg_content` TEXT NOT NULL COMMENT '站内信通知内容',
  `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态',
  `op_user` INT(10) UNSIGNED NOT NULL COMMENT '操作人',
  `updated_at` INT(10) UNSIGNED NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='通知模板表';