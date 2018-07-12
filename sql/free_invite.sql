CREATE TABLE `free_invite` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `invite_uid` INT(10) UNSIGNED NOT NULL COMMENT '被邀请用户ID',
  `period_id` BIGINT(20) UNSIGNED NOT NULL COMMENT '期数ID',
  `buy_num` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '购码数量',
  `invite_time` INT(10) UNSIGNED NOT NULL COMMENT '邀请时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `invite_uid` (`invite_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;