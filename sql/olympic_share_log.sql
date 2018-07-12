CREATE TABLE `olympic_share_log` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` INT(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `type` TINYINT(1) NOT NULL COMMENT '分享类型',
  `created_at` INT(10) NOT NULL COMMENT '分享时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '奥运会用户分享纪录表';