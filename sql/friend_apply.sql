CREATE TABLE `friend_apply` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` INT(10) UNSIGNED NOT NULL COMMENT '发起请求用户ID',
  `apply_userid` INT(10) UNSIGNED NOT NULL COMMENT '对方ID',
  `status` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `apply_time` INT(10) UNSIGNED NOT NULL COMMENT '请求时间',
  PRIMARY KEY (`id`),
  KEY `idx_apply_userid_status_time` (`apply_userid`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='好友表';