CREATE TABLE `user_invite` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT
  `user_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '第一人称用户ID',
  `invite_userid` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '被邀请的用户ID',
  `invite_no` VARCHAR (25) UNSIGNED NOT NULL DEFAULT '0' COMMENT '邀请编号',
  `status` TINYINT(1) UNSIGNED NOT NULL COMMENT '消费状态',
  `create_time` INT(10) UNSIGNED NOT NULL,
  KEY `idx_userid` (`userid`),
  UNIQUE KEY `idx_invite_userid` (`invite_userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户邀请表';